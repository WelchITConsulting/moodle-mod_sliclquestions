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
 * Filename : pupilassessment
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 28 Jun 2015
 */

require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/sliclquestions.class.php');
require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/student.class.php');

class sliclquestions_pupil_assessment
{
    static private $_instance;

    static public function get_instance(&$course, &$context, sliclquestions &$survey, &$url, &$params)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new sliclquestions_pupil_assessment($course, $context, $survey, $url, $params);
        }
        return self::$_instance;
    }

    public function __construct(&$course, &$context, &$survey, &$url, &$params)
    {
        global $OUTPUT;
        if (!$survey->is_open()) {
            $survey->render_page_header();
            notify(get_string('notopen', 'sliclquestions', userdate($survey->opendate)));
            echo $OUTPUT->footer();
            exit();
        } elseif ($survey->is_closed()) {
            $survey->render_page_header();
            notify(get_string('closed', 'sliclquestions', userdate($survey->closedate)));
            echo $OUTPUT->footer();
            exit();
        }

        if (!empty($params['act'])) {
            $this->do_action($context, $survey, $url, $params);
        } else {
            $this->display_pupils($survey, $url, $params);
        }
    }

    private function do_action(&$context, &$survey, &$url, &$params)
    {
        global $CFG, $DB, $OUTPUT, $PAGE, $USER;

        $PAGE->requires->js('/mod/sliclquestions/module.js');
        $pid = required_param('pid', PARAM_INT);
        $student = new sliclquestions_student($pid, null, $context);
        $data = new stdClass();
        $data->id      = $params['id'];
        $data->act     = 'save';
        $data->pid     = $student->id;
        $data->name    = $student->forename . ' ' . $student->surname;
        $data->kpi_level = $student->kpi_level;
        $data->kpi       = optional_param('kpi', 0, PARAM_INT);
        require_once($CFG->dirroot . '/mod/sliclquestions/assessment_form.php');
        $mform = new sliclquestions_assessment_form();
        if ($mform->is_cancelled()) {
            redirect($url);
        } elseif ($sdata = $mform->get_data()) {

            // Create the response record
            $d = new stdClass();
            $d->survey_id = $survey->id;
            $d->userid    = $USER->id;
            $d->pupilid   = $sdata->pid;
            $d->submitted = time();
            $d->complete  = 'y';
            $rid = $DB->insert_record('sliclquestions_response', $d);

            // Question 1 = KPI Level
            $d = new stdClass();
            $d->responseid = $rid;
            $d->questionid = 13;
            $d->response   = $sdata->kpi_level;
            $DB->insert_record('sliclquestions_resp_single', $d);

            // Question 2
            // KPI Level 1
            if (isset($sdata->kpi1_1)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 14;
                $d->response   = 42;
                $d->rank       = $sdata->kpi1_1;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi1_2)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 14;
                $d->response   = 43;
                $d->rank       = $sdata->kpi1_2;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi1_3)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 14;
                $d->response   = 44;
                $d->rank       = $sdata->kpi1_3;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi1_4)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 14;
                $d->response   = 45;
                $d->rank       = $sdata->kpi1_4;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi1_5)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 14;
                $d->response   = 46;
                $d->rank       = $sdata->kpi1_5;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi1_6)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 14;
                $d->response   = 47;
                $d->rank       = $sdata->kpi1_6;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }

            // KPI Level 2
            if (isset($sdata->kpi2_1)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 16;
                $d->response   = 48;
                $d->rank       = $sdata->kpi2_1;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi2_2)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 16;
                $d->response   = 49;
                $d->rank       = $sdata->kpi2_2;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi2_3)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 16;
                $d->response   = 50;
                $d->rank       = $sdata->kpi2_3;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi2_4)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 16;
                $d->response   = 51;
                $d->rank       = $sdata->kpi2_4;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi2_5)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 16;
                $d->response   = 52;
                $d->rank       = $sdata->kpi2_5;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi2_6)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 16;
                $d->response   = 53;
                $d->rank       = $sdata->kpi2_6;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi2_7)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 16;
                $d->response   = 54;
                $d->rank       = $sdata->kpi2_7;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi2_8)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 16;
                $d->response   = 55;
                $d->rank       = $sdata->kpi2_8;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi2_9)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 16;
                $d->response   = 56;
                $d->rank       = $sdata->kpi2_9;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }

            // KPI Level 3
            if (isset($sdata->kpi3_1)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 18;
                $d->response   = 57;
                $d->rank       = $sdata->kpi3_1;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi3_2)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 18;
                $d->response   = 58;
                $d->rank       = $sdata->kpi3_2;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi3_3)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 18;
                $d->response   = 59;
                $d->rank       = $sdata->kpi3_3;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi3_4)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 18;
                $d->response   = 60;
                $d->rank       = $sdata->kpi3_4;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi3_5)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 18;
                $d->response   = 61;
                $d->rank       = $sdata->kpi3_5;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi3_6)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 18;
                $d->response   = 62;
                $d->rank       = $sdata->kpi3_6;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi3_7)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 18;
                $d->response   = 62;
                $d->rank       = $sdata->kpi3_6;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }

            // KPI Level 4
            if (isset($sdata->kpi4_1)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 20;
                $d->response   = 63;
                $d->rank       = $sdata->kpi4_1;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi4_2)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 20;
                $d->response   = 64;
                $d->rank       = $sdata->kpi4_2;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi4_3)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 20;
                $d->response   = 65;
                $d->rank       = $sdata->kpi4_3;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi4_4)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 20;
                $d->response   = 66;
                $d->rank       = $sdata->kpi4_4;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi4_5)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 20;
                $d->response   = 67;
                $d->rank       = $sdata->kpi4_5;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->kpi4_6)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 20;
                $d->response   = 68;
                $d->rank       = $sdata->kpi4_6;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }

            // Question 3
            if (isset($sdata->personality_1)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 22;
                $d->response   = 70;
                $d->rank       = $sdata->personality_1;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->personality_2)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 22;
                $d->response   = 71;
                $d->rank       = $sdata->personality_2;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->personality_2)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 22;
                $d->response   = 72;
                $d->rank       = $sdata->personality_3;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->personality_2)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 22;
                $d->response   = 73;
                $d->rank       = $sdata->personality_4;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }
            if (isset($sdata->personality_2)) {
                $d = new stdClass();
                $d->responseid = $rid;
                $d->questionid = 22;
                $d->response   = 74;
                $d->rank       = $sdata->personality_5;
                $DB->insert_record('sliclquestions_resp_rank', $d);
            }

            // Question 4
            $d = new stdClass();
            $d->responseid = $rid;
            $d->questionid = 24;
            $d->response   = (empty($sdata->q4) ? '' : $sdata->q4);
            $DB->insert_record('sliclquestions_resp_text', $d);

            redirect('/mod/sliclquestions/view.php?id=' . $sdata->id);

        } else {
            echo $OUTPUT->header()
               . $OUTPUT->heading(format_text($survey->name));
            $mform->set_data($data);
            $mform->display();
            echo $OUTPUT->footer();
            exit();
        }
    }

    private function display_pupils(&$survey, &$url, &$params)
    {
        global $DB, $USER, $OUTPUT;

        // Define the table of pupils
        $table = new html_table();
        $table->head = array(get_string('tblname', 'sliclquestions'),
                             get_string('tblsex', 'sliclquestions'),
                             get_string('tblyear', 'sliclquestions'),
                             get_string('tblclass', 'sliclquestions'),
                             get_string('tblcommand', 'sliclquestions'));
        $table->align = array('left',
                              'center',
                              'center',
                              'center',
                              'center');
        $sql = 'SELECT s.*, r.id as responded FROM {sliclquestions_students} s'
             . ' LEFT OUTER JOIN {sliclquestions_response} r ON r.pupilid = s.id'
             . ' WHERE s.survey_id=? AND s.teacher_id=? AND s.deleteflag=0'
             . ' ORDER BY sex DESC, kpi_level ASC, surname ASC, forename ASC';
        $pupils = $DB->get_records_sql($sql, array($survey->register, $USER->id));
        if ($pupils) {
            foreach($pupils as $pupil) {
                if (empty($pupil->responded)) {
                    $params['pid'] = $pupil->id;
                    $params['act'] = 'assess';
//                    $assessurl = new moodle_url('/mod/sliclquestions/complete.php', $params);
                    $assessurl = new moodle_url('/mod/sliclquestions/view.php', $params);
//                    $assessurl->params(array('act' => 'assess',
//                                             'pid' => $pupil->id));
                    $assessbtn = '<a href="' . $assessurl . '">' . get_string('assess', 'sliclquestions') . '</a>';
                } else {
                    $assessbtn = get_string('complete', 'sliclquestions');
                }
                $table->data[] = array($pupil->forename . ' ' . $pupil->surname,
                                       ($pupil->sex == 'm' ? get_string('male', 'sliclquestions')
                                                           : get_string('female', 'sliclquestions')),
                                       $pupil->year_id,
                                       $pupil->class_id,
                                       $assessbtn);
            }
        } else {
            $table->data[] = array('None registered', ''. ''. ''. '');
        }

        $options = (empty($survey->displayoptions) ? array() : unserialize($survey->displayoptions));

        $content = file_rewrite_pluginfile_urls($survey->content,
                                                'pluginfile.php',
                                                $survey->context->id,
                                                'mod_sliclquestions', 'content',
                                                $survey->id);
        $formatopt              = new stdClass();
        $formatopt->noclean     = true;
        $formatopt->overflowdiv = true;
        $formatopt->context     = $survey->context;

        // Output the list of pupils
        echo $OUTPUT->header()
           . $OUTPUT->heading(format_text($survey->name))
           . ((!empty($options->printintro) && trim( strip_tags($survey->intro))) ? $OUTPUT->box(format_module_intro('sliclquestions', $survey, $survey->cm->id), 'mod_introbox', 'intro') : '')
           . $OUTPUT->box(format_text($survey->content, $survey->contentformat, $formatopt), 'generalbox center clearfix')
           . $OUTPUT->box_start('generalbox center clearfix')
           . html_writer::table($table)
           . $OUTPUT->box_end()
           . $OUTPUT->footer();
    }
}
