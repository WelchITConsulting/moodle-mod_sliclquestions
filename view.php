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
 * Filename : view
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once("../../config.php");
require_once($CFG->libdir  .  '/completionlib.php');
require_once($CFG->dirroot . '/mod/sliclquestions/sliclquestions.class.php');

if (!isset($SESSION->sliclquestions)) {
    $SESSION->sliclquestions = new stdClass();
}
$SESSION->sliclquestions->current_tab = 'view';

$id = optional_param('id', null, PARAM_INT);    // Course Module ID.
$a = optional_param('a', null, PARAM_INT);      // Or sliclquestions ID.
$sid = optional_param('sid', null, PARAM_INT);  // Survey id.

if ($id) {
    if (! $cm = get_coursemodule_from_id('sliclquestions', $id)) {
        print_error('invalidcoursemodule');
    }

    if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error('coursemisconf');
    }

    if (! $sliclquestions = $DB->get_record("sliclquestions", array("id" => $cm->instance))) {
        print_error('invalidcoursemodule');
    }

} else {
    if (! $sliclquestions = $DB->get_record("sliclquestions", array("id" => $a))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id" => $sliclquestions->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance("sliclquestions", $sliclquestions->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}

// Check login and get context.
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

$url = new moodle_url($CFG->wwwroot.'/mod/sliclquestions/view.php');
if (isset($id)) {
    $url->param('id', $id);
} else {
    $url->param('a', $a);
}
if (isset($sid)) {
    $url->param('sid', $sid);
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$sliclquestions = new sliclquestions(0, $sliclquestions, $course, $cm);

$PAGE->set_title(format_string($sliclquestions->name));

$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

echo $OUTPUT->heading(format_text($sliclquestions->name));

// Print the main part of the page.
if ($sliclquestions->intro) {
    echo $OUTPUT->box(format_module_intro('sliclquestions', $sliclquestions, $cm->id), 'generalbox', 'intro');
}

echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');

$cm = $sliclquestions->cm;
$currentgroupid = groups_get_activity_group($cm);
if (!groups_is_member($currentgroupid, $USER->id)) {
    $currentgroupid = 0;
}

if (!$sliclquestions->is_active()) {
    if ($sliclquestions->capabilities->manage) {
        $msg = 'removenotinuse';
    } else {
        $msg = 'notavail';
    }
    echo '<div class="message">'
    .get_string($msg, 'sliclquestions')
    .'</div>';

} else if (!$sliclquestions->is_open()) {
    echo '<div class="message">'
    .get_string('notopen', 'sliclquestions', userdate($sliclquestions->opendate))
    .'</div>';
} else if ($sliclquestions->is_closed()) {
    echo '<div class="message">'
    .get_string('closed', 'sliclquestions', userdate($sliclquestions->closedate))
    .'</div>';
} else if ($sliclquestions->survey->realm == 'template') {
    print_string('templatenotviewable', 'sliclquestions');
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer($sliclquestions->course);
    exit();
} else if (!$sliclquestions->user_is_eligible($USER->id)) {
    if ($sliclquestions->questions) {
        echo '<div class="message">'.get_string('noteligible', 'sliclquestions').'</div>';
    }
} else if (!$sliclquestions->user_can_take($USER->id)) {
    switch ($sliclquestions->qtype) {
        case SLICLQUESTIONSDAILY:
            $msgstring = ' '.get_string('today', 'sliclquestions');
            break;
        case SLICLQUESTIONSWEEKLY:
            $msgstring = ' '.get_string('thisweek', 'sliclquestions');
            break;
        case SLICLQUESTIONSMONTHLY:
            $msgstring = ' '.get_string('thismonth', 'sliclquestions');
            break;
        default:
            $msgstring = '';
            break;
    }
    echo ('<div class="message">'.get_string("alreadyfilled", "sliclquestions", $msgstring).'</div>');
} else if ($sliclquestions->user_can_take($USER->id)) {
    $select = 'survey_id = '.$sliclquestions->survey->id.' AND username = \''.$USER->id.'\' AND complete = \'n\'';
    $resume = $DB->get_record_select('sliclquestions_response', $select, null) !== false;
    if (!$resume) {
        $complete = get_string('answerquestions', 'sliclquestions');
    } else {
        $complete = get_string('resumesurvey', 'sliclquestions');
    }
    if ($sliclquestions->questions) { // Sanity check.
        echo '<a href="'.$CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/complete.php?'.
        'id='.$sliclquestions->cm->id).'">'.$complete.'</a>';
    }
}
if ($sliclquestions->is_active() && !$sliclquestions->questions) {
    echo '<p>'.get_string('noneinuse', 'sliclquestions').'</p>';
}
if ($sliclquestions->is_active() && $sliclquestions->capabilities->editquestions && !$sliclquestions->questions) { // Sanity check.
    echo '<a href="'.$CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/questions.php?'.
                'id='.$sliclquestions->cm->id).'">'.'<strong>'.get_string('addquestions', 'sliclquestions').'</strong></a>';
}
echo $OUTPUT->box_end();
if (isguestuser()) {
    $output = '';
    $guestno = html_writer::tag('p', get_string('guestsno', 'sliclquestions'));
    $liketologin = html_writer::tag('p', get_string('liketologin'));
    $output .= $OUTPUT->confirm($guestno."\n\n".$liketologin."\n", get_login_url(),
            get_referer(false));
    echo $output;
}

$usernumresp = $sliclquestions->count_submissions($USER->id);

if ($sliclquestions->capabilities->readownresponses && ($usernumresp > 0)) {
    echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
    $argstr = 'instance='.$sliclquestions->id.'&user='.$USER->id;
    if ($usernumresp > 1) {
        $titletext = get_string('viewyourresponses', 'sliclquestions', $usernumresp);
    } else {
        $titletext = get_string('yourresponse', 'sliclquestions');
        $argstr .= '&byresponse=1&action=vresp';
    }

    echo '<a href="'.$CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/myreport.php?'.
        $argstr).'">'.$titletext.'</a>';
    echo $OUTPUT->box_end();
}

if ($survey = $DB->get_record('sliclquestions_survey', array('id' => $sliclquestions->sid))) {
    $owner = (trim($survey->owner) == trim($course->id));
} else {
    $survey = false;
    $owner = true;
}
$numresp = $sliclquestions->count_submissions();

// Number of Responses in currently selected group (or all participants etc.).
if (isset($SESSION->sliclquestions->numselectedresps)) {
    $numselectedresps = $SESSION->sliclquestions->numselectedresps;
} else {
    $numselectedresps = $numresp;
}

// If sliclquestions is set to separate groups, prevent user who is not member of any group
// to view All responses.
$canviewgroups = true;
$groupmode = groups_get_activity_groupmode($cm, $course);
if ($groupmode == 1) {
    $canviewgroups = groups_has_membership($cm, $USER->id);;
}

$canviewallgroups = has_capability('moodle/site:accessallgroups', $context);
if (( (
            // Teacher or non-editing teacher (if can view all groups).
            $canviewallgroups ||
            // Non-editing teacher (with canviewallgroups capability removed), if member of a group.
            ($canviewgroups && $sliclquestions->capabilities->readallresponseanytime))
            && $numresp > 0 && $owner && $numselectedresps > 0) ||
            $sliclquestions->capabilities->readallresponses && ($numresp > 0) && $canviewgroups &&
            ($sliclquestions->resp_view == SLICLQUESTIONS_STUDENTVIEWRESPONSES_ALWAYS ||
                    ($sliclquestions->resp_view == SLICLQUESTIONS_STUDENTVIEWRESPONSES_WHENCLOSED
                            && $sliclquestions->is_closed()) ||
                    ($sliclquestions->resp_view == SLICLQUESTIONS_STUDENTVIEWRESPONSES_WHENANSWERED
                            && $usernumresp > 0)) &&
            $sliclquestions->is_survey_owner()) {
    echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
    $argstr = 'instance='.$sliclquestions->id.'&group='.$currentgroupid;
    echo '<a href="'.$CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.
            $argstr).'">'.get_string('viewallresponses', 'sliclquestions').'</a>';
    echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
