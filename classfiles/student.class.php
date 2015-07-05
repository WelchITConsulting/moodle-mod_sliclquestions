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
 * Filename : student
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 28 Jun 2015
 */

class sliclquestions_student
{
    public $id         = 0;
    public $survey_id  = 0;
    public $teacher_id = 0;
    public $forename   = '';
    public $surname    = '';
    public $sex        = 'm';
    public $year_id    = 3;
    public $class_id   = '';
    public $kpi_level  = 2;
    public $deleteflag = 0;
    public $context;

    public function __construct($id = 0, $student = null, $context = null)
    {
        global $DB;
echo '<pre>ID: ' . $id . '<pre>';
        if ($id) {
            $student = $DB->get_records('sliclquestions_students', array('id' => $id));
echo '<pre>Student: ' . print_r($student, true) . '<pre>';
            }
        if (is_object($student)) {
            $this->id         = $student->id;
            $this->survey_id  = $student->survey_id;
            $this->teacher_id = $student->teacher_id;
            $this->forename   = $student->forename;
            $this->surname    = $student->surname;
            $this->sex        = $student->sex;
            $this->year_id    = $student->year_id;
            $this->class_id   = $student->class_id;
            $this->kpi_level  = $student->kpi_level;
            $this->deleteflag = $student->deleteflag;
        }
        $this->context = $context;
    }

    public function is_assessed($sid)
    {
        global $DB;
        $sql = 'SELECT COUNT(*)'
             . ' FROM {sliclquestions_response}'
             . ' WHERE survey_id=? AND pupilid=?';
        if ($DB->count_records_sql($sql, array($sid, $this->id))) {
            return true;
        }
        return false;
    }
}
