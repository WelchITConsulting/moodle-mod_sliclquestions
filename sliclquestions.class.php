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
    public function __construct(&$course, &$cm, $id = 0, &$questionnaire = null, $addquestions = null)
    {
        global $DB;

        if ($id) {
            $questionnaire = $DB->get_record('sliclquestions', array('id' => $id));
        }
        if (is_object($questionnaire)) {
            $properties = get_object_vars($questionnaire);
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

    public function add_questions($id = false, $section = false)
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
            return $this->user_time_for_new_attempt($userid);
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
        $cb->view                   = has_capability('mod/sliclquestions:view', $ctx);
        $cb->submit                 = has_capability('mod/sliclquestions:submit', $ctx);
        $cb->printblank             = has_capability('mod/sliclquestions:printblank', $ctx);
        $cb->preview                = has_capability('mod/sliclquestions:preview', $ctx);
        $cb->manage                 = has_capability('mod/sliclquestions:manage', $ctx);
        $cb->assesspupils           = has_capability('mod/sliclquestions:assesspupils', $ctx);
        $cb->registerpupils         = has_capability('mod/sliclquestions:registerpupils', $ctx);
        $cb->viewstatistics         = has_capability('mod/sliclquestions:viewstatistics', $ctx);
//        $cb->downloadresponses      = has_capability('mod/sliclquestions:downloadresponses', $ctx);
//        $cb->deleteresponses        = has_capability('mod/sliclquestions:deleteresponses', $ctx);
//        $cb->editquestions          = has_capability('mod/sliclquestions:editquestions', $ctx);
//        $cb->createtemplate         = has_capability('mod/sliclquestions:createtemplates', $ctx);
//        $cb->createpublic           = has_capability('mod/sliclquestions:createpublic', $ctx);
//        $cb->readownresponses       = has_capability('mod/sliclquestions:readownresponses', $ctx);
//        $cb->readallresponses       = has_capability('mod/sliclquestions:readallresponses', $ctx);
//        $cb->readallresponseanytime = has_capability('mod/sliclquestions:readallresponseanytime', $ctx);
//        $cb->message                = has_capability('mod/sliclquestions:message', $ctx);
        return $cb;
    }

    private function survey_render(&$formdata, $section = 1, $message = '')
    {

    }

    private function print_survey_start($message, $section, $numsections, $hasrequired, $rid = '', $blankquestionnaire = false)
    {

    }

    private function print_survey_end($section, $numsections)
    {

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
