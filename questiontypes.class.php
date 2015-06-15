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
define('QUESCHOOSE', 0);
define('QUESYESNO', 1);
define('QUESTEXT', 2);
define('QUESESSAY', 3);
define('QUESRADIO', 4);
define('QUESCHECK', 5);
define('QUESDROP', 6);
define('QUESRATE', 8);
define('QUESDATE', 9);
define('QUESNUMERIC', 10);
define('QUESPAGEBREAK', 99);
define('QUESSECTIONTEXT', 100);

class sliclquestions_question
{
    private $id = 0;
    private $name = '';
    private $type = '';
    private $choices = array();
    private $typeid = 0;
    private $responsetable = '';

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
            $this->id = $question->id;

            $this->typeid = $question->type_id;
            $this->responsetable = $qtypes[$this->typeid]->response_table;
            if ($qtypes[$this->typeid]->has_choices == 'y') {
                $this->get_choices();
            }
        }
    }

    public function insert_response($rid)
    {
        $method = 'insert_' . $this->responsetable;
        if (method_exists($this, $method)) {
            return $this->$method($rid);
        }
        return false;
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
}