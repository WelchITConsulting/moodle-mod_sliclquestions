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
 * Filename : questiontypes
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 09 Jun 2015
 */

require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

// Define question constants
define('SLICLQUESCHOOSE', 0);
define('SLICLQUESYESNO', 1);
define('SLICLQUESTEXT', 2);
define('SLICLQUESESSAY', 3);
define('SLICLQUESRADIO', 4);
define('SLICLQUESCHECK', 5);
define('SLICLQUESDROP', 6);
define('SLICLQUESRATE', 8);
define('SLICLQUESDATE', 9);
define('SLICLQUESNUMERIC', 10);
define('SLICLQUESPAGEBREAK', 99);
define('SLICLQUESSECTIONTEXT', 100);

class sliclquestions_question
{
    private $id             = 0;
    private $surveyid       = 0;
    private $name           = '';
    private $type           = '';
    private $choices        = array();
    private $typeid         = 0;
    private $responsetable  = '';
    private $length         = 0;
    private $precise        = 0;
    private $position       = 0;
    private $content        = '';
    private $allchoices     = '';
    private $required       = 'n';
    private $deleted        = 'n';

    public function __construct($id = 0, $question = null, $context = null)
    {
        global $DB;
        static $qtypes = null;

        if (is_null($qtypes)) {
            $qtypes = $DB->get_records('sliclquestions_quest_type', array(), 'typeid',
                                       'typeid, type, has_choices, response_table');
        }

        if ($id) {
            $question = $DB->get_record('sliclquestions_question', array('id' => $id));
        }

        if (is_object($question)) {
            $this->id             = $question->id;
            $this->surveyid       = $question->survey_id;
            $this->name           = $question->name;
            $this->dependquestion = $question->dependquestion;
            $this->dependchoice   = $question->dependchoice;
            $this->length         = $question->length;
            $this->precise        = $question->precise;
            $this->position       = $question->position;
            $this->content        = $question->content;
            $this->required       = $question->required;
            $this->deleted        = $question->deleted;
            $this->typeid         = $question->type_id;
            $this->type           = $qtypes[$this->typeid]->type;
            $this->responsetable  = $qtypes[$this->typeid]->response_table;
            if ($qtypes[$this->typeid]->has_choices == 'y') {
                $this->get_choices();
            }
        }
        $this->context = $context;
    }

    public function insert_response($rid)
    {
        $method = 'insert_' . $this->responsetable;
        if (method_exists($this, $method)) {
            return $this->$method($rid);
        }
        return false;
    }

    public function display_results($rids, $sort)
    {
        $method = 'display_' . $this->responsetable . '_results';
        if (method_exists($this, $method)) {
            return $this->$method($rids, $sort);
        }
        return false;
    }

    public function survey_display($formdata, $descendantdata, $qnum = '', $blankquestionnaire = false)
    {
        $this->question_display($formdata, $descendantdata, $qnum, $blankquestionnaire);
    }

    public function questionstart_survey_display($qnum, $formdata='')
    {

    }

    public function questionend_survey_display()
    {
        echo html_writer::end_div()
           . html_writer::end_tag('fieldset');
    }

    public function response_display($data, $qnum = '')
    {

    }

    public function yesno_response_display($data)
    {

    }

    public function text_reponse_display($data)
    {

    }

    public function radio_response_display($data)
    {

    }

    public function check_response_display($data)
    {

    }

    public function drop_response_display($data)
    {

    }

    public function rate_response_display($data)
    {

    }

    public function date_response_display($data)
    {

    }

    public function numeric_response_display($data)
    {

    }

    public function sectiontext_reponse_display($data)
    {
        return;
    }






    private function insert_response_bool($rid)
    {
        global $DB;
        $val = optional_param('q' . $this->id, '', PARAM_ALPHANUMEXT);
        if (!empty($val)) {
            $rec = new object();
            $rec->response_id = $rid;
            $rec->question_id = $this->id;
            $rec->choice_id = $val;
            return $DB->insert_record('sliclquestions_' . $this->responsetable, $rec);
        }
        return false;
    }

    private function insert_response_text($rid)
    {
        global $DB;
        $val = optional_param('q' . $this->id, '', PARAM_CLEAN);
        if ($this->typeid == QUESNUMERIC) {
            $val = preg_replace("/[^0-9.\-]*(-?[0-9]*\.?[0-9]*).*/", '\1', $val);
        }
        if (preg_match("/[^ \t\n]/", $val)) {
            $rec = new object();
            $rec->response_id = $rid;
            $rec->question_id = $this->id;
            $rec->response = $val;
            return $DB->insert_record('sliclquestions_' . $this->responsetable, $rec);
        }
        return false;
    }

    /*******
     * NEED TO REWRITE THE PROCESS OF SAVING DATA TO USE BETTER LIGIC
     */
    private function insert_response_date($rid)
    {
        global $DB;
        $val = optional_param('q' . $this->id, '', PARAM_CLEAN);
        $dateresult = null;
        try {
            $dateresult = new DateTime();
        } catch (Exception $ex) {
            return false;
        }
        if (isnull($dateresult)) {
            $rec = new object();
            $rec->response_id = $rid;
            $rec->question_id = $this->id;
            $rec->response = $dateresult->format('c');
            return $DB->insert_record('sliclquestions_' . $this->responsetable, $rec);
        } return false;
    }

    private function insert_response_single($rid)
    {
        global $DB;
        $val = optional_param('q' . $this->id, null, PARAM_CLEAN);
        if (!empty($val)) {
            foreach($this->choices as $cid => $choice) {
                if (strpos($choice->content, '!other') === 0) {
                    $other = optional_param('q' . $this->id . '_' . $cid, null, PARAM_CLEAN);
                    if (!isset($other)) {
                        continue;
                    }
                    if (preg_match("/[^ \t\n]/", $other)) {
                        $rec = new Object();
                        $rec->response_id = $rid;
                        $rec->question_id = $this->id;
                        $rec->choice_id   = $cid;
                        $rec->response    = $other;
                        $resid = $DB->insert_record('sliclquestions_response_other', $rec);
                        $val = $cid;
                        break;
                    }
                }
            }
        }
        if (preg_match("/other_q([0-9]+)/", (isset($val) ? $val : ''), $regs)) {
            $cid = $regs[1];
            if (!isset($other)) {
                $other = optional_param('q' . $this->id . '_' . $cid, null, PARAM_CLEAN);
            }
            if (preg_match("/[^ \t\n]/", $other)) {
                $rec = new object();
                $rec->response_id = $rid;
                $rec->question_id = $this->id;
                $rec->choice_id   = $cid;
                $rec->response    = $other;
                $resid = $DB->insert_record('sliclquestions_response_other', $rec);
                $val = $cid;
            }
        }
        $rec = new object();
        $rec->response_id = $rid;
        $rec->question_id = $this->id;
        $rec->choice_id = (isset($val) ? $val : 0);
        if ($rec->choice_id) {
            return $DB->insert_record('sliclquestions_' . $this->responsetable, $rec);
        }
        return false;
    }

    private function insert_response_multiple($rid)
    {
        global $DB;
//        $val = optional_param('q' . $this->id, '', PARAM_ALPHANUMEXT);
//        if (!empty($val)) {
//            $rec = new object();
//            $rec->response_id = $rid;
//            $rec->question_id = $this->id;
//            $rec->choice_id = $val;
//            return $DB->insert_record('sliclquestions_' . $this->responsetable, $rec);
//        }
//        return false;
    }

    private function insert_response_rank($rid)
    {
        global $DB;
//        $val = optional_param('q' . $this->id, '', PARAM_ALPHANUMEXT);
//        if (!empty($val)) {
//            $rec = new object();
//            $rec->response_id = $rid;
//            $rec->question_id = $this->id;
//            $rec->choice_id = $val;
//            return $DB->insert_record('sliclquestions_' . $this->responsetable, $rec);
//        }
//        return false;
    }






    private function get_choices()
    {
        global $DB;

        if ($choices = $DB->get_records('sliclquestions_quest_choice', array('question_id' => $this->id), 'id ASC')) {
            foreach($choices as $choice) {
                $this->choices[$choice->id] = new stdClass();
                $this->choices[$choice->id]->content = $choice->content;
                $this->choices[$choice->id]->value   = $choice->value;
            }
        } else {
            $this->choices = array();
        }
    }

    private function get_results($rids = false)
    {
        $method = 'get_' . $this->responsetable . '_results';
        if (  method_exists($this, $method)) {
            return $this->$method($rids);
        }
        return false;
    }

    private function get_response_bool_results($rids = false)
    {

    }

    private function get_response_text_results($rids = false)
    {

    }

    private function get_response_date_results($rids = false)
    {

    }

    private function get_response_single_results($rids = false)
    {

    }

    private function get_response_multiple_results($rids = false)
    {
        return $this->get_response_single_results($rids);
    }

    private function get_response_rank_results($rids = false)
    {

    }

    // Display results functions

    private function display_response_bool_results($rids = false)
    {

    }

    private function display_response_text_results($rids = false)
    {

    }

    private function display_response_date_results($rids = false)
    {

    }

    private function display_response_single_results($rids = false)
    {

    }

    private function display_response_multiple_results($rids = false)
    {

    }

    private function display_response_rank_results($rids = false)
    {

    }

    private function question_display($formdata, $descendantdata, $qnum, $blankquestionnaire)
    {

    }

    private function response_check_required($data)
    {
        if ($this->typeid == SLICLQUESRATE) {
            foreach($this->choices as $cid => $choice) {
                $str = 'q' . $this->id . '_' . $cid;
                if (isset($data->$str)) {
                    return '&nbsp;';
                }
            }
        }
        if (($this->required == 'y') && empty($data->{'q' . $this->id})) {
            return '*';
        }
        return '&nbsp;';
    }

    private function yesno_survey_display($data, $descendantdata, $blankquestionnaire = false)
    {

    }

    private function text_survey_display($data)
    {

    }

    private function essay_survey_display($data)
    {

    }

    private function radio_survey_display($data, $descendantdata, $blankquestionnaire = false)
    {

    }

    private function check_survey_display($data)
    {

    }

    private function drop_survey_display($data, $descendantdata)
    {

    }

    private function rate_survey_display($data, $descendantdata = '', $blankquestionnaire = false)
    {

    }

    private function date_survey_display($data)
    {

    }

    private function numeric_survey_display($data)
    {

    }

    private function sectiontext_survey_display($data)
    {
        return;
    }

    private function mkrespercent($total, $precision, $showtotals, $sort)
    {

    }

    private function mkreslist($total, $precision, $showtotals)
    {

    }

    private function mkreslisttext($rows)
    {

    }

    private function mkreslistdate($total, $precision, $showtotals)
    {

    }

    private function mkreslistnumeric($total, $precision)
    {

    }

    private function mkresavg($total, $precision, $showtotals, $length, $sort, $stravgvalue = '')
    {

    }

    private function mkrescount($rids, $rows, $precision, $length, $sort)
    {

    }
}

function sortavgasc($a, $b)
{
    if (isset($a->avg) && isset($b->avg)) {
        if ($a->avg < $b->avg) {
            return -1;
        } elseif ($a->avg > $b->avg) {
            return 1;
        }
        return 0;
    }
}

function sortavgdesc($a, $b)
{
    if (isset($a->avg) && isset($b->avg)) {
        if ($a->avg > $b->avg) {
            return -1;
        } elseif ($a->avg < $b->avg) {
            return 1;
        }
        return 0;
    }
}
