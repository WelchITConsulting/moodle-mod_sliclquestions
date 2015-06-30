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
 * Filename : sliclquestions
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 25 Jun 2015
 */

require_once($CFG->dirroot . '/mod/sliclquestions/classes/question.class.php');

class sliclquestions
{
    public function __construct(&$course, &$cm, $id = 0, $sliclquestions = null, $addquestions = false)
    {
        global $DB;

        if ($id) {
            $sliclquestions = $DB->get_record('sliclquestions', array('id' => $id));
        }
        if (  is_object($sliclquestions)) {
            $properties = get_object_vars($sliclquestions);
            foreach($properties as $prop => $val) {
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
        // Load the modules capabilities
        if (!empty($this->cm->id)) {
            $this->capabilities = $this->load_capabilities();
        }
    }

    /**
     * Adds the surveys questions to the object
     *
     * @global XMLDB $DB The core database interface object
     * @param int $id The ID of the SLiCL Questions instance
     */
    public function add_questions($id = false)
    {
        global $DB;

        if ($id === false) {
            $id = $this->id;
        }
        if (!isset($this->questions)) {
            $this->questions      = array();
            $this->questionsbysec = array();
        }
        $select = 'survey_id=' . $id . ' AND deleted!=\'y\'';
        if ($records = $DB->get_records_select('sliclquestions_question', $select, null, 'position')) {
            $sec = 1;
            $isbreak =false;
            foreach($records as $record) {
                $this->questions[$record->id] = new sliclquestions_question(0, $record, $this->context);
                if ($record->type_id != SLICLQUESPAGEBREAK) {
                    $this->questionsbysec[sec][$record->id] = &$this->questions[$record->id];
                    $isbreak = false;
                } else {
                    if (($record->position != 1) && !$isbreak) {
                        $sec++;
                        $isbreak = true;
                    }
                }
            }
        }
    }

    public function render_page_header()
    {
        global $OUTPUT;

        $opts = (empty($this->displayoptions) ? array() : unserialize($this->displayoptions));

        if (!isset($opts['displayheading']) || !empty($opts['displayheading'])) {
            echo $OUTPUT->heading(format_string($this->name), 2);
        }
        if (!empty($opts['printintro']) && trim(strip_tags($page->intro))) {
            echo $OUTPUT->box(format_module_intro('sliclquestions',
                                                  $this, $this->cm->id),
                                                  'mod_intobox',
                                                  'sliclquestionsintro');
        }
        $content = file_rewrite_pluginfile_urls($this->content,
                                                'pluginfile.php',
                                                $this->context->id,
                                                'mod_sliclquestions',
                                                'content',
                                                $this->id);
        $formatopts = new stdClass();
        $formatopts->noclean = true;
        $formatopts->overflowdiv = true;
        $formatopts->context     = $this->context;
        echo $OUTPUT->box(format_text($content, $this->contentformat, $formatopts),
                          'generalbox center clearfix');
    }

    public function view($url)
    {
        $msg = $this->print_survey($USER->id);
        $viewform = data_submitted($url);
        if (!empty($viewform->rid)) {
            $viewform->rid = (int)$viewform->rid;
        }
        if (!empty($viewform->sec)) {
            $viewform->sec = (int)$viewform->sec;
        }
        if (data_submitted() && confirm_sesskey() && isset($viewform->submit) &&
                isset($viewform->submittype) && ($viewform->submittype == 'Submit survey') &&
                empty($msg)) {
            $this->response_delete($viewform->rid, $viewform->sec);
            $this->rid = $this->response_insert($this->id, $viewform->sec, $viewform->rid, $USER->id);
            $this->response_commit($this->rid);
            if (!empty($viewform->rid) && is_numeric($viewform->rid)) {
                $rid = $viewform->rid;
            } else {
                $rid = $this->rid;
            }

//            // Update completion status
//            $completion = new completion_info($this->course);
//            if ($completion->is_enabled($this->cm) && $this->completion_submit) {
//                $completion->update_state($this->cm, COMPLETION_COMPLETE);
//            }
//
//            // Log this submitted response
//            $context = context_module::instance($this->cm->id);
//            $event = \mod\sliclquestions\event\attempt_submitted::create(array('context'       => $context,
//                                                                               'courseid'      => $this->course->id,
//                                                                               'relateduserid' => $USER->id,
//                                                                               'other'         => array('sliclquestionsid' => $this->id)));
            $event->trigger();

            $this->response_send_email($this->rid);
            $this->response_goto_thankyou();
        } else {
            notify(get_string('alreadyfilled', 'sliclquestions'), $url);
        }
    }

    public function is_open()
    {
        return (($this->opendate > 0) ? ($this->opendate < time()) : false);
    }

    public function is_closed()
    {
        return (($this->closedate > 0) ? ($this->closedate < time()) : false);
    }

    public function user_is_eligible()
    {
        return ($this->capabilities->view && $this->capabilities->submit);
    }




    /**
     * Get the SLiCL Questions capabilities
     *
     * @staticvar stdClass $cb Stores the capabilities across multiple calls
     * @return stdClass The class of capabilities for the module
     */
    private function load_capabilities()
    {
        static $cb;
        if (empty($cb)) {
            $cb = new stdClass();
            $cb->view                    = has_capability('mod/sliclquestions:view', $this->context);
            $cb->submit                  = has_capability('mod/sliclquestions:submit', $this->context);
            $cb->viewsingleresponse      = has_capability('mod/sliclquestions:viewsingleresponse', $this->context);
            $cb->downloadresponses       = has_capability('mod/sliclquestions:downloadresponses', $this->context);
            $cb->deleteresponses         = has_capability('mod/sliclquestions:deleteresponses', $this->context);
            $cb->manage                  = has_capability('mod/sliclquestions:readallresponses', $this->context);
            $cb->editquestions           = has_capability('mod/sliclquestions:editquestions', $this->context);
            $cb->createtemplates         = has_capability('mod/sliclquestions:createtemplates', $this->context);
            $cb->createpublic            = has_capability('mod/sliclquestions:createpublic', $this->context);
            $cb->readownresponses        = has_capability('mod/sliclquestions:readownresponses', $this->context);
            $cb->readallresponses        = has_capability('mod/sliclquestions:readallresponses', $this->context);
            $cb->readallresponsesanytime = has_capability('mod/sliclquestions:readallresponsesanytime', $this->context);
            $cb->printblank              = has_capability('mod/sliclquestions:printblank', $this->context);
            $cb->preview                 = has_capability('mod/sliclquestions:preview', $this->context);

            $cb->viewhiddenactivities    = has_capability('moodle/course:viewhiddenactivities', $this->context);
        }
        return $cb;
    }

    private function print_survey($userid = false)
    {
        global $CFG, $OUTPUT;

        $formdata = new stdClass();
        if (data_submitted() && confirm_sesskey()) {
            $formdata = data_submitted();
        }
        $formdata->rid = $this->get_response($userid);

        $msg = '';
        $numsections = isset($this->questionsbysec) ? count($this->questionsbysec) : 0;
        $action = $CFG->wwwroot . '/mod/sliclquestions/complete.php?id=' . $this->cm->id;








        $formdatareferer = !empty($formdata->referer) ? htmlspecialchars($formdata->referer) : '';
        $formdatsrid     = isset($formdata->rid) ? $formdata->rid : 0;
        echo $OUPUT->box_start('generalbox')
           . html_writer::start_tag('form', array('id'     => 'phpesp_response',
                                                  'method' => 'post',
                                                  'action' => $action))
           . html_writer::empty_tag('input', array('type'  => 'hidden',
                                                   'name'  => 'referer',
                                                   'value' => (!empty($formdata->referer) ? htmlspecialchars($formdata->referer) : '')))
           . html_writer::empty_tag('input', array('type'  => 'hidden',
                                                   'name'  => 'a',
                                                   'value' => $this->id))
           . html_writer::empty_tag('input', array('type'  => 'hidden',
                                                   'name'  => 'rid',
                                                   'value' => $this->rid))
           . html_writer::empty_tag('input', array('type'  => 'hidden',
                                                   'name'  => 'sec',
                                                   'value' => $this->sec))
           . html_writer::empty_tag('input', array('type'  => 'hidden',
                                                   'name'  => 'sesskey',
                                                   'value' => sesskey()));
        if (isset($this->questions) && $numsections) {
            //********************* Add code from survey_render function as only called from here
            $this->usehtmleditor = null;
            if ($formdata->sec > $numsections) {
                $formdata->sec = $numsections;
                echo html_writer::div(get_string('finished', 'sliclquestions'), 'warning');
                return false;
            }
            $hasrequired = $this->has_required($formdata->sec);
            $i = 0;
            if ($formdata->sec > 1) {
                for ($j = 2; $j <= $formdata->sec; $j++) {
                    foreach($this->questionsbysec[$j - 1] as $question) {
                        if ($question->type_id < SLICLQUESPAGEBREAK) {
                            $i++;
                        }
                    }
                }
            }
            $this->print_survey_start($msg, $formdata->sec, $numsections, $hasrequired, '', 1);
            foreach ($this->questionsbysec[$section] as $question) {
                if ($question->type_id != SLICLQUESSECTIONTEXT) {
                    $i++;
                }
                $question->survey_display($fordata, $descendantdata = '', $i, $this->usehtmleditor);
            }
            $this->print_survey_end($fordata->sec, $numsections);
            // End of survey_render code
            echo html_writer::start_div('notice', array('style' => 'padding: .5em 0 .5em .2em'))
               . html_writer::start_div('buttons')
               . (($formdata->sec > 1) ? html_writer::empty_tag('input', array('name'  => 'prev',
                                                                               'type'  => 'submit',
                                                                               'value' => get_string('previouspage', 'sliclquestions')))
                                       : '')
               . ($this->resume ? html_writer::empty_tag('input', array('name'  => 'resume',
                                                                        'type'  => 'submit',
                                                                        'value' => get_string('save', 'sliclquestions')))
                                : '');
            if ($formdata->sec == $numsections) {
                echo html_writer::start_div()
                   . html_writer::empty_tag('input', array('type'  => 'hidden',
                                                           'name'  => 'submittype',
                                                           'value' => 'Submit survey'))
                   . html_writer::empty_tag('input', array('type'  => 'submit',
                                                           'name'  => 'submit',
                                                           'value' => get_string('submitsurvey', 'sliclquestions')))
                   . html_writer::end_div();
            } else {
                echo html_writer::start_div()
                   . html_writer::empty_tag('input', array('type'  => 'submit',
                                                           'name'  => 'next',
                                                           'value' => get_string('nextpage', 'sliclquestions')))
                   . html_writer::end_div();
            }
            echo html_writer::end_div()
               . html_writer::end_div()
               . html_writer::end_tag('form')
               . html_writer::end_div();
            return $msg;
        }

        echo html_writer::table('p', get_string('noneinuse', 'sliclquestions'))
           . html_writer::end_tag('form')
           . html_writer::end_div();
    }

    private function print_survey_start($msg, $section, $numsections, $hasrequired, $rid = '', $blankquestionnaire = false)
    {
        global $DB, $OUTPUT;

        $Resp      = '';
        $userid    = 0;
        $groupname = '';
        if ($rid) {
            if ($resp = $DB->get_record('sliclquestions_reponse', array('id' => $rid))) {
                $userid         = $resp->userid;
                $currentgroupid = 0;
                if ($this->cm->groupmode > 0) {
                    if ($groups = groups_get_all_groups($this->course->id, $userid)) {
                        if (count($groups) == 1) {
                            $group = current($groups);
                            $currentgroupid = $group->id;
                            $groupname = ' (' . get_string('group') . ': ' . $group->name . ')';
                        } else {
                            $groupname = ' (' . get_string('groups') . ': ';
                            foreach($grous as $group) {
                                $groupname .= $group->name . ', ';
                            }
                            $groupname = substr($groupname, 0, strlen($groupname) - 2) . ')';
                        }
                    } else {
                        $groupname = ' (' . get_string('groupnonmembers') . ')';
                    }
                }
                $params = array('objectid'      => $this->id,
                                'context'       => $this->context,
                                'courseid'      => $this->course->id,
                                'relateduserid' => $userid,
                                'other'         => array('action'         => 'vresp',
                                                         'currentgroupid' => $currentgroupid,
                                                         'rid'            => $rid));
                $event = \mod_sliclquestions\event\response_viewed::create($params);
                $event->trigger();
            }
        }
        $ruser = '';
        $timesubmitted = '';
        if ($resp && !$blankquestionnaire) {
            if ($userid) {
                if ($user = $DB->get_record('user', array('id' => $id))) {
                    $ruser = fullname($user);
                }
            }
            if ($resp->submitted) {
                $timesubmitted = '&nbsp;'
                               . get_string('submitted', 'sliclquestions')
                               . '&nbsp;'
                               . userdate($resp->submitted);
            }
        }
        if ($ruser) {
            echo html_writer::start_div('respondent')
               . get_string('respondent', 'sliclquestions')
               . ': '
               . html_writer::tag('strong', $ruser)
               . $groupname
               . $timesubmitted
               . html_writer::end_div();
        }
        echo html_writer::tag('h3', format_text($this->title, FORMAT_HTML), array('class' => 'surveytitle'));
        if ($this->capabilities->printblank && $blankquestionnaire && ($section == 1)) {

            $link = new moodle_url('/mod/sliclquestions/print.php', array('qid'      => $this->id,
                                                                          'rid'      => 0,
                                                                          'courseid' => $this->course->id,
                                                                          'sec'      => 1));
            $title = get_string('printblanktooltip', 'sliclquestions');
            echo $OUTPUT->action_link($link,
                                      '&nbsp' . get_string('printblank', 'sliclquestions'),
                                      new popup_action('click', $link, 'popup', array('menubar'    => true,
                                                                                      'location'   => false,
                                                                                      'scrollbars' => true,
                                                                                      'resizable'  => true,
                                                                                      'height'     => 600,
                                                                                      'width'      => 800,
                                                                                      'title'      => $title)),
                                      array('class' => 'floatprinticon',
                                            'title' => $title),
                                      new pix_icon('t/print', $title));
        }
        if ($msg) {
            echo html_writer::div($msg, 'notifyproblem');
        }
    }

    private function print_survey_end($section, $numsections)
    {
        if ($numsections > 1) {
            $a           = new stdClass();
            $a->page     = $section;
            $a->totpages = $numsections;
            echo html_writer::div(get_string('pageof', 'sliclquestions'), 'surveypage');
        }
    }
}
