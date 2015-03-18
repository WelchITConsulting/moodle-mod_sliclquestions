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
 * Filename : qsettings
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sliclquestions/settings_form.php');
require_once($CFG->dirroot.'/mod/sliclquestions/sliclquestions.class.php');

$id = required_param('id', PARAM_INT);    // Course module ID.
$currentgroupid = optional_param('group', 0, PARAM_INT); // Groupid.
$cancel = optional_param('cancel', '', PARAM_ALPHA);
$submitbutton2 = optional_param('submitbutton2', '', PARAM_ALPHA);

if (! $cm = get_coursemodule_from_id('sliclquestions', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

if (! $sliclquestions = $DB->get_record("sliclquestions", array("id" => $cm->instance))) {
    print_error('invalidcoursemodule');
}

// Needed here for forced language courses.
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

$url = new moodle_url($CFG->wwwroot.'/mod/sliclquestions/qsettings.php', array('id' => $id));
$PAGE->set_url($url);
$PAGE->set_context($context);
if (!isset($SESSION->sliclquestions)) {
    $SESSION->sliclquestions = new stdClass();
}
$sliclquestions = new sliclquestions(0, $sliclquestions, $course, $cm);
$SESSION->sliclquestions->current_tab = 'settings';

if (!$sliclquestions->capabilities->manage) {
    print_error('nopermissions', 'error', 'mod:sliclquestions:manage');
}

$settingsform = new sliclquestions_settings_form('qsettings.php');
$sdata = clone($sliclquestions->survey);
$sdata->sid = $sliclquestions->survey->id;
$sdata->id = $cm->id;

$draftideditor = file_get_submitted_draft_itemid('info');
$currentinfo = file_prepare_draft_area($draftideditor, $context->id, 'mod_sliclquestions', 'info',
                $sdata->sid, array('subdirs' => true), $sliclquestions->survey->info);
$sdata->info = array('text' => $currentinfo, 'format' => FORMAT_HTML, 'itemid' => $draftideditor);

$draftideditor = file_get_submitted_draft_itemid('thankbody');
$currentinfo = file_prepare_draft_area($draftideditor, $context->id, 'mod_sliclquestions', 'thankbody',
                $sdata->sid, array('subdirs' => true), $sliclquestions->survey->thank_body);
$sdata->thank_body = array('text' => $currentinfo, 'format' => FORMAT_HTML, 'itemid' => $draftideditor);

$draftideditor = file_get_submitted_draft_itemid('feedbacknotes');
$currentinfo = file_prepare_draft_area($draftideditor, $context->id, 'mod_sliclquestions', 'feedbacknotes',
        $sdata->sid, array('subdirs' => true), $sliclquestions->survey->feedbacknotes);
$sdata->feedbacknotes = array('text' => $currentinfo, 'format' => FORMAT_HTML, 'itemid' => $draftideditor);

$settingsform->set_data($sdata);

if ($settingsform->is_cancelled()) {
    redirect ($CFG->wwwroot.'/mod/sliclquestions/view.php?id='.$sliclquestions->cm->id, '');
}

if ($settings = $settingsform->get_data()) {
    $sdata = new Object();
    $sdata->id = $settings->sid;
    $sdata->name = $settings->name;
    $sdata->realm = $settings->realm;
    $sdata->title = $settings->title;
    $sdata->subtitle = $settings->subtitle;

    $sdata->infoitemid = $settings->info['itemid'];
    $sdata->infoformat = $settings->info['format'];
    $sdata->info       = $settings->info['text'];
    $sdata->info       = file_save_draft_area_files($sdata->infoitemid, $context->id, 'mod_sliclquestions', 'info',
                                                    $sdata->id, array('subdirs' => true), $sdata->info);

    $sdata->theme = ''; // Deprecated theme field.
    $sdata->thanks_page = $settings->thanks_page;
    $sdata->thank_head = $settings->thank_head;

    $sdata->thankitemid = $settings->thank_body['itemid'];
    $sdata->thankformat = $settings->thank_body['format'];
    $sdata->thank_body  = $settings->thank_body['text'];
    $sdata->thank_body  = file_save_draft_area_files($sdata->thankitemid, $context->id, 'mod_sliclquestions', 'thankbody',
                                                     $sdata->id, array('subdirs' => true), $sdata->thank_body);
    $sdata->email = $settings->email;

    if (isset ($settings->feedbackscores)) {
        $sdata->feedbackscores = $settings->feedbackscores;
    } else {
        $sdata->feedbackscores = 0;
    }

    if (isset ($settings->feedbacknotes)) {
        $sdata->fbnotesitemid = $settings->feedbacknotes['itemid'];
        $sdata->fbnotesformat = $settings->feedbacknotes['format'];
        $sdata->feedbacknotes  = $settings->feedbacknotes['text'];
        $sdata->feedbacknotes  = file_save_draft_area_files($sdata->fbnotesitemid,
                        $context->id, 'mod_sliclquestions', 'feedbacknotes',
                        $sdata->id, array('subdirs' => true), $sdata->feedbacknotes);
    } else {
        $sdata->feedbacknotes = '';
    }

    if (isset ($settings->feedbacksections)) {
        $sdata->feedbacksections = $settings->feedbacksections;
        if ($CFG->sliclquestions_usergraph) {
            if ($settings->feedbacksections == 1) {
                $sdata->chart_type = $settings->chart_type_global;
            } else if ($settings->feedbacksections == 2) {
                $sdata->chart_type = $settings->chart_type_two_sections;
            } else if ($settings->feedbacksections > 2) {
                $sdata->chart_type = $settings->chart_type_sections;
            }
        }
    } else {
        $sdata->feedbacksections = '';
    }
    $sdata->owner = $settings->owner;
    if (!($sid = $sliclquestions->survey_update($sdata))) {
        print_error('couldnotcreatenewsurvey', 'sliclquestions');
    } else {
        if ($submitbutton2) {
            $redirecturl = course_get_url($cm->course);
        } else {
            $redirecturl = $CFG->wwwroot.'/mod/sliclquestions/view.php?id='.$sliclquestions->cm->id;
        }

        // Save current advanced settings only.
        if (isset($settings->submitbutton) || isset($settings->submitbutton2)) {
            redirect ($redirecturl, get_string('settingssaved', 'sliclquestions'));
        }

        // Delete existing section and feedback records for this sliclquestions if any were previously set and None are wanted now
        // or Global feedback is now wanted.
        if ($sdata->feedbacksections == 0 || ($sliclquestions->survey->feedbacksections > 1 && $sdata->feedbacksections == 1)) {
            if ($feedbacksections = $DB->get_records('sliclquestions_fb_sections',
                    array('survey_id' => $sid), '', 'id') ) {
                foreach ($feedbacksections as $key => $feedbacksection) {
                    $DB->delete_records('sliclquestions_feedback', array('section_id' => $key));
                }
                $DB->delete_records('sliclquestions_fb_sections', array('survey_id' => $sid));
            }
        }

        // Save current advanced settings and go to edit feedback page(s).
        $SESSION->sliclquestions->currentfbsection = 1;
        switch ($settings->feedbacksections) {
            // 1 fbsection means Global feedback, redirect immediately to the fb settings page.
            case 1:
                redirect ($CFG->wwwroot.'/mod/sliclquestions/fbsettings.php?id='.$sliclquestions->cm->id,
                        get_string('settingssaved', 'sliclquestions'), 0);
                break;
            // More than 1 section, go to fb sections page for user to put questions inside sections.
            default:
                // This sliclquestions has more than one feedback sections, so needs to set sections questions first
                // before setting feedback messages.
                redirect ($CFG->wwwroot.'/mod/sliclquestions/fbsections.php?id='.$sliclquestions->cm->id, '', 0);
                break;
        }
    }
}

// Print the page header.
$PAGE->set_title(get_string('editingsliclquestions', 'sliclquestions'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string('editingsliclquestions', 'sliclquestions'));
echo $OUTPUT->header();
require('tabs.php');
$settingsform->display();
echo $OUTPUT->footer($course);
