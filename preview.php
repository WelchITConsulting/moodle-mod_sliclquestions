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
 * Created  : 23 Jun 2015
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/sliclquestions.class.php');

$id             = optional_param('id', 0, PARAM_INT);
$sid            = optional_param('s', 0, PARAM_INT);
$qid            = optional_param('q', 0, PARAM_INT);
$popup          = optional_param('p', 0, PARAM_INT);
$currentgroupid = optional_param('g', 0, PARAM_INT);

if ($id) {
    if (!$cm = get_coursemodule_from_id('sliclquestions', $id)) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }
    if (!$sliclquestions = $DB->get_record('sliclquestions', array('id' => $cm->instance))) {
        print_error('invalidcoursemodule');
    }
} elseif ($sid) {
    if (!$sliclquestions = $DB->get_record('sliclquestions', array('id' => $sid))) {
        print_error('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $sliclquestions->course))) {
        print_error('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance('sliclquestions', $sliclquestions->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
}
if (!$popup) {
    require_login($course);
}
$context = context_module::instance($cm->id);
$params = array();
if ($id) {
    $params['id'] = $id;
}
if ($sid) {
    $params['s'] = $sid;
}
$PAGE->set_url(new moodle_url('/mod/sliclquestions/preview.php', $params));
$PAGE->set_context($context);
$PAGE->set_cm($cm);

$sliclquestions = new sliclquestions($qid, $sliclquestions, $course, $cm);

$canpreview = (!isset($sliclquestions->capabilities) &&
               has_capability('mod/sliclquesions:preview', context_course::instance($course->id))) ||
               (isset($sliclquestions->capabilities) && $sliclquestions->capabilities->preview);
if (!$canpreview && !$popup) {
    print_error('nopermissions', 'sliclquestions', '/mod/sliclquestions/view.php?id=' . $cm->id);
}
if (!isset($SESSION->sliclquestions)) {
    $SESSION->sliclquestions = new stdClass();
}
$SESSION->sliclquestions->current_tab = new stdClass();
$SESSION->sliclquestions->current_tab = 'preview';

if ($popup) {
    $PAGE->set_pagelayout('popup');
}
$PAGE->set_title(format_string(get_string('preview_questionnaire', 'sliclquestions')));
if (!$popup) {
    $PAGE->set_heading(format_string($course->fullname));
}
$PAGE->requires->js('/mod/sliclquestions/javascript/module.js');
echo $OUTPUT->header();
if (!$popup) {
    require('tabs.php');
}
echo $OUTPUT->heading(get_string('previewing', 'sliclquestions'));

if ($sliclquestions->capabilities->printblank) {
    $link     = new moodle_url('/mod/sliclquestions/print.php', array('q' => $sliclquestions->id,
                                                                      'r' => 0,
                                                                      'c' => $sliclquestions->course,
                                                                      's' => 1));
    $linkname = '&nbsp;' . get_string('printblank', 'sliclquestions');
    $title    = get_string('printblanktooltip', 'sliclquestions');
    $action   = new popup_action('click', $link, 'popup', array('menubar' => true,
                                                                'location' => false,
                                                                'scrollbars' => true,
                                                                'resizable'  => true,
                                                                'height'     => 600,
                                                                'width'      => 800,
                                                                'title'      => $title));
    echo $OUTPUT->action_link($link, $linkname, $action, array('class' => 'floatprinticon',
                                                               'title' => $title),
                              new pix_icon('t/print', $title));
}
$sliclquestions->survey_print_render($course->id, '', 'preview', $rid = 0, $popup);
if ($popup) {
    echo $OUTPUT->close_window_button();
}
echo $OUTPUT->footer($course);

// Log this preview
$context = context_module::instance($sliclquestions->cm->id);
$anon = $sliclquestions->respondenttype == 'anonymous';
$event = \mod_sliclquestions\event\sliclquestions_previewed::create(array('objectid'  => $sliclquestions->id,
                                                                          'anonymous' => $anon,
                                                                          'context'   => $context));
$event->trigger();
