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
 * Filename : pupilregister
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 22 Jun 2015
 */

require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/sliclquestions.class.php');

class sliclquestions_pupil_register
{
    static private $_instance;

    static public function get_instance(&$course, &$context, &$survey, &$url, &$params)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new sliclquestions_pupil_register($course, $context, $survey, $url, $params);
        }
        return self::$_instance;
    }

    public function __construct(&$course, &$context, &$survey, &$url, &$params)
    {
        if (!empty($params['act'])) {
            $this->perform_action($survey->id, $url, $params);
        } elseif (has_capability('mod/sliclquestions:viewstatistics', $context)) {
            $this->display_statistics($survey, $course, $context, $url);
        } else {
            $this->display($survey, $url, $params);
        }
    }

    private function perform_action($surveyid, $url, $params)
    {
        global $CFG, $DB, $USER, $PAGE, $OUTPUT;
        require_once($CFG->dirroot . '/mod/sliclquestions/pupil_form.php');
        $mform = new mod_sliclquestions_pupil_form();

        $data = new stdClass();
        $data->id         = $params['id'];
        $data->act        = $params['act'];
        $data->pid        = 0;
        $data->survey_id  = $surveyid;
        $data->teacher_id = $USER->id;

        if ($params['act'] == 'edit') {
            $pid = required_param('pid', PARAM_INT);
            $data = $DB->get_record('sliclquestions_students', array('id' => $pid));
            $data->pid = $pid;
            $data->id  = $params['id'];
            $data->act = $params['act'];
        } elseif ($params['act'] == 'delete') {
            $pid = required_param('pid', PARAM_INT);
            $DB->set_field('sliclquestions_students', 'deleteflag', 1, array('id' => $pid));
            redirect($url);
        }
        if ($mform->is_cancelled()) {
            redirect($url);
        } elseif ($mdata = $mform->get_data()) {
            $data->forename     = $mdata->forename;
            $data->surname      = $mdata->surname;
            $data->sex          = $mdata->sex;
            $data->year_id      = $mdata->year_id;
            $data->class_id     = $mdata->class_id;
            $data->kpi_level    = $mdata->kpi_level;
            $data->timemodified = time();
            if ($params['act'] == 'new') {
                $data->timecreated = $data->timemodified;
                $DB->insert_record('sliclquestions_students', $data);
            } else {
                $data->id = $data->pid;
                $DB->update_record('sliclquestions_students', $data);
            }
            redirect($url);
        } else {
            echo $OUTPUT->header();
            $mform->set_data($data);
            $mform->display();
            echo $OUTPUT->footer();
        }
    }

    private function display(&$survey, &$url, &$params)
    {
        global $DB, $USER, $OUTPUT;

        // Define the table of pupils
        $table = new html_table();
        $table->head = array(get_string('tblname', 'sliclquestions'),
                             get_string('tblsex', 'sliclquestions'),
                             get_string('tblyear', 'sliclquestions'),
                             get_string('tblclass', 'sliclquestions'),
                             get_string('tblkpilevel', 'sliclquestions'),
                             get_string('tblcommand', 'sliclquestions'));
        $table->align = array('left',
                              'center',
                              'center',
                              'center',
                              'center',
                              'center');
        $sql = 'SELECT * FROM {sliclquestions_students} WHERE survey_id=? '
             . 'AND teacher_id=? AND deleteflag=0 ORDER BY sex DESC, kpi_level '
             . 'ASC, surname ASC, forename ASC';
        $pupils = $DB->get_records_sql($sql, array($survey->id, $USER->id));
        if ($pupils) {
            foreach($pupils as $pupil) {
                $editurl = $url;
                $editurl->params(array('act' => 'edit',
                                       'pid' => $pupil->id));
                $editbtn = '<a href="' . $editurl . '">' . get_string('edit') . '</a>';
                $deleteurl = $url;
                $deleteurl->params(array('act' => 'delete',
                                         'pid' => $pupil->id));
                $deletebtn = '<a href="' . $deleteurl . '">' . get_string('delete') . '</a>';
                $table->data[] = array($pupil->forename . ' ' . $pupil->surname,
                                       ($pupil->sex == 'm' ? get_string('male', 'sliclquestions')
                                                           : get_string('female', 'sliclquestions')),
                                       $pupil->year_id,
                                       $pupil->class_id,
                                       $pupil->kpi_level,
                                       $editbtn . '<br>' . $deletebtn);
            }
        } else {
            // No pupils registered
        }

        // Define the URL for add pupil links
        $params['act'] = 'add';
        $addurl = $url;
        $addurl->param('act', 'add');
        $addbutton = new single_button($addurl, get_string('addpupil', 'sliclquestions'));
//        $addbutton->class = 'breadcrumb-button';

        $options = (empty($survey->displayoptions) ? array() : unserialize($survey->displayoptions));

        $content = file_rewrite_pluginfile_urls($survey->content, 'pluginfile.php', $survey->context->id, 'mod_sliclquestions', 'content', $survey->id);
        $formatopt              = new stdClass();
        $formatopt->noclean     = true;
        $formatopt->overflowdiv = true;
        $formatopt->context     = $survey->context;

        // Output the list of pupils
        echo $OUTPUT->header()
           . $OUTPUT->heading(format_text($survey->name))
           . ((!empty($options->printintro) && trim( strip_tags($survey->intro))) ? $OUTPUT->box(format_module_intro('sliclquestions', $survey, $survey->cm->id), 'mod_introbox', 'intro') : '')
           . $OUTPUT->box(format_text($survey->content, $survey->contentformat, $formatopt), 'generalbox center clearfix')
           . $OUTPUT->render($addbutton)
//           . html_writer::link($addurl, get_string('addpupil', 'sliclquestions'))
           . $OUTPUT->box_start('generalbox center clearfix')
           . html_writer::table($table)
           . $OUTPUT->box_end()
           . $OUTPUT->render($addbutton)
//           . html_writer::link($addurl, get_string('addpupil', 'sliclquestions'))
           . $OUTPUT->footer();
    }
}
