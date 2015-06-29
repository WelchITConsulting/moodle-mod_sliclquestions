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
require_once($CFG->dirroot . '/mod/sliclquestions/sliclquestions.class.php');
require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

$id  = optional_param('id', null, PARAM_INT);       // Course Module ID
$a   = optional_param('a', null, PARAM_INT);        // Or sliclquestions ID
$act = optional_param('act', null, PARAM_ALPHA);    // Action to be performed

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
} else if ($a) {
    if (! $sliclquestions = $DB->get_record("sliclquestions", array("id" => $a))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id" => $sliclquestions->course))) {
        print_error('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance("sliclquestions", $sliclquestions->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
} else {
    print_error('missingparameter');
}

// Check login and get context.
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
$PAGE->set_context($context);
$PAGE->set_cm($cm);

// Create a SLiCL Questions class instance
$sliclquestions = new sliclquestions($course, $cm, 0, $sliclquestions, true);

// Define the page URL
$params = array();
if ($id) {
    $params['id'] = $id;
} elseif ($a) {
    $params['a'] = $a;
}
$url = new moodle_url('/mod/sliclquestions/view.php', $params);
$PAGE->set_url($url);
if ($act) {
    $params['act'] = $act;
}

// Setup site header
$PAGE->set_title(format_string($sliclquestions->name));
$PAGE->set_heading(format_string($course->fullname));




notice(get_string('noviewpermission', 'sliclquestions'), $url);

die();


// Capability checks
if (empty($cm->visible) && !has_capability('moodle/course:viewhiddenactivities', $context)) {
    notice(get_string('activityiscurrentlyhidden'));
}
if (!has_capability('mod/sliclquestions:view', $context)) {
    notice(get_string('noviewpermission', 'sliclquestions'));
}
$currentgroupid = groups_get_activity_group($cm);
if (!groups_is_member($currentgroupid, $USER->id)) {
    $currentgroupid = 0;
}

// Check if we have manage permissions
if ( has_capability('mod/sliclquestions:manage', $context)) {

    // Load the management console
    require_once($CFG->dirroot . '/mod/sliclquestions/manager.class.php');
    mod_sliclquestions_management_console::get_instance($course, $context, $sliclquestions, $url, $params);
}

//echo $OUTPUT->box_start('generalbox sliclquestions boxwidthwide');

// Check the type of the page to show
switch($sliclquestions->questype) {

    case SLICLQUESTIONS_PUPILREGISTRATION:

        if ( has_capability('mod/sliclquestions:registerpupils', $context)) {

            // Load the pupil registration manager
            require_once($CFG->dirroot . '/mod/sliclquestions/pupilregister.class.php');
            mod_sliclquestions_pupil_register::get_instance($course, $context, $sliclquestions, $url, $params);

        } else {

            // Display the no view permisson message
            notice(get_string('noviewpermission', 'sliclquestions'));

        }
        break;

    case SLICLQUESTIONS_PUPILASSESSMENT:

        if ( has_capability('mod/sliclquestions:assesspupils', $context)) {

            // Load the pupil registration manager
            require_once($CFG->dirroot . '/mod/sliclquestions/pupilassessment.class.php');
            mod_sliclquestions_pupil_assessment::get_instance($sliclquestions, $context, $url, $params);

        } else {

            // Display the no view permisson message
            notice(get_string('noviewpermission', 'sliclquestions'));

        }
        break;

    case SLICLQUESTIONS_SURVEY:

        if ( has_capability('mod/sliclquestions:submit', $context)) {

            // Load the pupil registration manager
            require_once($CFG->dirroot . '/mod/sliclquestions/survey.class.php');
            mod_sliclquestions_survey::get_instance($sliclquestions, $context, $url, $params);

        } else {

            // Display the no view permisson message
            notice(get_string('noviewpermission', 'sliclquestions'));

        }
        break;

    default:
        echo "View permissions only";
        break;
}
