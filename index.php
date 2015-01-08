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
 * Filename : index
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 06 Jan 2015
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/sliclquestions/locallib.php');

$id = required_param('id', PARAM_INT);

$PAGE->set_url('/mod/sliclquestions/index.php', array('id' => $id));
if (!($course = $DB->get_record('course', array('id' => $id)))) {
    print_error('incorrectcourseid', 'sliclquestions');
}
$course_context = context_course::instance($id);
require_login($course->id);
$PAGE->set_pagelayout('incourse');

// Log the page access
add_to_log($course->id, 'sliclquestions', 'view all', "index.php?id={$course->id}", "");

// Print the page header
$str_questions = get_string('modulenameplural', 'sliclquestions');
$PAGE->navbar->add($str_questions);
$PAGE->set_title($course->shortname.': '.$str_questions);
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();

// Get all the appropriate data
if (!($questionnaires = get_all_instances_in_course('sliclquestions', $course))) {
    notice(get_string('therearenone', 'moodle', $str_questions), '../../course/view.php?id='.$course->id);
    die;
}

// Check if the closing date header is required
$show_closing_header = false;
foreach($questionnaires as $questionnaire) {
    if ($questionnaire->closedate != 0) {
        $show_closing_header = true;
        break;
    }
}

// Configure table to display the list of instances
$headings = array(get_string('name'));
$align    = array('left');

if ($showclosingheader) {
    array_push($headings, get_string('questionnairecloses', 'sliclquestions'));
    array_push($align, 'left');
}
array_unshift($headings, get_string('section_name', 'format_'.$course->format));
array_unshift($align, 'left');

$showing = '';

// Check the permissions
if (has_capability('mod/sliclquestions:viewsingleresponse', $course_context)) {
    array_push($headings, get_string('responses', 'sliclquestions'));
    array_push($align, 'center');
    $showing = 'stats';
    array_push($headings, get_string('realm', 'sliclquestions'));
    array_push($align, 'left');
} else if (has_capability('mod/sliclquestions:submit', $course_context)) {
    array_push($headings, get_string('status'));
    array_push($align, 'left');
    $showing = 'responses';
}
$table = new html_table;
$table->head  = $headings;
$table->align = $align;

// Populate the table with the instances
$current_section = '';
foreach($questionnaires as $questionnaire) {
    $cmid = $questionnaire->coursemodule;
    $data = array();
    $realm = $DB->get_field('sliclquestions_survey', 'realm', array('id' => $questionnaire->$id));
    if (!(($realm == 'template') && !has_capability('mod/sliclquestions:manage', context_module::instance($cmid)))) {

        // Section number if necessary
        $str_section = '';
        if ($questionnaire->section != $current_section) {
            $str_section = get_section_name($course, $questionnaire->section);
            $current_section = $questionnaire->section;
        }
        $data[] = $str_section;

        // Show normal if the mod is visible
        $class = '';
        if (!$questionnaire->visible) {
            $class = ' class="dimmed"';
        }
        $data = '<a href="view.php?id='.$cmid.'"'.$class.'>'.$questionnaire->name.'</a>';

        // Close date
        if ($questionnaire->closedate) {
            $data[] = userdate($questionnaire->closedate);
        } else {
            $data[] = '';
        }

        if ($showing == 'responses') {
            $status = '';
            if (($responses = sliclquestions_get_user_responses($questionnaire->id, $USER->id, $complete = false))) {
                foreach($responses as $response) {
                    if ($response->complete == 'y') {
                        $status .= get_string('submitted', 'sliclquestions')
                                  .' '
                                  .userdate($response->submitted)
                                  .'<br>';
                    } else {
                        $status .= get_string('attemptstillinprogress', 'sliclquestions')
                                  .' '
                                  .userdate($response->submitted)
                                  .'<br>';
                    }
                }
            }
            $data[] = $status;
        } elseif ($showing == 'stats') {
            $data[] = $DB->count_records('sliclquestions_response', array('surveyid' => $questionaire->sid, 'complete' => 'y'));
            if (($survey = $DB->get_record('sliclquestions_survey', array('id' => $questionaire->id)))) {
                // For a public questionnaire, look for the original public questionnaire
                if ($survey->realm == 'public') {

                } else {
                    $data[] = get_string($resalm, 'sliclquestions');
                }
            } else {
                // Original questionnaire has been deleted
                $data[] = get_string('removenitinuse', 'sliclquestions');
            }
        }
    }
    $table->data[] = $data;
}
echo html_writer::table($table);

// Complete the page
echo $OUTPUT->footer();
