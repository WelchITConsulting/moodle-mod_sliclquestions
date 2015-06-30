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
 * Filename : print
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 23 Jun 2015
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/sliclquestions/classes/sliclquestions.class.php');

$qid = required_param('q', PARAM_INT);
$rid = required_param('r', PARAM_INT);
$cid = required_param('c', PARAM_INT);
$sec = required_param('s', PARAM_INT);

// ?????NOT NEEDED????
$referer = $CFG->wwwroot . '/mod/sliclquestions/report.php';

if (!$sliclquestions = $DB->get_record('sliclquestions', array('id' => $qid))) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $sliclquestions->course))) {
    print_error('coursemisconf');
}
if (!$cm = get_coursemodule_from_instance('sliclquestions', $sliclquestions->id, $course->id)) {
    printf_error('invalidcoursemodule');
}
require_login($course);

$sliclquestions = new sliclquestions(0, $sliclquestions, $course, $cm);

if (!($sliclquestions->capabilities->view && (($rid == 0) || $sliclquestions->can_view_response($rid)))) {
    print_error('nopermissions', 'moodle', $CFG->wwwroot . '/mod/sliclquestions/view.php?id=' . $cm->id);
}
$blankquestionnaire = true;
if ($rid != 0) {
    $blankquestionnaire = false;
}
$url = new moodle_url('/mod/sliclquestions/print.php', array('q' => $qid,
                                                             'r' => $rid,
                                                             'c' => $cid,
                                                             's' => $sec));
$PAGE->set_url($url);
$PAGE->set_title($sliclquestions->survey->title);
$PAGE->set_pagelayout('popup');
echo $OUTPUT->header();
$sliclquestions->survey_print_render($courseid, $message = '', $referer = 'print', $rid, $blankquestionnaire);
echo $OUTPUT->close_window_button()
   . $OUTPUT->footer();
