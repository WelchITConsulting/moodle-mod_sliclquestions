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
 * Filename : sliclquestiontypes
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 25 Jun 2015
 */

define('SLICLQUESCHOOSE',         0);
define('SLICLQUESYESNO',          1);
define('SLICLQUESTEXT',           2);
define('SLICLQUESESSAY',          3);
define('SLICLQUESRADIO',          4);
define('SLICLQUESCHECK',          5);
define('SLICLQUESDROP',           6);
define('SLICLQUESRATE',           8);
define('SLICLQUESDATE',           9);
define('SLICLQUESNUMERIC',       10);
define('SLICLQUESPAGEBREAK',     99);
define('SLICLQUESSECTIONTEXT',  100);

global $qtypenames;
$qtypenames = array(SLICLQUESYESNO          => 'yesno',
                    SLICLQUESTEXT           => 'text',
                    SLICLQUESESSAY          => 'essay',
                    SLICLQUESRADIO          => 'radio',
                    SLICLQUESCHECK          => 'check',
                    SLICLQUESDROP           => 'drop',
                    SLICLQUESRATE           => 'rate',
                    SLICLQUESDATE           => 'date',
                    SLICLQUESNUMERIC        => 'numeric',
                    SLICLQUESPAGEBREAK      => 'pagebreak',
                    SLICLQUESSECTIONTEXT    => 'sectiontext');

class sliclquestions_question
{
    private $id             = 0;
    private $name           = '';
    private $dependquestion = 0;
    private $dependchoice   = '';
    private $type           = '';
    private $choices        = array();
    private $responsetable  = '';
    private $length         = 0;
    private $precise        = 0;
    private $position       = 0;
    private $content        = '';
    private $required       = false;
    private $deleted        = false;
    private $context        = '';

    public function __constuct($id = 0, $question = null, $context = null)
    {
        global $DB;
        static $qtypes = null;

        if (is_null($qtypes)) {
            $qtypes = $DB->get_records('sliclquestions_question_type', array(), 'typeid',
                                       'typeid, type, has_choices, response_table');
        }
        if ($id) {
            $question = $DB->get_record('sliclquestions_question', array('id' => $id));
        }
        if (is_object($question)) {
            $this->id             = $question->id;
            $this->name           = $question->name;
            $this->dependquestion = $question->dependquestion;
            $this->dependchoice   = $question->dependchoice;
            $this->length         = $question->length;
            $this->precise        = $question->precise;
            $this->position       = $question->position;
            $this->content        = $question->content;
            $this->required       = $question->required;
            $this->deleted        = $question->deleted;
            $this->type_id        = $question->type_id;
            $this->type           = $qtypes[$this->type_id];
            $this->responsetable  = $qtypes[$this->type_id]->response_table;
            if ($qtypes[$this->type->id]->has_choices) {
                $this->get_choices();
            }
        }
        $this->context = $context;
    }

    public function insert_response($rid)
    {
        $method = 'insert_' . $this->responsetable;
        if (  method_exists($this, $method)) {
            return $this->$method($rid);
        }
        return false;
    }

    public function survey_display($formdata, $descendantsdata, $qnum = '', $blankquestionnaire = false)
    {
        global $qtypenames;
        $method = $qtypenames[$this->type_id] . '_survey_display';
        if (method_exists($this, $method)) {
            $this->questionstart_survey_display($qnum, $formdata, $descendantsdata);
            $this->$method($formdata, $descendantsdata, $blankquestionnaire);
            $this->questionend_survey_display($qnum);
        } else {
            print_error('displaymethod', 'sliclquestions');
        }
    }









    /**************************************************************************
     * Private Functions
     **************************************************************************/

    /**
     * Fetch the question choices from the database
     *
     * @global XMLDB $DB Moodle database interface
     */
    private function get_choices()
    {
        global $DB;
        if ($choices = $DB->get_records('sliclquestions_quest_choice', array('question_id' => $this->id), 'is ASC')) {
            foreach($choices as $choice) {
                $this->choices[$choice->id] = new stdClass();
                $this->choices[$choice->id]->content = $choice->content;
                $this->choices[$choice->id]->value   = $choice->value;
            }
        } else {
            $this->choices = array();
        }
    }

    private function insert_response_bool($rid)
    {
        $val = optional_param('q' . $this->id, '', PARAM_ALPHANUMEXT);
        if (!empty($val)) {
            $rec = new stdClass();
            $rec->choice_id = $val;
            return $this->insert_response_to_database($rid, $rec);
        }
        return false;
    }

    private function insert_response_text($rid)
    {
        $val = optional_param('q' . $this->id, '', PARAM_CLEAN);
        if (!empty($val)) {
            $rec = new stdClass();
            $rec->response = $val;
            return $this->insert_response_to_database($rid, $rec);
        }
        return false;
    }

    private function insert_response_date($rid)
    {
        $val = optional_param('q' . $this->id, '', PARAM_CLEAN);
        if ($val = $this->check_date($val)) {
            $rec = new stdClass();
            $rec->response = $val;
            return $this->insert_response_to_database($rid, $rec);
        }
        return false;
    }

    private function insert_resp_single($rid)
    {
//        $val = optional_param('q' . $this->id, '', PARAM_CLEAN);
//        if (!empty($val)) {
//            $rec = new stdClass();
//            $rec->response = $val;
//            return $this->insert_response_to_database($rid, $rec);
//        }
        return false;
    }

    private function insert_resp_multiple($rid)
    {
//        $val = optional_param('q' . $this->id, '', PARAM_CLEAN);
//        if (!empty($val)) {
//            $rec = new stdClass();
//            $rec->response = $val;
//            return $this->insert_response_to_database($rid, $rec);
//        }
        return false;
    }

    private function insert_response_rank($rid)
    {
        $resid = false;
        foreach($this->choices as $cid => $choice) {
            $other = optional_param('q' . $this->id . '_'. $cid, null, PARAM_CLEAN);
            if (!empty($other)) {
                if ($other == get_string('notapplicable', 'sliclquestions')) {
                    $rank = -1;
                } else {
                    $rank = intval($other);
                }
                $rec = new stdClass();
                $rec->choice_id = $cid;
                $rec->rank      = $rank;
                $resid = $this->insert_response_to_database($rid, $rec);
                break;
            }
        }
        return $resid;
    }




    private function insert_response_to_database($rid, $obj)
    {
        global $DB;
        $obj->response_id = $rid;
        $rec->question_id = $this->id;
        return $DB->insert_record('sliclquestions_' . $this->responsetable, $obj);
    }

    private function check_date($thisdate)
    {
        $dateformat = get_string('strfdate', 'sliclquestions');
        if (preg_match('/(%[mdyY])(.+)(%[mdyY])(.+)(%[mdyY])/', $dateformat, $matches)) {
            $datepieces = explode($matches[2], $thisdate);
            foreach($datepieces as $datepiece) {
                if (!is_numeric($datepiece)) {
                    return false;
                }
            }
            $dateorder = strtolower(preg_replace('/[^dmy]/i', '', $dateformat));
            $numpieces = count($datepieces);
            if ($numpieces == 1) {
                switch($dateorder) {
                    case 'dmy':
                    case 'mdy':
                        $datepieces[2] = $datepieces[0];
                        $datepieces[0] = '1';
                        $datepieces[1] = '1';
                        break;
                    case 'ymd':
                        $datepieces[1] = '1';
                        $datepieces[2] = '1';
                        break;
                }
            } elseif ($numpieces == 2) {
                switch($dateorder) {
                    case 'dmy':
                        $datepieces[2] = $datepieces[1];
                        $datepieces[1] = $datepieces[0];
                        $datepieces[0] = '1';
                    case 'mdy':
                        $datepieces[2] = $datepieces[1];
                        $datepieces[1] = '1';
                        break;
                    case 'ymd':
                        $datepieces[2] = '1';
                        break;
                }
            }
            if (count($datepieces) > 1) {

                if ($matches[1] == '%m') {
                    $month = $datepieces[0];
                } elseif ($matches[1] == '%d') {
                    $day = $datepieces[0];
                } elseif ($matches[1] == '%y') {
                    $year = strftime('%C') . $datepieces[0];
                } elseif ($matches[1] == '%Y') {
                    $year = $datepieces[0];
                }

                if ($matches[3] == '%m') {
                    $month = $datepieces[1];
                } elseif ($matches[3] == '%d') {
                    $day = $datepieces[1];
                } elseif ($matches[3] == '%y') {
                    $year = strftime('%C') . $datepieces[1];
                } elseif ($matches[3] == '%Y') {
                    $year = $datepieces[1];
                }

                if ($matches[5] == '%m') {
                    $month = $datepieces[2];
                } elseif ($matches[5] == '%d') {
                    $day = $datepieces[2];
                } elseif ($matches[5] == '%y') {
                    $year = strftime('%C') . $datepieces[2];
                } elseif ($matches[5] == '%Y') {
                    $year = $datepieces[2];
                }

                $month = min(12, $month);
                $month = max(1, $month);
                if ($month == 2) {
                    $day = min(29, $day);
                } elseif (($month == 4) || ($month == 6) || ($month == 9) || ($month == 11)) {
                    $day = min(30, $day);
                } else {
                    $day = min(31, $day);
                }
                $day = max(1, $day);
                if (!$thisdate = gmktime(0, 0, 0, $month, $day, $year)) {
                    return false;
                } else {
                    if ($insert) {
                        $thisdate = trim(userdate($thisdate, '%Y-%m-%d', '1', false));
                    } else {
                        $thisdate = trim(userdate($thisdate, $dateformat, '1', false));
                    }
                }
                return $thisdate;
            }
        }
        return false;
    }
}
