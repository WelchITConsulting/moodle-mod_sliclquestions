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
 * Filename : fbsections
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sliclquestions/sliclquestions.class.php');

$id     = optional_param('id', 0, PARAM_INT);
$sid    = optional_param('sid', 0, PARAM_INT);

if ($id) {
    if (! $cm = get_coursemodule_from_id('sliclquestions', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }
    if (! $sliclquestions= $DB->get_record('sliclquestions', array("id" => $cm->instance))) {
        print_error('invalidcoursemodule');
    }
}

// Check login and get context.
require_login($course->id, false, $cm);
$context = $cm ? context_module::instance($cm->id) : false;

$url = new moodle_url('/mod/sliclquestions/fbsections.php');
if ($id !== 0) {
    $url->param('id', $id);
}
if ($sid) {
    $url->param('sid', $sid);
}
$sliclquestions = new sliclquestions(0, $sliclquestions, $course, $cm);
$questions = $sliclquestions->questions;
$sid = $sliclquestions->survey->id;
$viewform = data_submitted($CFG->wwwroot."/mod/sliclquestions/fbsections.php");
$feedbacksections = $sliclquestions->survey->feedbacksections;
$errormsg = '';

// Check if there are any feedbacks stored in database already to use them to check
// the radio buttons on select questions in sections page.
if ($fbsections = $DB->get_records('sliclquestions_fb_sections', array('survey_id' => $sid)) ) {
    $scorecalculation = '';
    $questionsinsections = array();
    for ($section = 1; $section <= $feedbacksections; $section++) {
        // Retrieve the scorecalculation formula and the section heading only once.
        foreach ($fbsections as $fbsection) {
            if (isset($fbsection->scorecalculation) && $fbsection->section == $section) {
                $scorecalculation = unserialize($fbsection->scorecalculation);
                foreach ($scorecalculation as $qid => $key) {
                    $questionsinsections[$qid] = $section;
                }
                break;
            }
        }
    }
    // If Global Feedback (only 1 section) and no questions have yet been put in section 1 check all questions.
    /* if ($feedbacksections == 1 && !isset($fbsections[0]->scorecalculation)) {
            $scorecalculation = unserialize($fbsection->scorecalculation);
            foreach ($scorecalculation as $qid => $key) {
                $questionsinsections[$qid] = $section;
            }
    } */
    if (!empty($questionsinsections)) {
        $vf = $questionsinsections;
    }
}
if (data_submitted()) {
    $vf = (array) $viewform;
    if (isset($vf['savesettings'])) {
        $action = 'savesettings';
        unset($vf['savesettings']);
    }
    $scorecalculation = array();
    $submittedvf = array();
    foreach ($vf as $qs) {
        $sectionqid = explode("_", $qs);
        if ($sectionqid[0] != 0) {
            $scorecalculation[$sectionqid[0]][$sectionqid[1]] = null;
            $submittedvf[$sectionqid[1]] = $sectionqid[0];
        }
    }
    $c = count($scorecalculation);
    if ($c < $feedbacksections) {
        $sectionsnotset = '';
        for ($section = 1; $section <= $feedbacksections; $section++) {
            if (!isset($scorecalculation[$section])) {
                $sectionsnotset .= $section.'&nbsp;';
            }
        }
        $errormsg = get_string('sectionsnotset', 'sliclquestions', $sectionsnotset);
        $vf = $submittedvf;
    } else {
        for ($section = 1; $section <= $feedbacksections; $section++) {
            $fbcalculation[$section] = serialize($scorecalculation[$section]);
        }

        $sections = $DB->get_records('sliclquestions_fb_sections',
            array('survey_id' => $sliclquestions->survey->id), 'section DESC');
        // Delete former feedbacks if number of feedbacksections has been reduced.
        foreach ($sections as $section) {
            if ($section->section > $feedbacksections) {
                // Delete section record.
                $DB->delete_records('sliclquestions_fb_sections', array('survey_id' => $sid, 'section' => $section->section));
                // Delete associated feedback records.
                $DB->delete_records('sliclquestions_feedback', array('section_id' => $section->section));
            }
        }

        // Check if the number of feedback sections has been increased and insert new ones
        // must also insert section heading!
        for ($section = 1; $section <= $feedbacksections; $section++) {
            if ($existsection = $DB->get_record('sliclquestions_fb_sections',
                        array('survey_id' => $sid, 'section' => $section), '*', IGNORE_MULTIPLE) ) {
                $DB->set_field('sliclquestions_fb_sections', 'scorecalculation', serialize($scorecalculation[$section]),
                        array('survey_id' => $sid, 'section' => $section));
            } else {
                $feedbacksection = new stdClass();
                $feedbacksection->survey_id = $sid;
                $feedbacksection->section = $section;
                $feedbacksection->scorecalculation = serialize($scorecalculation[$section]);
                $feedbacksection->id = $DB->insert_record('sliclquestions_fb_sections', $feedbacksection);
            }
        }

        $currentsection = 1;
        $SESSION->sliclquestions->currentfbsection = 1;
        redirect ($CFG->wwwroot.'/mod/sliclquestions/fbsettings.php?id='.
            $sliclquestions->cm->id.'&currentsection='.$currentsection, '', 0);
    }
}

$PAGE->set_url($url);
// Print the page header.
$PAGE->set_title(get_string('feedbackeditingsections', 'sliclquestions'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string('feedbackeditingsections', 'sliclquestions'));
echo $OUTPUT->header();
echo '<form id="fbsections" method="post">';
$feedbacksections = $sliclquestions->survey->feedbacksections + 1;

if ($errormsg != '') {
    sliclquestions_notify($errormsg);
}
$n = 0;
$bg = 'c0';

echo $OUTPUT->box_start();

echo $OUTPUT->help_icon('feedbacksectionsselect', 'sliclquestions');
echo '<b>Sections:</b><br /><br />';
$formdata = new stdClass();
$descendantsdata = array();

foreach ($sliclquestions->questions as $question) {
    $qtype = $question->type_id;
    $qname = $question->name;
    $qprecise = $question->precise;
    $required = $question->required;
    $qid = $question->id;

    // Questions to be included in feedback sections must be required, have a name
    // and must not be child of a parent question.
    if ($qtype != QUESPAGEBREAK && $qtype != SLICLQUESSECTIONTEXT) {
        $n++;
    }

    $cannotuse = false;
    $strcannotuse = '';
    if ($qtype != SLICLQUESSECTIONTEXT && $qtype != SLICLQUESPAGEBREAK
                    && ($qtype != SLICLQUESYESNO && $qtype != SLICLQUESRADIO && $qtype != SLICLQUESRATE
                    || $required != 'y' || $qname == '' || $question->dependquestion != 0)) {
        $cannotuse = true;
        $qn = '<strong>'.$n.'</strong>';
        if ($qname == '') {
            $strcannotuse = get_string('missingname', 'sliclquestions', $qn);
        }
        if ($required != 'y') {
            if ($qname == '') {
                $strcannotuse = get_string('missingnameandrequired', 'sliclquestions', $qn);
            } else {
                $strcannotuse = get_string('missingrequired', 'sliclquestions', $qn);
            }
        }
        if ($question->dependquestion != 0) {
            continue;
        }
    }

    $qhasvalues = false;
    if (!$cannotuse) {
        if ($qtype == QUESRADIO || $qtype == QUESDROP) {
            if ($choices = $DB->get_records('sliclquestions_quest_choice', array('question_id' => $qid = $question->id))) {
                foreach ($choices as $choice) {
                    if ($choice->value != null) {
                        $qhasvalues = true;
                        break;
                    }
                }
            }
        }

        // Valid questions in feedback sections can be of QUESNO type
        // or of QUESRATE "normal" option type (i.e. not N/A nor nodupes).
        if ($qtype == QUESYESNO || ($qtype == QUESRATE && ($qprecise == 0 || $qprecise == 3)) ) {
            $qhasvalues = true;
        }

        if ($qhasvalues) {
            $emptyisglobalfeedback = $sliclquestions->survey->feedbacksections == 1 && empty($questionsinsections);
            echo '<div style="margin-bottom:5px;">['.$qname.']</div>';
            for ($i = 0; $i < $feedbacksections; $i++) {
                $output = '<div style="float:left; padding-right:5px;">';
                if ($i != 0) {
                    $output .= '<div class="'.$bg.'"><input type="radio" name="'.$n.'" id="'.$qid.'_'.$i.'" value="'.$i.'_'.
                        $qid.'"';
                } else {
                    $output .= '<div class="'.$bg.'"><input type="radio" name="'.$n.'" id="'.$i.'" value="'.$i.'"';
                }
                if ($i == 0) {
                    $output .= ' checked="checked"';
                }
                // Question already present in this section OR this is a Global feedback and questions are not set yet.
                if ((isset($vf[$qid]) && $vf[$qid] == $i) || $emptyisglobalfeedback) {
                    $output .= ' checked="checked"';
                }
                $output .= ' />';
                $output .= '<label for="'.$qid.'_'.$i.'">'.'<div style="padding-left: 2px;">'.$i.'</div>'.'</label></div></div>';
                echo $output;
                if ($bg == 'c0') {
                    $bg = 'c1';
                } else {
                    $bg = 'c0';
                }
            }
        }
        if ($qhasvalues || $qtype == QUESSECTIONTEXT) {
            $question->survey_display($formdata, $descendantsdata = '', $qnum = $n, $blanksliclquestions = true);
        }
    } else {
        echo '<div class="notifyproblem">';
        echo $strcannotuse;
        echo '</div>';
        echo '<div class="qn-question">'.$question->content.'</div>';
    }
}
// Submit/Cancel buttons.
$url = $CFG->wwwroot.'/mod/sliclquestions/view.php?id='.$cm->id;
echo '<div><input type="submit" name="savesettings" value="'.get_string('feedbackeditmessages', 'sliclquestions').'" />
          <a href="'.$url.'">'.get_string('cancel').'</a>
      </div>';
echo '</form>';
echo $OUTPUT->box_end();
echo $OUTPUT->footer($course);
