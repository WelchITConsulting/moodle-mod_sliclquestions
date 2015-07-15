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
 * Filename : report
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 08 Jul 2015
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/sliclquestions.class.php');
require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/manager.class.php');

$id             = optional_param('id', false, PARAM_INT);       // Course module ID
$sid            = optional_param('sid', false, PARAM_INT);      // SLiCL Questions ID

if ($id) {
    if (!$cm = get_coursemodule_from_id('sliclquestions', $id)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }
    if (!$survey = $DB->get_record('sliclquestions', array('id' => $cm->instance))) {
        print_error('invalidcoursemodule');
    }
} elseif ($sid) {
    if (!$survey = $DB->get_record('sliclquestions', array('id' => $sid))) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $survey->course))) {
        print_error('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance('sliclquestions', $survey->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
} else {
    print_error('missingparameter');
}
require_course_login($course, true, $cm);
$survey = new sliclquestions($course, $cm, 0, $survey);

$context = context_module::instance($cm->id);
$PAGE->set_context($context);

// Check permissions to view report
if (!has_capability('mod/sliclquestions:viewallresponses', $context) &&
        !has_capability('mod/sliclquestions:viewownresponses', $context)) {
    notice(get_string('noviewpermission', 'sliclquestions'));
}

$params = array();
if ($id)  { $params['id'] = $id; }
if ($sid) { $params['sid'] = $sid; }
if ($act) { $params['act'] = $act; }
if ($rid) { $params['rid'] = $rid; }

$url = new moodle_url('/mod/sliclquestions/report.php', $params);
$PAGE->set_url($url);

mod_sliclquestions_management_console::get_instance($course, $context, $survey, $url);
