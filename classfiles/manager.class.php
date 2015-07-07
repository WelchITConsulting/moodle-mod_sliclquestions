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
 * Filename : manager
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 21 Jun 2015
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');
require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/sliclquestions.class.php');

class mod_sliclquestions_management_console
{
    static private $_instance;

    static public function get_instance(&$course, &$context, &$survey, &$url, &$params)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new mod_sliclquestions_management_console($course, $context, $survey, $url, $params);
        }
        return self::$_instance;
    }

    public function __construct(&$course, &$context, &$survey, &$url, &$params)
    {
        if ($survey->questype == SLICLQUESTIONS_PUPILREGISTRATION) {
            $this->pupil_registration_statistics($survey, $course, $context, $url);
        } elseif ($survey->questype == SLICLQUESTIONS_PUPILREGISTRATION) {
            $this->pupil_assessment_statistics($survey, $course, $context, $url);
        } elseif ($survey->questype == SLICLQUESTIONS_SURVEY) {
            $this->display_statistics();
        } else {
            notice(get_string('invalidquesttype', 'sliclquestions'), $url);
        }
    }

    private function display_statistics()
    {

    }

    private function get_non_respondents()
    {

    }

    private function pupil_registration_statistics(&$survey, &$course, $context, $url)
    {
        global $CFG, $DB, $OUTPUT;

        $sort  = optional_param('s', 'lastname', PARAM_ALPHA);
        $order = optional_param('o', 'ASC', PARAM_ALPHA);
        $firstnamesort = array('s' => 'firstname');
        if ($sort == 'firstname') {
            $firstnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $firstnamesort['o'] = 'ASC';
        }
        $lastnamesort = array('s' => 'lastname');
        if ($sort == 'lastname') {
            $lastnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $lastnamesort['o'] = 'ASC';
        }
        $nameheader = '<a href="' . $url->out(true, $firstnamesort) . '">'
                    . get_string('firstname') . '</a> / <a href="'
                    . $url->out(true, $lastnamesort) . '">'
                    . get_string('lastname') . '</a>';
        $table = new html_table();
        $table->head = array($nameheader,
                             get_string('pupilsfemale', 'sliclquestions'),
                             get_string('pupilsmale', 'sliclquestions'));
        $table->align = array('left', 'center', 'center');
        $totalmales = 0;
        $totalfemales = 0;
        $sql = 'SELECT DISTINCT CONCAT(ce.id,sr.sex) AS ind, ce.id, ce.firstname, ce.lastname, sr.sex, count(*) AS numrec'
             . ' FROM (SELECT u.id, u.firstname, u.lastname FROM {user} u, {role_assignments} ra,'
             . ' {role} r WHERE u.id = ra.userid AND ra.roleid = r.id'
             . ' AND r.shortname=? AND ra.contextid=?) AS ce '
             . ' LEFT OUTER JOIN {sliclquestions_students} sr ON ce.id=sr.teacher_id'
             . ' AND sr.survey_id=1 AND sr.deleteflag=0 GROUP BY ce.firstname,ce.lastname,sr.teacher_id,sr.sex'
             . ' ORDER BY ';
        if ($sort == 'firstname') {
            $sql .= 'ce.firstname '
                  . ($order == 'ASC' ? 'ASC' : 'DESC')
                  . ',ce.lastname ASC,sr.sex DESC';
        } else {
            $sql .= 'ce.lastname '
                  . ($order == 'ASC' ? 'ASC' : 'DESC')
                  . ',ce.firstname ASC,sr.sex DESC';
        }
        $context = context_course::instance($course->id);
        $results = $DB->get_records_sql($sql, array('sbenquirer',
                                                    $context->id));
        $data = array();
        foreach($results as $record) {
            if (!array_key_exists($record->id, $data)) {
                $data[$record->id] = array($record->firstname . ' ' . $record->lastname, 0, 0);
            }
            if ($record->sex == 'm') {
                $data[$record->id][2] = $record->numrec;
                $totalmales += $record->numrec;
            } elseif ($record->sex == 'f') {
                $data[$record->id][1] = $record->numrec;
                $totalfemales += $record->numrec;
            }
        }
        $table->data = $data;
        $totaltable = new html_table();
        $totaltable->head   = array('',
                                    get_string('pupilsfemale', 'sliclquestions'),
                                    get_string('pupilsmale', 'sliclquestions'),
                                    get_string('pupilstotal', 'sliclquestions'));
        $totaltable->align  = array('left', 'center', 'center', 'center');
        $totaltable->data[] = array(get_string('pupilsregistered', 'sliclquestions'),
                                    $totalfemales,
                                    $totalmales,
                                    ($totalfemales + $totalmales));

        // Output the list of pupils
        echo $survey->render_page_header()
           . $OUTPUT->box_start('generalbox center clearfix')
           . html_writer::tag('p', get_string('stats_content', 'sliclquestions'))
           . html_writer::start_div('slicl-registered-pupils')
           . html_writer::table($totaltable)
           . html_writer::end_div()
           . html_writer::table($table)
           . $OUTPUT->box_end()
           . $OUTPUT->footer();
        exit();
    }

    private function pupil_assessment_statistics(&$survey, &$course, $context, $url)
    {
        global $CFG, $DB, $OUTPUT;

        $sort  = optional_param('s', 'lastname', PARAM_ALPHA);
        $order = optional_param('o', 'ASC', PARAM_ALPHA);
        $firstnamesort = array('s' => 'firstname');
        if ($sort == 'firstname') {
            $firstnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $firstnamesort['o'] = 'ASC';
        }
        $lastnamesort = array('s' => 'lastname');
        if ($sort == 'lastname') {
            $lastnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $lastnamesort['o'] = 'ASC';
        }
        $nameheader = '<a href="' . $url->out(true, $firstnamesort) . '">'
                    . get_string('firstname') . '</a> / <a href="'
                    . $url->out(true, $lastnamesort) . '">'
                    . get_string('lastname') . '</a>';
        $table = new html_table();
        $table->head = array($nameheader,
                             get_string('pupilsfemale', 'sliclquestions'),
                             get_string('pupilsmale', 'sliclquestions'));
        $table->align = array('left', 'center', 'center');
        $totalmales = 0;
        $totalfemales = 0;
        $sql = 'SELECT DISTINCT CONCAT(ce.id,sr.sex) AS ind, ce.id, ce.firstname, ce.lastname, sr.sex, count(*) AS numrec'
             . ' FROM (SELECT u.id, u.firstname, u.lastname FROM {user} u, {role_assignments} ra,'
             . ' {role} r WHERE u.id = ra.userid AND ra.roleid = r.id'
             . ' AND r.shortname=? AND ra.contextid=?) AS ce '
             . ' LEFT OUTER JOIN {sliclquestions_students} sr ON ce.id=sr.teacher_id'
             . ' AND sr.survey_id=1 AND sr.deleteflag=0 GROUP BY ce.firstname,ce.lastname,sr.teacher_id,sr.sex'
             . ' ORDER BY ';
        if ($sort == 'firstname') {
            $sql .= 'ce.firstname '
                  . ($order == 'ASC' ? 'ASC' : 'DESC')
                  . ',ce.lastname ASC,sr.sex DESC';
        } else {
            $sql .= 'ce.lastname '
                  . ($order == 'ASC' ? 'ASC' : 'DESC')
                  . ',ce.firstname ASC,sr.sex DESC';
        }
        $context = context_course::instance($course->id);
        $results = $DB->get_records_sql($sql, array('sbenquirer',
                                                    $context->id));
        $data = array();
        foreach($results as $record) {
            if (!array_key_exists($record->id, $data)) {
                $data[$record->id] = array($record->firstname . ' ' . $record->lastname, 0, 0);
            }
            if ($record->sex == 'm') {
                $data[$record->id][2] = $record->numrec;
                $totalmales += $record->numrec;
            } elseif ($record->sex == 'f') {
                $data[$record->id][1] = $record->numrec;
                $totalfemales += $record->numrec;
            }
        }
        $table->data = $data;
        $totaltable = new html_table();
        $totaltable->head   = array('',
                                    get_string('pupilsfemale', 'sliclquestions'),
                                    get_string('pupilsmale', 'sliclquestions'),
                                    get_string('pupilstotal', 'sliclquestions'));
        $totaltable->align  = array('left', 'center', 'center', 'center');
        $totaltable->data[] = array(get_string('pupilsregistered', 'sliclquestions'),
                                    $totalfemales,
                                    $totalmales,
                                    ($totalfemales + $totalmales));

        // Output the list of pupils
        echo $survey->render_page_header()
           . $OUTPUT->box_start('generalbox center clearfix')
           . html_writer::tag('p', get_string('stats_content', 'sliclquestions'))
           . html_writer::start_div('slicl-registered-pupils')
           . html_writer::table($totaltable)
           . html_writer::end_div()
           . html_writer::table($table)
           . $OUTPUT->box_end()
           . $OUTPUT->footer();
        exit();
    }
}
