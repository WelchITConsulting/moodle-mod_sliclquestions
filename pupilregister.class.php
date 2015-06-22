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

    static public function get_instance($course, $cm, $survey, $url, $params)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new mod_sliclquestions_pupil_register($course, $cm, $survey, $url, $params);
        }
        return self::$_instance;
    }

    public function __construct($course, $cm, $survey, $url, $params)
    {
        if (!empty($params['act'])) {
            $this->perform_action($survey->id, $url, $params);
        } elseif (has_capability('mod/sliclquestions:viewstatistics', $context)) {
            $this->display_statistics();
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
            $p = array_remove_by_key($params, 'act');
            redirect($url);
        } elseif ($data = $mform->get_data()) {
            $data->survey_id = $survey->id;
            $data->teacher_id = $USER->id;
            $data->timemodified = time();
            if ($params['act'] == 'new') {
                $data->timecreated = $data->timemodified;
                $DB->insert_record('sliclquestions_students', $data);
            } else {
                $data->id = $data->pid;
                $DB->update_record('sliclquestions_students', $data);
            }
        } else {
            $mform->set_data($data);
            $mform->display();
        }
    }

    private function display_statistics($courseid, $url)
    {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/locallib.php');
        $sort  = optional_param('sort', 'firstname', PARAM_ALPHA);
        $order = optional_param('order', 'ASC', PARAM_ALPHA);
        $firstnamesort = array('s' => 'firstname');
        if ($sort == 'firstname') {
            $firstnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $firstnamesort['o'] = 'ASC';
        }
        $surnamesort = array('s' => 'surname');
        if ($sort == 'surname') {
            $surnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $surnamesort['o'] = 'ASC';
        }
        $nameheader = '<a href="' . $url->out(true, $firstnamesort) . '">'
                    . get_string('forename', 'sliclquestions') . '</a> / <a href'
                    . $url->out(true, $surnamesort) . '">'
                    . get_string('surname', 'sliclquestions') . '</a>';
        $table = new html_table();
        $table->head = array($nameheader,
                             get_string('pupilsfemale', 'sliclquestions'),
                             get_string('pupilsmale', 'sliclquestions'));
        $table->align('left', 'center', 'center');
        $totalmales = 0;
        $totalfemales = 0;
//        $sql = 'SELECT u.firstname,u.lastname,r.teacher_id,r.sex,count(*) AS numrec'
//             . ' FROM {sliclregister_pupils} r, {user} u'
//             . ' WHERE r.teacher_id=u.id AND r.register_id=? AND r.deleteflag=0'
//             . ' GROUP BY u.firstname,u.lastname,r.teacher_id,r.sex'
//             . ' ORDER BY u.lastname ASC,u.firstname ASC,r.sex DESC';
//        $results = $DB->get_records_sql($sql, array($courseid));
//        $data = array();
//        foreach($results as $record) {
//            if (!array_key_exists($record->teacher_id, $data)) {
//                $data[$record->teacher_id] = array($record->firstname . ' ' . $record->lastname);
//            }
//            if ($record->sex == 'f') {
//                $data[$record->teacher_id][]
//                $totalfemales += $record->numrec;
//            }
//            $data[$record->teacher_id] = array(,
//                                               ( ? $record->numrec : 0),
//                                               ($record->sex == 'm' ? $record->numrec : 0));
//        }






        $manager = new course_enrolement_manager(null, $course, 0, 3);
        foreach($manager->get_users($sort, $order) AS $userobj) {
            $sql = 'SELECT COUNT(*) FROM {sliclquestions_students} '
                 . 'WHERE deleteflag=0 AND sex=? AND teacher_id=?';
            $males   = (int)$DB->count_records_sql($sql, array('m', $userobj->id));
            $females = (int)$DB->count_records_sql($sql, array('f', $userobj->id));
            $table->data[] = array($userobj->firstname . ' ' . $userobj->lastname,
                                   $females,
                                   $males);
            $totalmales   += $males;
            $totalfemales += $females;
        }
        $totaltable = new html_table();
        $totaltable->head   = array();
        $totaltable->align  = array('left', 'center', 'center', 'center');
        $totaltable->data[] = array('', $totalfemales, $totalmales, ($totalfemales + $totalmales));
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
        $sql = 'SELECT * FROM {sliclregister_students} WHERE register_id=? '
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
                $table->data[] = array($pupil->forename . ' ' . $pupil->surname.
                                       ($pupil->sex == m ? get_string('male', 'sliclquestions')
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