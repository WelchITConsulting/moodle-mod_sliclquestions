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
        if (!$survey->is_open()) {
            notice(get_string('notopen', 'sliclquestions', userdate($survey->opendate)), $url);
        } elseif ($survey->is_closed()) {
            notice(get_string('closed', 'sliclquestions', userdate($survey->closedate)), $url);
        }
        $this->display_pupils($survey, $url, $params);
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
        $sql = 'SELECT * FROM {sliclquestions_students} WHERE survey_id=? '
             . 'AND teacher_id=? AND deleteflag=0 ORDER BY sex DESC, kpi_level '
             . 'ASC, surname ASC, forename ASC';
        $pupils = $DB->get_records_sql($sql, array($survey->register, $USER->id));
        if ($pupils) {
            foreach($pupils as $pupil) {
                $assessurl = $url;
                $assessurl->params(array('act' => 'assess',
                                         'pid' => $pupil->id));
                $assessbtn = '<a href="' . $assessurl . '">' . get_string('edit') . '</a>';
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
