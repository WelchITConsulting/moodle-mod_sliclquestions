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
        if (method_exists($this, $method)) {
            return $this->$method($rid);
        }
        return false;
    }

    public function render($formdata, $descendantsdata, $qnum = '', $blankquestionnaire = false)
    {
        global $qtypenames;
        $method = 'render_' . $qtypenames[$this->type_id];
        if (method_exists($this, $method)) {
            $this->render_start($qnum, $formdata, $descendantsdata);
            $this->$method($formdata, $descendantsdata, $blankquestionnaire);
            $this->render_end();
        } else {
            print_error('displaymethod', 'sliclquestions');
        }
    }

    public function is_required()
    {
        return $this->required;
    }

    public function get_type_id()
    {
        return $this->type_id;
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

    private function render_start($qnum, $formdata = '')
    {
        global $PAGE, $SESSION;

        $currenttab = $SESSEION->sliclquestions_current_tab;
        $pagetype = $PAGE->pagetype;
        $skippedquestion = false;
        $skppedclass = '';

    }

    private function render_end()
    {
        echo html_writer::end_div()
           . html_writer::end_tag('fieldset');
    }

    private function render_yesno($data, $descendantdata, $blankquestionnaire = false)
    {

    }

    private function render_text($data, $descendantdata, $blankquestionnaire = false)
    {
        $params = array('type'       => 'text',
                        'name'       => 'q' . $this->id,
                        'id'         => $this->type . $this->id,
                        'size'       => $this->length,
                        'value'      => (isset($data->{'q' . $this->id}) ? stripslashes($data->{'q' . $this->id}) : ''),
                        'onkeypress' => 'return event.keycode != 13');
        if ($this->precise > 0) {
            $params['maxlength'] = $this->precise;
        }
        echo html_writer::empty_tag('input', $params);
    }

    private function render_essay($data, $descendantdata, $blankquestionnaire = false)
    {
        $rows = 15;
        if ($this->precise == 0) {
            $canusehtmleditor = true;
            $rows = (($this->length == 0) ? $rows : $this->length);
        } else {
            $canusehtmleditor = false;
            $rows = (($this->precise > 1) ? $this->precise : $this->length);
        }
        $name = 'q' . $this->id;
        if ($canusehtmleditor) {
            $editor = editors_get_preferred_editor();
            $editor->use_editor($name, array('subdirs' => 0, 'maxbytes' => 0,
                                             'maxfiles' => -1, 'context' => $this->context,
                                             'noclean' => 0, 'trusttext' => 0));
        }
        echo html_writer::tag('textarea',
                              (isset($data->$name) ? $data->$name : ''),
                              array('name' => $name,
                                    'id'   => $name,
                                    'rows' => $rows,
                                    'cols' => 80));
    }

    private function render_radio($data, $descendantdata, $blankquestionnaire = false)
    {

    }

    private function render_check($data, $descendantdata, $blankquestionnaire = false)
    {

    }

    private function render_drop($data, $descendantdata, $blankquestionnaire = false)
    {

    }

    private function render_rate($data, $descendantdata, $blankquestionnaire = false)
    {
        $name = 'q' . $this->id;
        if (!empty($data) && (!isset($data->$name) || !is_array($data->name))) {
            $data->$name = array();
        }
        $isna         = $this->precise = 1;
        $osgood       = $this->precise = 3;
        $n            = array();
        $v            = array();
        $nameddegrees = 0;
        $maxndlen     = 0;
        $nocontent    = false;
        foreach($this->choices as $cid => $choice) {
            if (empty($choice->content)) {
                $nocontent = true;
                $contents  = sliclquestions_choice_values($content);
                if ($contents->modname) {
                    $choice->content = $contents->text;
                }
            } else {
                if (preg_match('/^([0-9]{1,3})=(.*)$/', $choice->content, $ndd)) {
                    $n[$nameddegrees] = format_text($ndd[2], FORMAT_HTML);
                    if (strlen($n[$nameddegrees]) > $maxndlen) {
                        $maxndlen = strlen($n[$nameddegrees]);
                    }
                    $v[$nameddegrees] = $ndd[1];
                    $this->choices[$cid] = '';
                    $nameddegrees++;
                }
            }
        }
        echo html_writer::start_tag('table', array('style' => 'width:' . ($nocontent ? '50%' : '99.9%')))
           . html_writer::start_tag('tbody')
           . html_writer::start_tag('tr');
        if ($osgood) {
            if ($maxndlen < 4) {
                $width = '45%';
            } elseif ($maxndlen < 13) {
                $width = '40%';
            } else {
                $width = '30%';
            }
            $colwidth  = ((100 - ($width * 2)) / $this->length) . '%';
            $textalign = 'right';
        } elseif ($nocontent) {
            $width     = '0%';
            $colwidth  = (100 / $this->length) . '%';
            $textalign = 'right';
        } else {
            $width     = '59%';
            $colwidth  = (40 / $this->length) . '%';
            $textalign = 'left';
        }
        echo html_writer::tag('td', '', array('style' => 'width:' . $width));
        if ($isna) {
            $na = get_string('notapplicable', 'sliclquestions');
        } else {
            $na = '';
        }
        if ($this->precise == 2) {
            $order     = true;
            $nbchoices = $this->length;
        } else {
            $order     = false;
            $nbchoices = count($this->choices) - $nameddegrees;
        }
        if (($nbchoices > 1) && ($this->precise != 2) && !$blankquestionnaire) {
            echo html_writer::tag('td', '');
        }
        for ($j = 0; $j < $this->length; $j++) {
            if (isset($n[$j])) {
                $str = $n[$j];
                $val = $v[$j];
            } else {
                $str = $j + 1;
                $val = $j + 1;
            }
            if ($blankquestionnaire) {
                $val = html_writer::empty_tag('br')
                     . '('
                     . $val
                     . ')';
            } else {
                $val = '';
            }
            echo html_writer::tag('td', $str . $val, array('style' => 'width:' . $colwidth . ';text-align:center',
                                                           'class' => 'smalltext'));
        }
        if ($na) {
            echo html_writer::tag('td', $na . array('style' => 'width:' . $colwidth . ';text-align:center',
                                                    'class' => 'smalltext'));
        }
        echo html_writer::end_tag('tr');
        $num = 0;
        foreach($this->choices as $cid => $choice) {
            $str = $name . '_' . $cid;
            $num += (isset($data->$str) && ($data->$str != -999));
        }
        $notcomplete = false;
        if (($num != $nbchoices) && ($num != 0)) {
            notify(get_string('checkallradiobuttons', 'sliclquestions', $nbchoices));
            $notcomplete = true;
        }
        foreach($this->choices as $cid => $choice) {
            if (isset($choice->content)) {
                $str = $name . '_' . $cid;
                $content = $choice->content;
                if ($osgood) {
                    list($content, $contentright) = preg_split('/[|]/', $this->content);
                }
                echo html_writer::start_tag('tr', array('class' => 'raterow'))
                   . html_writer::tag('td', format_text($content, FORMAT_HTML),
                                      array('style' => 'text-align:' . $textalign));
                $bg = 'c0';
                if (($nbchoices > 1) && ($this->precise != 2) && !$blankquestionnaire) {
                    $completeclass = 'notanswered';
                    $title         = '';
                    if ($notcomplete && isset($data->$str) && ($data->$str == -999)) {
                        $completeclass = 'notcomplete';
                        $title         = get_string('pleasecomplete', 'sliclquestions');
                    }
                    $inpparams = array('name'    => $str,
                                       'type'    => 'radio',
                                       'value'   => '-999',
                                       'checked' => 'checked');
                    if ($order) {
                        $inpparams['onclick'] = 'other_rate_uncheck(name,value)';
                    }
                    echo html_writer::start_tag('td', array('title' => $title,
                                                            'class' => $completeclass,
                                                            'style' => 'width:1%'))
                       . html_writer::empty_tag('input', $inpparams)
                       . html_writer::end_tag('td');
                }
                for ($j = 0; $j < $this->length + $isna; $j++) {
                    $inpparams = array('name'    => $str,
                                       'type'    => 'radio',
                                       'value'   => (($j < $this->length) ? $j : -1));
                    if (isset($data->$str) && (($j == $data->$str) || (($j == $this->length) && ($data->$str == -1)))) {
                        $params['checked'] = 'checked';
                    }
                    if ($blankquestionnaire) {
                        $params['disabled'] = 'disabled';
                    }
                    echo html_writer::start_tag('td', array('style' => 'text-align:center',
                                                            'class' => $bg . ' raterow'))
                       . html_writer::tag('span',
                                          get_string('option', 'sliclquestions', ($j + 1)),
                                          array('class' => 'accesshide'))
                       . html_writer::empty_tag('input', $inpparams)
                       . html_writer::end_tag('td');
                    $bg = (($bg == 'c0') ? 'c1' : 'c0');
                }
                if ($osgood) {
                    echo html_writer::tag('td', format_text($contentright, FORMAT_HTML));
                }
                echo html_writer::end_tag('tr');
            }
        }
        echo html_writer::end_tag('tbody')
           . html_writer::end_tag('table');
    }

    private function render_date($data, $descendantdata, $blankquestionnaire = false)
    {

    }

    private function render_numeric($data, $descendantdata, $blankquestionnaire = false)
    {

    }

    private function render_pagebreak($data, $descendantdata, $blankquestionnaire = false)
    {

    }

    private function render_sectiontext($data, $descendantdata, $blankquestionnaire = false)
    {

    }
}
