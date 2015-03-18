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
 * Created  : 18 Mar 2015
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sliclquestions/sliclquestions.class.php');

$qid = required_param('qid', PARAM_INT);
$rid = required_param('rid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$sec = required_param('sec', PARAM_INT);
$null = null;
$referer = $CFG->wwwroot.'/mod/sliclquestions/report.php';

if (! $sliclquestions = $DB->get_record("sliclquestions", array("id" => $qid))) {
    print_error('invalidcoursemodule');
}
if (! $course = $DB->get_record("course", array("id" => $sliclquestions->course))) {
    print_error('coursemisconf');
}
if (! $cm = get_coursemodule_from_instance("sliclquestions", $sliclquestions->id, $course->id)) {
    print_error('invalidcoursemodule');
}

// Check login and get context.
require_login($courseid);

$sliclquestions = new sliclquestions(0, $sliclquestions, $course, $cm);

// If you can't view the questionnaire, or can't view a specified response, error out.
if (!($sliclquestions->capabilities->view && (($rid == 0) || $sliclquestions->can_view_response($rid)))) {
    // Should never happen, unless called directly by a snoop...
    print_error('nopermissions', 'moodle', $CFG->wwwroot.'/mod/sliclquestion/view.php?id='.$cm->id);
}
$blankquestionnaire = true;
if ($rid != 0) {
    $blankquestionnaire = false;
}
$url = new moodle_url($CFG->wwwroot.'/mod/sliclquestions/print.php');
$url->param('qid', $qid);
$url->param('rid', $rid);
$url->param('courseid', $courseid);
$url->param('sec', $sec);
$PAGE->set_url($url);
$PAGE->set_title($sliclquestions->survey->title);
$PAGE->set_pagelayout('popup');
echo $OUTPUT->header();
$sliclquestions->survey_print_render($message = '', $referer = 'print', $courseid, $rid, $blanksliclquestions);
echo $OUTPUT->close_window_button();
echo $OUTPUT->footer();
