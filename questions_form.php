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
 * Filename : questions_form
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/sliclquestions/questiontypes.class.php');

class sliclquestions_questions_form extends moodleform
{
    public function __construct($action, $moveq=false)
    {
        $this->moveq = $moveq;
        return parent::__construct($action);
    }

    public function definition()
    {
        global $CFG, $sliclquestions, $SESSION, $OUTPUT, $DB;

        $sid = $sliclquestions->survey->id;
        $mform    =& $this->_form;

        $mform->addElement('header', 'questionhdr', get_string('addquestions', 'sliclquestions'));
        $mform->addHelpButton('questionhdr', 'questiontypes', 'sliclquestions');

        $stredit = get_string('edit', 'sliclquestions');
        $strremove = get_string('remove', 'sliclquestions');
        $strmove = get_string('move');
        $strmovehere = get_string('movehere');
        $stryes = get_string('yes');
        $strno = get_string('no');
        $strposition = get_string('position', 'sliclquestion');

        if (!isset($sliclquestions->questions)) {
            $sliclquestions->questions = array();
        }
        if ($this->moveq) {
            $moveqposition = $sliclquestions->questions[$this->moveq]->position;
        }

        $pos = 0;
        $select = '';
        if (!($qtypes = $DB->get_records_select_menu('sliclquestions_question_type', $select, null, '', 'typeid,type'))) {
            $qtypes = array();
        }
        // Needed for non-English languages.
        foreach ($qtypes as $key => $qtype) {
            $qtypes[$key] = sliclquestions_get_type($key);
        }
        natsort($qtypes);
        $addqgroup = array();
        $addqgroup[] =& $mform->createElement('select', 'type_id', '', $qtypes);

        // The 'sticky' type_id value for further new questions.
        if (isset($SESSION->sliclquestions->type_id)) {
                $mform->setDefault('type_id', $SESSION->sliclquestions->type_id);
        }

        $addqgroup[] =& $mform->createElement('submit', 'addqbutton', get_string('addselqtype', 'sliclquestions'));

        $sliclquestionshasdependencies = sliclquestions_has_dependencies($sliclquestions->questions);

        $mform->addGroup($addqgroup, 'addqgroup', '', ' ', false);

        if (isset($SESSION->sliclquestions->validateresults) &&  $SESSION->sliclquestions->validateresults != '') {
            $mform->addElement('static', 'validateresult', '', '<div class="qdepend warning">'.
                $SESSION->sliclquestions->validateresults.'</div>');
            $SESSION->sliclquestions->validateresults = '';
        }

        $qnum = 0;

        // JR skip logic :: to prevent moving child higher than parent OR parent lower than child
        // we must get now the parent and child positions.

        if ($sliclquestionshasdependencies) {
            $parentpositions = sliclquestions_get_parent_positions ($sliclquestions->questions);
            $childpositions = sliclquestions_get_child_positions ($sliclquestions->questions);
        }

        $mform->addElement('header', 'manageq', get_string('managequestions', 'sliclquestions'));
        $mform->addHelpButton('manageq', 'managequestions', 'sliclquestions');

        $mform->addElement('html', '<div class="qcontainer">');

        foreach ($sliclquestions->questions as $question) {

            $manageqgroup = array();

            $qid = $question->id;
            $tid = $question->type_id;
            $qtype = $question->type;
            $required = $question->required;

            // Does this questionnaire contain branching questions already?
            $dependency = '';
            if ($sliclquestionshasdependencies) {
                if ($question->dependquestion != 0) {
                    $parent = sliclquestions_get_parent ($question);
                    $dependency = '<strong>' . get_string('dependquestion', 'sliclquestion') . '</strong> : '.
                        $strposition . ' ' . $parent[$qid]['parentposition'] . ' (' . $parent[$qid]['parent'] . ')';
                }
            }

            $pos = $question->position;
            if ($tid != QUESPAGEBREAK && $tid != QUESSECTIONTEXT) {
                $qnum++;
            }

            // Needed for non-English languages JR.
            $qtype = '[' . sliclquestions_get_type($tid) . ']';
            $content = '';
            // If question text is "empty", i.e. 2 non-breaking spaces were inserted, do not display any question text.
            if ($question->content == '<p>  </p>') {
                $question->content = '';
            }
            if ($tid != QUESPAGEBREAK) {
                // Needed to print potential media in question text.
                $content = format_text(file_rewrite_pluginfile_urls($question->content, 'pluginfile.php',
                                $question->context->id, 'mod_sliclquestions', 'question', $question->id), FORMAT_HTML);
            }
            $moveqgroup = array();

            $spacer = $OUTPUT->pix_url('spacer');

            if (!$this->moveq) {
                $mform->addElement('html', '<div class="qn-container">'); // Begin div qn-container.
                $mextra = array('value' => $question->id,
                                'alt' => $strmove,
                                'title' => $strmove);
                $eextra = array('value' => $question->id,
                                'alt' => get_string('edit', 'questionnaire'),
                                'title' => get_string('edit', 'questionnaire'));
                $rextra = array('value' => $question->id,
                                'alt' => $strremove,
                                'title' => $strremove);

                if ($tid == QUESPAGEBREAK) {
                    $esrc = $CFG->wwwroot.'/mod/sliclquestions/images/editd.gif';
                    $eextra = array('disabled' => 'disabled');
                } else {
                    $esrc = $CFG->wwwroot.'/mod/sliclquestions/images/edit.gif';
                }

                if ($tid == QUESPAGEBREAK) {
                    $esrc = $spacer;
                    $eextra = array('disabled' => 'disabled');
                } else {
                    $esrc = $OUTPUT->pix_url('t/edit');
                }
                $rsrc = $OUTPUT->pix_url('t/delete');
                        $qreq = '';

                // Question numbers.
                $manageqgroup[] =& $mform->createElement('static', 'qnums', '',
                                '<div class="qnums">'.$strposition.' '.$pos.'</div>');

                // Need to index by 'id' since IE doesn't return assigned 'values' for image inputs.
                $manageqgroup[] =& $mform->createElement('static', 'opentag_'.$question->id, '', '');
                $msrc = $OUTPUT->pix_url('t/move');

                if ($sliclquestionshasdependencies) {
                    // Do not allow moving parent question at position #1 to be moved down if it has a child at position < 4.
                    if ($pos == 1) {
                        if (isset($childpositions[$qid])) {
                            $maxdown = $childpositions[$qid];
                            if ($maxdown < 4) {
                                $strdisabled = get_string('movedisabled', 'sliclquestions');
                                $msrc = $OUTPUT->pix_url('t/block');
                                $mextra = array('value' => $question->id,
                                                'alt' => $strdisabled,
                                                'title' => $strdisabled);
                                $mextra += array('disabled' => 'disabled');
                            }
                        }
                    }
                    // Do not allow moving or deleting a page break if immediately followed by a child question
                    // or immediately preceded by a question with a dependency and followed by a non-dependent question.
                    if ($tid == QUESPAGEBREAK) {
                        if ($nextquestion = $DB->get_record('sliclquestions_question', array('survey_id' => $sid,
                                        'position' => $pos + 1, 'deleted' => 'n' ), $fields = 'dependquestion, name, content') ) {
                            if ($previousquestion = $DB->get_record('sliclquestions_question', array('survey_id' => $sid,
                                            'position' => $pos - 1, 'deleted' => 'n' ),
                                            $fields = 'dependquestion, name, content')) {
                                if ($nextquestion->dependquestion != 0
                                                || ($previousquestion->dependquestion != 0
                                                    && $nextquestion->dependquestion == 0) ) {
                                    $strdisabled = get_string('movedisabled', 'sliclquestions');
                                    $msrc = $OUTPUT->pix_url('t/block');
                                    $mextra = array('value' => $question->id,
                                                    'alt' => $strdisabled,
                                                    'title' => $strdisabled);
                                    $mextra += array('disabled' => 'disabled');

                                    $rsrc = $msrc;
                                    $strdisabled = get_string('deletedisabled', 'sliclquestions');
                                    $rextra = array('value' => $question->id,
                                                    'alt' => $strdisabled,
                                                    'title' => $strdisabled);
                                    $rextra += array('disabled' => 'disabled');
                                }
                            }
                        }
                    }
                }
                $manageqgroup[] =& $mform->createElement('image', 'movebutton['.$question->id.']',
                                $msrc, $mextra);
                $manageqgroup[] =& $mform->createElement('image', 'editbutton['.$question->id.']', $esrc, $eextra);
                $manageqgroup[] =& $mform->createElement('image', 'removebutton['.$question->id.']', $rsrc, $rextra);

                if ($tid != QUESPAGEBREAK && $tid != QUESSECTIONTEXT) {
                    if ($required == 'y') {
                        $reqsrc = $OUTPUT->pix_url('t/stop');
                        $strrequired = get_string('required', 'sliclquestions');
                    } else {
                        $reqsrc = $OUTPUT->pix_url('t/go');
                        $strrequired = get_string('notrequired', 'sliclquestions');
                    }
                    $strrequired .= ' '.get_string('clicktoswitch', 'sliclquestions');
                    $reqextra = array('value' => $question->id,
                                    'alt' => $strrequired,
                                    'title' => $strrequired);
                    $manageqgroup[] =& $mform->createElement('image', 'requiredbutton['.$question->id.']', $reqsrc, $reqextra);
                }
                $manageqgroup[] =& $mform->createElement('static', 'closetag_'.$question->id, '', '');

            } else {
                $manageqgroup[] =& $mform->createElement('static', 'qnum', '',
                                '<div class="qnums">'.$strposition.' '.$pos.'</div>');
                $moveqgroup[] =& $mform->createElement('static', 'qnum', '', '');

                $display = true;
                if ($sliclquestionshasdependencies) {
                    // Prevent moving child to higher position than its parent.
                    if (isset($parentpositions[$this->moveq])) {
                        $maxup = $parentpositions[$this->moveq];
                        if ($pos <= $maxup) {
                            $display = false;
                        }
                    }
                    // Prevent moving parent to lower position than its (first) child.
                    if (isset($childpositions[$this->moveq])) {
                        $maxdown = $childpositions[$this->moveq];
                        if ($pos >= $maxdown) {
                            $display = false;
                        }
                    }
                }

                $typeid = $DB->get_field('sliclquestions_question', 'type_id', array('id' => $this->moveq));

                if ($display) {
                    // Do not move a page break to first position.
                    if ($typeid == QUESPAGEBREAK && $pos == 1) {
                        $manageqgroup[] =& $mform->createElement('static', 'qnums', '', '');
                    } else {
                        if ($this->moveq == $question->id) {
                            $moveqgroup[] =& $mform->createElement('cancel', 'cancelbutton', get_string('cancel'));
                        } else {
                            $mextra = array('value' => $question->id,
                                            'alt' => $strmove,
                                            'title' => $strmovehere.' (position '.$pos.')');
                            $msrc = $OUTPUT->pix_url('movehere');
                            $moveqgroup[] =& $mform->createElement('static', 'opentag_'.$question->id, '', '');
                            $moveqgroup[] =& $mform->createElement('image', 'moveherebutton['.$pos.']', $msrc, $mextra);
                            $moveqgroup[] =& $mform->createElement('static', 'closetag_'.$question->id, '', '');
                        }
                    }
                } else {
                    $manageqgroup[] =& $mform->createElement('static', 'qnums', '', '');
                    $moveqgroup[] =& $mform->createElement('static', 'qnums', '', '');
                }
            }
            if ($question->name) {
                $qname = '('.$question->name.')';
            } else {
                $qname = '';
            }
            $manageqgroup[] =& $mform->createElement('static', 'qtype_'.$question->id, '', $qtype);
            $manageqgroup[] =& $mform->createElement('static', 'qname_'.$question->id, '', $qname);

            if ($dependency) {
                $mform->addElement('static', 'qdepend_'.$question->id, '', '<div class="qdepend">'.$dependency.'</div>');
            }
            if ($tid != QUESPAGEBREAK) {
                if ($tid != QUESSECTIONTEXT) {
                    $qnumber = '<div class="qn-info"><h2 class="qn-number">'.$qnum.'</h2></div>';
                } else {
                    $qnumber = '';
                }
            }

            if ($this->moveq && $pos < $moveqposition) {
                $mform->addGroup($moveqgroup, 'moveqgroup', '', '', false);
            }
            if ($this->moveq) {
                if ($this->moveq == $question->id && $display) {
                    $mform->addElement('html', '<div class="moving" title="'.$strmove.'">'); // Begin div qn-container.
                } else {
                    $mform->addElement('html', '<div class="qn-container">'); // Begin div qn-container.
                }
            }
            $mform->addGroup($manageqgroup, 'manageqgroup', '', '&nbsp;', false);
            if ($tid != QUESPAGEBREAK) {
                $mform->addElement('static', 'qcontent_'.$question->id, '',
                    $qnumber.'<div class="qn-question">'.$content.'</div>');
            }
            $mform->addElement('html', '</div>'); // End div qn-container.

            if ($this->moveq && $pos >= $moveqposition) {
                $mform->addGroup($moveqgroup, 'moveqgroup', '', '', false);
            }
        }

        if ($this->moveq) {
            $mform->addElement('hidden', 'moveq', $this->moveq);
        }

        // Hidden fields.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'sid', 0);
        $mform->setType('sid', PARAM_INT);
        $mform->addElement('hidden', 'action', 'main');
        $mform->setType('action', PARAM_RAW);
        $mform->setType('moveq', PARAM_RAW);

        $mform->addElement('html', '</div>');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }

}

class sliclquestions_edit_question_form extends moodleform
{
    public function definition()
    {
        global $CFG, $COURSE, $sliclquestions, $question, $questionnairerealms, $SESSION;
        global $DB;

        // The 'sticky' required response value for further new questions.
        if (isset($SESSION->sliclquestions->required) && !isset($question->qid)) {
            $question->required = $SESSION->sliclquestions->required;
        }
        if (!isset($question->type_id)) {
            print_error('undefinedquestiontype', 'sliclquestions');
        }

        // Initialize question type defaults.
        switch ($question->type_id) {
            case QUESTEXT:
                $deflength = 20;
                $defprecise = 25;
                $lhelpname = 'fieldlength';
                $phelpname = 'maxtextlength';
                break;
            case QUESESSAY:
                $deflength = '';
                $defprecise = '';
                $lhelpname = 'textareacolumns';
                $phelpname = 'textarearows';
                break;
            case QUESCHECK:
                $deflength = 0;
                $defprecise = 0;
                $lhelpname = 'minforcedresponses';
                $phelpname = 'maxforcedresponses';
                $olabelname = 'possibleanswers';
                $ohelpname = 'checkboxes';
                break;
            case QUESRADIO:
                $deflength = 0;
                $defprecise = 0;
                $lhelpname = 'alignment';
                $olabelname = 'possibleanswers';
                $ohelpname = 'radiobuttons';
                break;
            case QUESRATE:
                $deflength = 5;
                $defprecise = 0;
                $lhelpname = 'numberscaleitems';
                $phelpname = 'kindofratescale';
                $olabelname = 'possibleanswers';
                $ohelpname = 'ratescale';
                break;
            case QUESNUMERIC:
                $deflength = 10;
                $defprecise = 0;
                $lhelpname = 'maxdigitsallowed';
                $phelpname = 'numberofdecimaldigits';
                break;
            case QUESDROP:
                $deflength = 0;
                $defprecise = 0;
                $olabelname = 'possibleanswers';
                $ohelpname = 'dropdown';
                break;
            default:
                $deflength = 0;
                $defprecise = 0;
        }

        $defdependquestion = 0;
        $defdependchoice = 0;
        $dlabelname = 'dependquestion';

        $mform    =& $this->_form;

        // Display different messages for new question creation and existing question modification.
        if (isset($question->qid)) {
            $streditquestion = get_string('editquestion', 'sliclquestions', sliclquestions_get_type($question->type_id));
        } else {
            $streditquestion = get_string('addnewquestion', 'sliclquestions', sliclquestions_get_type($question->type_id));
        }
        switch ($question->type_id) {
            case QUESYESNO:
                $qtype = 'yesno';
                break;
            case QUESTEXT:
                $qtype = 'textbox';
                break;
            case QUESESSAY:
                $qtype = 'essaybox';
                break;
            case QUESRADIO:
                $qtype = 'radiobuttons';
                break;
            case QUESCHECK:
                $qtype = 'checkboxes';
                break;
            case QUESDROP:
                $qtype = 'dropdown';
                break;
            case QUESRATE:
                $qtype = 'ratescale';
                break;
            case QUESDATE:
                $qtype = 'date';
                break;
            case QUESNUMERIC:
                $qtype = 'numeric';
                break;
            case QUESSECTIONTEXT:
                $qtype = 'sectiontext';
                break;
            case QUESPAGEBREAK:
                $qtype = 'sectionbreak';
        }

        $mform->addElement('header', 'questionhdredit', $streditquestion);
        $mform->addHelpButton('questionhdredit', $qtype, 'sliclquestions');

        // Name and required fields.
        if ($question->type_id != QUESSECTIONTEXT && $question->type_id != '') {
            $stryes = get_string('yes');
            $strno  = get_string('no');

            $mform->addElement('text', 'name', get_string('optionalname', 'sliclquestions'),
                            array('size' => '30', 'maxlength' => '30'));
            $mform->setType('name', PARAM_TEXT);
            $mform->addHelpButton('name', 'optionalname', 'sliclquestions');

            $reqgroup = array();
            $reqgroup[] =& $mform->createElement('radio', 'required', '', $stryes, 'y');
            $reqgroup[] =& $mform->createElement('radio', 'required', '', $strno, 'n');
            $mform->addGroup($reqgroup, 'reqgroup', get_string('required', 'sliclquestions'), ' ', false);
            $mform->addHelpButton('reqgroup', 'required', 'sliclquestions');
        }

        // Length field.
        if ($question->type_id == QUESYESNO || $question->type_id == QUESDROP || $question->type_id == QUESDATE ||
            $question->type_id == QUESSECTIONTEXT) {
            $mform->addElement('hidden', 'length', $deflength);
        } else if ($question->type_id == QUESRADIO) {
            $lengroup = array();
            $lengroup[] =& $mform->createElement('radio', 'length', '', get_string('vertical', 'sliclquestions'), '0');
            $lengroup[] =& $mform->createElement('radio', 'length', '', get_string('horizontal', 'sliclquestions'), '1');
            $mform->addGroup($lengroup, 'lengroup', get_string($lhelpname, 'sliclquestions'), ' ', false);
            $mform->addHelpButton('lengroup', $lhelpname, 'sliclquestions');
        } else if ($question->type_id == QUESTEXT || $question->type_id == QUESRATE) {
            $question->length = isset($question->length) ? $question->length : $deflength;
            $mform->addElement('text', 'length', get_string($lhelpname, 'sliclquestions'), array('size' => '1'));
            $mform->setType('length', PARAM_TEXT);
            $mform->addHelpButton('length', $lhelpname, 'sliclquestions');
        } else if ($question->type_id == QUESESSAY) {
            $responseformats = array(
                            "0" => get_string('formateditor', 'sliclquestions'),
                            "1" => get_string('formatplain', 'sliclquestions'));
            $mform->addElement('select', 'precise', get_string('responseformat', 'sliclquestions'), $responseformats);
        } else if ($question->type_id == QUESCHECK || $question->type_id == QUESNUMERIC) {
            $question->length = isset($question->length) ? $question->length : $deflength;
            $mform->addElement('text', 'length', get_string($lhelpname, 'sliclquestions'), array('size' => '1'));
        }

        $mform->setType('length', PARAM_INT);

        // Precision field.
        if ($question->type_id == QUESYESNO || $question->type_id == QUESDROP || $question->type_id == QUESDATE ||
            $question->type_id == QUESSECTIONTEXT || $question->type_id == QUESRADIO) {
            $mform->addElement('hidden', 'precise', $defprecise);
        } else if ($question->type_id == QUESRATE) {
            $precoptions = array("0" => get_string('normal', 'sliclquestions'),
                                 "1" => get_string('notapplicablecolumn', 'sliclquestions'),
                                 "2" => get_string('noduplicates', 'sliclquestions'),
                                 "3" => get_string('osgood', 'sliclquestions'));
            $mform->addElement('select', 'precise', get_string($phelpname, 'sliclquestions'), $precoptions);
            $mform->addHelpButton('precise', $phelpname, 'sliclquestions');
        } else if ($question->type_id == QUESESSAY) {
            $choices = array();
            for ($lines = 5; $lines <= 40; $lines += 5) {
                $choices[$lines] = get_string('nlines', 'sliclquestions', $lines);
            }
            $mform->addElement('select', 'length', get_string('responsefieldlines', 'sliclquestions'), $choices);
        } else if ($question->type_id == QUESCHECK || $question->type_id == QUESNUMERIC || $question->type_id == QUESTEXT) {
            $question->precise = isset($question->precise) ? $question->precise : $defprecise;
            $mform->addElement('text', 'precise', get_string($phelpname, 'sliclquestions'), array('size' => '1'));
        }

        $mform->setType('precise', PARAM_INT);

        // Dependence fields.

        if ($sliclquestions->navigate) {
            $position = isset($question->position) ? $question->position : count($sliclquestions->questions) + 1;
            $dependencies = sliclquestions_get_dependencies($sliclquestions->questions, $position);
            $canchangeparent = true;
            if (count($dependencies) > 1) {
                if (isset($question->qid)) {
                    $haschildren = sliclquestions_get_descendants ($sliclquestions->questions, $question->qid);
                    if (count($haschildren) !== 0) {
                        $canchangeparent = false;
                        $parent = sliclquestions_get_parent ($question);
                        $fixeddependency = $parent [$question->id]['parent'];
                    }
                }
                if ($canchangeparent) {
                    $question->dependquestion = isset($question->dependquestion) ? $question->dependquestion.','.
                                    $question->dependchoice : '0,0';
                    $group = array($mform->createElement('selectgroups', 'dependquestion', '', $dependencies) );
                    $mform->addGroup($group, 'selectdependency', get_string('dependquestion', 'sliclquestions'), '', false);
                    $mform->addHelpButton('selectdependency', 'dependquestion', 'sliclquestions');
                } else {
                    $mform->addElement('static', 'selectdependency', get_string('dependquestion', 'sliclquestions'),
                                    '<div class="dimmed_text">'.$fixeddependency.'</div>');
                }
                $mform->addHelpButton('selectdependency', 'dependquestion', 'sliclquestions');
            }
        }

        // Content field.
        $modcontext    = $this->_customdata['modcontext'];
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext' => true, 'context' => $modcontext);
        $mform->addElement('editor', 'content', get_string('text', 'sliclquestions'), null, $editoroptions);
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', null, 'required', null, 'client');

        // Options section:
        // has answer options ... so show that part of the form.
        if ($DB->get_field('sliclquestions_question_type', 'has_choices', array('typeid' => $question->type_id)) == 'y' ) {
            if (!empty($question->choices)) {
                $numchoices = count($question->choices);
            } else {
                $numchoices = 0;
            }

            if (!empty($question->choices)) {
                foreach ($question->choices as $choiceid => $choice) {
                    if (!empty($question->allchoices)) {
                        $question->allchoices .= "\n";
                    }
                    $question->allchoices .= $choice->content;
                }
            } else {
                $question->allchoices = '';
            }

            $mform->addElement('html', '<div class="qoptcontainer">');

            $options = array('wrap' => 'virtual', 'class' => 'qopts');
            $mform->addElement('textarea', 'allchoices', get_string('possibleanswers', 'sliclquestions'), $options);
            $mform->setType('allchoices', PARAM_RAW);
            $mform->addRule('allchoices', null, 'required', null, 'client');
            $mform->addHelpButton('allchoices', $ohelpname, 'sliclquestions');

            $mform->addElement('html', '</div>');

            $mform->addElement('hidden', 'num_choices', $numchoices);
            $mform->setType('num_choices', PARAM_INT);
        }

        // Hidden fields.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'qid', 0);
        $mform->setType('qid', PARAM_INT);
        $mform->addElement('hidden', 'sid', 0);
        $mform->setType('sid', PARAM_INT);
        $mform->addElement('hidden', 'type_id', $question->type_id);
        $mform->setType('type_id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'question');
        $mform->setType('action', PARAM_RAW);

        // Buttons.
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        if (isset($question->qid)) {
            $buttonarray[] = &$mform->createElement('submit', 'makecopy', get_string('saveasnew', 'sliclquestions'));
        }
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // If this is a rate question.
        if ($data['type_id'] == QUESRATE) {
            if ($data['length'] < 2) {
                $errors["length"] = get_string('notenoughscaleitems', 'sliclquestions');
            }
            // If this is a rate question with no duplicates option.
            if ($data['precise'] == 2 ) {
                $allchoices = $data['allchoices'];
                $allchoices = explode("\n", $allchoices);
                $nbnameddegrees = 0;
                $nbvalues = 0;
                foreach ($allchoices as $choice) {
                    if ($choice && !preg_match("/^[0-9]{1,3}=/", $choice)) {
                            $nbvalues++;
                    }
                }
                if ($nbvalues < 2) {
                    $errors["allchoices"] = get_string('noduplicateschoiceserror', 'sliclquestions');
                }
            }
        }
        return $errors;
    }
}
