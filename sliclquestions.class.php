<?php
/*
 * Copyright (C) 2015 Welch IT Consulting
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Filename : questionnaire
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 23 Jun 2015
 */

class sliclquestions
{
    public function __construct(&$course, &$cm, $id = 0, &$sliclquestions = null, $addquestions = null)
    {
        global $DB;

        if ($id) {
            $sliclquestions = $DB->get_record('sliclquestions', array('id' => $id));
        }
        if (is_object($sliclquestions)) {
            $properties = get_object_vars($sliclquestions);
            foreach ($properties as $prop => $val) {
                $this->$prop = $val;
            }
        }
        $this->course = $course;
        $this->cm     = $cm;
        if (!empty($cm) && !empty($this->id)) {
            $this->context = context_module::instance($cm->id);
        } else {
            $this->context = null;
        }
        if ($addquestions) {
            $this->add_questions($this->id);
        }
        if (!empty($this->cm->id)) {
            $this->capabilities = $this->load_capabilities($this->cm->id);
        }
    }

    public function add_questions($id)
    {
        global $DB;

        if (!isset($this->questions)) {
            $this->questions = array();
            $this->questionsbysec = array();
        }
        $select = 'survey_id = ' . $id . ' AND deleted != \'y\'';
        if ($records = $DB->get_records_select('sliclquestions_question', $select, null, 'position')) {
            $sec = 1;
            $isbreak = false;
            foreach($records as $record) {
                $this->questions[$record->id] = new sliclquestions_question(0, $record, $this->context);
                if ($record->type_id == SLICLQUESPAGEBREAK) {
                    $this->questionsbysec[$sec][$record->id] = &$this->questions[$record->id];
                    $isbreak = false;
                } else {
                    if (($record->position != 1) && ($isbreak == false)) {
                        $sec++;
                        $isbreak = true;
                    }
                }
            }
        }
    }

    public function view()
    {
        global $CFG, $USER, $PAGE, $OUTPUT;

        $PAGE->set_title(format_string($this->name));
        $PAGE->set_heading(format_string($this->course->fullname));

        // Initialise the Javascript
        $PAGE->requires->js_init_call('M.mod_sliclquestions.init_attempt_form',
                                      null, false, array('name'     => 'mod_sliclquestions',
                                                         'fullpath' => '/mod/sliclquestions/javascript/module.js',
                                                         'requires' => array('base',
                                                                             'dom',
                                                                             'event-delegate',
                                                                             'event-key',
                                                                             'core_question_engine',
                                                                             'moodle-core-formchangechecker'),
                                                         'strings'  => array(array('cancel', 'moodle'),
                                                                             array('flagged', 'question'),
                                                                             array('functiondisabledbysecuremode', 'quiz'),
                                                                             array('startattempt', 'quiz'),
                                                                             array('timesup', 'quiz'),
                                                                             array('changesmadereallygoaway', 'moodle'))));
        echo $OUTPUT->header();
        if (!$this->cm->visible && !$this->capabilites->viewhiddenactivities) {
            notice(get_string('activityiscurrentlyhidden'));
        }
        if (!$this->capabilities->view) {
            $OUTPUT->notification(get_string('noteligible', 'sliclquestions', $this->name));//, 'notifyproblem');
            echo html_writer::link(new moodle_url('/course/view.php',
                                                  array('id' => $this->course->id)),
                                   get_string('continue'));
            exit;
        }
        if (!$this->is_open()) {
            echo html_writer::div(get_text('notopen', 'sliclquestions'), 'notifyproblem');
        } elseif ($this->is_closed ()) {
            echo html_writer::div(get_text('closed', 'sliclquestions'), 'notifyproblem');
        } elseif (!$this->user_is_eligible($USER->id)) {
            echo html_writer::div(get_string('noteligible', 'sliclquestions'), 'notifyproblem');
        } elseif ($this->user_can_take($USER->id)) {
            $msg = $this->print_survey($USER->id);
            $viewform = data_submitted($CFG->wwwroot . '/mod/sliclquestions/complete.php');
            if (!empty($viewform->rid)) {
                $viewform->rid = (int)$viewform->rid;
            }
            if (!empty($viewform->sec)) {
                $viewform->sec = (int)$viewform->sec;
            }
            if (data_submitted() && confirm_sesskey() && isset($viewform->submit) &&
                    isset($viewform->submittype) && ($viewform->submittype == 'Submit survey') && empty($msg)) {
                $this->response_delete($viewform->rid, $viewform->sec);
                $this->rid = $this->response_insert($this->id, $viewform->sec, $viewform->rid, $USER->id);
                $this->response_commit($viewform->rid);
                if (!empty($viewform->rid) && is_numeric($viewform->rid)) {
                    $rid = $viewform->rid;
                } else {
                    $rid = $this->rid;
                }
                $DB->insert_record('sliclquestions_attempts',
                                   (object)array('qid'          => $this->id,
                                                 'userid'       => $USER->id,
                                                 'rid'          => $rid,
                                                 'timemodified' => time()),
                                   false);
                $context = context_module::instance($this->cm->id);
                $params = array('context'       => $context,
                                'courseid'      => $this->course->id,
                                'relateduserid' => $USER->id,
                                'anonymous'     => false,
                                'other'         => array('sliclquestionsid' => $this->id));
                $event = \mod_sliclquestions\event\attempt_submitted::create($params);
                $event->trigger();
                $this->response_send_email($this->rid);
                $this->response_goto_thankyou();
            }
        } else {
            echo html_writer::div(get_string('alreadyfilled', 'sliclquestions', ''), 'notifyproblem');
        }
        echo $OUTPUT->footer($this->course);
    }

    public function view_response($rid, $referer = '', $blankquestionnaire = false, $resps = '', $compare = false, $isgroupmember = false, $allresponses = false, $currentgroupid = 0)
    {

    }

    public function view_all_responses($resps)
    {

    }

    public function is_open()
    {
        return (($this->opendate > 0) ? ($this->opendate < time()) : true);
    }

    public function is_closed()
    {
        return (($this->closedate > 0) ? ($this->closedate < time()) : false);
    }

    public function user_can_take($userid)
    {
        if (!$this->user_is_eligible($userid)) {
            return false;
        } elseif ($this->qtype == SLICLQUESTIONNAIREUNLIMITED) {
            return true;
        } elseif ($userid > 0) {
            return true;
        }
        return false;
    }

    public function user_is_eligible($userid)
    {
        return ($this->capabilities->view && $this->capabilities->submit);
    }

    public function user_time_for_new_attempt($userid)
    {
        global $DB;
    }

    public function is_survey_owner()
    {
        return (!empty($this->survey->owner) && ($this->course->id == $this->survey->owner));
    }

    public function can_view_response($rid)
    {

    }

    public function count_submissions($userid = false)
    {

    }

    public function print_survey($userid = false, $quser)
    {

    }

    public function survey_print_render($courseid, $message = '', $referer = '', $rid = 0, $blankquestionnaire = false)
    {
        global $CFG, $DB, $OUTPUT, $USER;

        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            print_error('incorrectcourseid', 'sliclquestions');
        }
        $this->course = $course;
        if (!empty($rid)) {
            $this->view_response($rid, $referer, $blankquestionnaire);
            return;
        }
        if (empty($section)) {
            $section = 1;
        }
        if (isset($this->questionsbysec)) {
            $numsections = count($this->questionsbysec);
        } else {
            $numsections = 0;
        }
        if ($section > $numsections) {
            return false;
        }
        $hasrequired = $this->has_required();
        $i = 1;
        for ($j = 2; $j <= $section; $j++) {
            $i += count($this->questionsbysec[$j - 1]);
        }
        echo html_writer::start_tag('form', array('id'     => 'phpesp_response',
                                                  'method' => 'post',
                                                  'action' => $CFG->wwwroot
                                                            . '/mod/sliclquestions/preview.php?id='
                                                            . $this->cm->id));
        $formdata = new stdClass();
        $errors = 1;
        if (data_submitted()) {
            $formdata = data_submitted();
            $pageerror = '';
            $s = 1;
            $errors = 0;
            foreach($this->questionsbysec as $section) {
                $errormsg = $this->response_check_format($s, $formdata);
                if ($errormsg) {
                    if ($numsections > 1) {
                        $pageerror = get_string('page', 'sliclquestions') . ' ' . $s . ' : ';
                    }
                    echo '<div class="notifyproblem">'
                       . $pageerror
                       . '</div>';
                }
                $s++;
            }
        }
        echo $OUTPUT->box_start();
        $this->print_survey_start($message, $section = 1, 1, $hasrequired, $rid = '');
        $descendantsandchoices = array();
        if (($referer == 'preview') && sliclquestions_hasd_dependencies($this->questions)) {
            $descendantsandchoices = sliclquestions_get_descendants_and_choices($this->questions);
        }
        if ($errors == 0) {
            echo html_writer::div(get_string('submitpreviewcorrect', 'sliclquestions'),
                                  array('class' => 'message'));
        }
        $page = 1;
        foreach($this->questionsbysec as $section) {
            if ($numsections > 1) {
                echo html_writer::div(get_string('page', 'sliclquestions') . ' ' . $page,
                                      array('class' => 'surveypage'));
                $page++;
            }
            foreach($section as $question) {
                $descendantsdata = array();
                if ($question->type_id == SLICLQUESSECTIONTEXT) {
                    $i--;
                }
                if (($referer == 'preview') && $descendantsandchoices &&
                        (($question->type_id == SLICLQUESYESNO) || ($question->type_id == SLICLQUESRADIO) || ($question->type_id == SLICLQUESDROP))) {
                    if (isset($descendantsandchoices['descendants'][$question->id])) {
                        $descendantsdata['descendants'] = $descendantsandchoices['descendants'][$question->id];
                        $descendantsdata['choices']     = $descendantsandchoices['choices'][$question->id];
                    }
                }
                $question->survey_display($formdata, $descendantsdata, $i++, $usehtmleditor = null, $blankquestionnaire, $referer);
            }
        }
        if (($referer == 'preview') && !$blankquestionnaire) {
            echo html_writer::start_div()
               . html_writer::empty_tag('input', array('type'  => 'submit',
                                                       'name'  => 'submit',
                                                       'value' => get_string('submitpreview', 'sliclquestions')))
               . html_writer::link('/mod/sliclquestions/preview.php?id' . $this->cm->id, get_string('reset'))
               . html_writer::end_div();
        }
    }

    public function survey_update($data)
    {

    }

    public function survey_copy($owner)
    {

    }

    public function type_has_choices()
    {
        global $DB;
        $haschoices = array();
        if ($record = $DB->get_records('sliclquestions_question_type', array(), 'typeid', 'typeid, has_choices')) {
            foreach($records as $record) {
                $haschoices[$typeid] = (($record->has_choices == 'y') ? 1 : 0);
            }
        }
        return $haschoices;
    }

    public function survey_results_navbar_alpha($currid, $currentgroupid, $cm, $byresponse)
    {

    }

    public function survey_results_navbar_student($currid, $userid, $instance, $resps, $reporttype = 'myreport', $sid = '')
    {

    }

    public function survey_results($precision = 1, $showtotals = 1, $qid = '', $cids = '', $rid = '', $uid = false, $currentgroupid = '', $sort = '')
    {

    }

    public function generate_csv($rid = '', $userid = '', $choicecodes = 1, $choicetext = 0, $currentgroupid = '')
    {

    }

    public function move_question($moveqid, $movetopos)
    {

    }

    public function response_analysis($rid, $resps, $compare, $isgroupmember, $allresponses, $currentgroupid)
    {

    }


    /**
     * Private Functions
     */

    /**
     *
     * @param type $section
     */
    private function has_required($section = 0)
    {

    }

    private function load_capabilities($cmid)
    {
        static $cb;
        if (isset($cb)) {
            return $cb;
        }
        if (!$ctx = context_module::instance($cmid)) {
            print_error('badcontext');
        }

        $cb = new stdClass();
        $cb->addinstance            = has_capability('mod/sliclquestions:addinstance', $ctx);
        $cb->assesspupils           = has_capability('mod/sliclquestions:assesspupils', $ctx);
        $cb->editquestions          = has_capability('mod/sliclquestions:editquestions', $ctx);
        $cb->manage                 = has_capability('mod/sliclquestions:manage', $ctx);
        $cb->preview                = has_capability('mod/sliclquestions:preview', $ctx);
        $cb->printblank             = has_capability('mod/sliclquestions:printblank', $ctx);
        $cb->submit                 = has_capability('mod/sliclquestions:submit', $ctx);
        $cb->view                   = has_capability('mod/sliclquestions:view', $ctx);
        $cb->viewstatistics         = has_capability('mod/sliclquestions:viewstatistics', $ctx);
//        $cb->downloadresponses      = has_capability('mod/sliclquestions:downloadresponses', $ctx);
//        $cb->deleteresponses        = has_capability('mod/sliclquestions:deleteresponses', $ctx);
//        $cb->createtemplate         = has_capability('mod/sliclquestions:createtemplates', $ctx);
//        $cb->createpublic           = has_capability('mod/sliclquestions:createpublic', $ctx);
//        $cb->readownresponses       = has_capability('mod/sliclquestions:readownresponses', $ctx);
//        $cb->readallresponses       = has_capability('mod/sliclquestions:readallresponses', $ctx);
//        $cb->readallresponseanytime = has_capability('mod/sliclquestions:readallresponseanytime', $ctx);
//        $cb->message                = has_capability('mod/sliclquestions:message', $ctx);

        $cb->viewhiddenactivities   = has_capability('moodle/course:viewhiddenactivities', $ctx);

        return $cb;
    }

    private function survey_render(&$formdata, $section = 1, $message = '')
    {

    }

    private function print_survey_start($message, $section, $numsections, $hasrequired, $rid = '', $blankquestionnaire = false)
    {
        global $CFG, $DB, $OUTPUT;
        require_once($CFG->libdir . '/filelib.php');
        $userid = '';
        $resp = '';
        $groupname = '';
        $currentgroupid = 0;
        $timesubmitted = '';
        if ($rid) {
            $courseid = $this->course->id;
            if ($resp = $db->get_record('sliclquestions_response', array('id' => $rid))) {
                $userid = $resp->userid;
                if ($this->cm->groupmode > 0) {
                    if ($groups = groups_get_all_groups($courseid, $resp->id)) {
                        if (count($groups) == 1) {
                            $group = current($groups);
                            $currentgroupid = $group->id;
                            $groupname = ' (' . get_string('group') . ': ' . $group->name . ')';
                        } else {
                            $groupname = ' (' . get_string('groups') . ': ';
                            foreach ($groups as $group) {
                                $groupname .= $group->name . ', ';
                            }
                            $groupname = substr($groupname, 0, strlen($groupname) - 2) . ')';
                        }
                    } else {
                        $groupname = ' (' . get_string('groupnonmembers') . ')';
                    }
                }
                $event = \mod_sliclquestions\event\response_viewed::create(array('objectid'      => $this->id,
                                                                                 'context'       => $this->context,
                                                                                 'courseid'      => $this->course->id,
                                                                                 'relateduserid' => $userid,
                                                                                 'other'         => array('action'         => 'vresp',
                                                                                                          'currentgroupid' => $currentgroupid,
                                                                                                          'rid'            => $rid)));
                $event->trigger();
            }
        }
        $ruser = '';
        if ($resp && !$blankquestionnaire) {
            if ($userid) {
                if ($user = $DB->get_record('user', array('id' => $userid))) {
                    $ruser = fullname($user);
                }
            }
            if ($resp->submitted) {
                $timesubmitted = '&nbsp;' . get_string('submitted', 'sliclquestions') . '&nbsp;' . userdate($resp->submitted);
            }
        }
        if ($ruser) {
            echo get_string('respondent', 'sliclquestions') . ': <strong>'
               . $ruser . '</strong>' . $groupname . $timesubmitted
               . html_writer::tag('h3', format_text($this->name, FORMAT_HTML), array('class' => 'surveyTitle'));
            if ($this->capabilities->printblank && $blankquestionnaire && ($section == 1)) {
                $linkname = '&nbsp;' . get_string('printblank', 'sliclquestions');
                $title = get_string('printblanktooltip', 'sliclquestions');
                $link =  new moodle_url('/mod/sliclquestions/print.php', array('id'  => $this->id,
                                                                               'rid' => 0,
                                                                               'courseid' => $this->course->id,
                                                                               'sec'      => 1));
                $options = array('menubar'    => true,
                                 'location'   => false,
                                 'scrollbars' => true,
                                 'resizable'  => true,
                                 'height'     => 600,
                                 'width'      => 800,
                                 'title'      => $title);
                $name = 'popup';
                $action = new popup_action('click', $link, $name, $options);
                $class  = 'floatprinticon';
                echo $OUTPUT->action_link($link, $linkname, $action,
                                          array('class' => $class,
                                                'title' => $title),
                                          new pix_icon('t/print', $title));
            }
            if ($message) {
                echo html_writer::div($message, 'notifyproblem');
            }
        }
    }

    private function print_survey_end($section, $numsections)
    {
        if ($numsections > 1) {
            $a = new stdClass();
            $a->page = $section;
            $a->totpages = $numsections;
            echo html_writer::div(get_string('pageof', 'sliclquestions', $a) . '&nbsp;&nbsp;');
        }
    }

    private function response_check_format($section, $formdata, $checkmissing = true, $checkwrongformat = true)
    {

    }

    private function response_delete($rid, $sec = null)
    {

    }

    private function response_import_sec($rid, $sec, &$varr)
    {

    }

    private function response_import_all($rid, &$varr)
    {

    }

    private function response_commit($rid)
    {

    }

    private function get_response($username, $rid = 0)
    {

    }

    private function response_select_max_sec($rid)
    {

    }

    private function response_select_max_pos($rid)
    {

    }

    private function response_select_name($rid, $choicecodes, $choicetext)
    {

    }

    private function response_send_email($rid, $userid = false)
    {

    }

    private function response_insert($sid, $section, $rid, $userid, $resume = false)
    {

    }

    private function response_select($rid, $col = null, $csvexport = false, $choicecodes = 0, $choicetext = 1)
    {

    }

    private function response_goto_thankyou()
    {

    }

    private function response_goto_saved($url)
    {

    }

    private function export_csv($filename)
    {

    }
 }
