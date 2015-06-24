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

class mod_sliclquestions_pupil_register
{
    static private $_instance;

    static public function get_instance(&$course, $context, &$survey, $url, $params)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new mod_sliclquestions_pupil_register($course, $context, $survey, $url, $params);
        }
        return self::$_instance;
    }

    public function __construct(&$course, $context, &$survey, $url, $params)
    {
        if (!empty($params['act'])) {
            $this->perform_action($survey->id, $url, $params);
        } elseif (has_capability('mod/sliclquestions:viewstatistics', $context)) {
            $this->display_statistics($course, $context, $url);
        } else {
            $this->display($survey->id, $url, $params);
        }
    }

    private function perform_action($surveyid, $url, $params)
    {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/mod/sliclquestions/pupil_form.php');
        $mform = new mod_sliclquestions_pupil_form();
        $data = new stdClass();
        $data->id  = $params['id'];
        $data->act = $params['act'];
        $data->pid = 0;

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
            $mdata->survey_id = $survey->id;
            $mdata->teacher_id = $USER->id;
            $mdata->timemodified = time();
            if ($params['act'] == 'new') {
                $mdata->timecreated = $data->timemodified;
                $DB->insert_record('sliclquestions_students', $mdata);
            } else {
                $mdata->id = $data->pid;
                $DB->update_record('sliclquestions_students', $mdata);
            }
        } else {
            $mform->set_data($data);
            $mform->display();
        }
    }

    private function display_statistics(&$course, $context, $url)
    {
        global $CFG, $DB;
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
//        $context = context_course::instance($course->cm->id);
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
        echo html_writer::tag('p', get_string('stats_content', 'sliclquestions'))
           . html_writer::start_div('slicl-registered-pupils')
           . html_writer::table($totaltable)
           . html_writer::end_div()
           . html_writer::table($table);
    }

    private function display($surveyid, $url, $params)
    {
        global $DB, $USER;
        $params['act'] = 'add';
        $addurl = $url;
        $addurl->param('act', 'add');
        echo html_writer::link($addurl, get_string('addpupil', 'sliclquestions'));
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
        $pupils = $DB->get_records_sql($sql, array($surveyid, $USER->id));
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
        echo html_writer::table($table);
        echo html_writer::link($addurl, get_string('addpupil', 'sliclquestions'));
    }
}
