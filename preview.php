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
 * Filename : preview
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sliclquestions/sliclquestions.class.php');

$id     = optional_param('id', 0, PARAM_INT);
$sid    = optional_param('sid', 0, PARAM_INT);
$popup  = optional_param('popup', 0, PARAM_INT);
$qid    = optional_param('qid', 0, PARAM_INT);
$currentgroupid = optional_param('group', 0, PARAM_INT); // Groupid.

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
    if (! $survey = $DB->get_record("sliclquestions_survey", array("id" => $sid))) {
        print_error('surveynotexists', 'sliclquestions');
    }
    if (! $course = $DB->get_record("course", array("id" => $survey->owner))) {
        print_error('coursemisconf');
    }
    // Dummy sliclquestions object.
    $sliclquestions = new Object();
    $sliclquestions->id = 0;
    $sliclquestions->course = $course->id;
    $sliclquestions->name = $survey->title;
    $sliclquestions->sid = $sid;
    $sliclquestions->resume = 0;
    // Dummy cm object.
    if (!empty($qid)) {
        $cm = get_coursemodule_from_instance('sliclquestions', $qid, $course->id);
    } else {
        $cm = false;
    }
}

// Check login and get context.
// Do not require login if this sliclquestions is viewed from the Add sliclquestions page
// to enable teachers to view template or public sliclquestionss located in a course where they are not enroled.
if (!$popup) {
    require_login($course->id, false, $cm);
}
$context = $cm ? context_module::instance($cm->id) : false;

$url = new moodle_url('/mod/sliclquestions/preview.php');
if ($id !== 0) {
    $url->param('id', $id);
}
if ($sid) {
    $url->param('sid', $sid);
}
$PAGE->set_url($url);

$PAGE->set_context($context);

$sliclquestions = new sliclquestions($qid, $sliclquestions, $course, $cm);

$canpreview = (!isset($sliclquestions->capabilities) &&
               has_capability('mod/sliclquestions:preview', context_course::instance($course->id))) ||
              (isset($sliclquestions->capabilities) && $sliclquestions->capabilities->preview);
if (!$canpreview && !$popup) {
    // Should never happen, unless called directly by a snoop...
    print_error('nopermissions', 'sliclquestions', $CFG->wwwroot.'/mod/sliclquestions/view.php?id='.$cm->id);
}

if (!isset($SESSION->sliclquestions)) {
    $SESSION->sliclquestions = new stdClass();
}
$SESSION->sliclquestions->current_tab = new stdClass();
$SESSION->sliclquestions->current_tab = 'preview';

$qp = get_string('preview_sliclquestions', 'sliclquestions');
$pq = get_string('previewing', 'sliclquestions');

// Print the page header.
if ($popup) {
    $PAGE->set_pagelayout('popup');
}
$PAGE->set_title(format_string($qp));
if (!$popup) {
    $PAGE->set_heading(format_string($course->fullname));
}

// Include the needed js.


$PAGE->requires->js('/mod/sliclquestions/module.js');
// Print the tabs.


echo $OUTPUT->header();
if (!$popup) {
    require('tabs.php');
}
echo $OUTPUT->heading($pq);
$sliclquestions->survey_print_render('', 'preview', $course->id, $rid = 0, $popup);
if ($popup) {
    echo $OUTPUT->close_window_button();
}
echo $OUTPUT->footer($course);