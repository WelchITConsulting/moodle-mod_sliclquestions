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
 * Filename : fbsettings
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sliclquestions/fbsettings_form.php');
require_once($CFG->dirroot.'/mod/sliclquestions/sliclquestions.class.php');

$id = required_param('id', PARAM_INT); // Course module ID.
$currentsection   = $SESSION->sliclquestions->currentfbsection;
if (! $cm = get_coursemodule_from_id('sliclquestions', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

if (! $sliclquestions = $DB->get_record("sliclquestions", array("id" => $cm->instance))) {
    print_error('invalidcoursemodule');
}

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_once($CFG->dirroot.'/mod/sliclquestions/lib.php');

$url = new moodle_url($CFG->wwwroot.'/mod/sliclquestions/fbsettings.php', array('id' => $id));
$PAGE->set_url($url);
$PAGE->set_context($context);

$sliclquestions = new sliclquestions(0, $sliclquestions, $course, $cm);

if (!$sliclquestions->capabilities->manage) {
    print_error('nopermissions', 'error', 'mod:sliclquestions:manage');
}
$sid = $sliclquestions->survey->id;

$sdata = clone($sliclquestions->survey);
$sdata->sid = $sid;
$sdata->id = $cm->id;

$feedbacksections = $sliclquestions->survey->feedbacksections;

// Get the current section heading.
$sectionid = null;
$scorecalculation = null;
if ($section = $DB->get_record('sliclquestions_fb_sections',
        array('survey_id' => $sid, 'section' => $currentsection))) {
    $sectionid = $section->id;
    $sectionheading = $section->sectionheading;
    $scorecalculation = $section->scorecalculation;
    $draftideditor = file_get_submitted_draft_itemid('sectionheading');
    $currentinfo = file_prepare_draft_area($draftideditor, $context->id, 'mod_sliclquestions', 'sectionheading',
            $sectionid, array('subdirs' => true), $sectionheading);
    $sdata->sectionlabel = $section->sectionlabel;
    $sdata->sectionheading = array('text' => $currentinfo, 'format' => FORMAT_HTML, 'itemid' => $draftideditor);
}

$feedbackform = new sliclquestions_feedback_form( null, array('currentsection' => $currentsection, 'sectionid' => $sectionid) );
$feedbackform->set_data($sdata);
if ($feedbackform->is_cancelled()) {
    // Redirect to view sliclquestions page.
    redirect($CFG->wwwroot.'/mod/sliclquestions/view.php?id='.$sliclquestions->cm->id);
}
if ($settings = $feedbackform->get_data()) {
    $i = 0;
    while (!empty($settings->feedbackboundaries[$i])) {
        $boundary = trim($settings->feedbackboundaries[$i]);
        if (strlen($boundary) > 0 && $boundary[strlen($boundary) - 1] == '%') {
            $boundary = trim(substr($boundary, 0, -1));
        }
        $settings->feedbackboundaries[$i] = $boundary;
        $i += 1;
    }
    $numboundaries = $i;
    $settings->feedbackboundaries[-1] = 101;
    $settings->feedbackboundaries[$numboundaries] = 0;
    $settings->feedbackboundarycount = $numboundaries;

    // Save current section.
    $section = new stdClass();
    $section->survey_id = $settings->sid;
    $section->section = $currentsection;
    $section->scorecalculation = $scorecalculation;
    $section->sectionlabel = $settings->sectionlabel;
    $section->sectionheading = '';
    $section->sectionheadingformat = $settings->sectionheading['format'];

    // Check if we are updating an existing section record or creating a new one.
    if ($existsection = $DB->get_record('sliclquestions_fb_sections',
            array('survey_id' => $sid, 'section' => $currentsection) ) ) {
        $section->id = $existsection->id;
    } else {
        $section->id = $DB->insert_record('sliclquestions_fb_sections', $section);
    }
    $sectionheading = file_save_draft_area_files((int)$settings->sectionheading['itemid'],
            $context->id, 'mod_sliclquestions', 'sectionheading', $section->id,
            array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
            $settings->sectionheading['text']);
    $DB->set_field('sliclquestions_fb_sections', 'sectionheading', $sectionheading,
            array('id' => $section->id));
    $DB->set_field('sliclquestions_fb_sections', 'sectionlabel', $settings->sectionlabel,
            array('id' => $section->id));

    // Save current section's feedbacks
    // first delete all existing feedbacks for this section - if any
    // because we never know whether editing feedbacks will have more or less texts, so it's easiest to delete all and stard afresh.
    $DB->delete_records('sliclquestions_feedback', array('section_id' => $section->id));
    for ($i = 0; $i <= $settings->feedbackboundarycount; $i++) {
        $feedback = new stdClass();
        $feedback->section_id = $section->id;
        if (isset($settings->feedbacklabel[$i])) {
            $feedback->feedbacklabel = $settings->feedbacklabel[$i];
        }
        $feedback->feedbacktext = '';
        $feedback->feedbacktextformat = $settings->feedbacktext[$i]['format'];
        $feedback->minscore = $settings->feedbackboundaries[$i];
        $feedback->maxscore = $settings->feedbackboundaries[$i - 1];
        $feedback->id = $DB->insert_record('sliclquestions_feedback', $feedback);

        $feedbacktext = file_save_draft_area_files((int)$settings->feedbacktext[$i]['itemid'],
                $context->id, 'mod_sliclquestions', 'feedback', $feedback->id,
                array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
                $settings->feedbacktext[$i]['text']);
        $DB->set_field('sliclquestions_feedback', 'feedbacktext', $feedbacktext,
                array('id' => $feedback->id));
    }
}
if (isset($settings->savesettings)) {
    redirect ($CFG->wwwroot.'/mod/sliclquestions/view.php?id='.$sliclquestions->cm->id, '', 0);
} else if (isset($settings->submitbutton)) {
    $SESSION->sliclquestions->currentfbsection ++;
    redirect ($CFG->wwwroot.'/mod/sliclquestions/fbsettings.php?id='.$sliclquestions->cm->id, '', 0);
}

// Print the page header.
    $PAGE->set_title(get_string('feedbackeditingmessages', 'sliclquestions'));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->navbar->add(get_string('feedbackeditingmessages', 'sliclquestions'));
    echo $OUTPUT->header();
    $feedbackform->display();
    echo $OUTPUT->footer($course);
