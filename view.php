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
//require_once($CFG->libdir  .  '/completionlib.php');
//require_once($CFG->dirroot . '/mod/sliclquestions/sliclquestions.class.php');
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

// Define the page URL
$url = new moodle_url($CFG->wwwroot.'/mod/sliclquestions/view.php');
if (isset($id)) {
    $url->param('id', $id);
} else {
    $url->param('a', $a);
}

// Define the PAGE object
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($sliclquestions->name));
$PAGE->set_heading(format_string($course->fullname));

// Define the page headings
echo $OUTPUT->header()
   . $OUTPUT->heading(format_text($sliclquestions->name));

if (has_capability('mod/sliclregister:view', $context)) {


}

echo $OUTPUT->box_end();

// Finalise the page output
echo $OUTPUT->footer();
