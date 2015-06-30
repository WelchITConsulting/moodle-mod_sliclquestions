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
 * Filename : complete
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 24 Jun 2015
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/sliclquestions/classes/sliclquestions.class.php');

if (!isset($SESSION->sliclquestions)) {
    $SESSION->sliclquestions = new stdClass();
}
$SESSION->sliclquestions->current_tab = 'view';

$id  = optional_param('id', null, PARAM_INT);       // Course module ID
$a   = optional_param('a',  null, PARAM_INT);       // SLiCL questions ID
$act = optional_param('act', null, PARAM_ALPHA);    // Action to perform

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
$sliclquestions = new sliclquestions($course, $cm, 0, $sliclquestions);

// Define the page URL
$params = array();
if ($id) {
    $params['id'] = $id;
} elseif ($a) {
    $params['a'] = $a;
}
$url = new moodle_url('/mod/sliclquestions/view.php', $params);
if ($act) {
    $params['act'] = $act;
}
$PAGE->set_url(new moodle_url('/mod/sliclquestions/complete.php', $params));

// Print site header
$PAGE->set_title(iformat_string($sliclquestions->name));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();

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
if (!$sliclquestions->is_open()) {
    notice(get_string('notopen', 'sliclquestions'), $url);
} elseif (!$sliclquestions->is_closed()) {
    notice(get_string('closed', 'sliclquestions'), $url);
} elseif (!$sliclquestions->user_is_eligible()) {
    notice(get_string('', 'sliclquestions'), $url);
} else {
    $sliclquestions->view($url);
}
echo $OUTPUT->footer();
