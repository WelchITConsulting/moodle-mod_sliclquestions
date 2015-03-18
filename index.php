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
if (!($sliclquestionnaires = get_all_instances_in_course('sliclquestions', $course))) {
    notice(get_string('therearenone', 'moodle', $str_questions), '../../course/view.php?id='.$course->id);
    die;
}

// Check if the closing date header is required
$show_closing_header = false;
foreach($sliclquestionnaires as $sliclquestions) {
    if ($sliclquestions->closedate != 0) {
        $show_closing_header = true;
        break;
    }
}

// Configure table to display the list of instances
$headings = array(get_string('name'));
$align    = array('left');

if ($show_closing_header) {
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
foreach($sliclquestionnaires as $sliclquestions) {
    $cmid = $sliclquestions->coursemodule;
    $data = array();
    $realm = $DB->get_field('sliclquestions_survey', 'realm', array('id' => $sliclquestions->$id));
    if (!(($realm == 'template') && !has_capability('mod/sliclquestions:manage', context_module::instance($cmid)))) {

        // Section number if necessary
        $str_section = '';
        if ($sliclquestions->section != $current_section) {
            $str_section = get_section_name($course, $sliclquestions->section);
            $current_section = $sliclquestions->section;
        }
        $data[] = $str_section;

        // Show normal if the mod is visible
        $class = '';
        if (!$sliclquestions->visible) {
            $class = ' class="dimmed"';
        }
        $data = '<a href="view.php?id='.$cmid.'"'.$class.'>'.$sliclquestions->name.'</a>';

        // Close date
        if ($sliclquestions->closedate) {
            $data[] = userdate($sliclquestions->closedate);
        } elseif ($show_closing_header) {
            $data[] = '';
        }

        if ($showing == 'responses') {
            $status = '';
            if (($responses = sliclquestions_get_user_responses($sliclquestions->id, $USER->id, $complete = false))) {
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
            $data[] = $DB->count_records('sliclquestions_response', array('surveyid' => $sliclquestions->sid, 'complete' => 'y'));
            if (($survey = $DB->get_record('sliclquestions_survey', array('id' => $sliclquestions->id)))) {
                // For a public questionnaire, look for the original public questionnaire
                if ($survey->realm == 'public') {
                    $strpreview = get_string('preview_questionnaire', 'sliclquestions');
                    if ($survey->owner != $course->id) {
                        $publicoriginal = '';
                        $originalcourse = $DB->get_record('course', array('id' => $survey->owner));
                        $originalcoursecontext = context_course::instance($survey->owner);
                        $originalsliclquestions = $DB->get_record('sliclquestions',
                                                                  array('sid'    => $survey->id,
                                                                        'course' => $survey->owner));
                        $cm = get_coursemodule_from_instance("sliclquestions", $originalsliclquestions->id, $survey->owner);
                        $context = context_course::instance($survey->owner, MUST_EXIST);
                        $canvieworiginal = has_capability('mod/sliclquestions:preview', $context, $USER->id, true);
                        // If current user can view questionnaires in original course,
                        // provide a link to the original public questionnaire.
                        if ($canvieworiginal) {
                            $publicoriginal = '<br />'.get_string('publicoriginal', 'questionnaire').'&nbsp;'.
                                '<a href="'.$CFG->wwwroot.'/mod/sliclquestions/preview.php?id='.
                                $cm->id.'" title="'.$strpreview.']">'.$originalsliclquestions->name.' ['.
                                $originalcourse->fullname.']</a>';
                        } else {
                            // If current user is not enrolled as teacher in original course,
                            // only display the original public questionnaire's name and course name.
                            $publicoriginal = '<br />'.get_string('publicoriginal', 'sliclquestions').'&nbsp;'.
                                $originalsliclquestions->name.' ['.$originalcourse->fullname.']';
                        }
                        $data[] = get_string($realm, 'sliclquestions').' '.$publicoriginal;
                    } else {
                        // Original public questionnaire was created in current course.
                        // Find which courses it is used in.
                        $publiccopy = '';
                        $select = 'course != '.$course->id.' AND sid = '.$sliclquestions->sid;
                        if ($copies = $DB->get_records_select('sliclquestions', $select, null, $sort = 'course ASC', $fields = 'id, course, name')) {
                            foreach ($copies as $copy) {
                                $copycourse = $DB->get_record('course', array('id' => $copy->course));
                                $select = 'course = '.$copycourse->id.' AND sid = '.$sliclquestions->sid;
                                $copysliclquestions = $DB->get_record('sliclquestions',
                                                                      array('id'     => $copy->id,
                                                                            'sid'    => $survey->id,
                                                                            'course' => $copycourse->id));
                                $cm = get_coursemodule_from_instance("sliclquestions", $copysliclquestions->id, $copycourse->id);
                                $context = context_course::instance($copycourse->id, MUST_EXIST);
                                $canviewcopy = has_capability('mod/sliclquestions:view', $context, $USER->id, true);
                                if ($canviewcopy) {
                                    $publiccopy .= '<br />'.get_string('publiccopy', 'sliclquestions').'&nbsp;:&nbsp;'.
                                        '<a href = "'.$CFG->wwwroot.'/mod/sliclquestions/preview.php?id='.
                                        $cm->id.'" title = "'.$strpreview.'">'.
                                        $copysliclquestions->name.' ['.$copycourse->fullname.']</a>';
                                } else {
                                    // If current user does not have "view" capability in copy course,
                                    // only display the copied public questionnaire's name and course name.
                                    $publiccopy .= '<br />'.get_string('publiccopy', 'sliclquestions').'&nbsp;:&nbsp;'.
                                        $copysliclquestions->name.' ['.$copycourse->fullname.']';
                                }
                            }
                        }
                        $data[] = get_string($realm, 'sliclquestions').' '.$publiccopy;
                    }
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
