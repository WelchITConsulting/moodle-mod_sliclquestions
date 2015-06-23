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

class mod_sliclquestions_questionnaire
{
    public function __construct(&$course, &$cm, $id = 0, $questionnaire = null, $addquestions = null)
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
            $this->capabilities = sliclquestions_load_capabilities($this->cm->id);
        }
    }

    public function add_questions($id = false)
    {
        global $DB;


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




    private function has_required($section = 0)
    {

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
 }
