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

$id             = optional_param('id', false, PARAM_INT);       //
$sid            = optional_param('sid', false, PARAM_INT);      // SLiCL Questions ID
$act            = optional_param('act', 'vall', PARAM_ALPHA);   // Action to perform
$rid            = optional_param('rid', false, PARAM_INT);      // Response ID
$currentgroupid = optional_param('group', 0, PARAM_INT);        // Group

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

if (!isset($SESSION->sliclquestions)) {
    $SESSION->sliclquestions = new stdClass();
}
$SESSION->sliclquestions->current_tab = 'allreport';

// Get all the responses for all the reports
$sql = 'SELECT * FROM {sliclquestions_response}'
     . ' WHERE survey_id=? AND complete=\'y\'';
if (!$allresponses = $DB->get_records_sql($sql, array($sid))) {
    $allresponses = array();
}
$SESSION->sliclquestions->numresponses = count($allresponses);
$SESSION->sliclquestions->numselectedresps = $SESSION->sliclquestions->numresponses;
$SESSION->sliclquestions->respscount = 0;
$SESSION->sliclquestions->surveyid = $sid;

// Group processing
$groupmode = groups_get_activity_groupmode($cm, $course);
$sliclquestionsgroups = '';
if ($groupmode > 0) {
    if ($groupmode == 1) {
        $sliclquestionsgroups = groups_get_all_groupings($course->id, $USER->id);
    }
    if ($groupmode == 2 || $survey->capabilities->canviewallgroups) {
        $sliclquestionsgroups = groups_get_all_groupings($course->id);
    }
    if (!empty($sliclquestionsgroups)) {
        $groupscount = count($sliclquestionsgroups);
        foreach($sliclquestionsgroups as $grp) {
            $firstgroupid = $grp->id;
            break;
        }
        if (($groupscount == 0) && ($groupmode == 1)) {
            $currentgroupid = 0;
        }
        if (($groupmode == 1) && !$survey->capabilities->canviewallgroups &&
                ($currentgroupid == 0)) {
            $currentgroupid = $firstgroupid;
        }
        $sql = 'SELECT r.* FROM {sliclquestions_reponse} r, {groups_members} gm'
             . ' WHERE r.userid=g.userid AND r.survey_id=? AND r.complete=\'y\''
             . ' AND g,groupid=? ORDER BY r.id';
        if (!$currentgroupresps = $DB->get_records_sql($sql, array($sid, $currentgroupid))) {
            $currentgroupresps = array();
        }
        $SESSION->sliclquestions->numcurrentgroupresps = count($currentgroupresps);
    } else {
        if (!$survey->capabilities->canviewallgroups) {
            $currentgroupid = 0;
        }
    }
    if ($currentgroupid > 0) {
        $groupname = get_string('group')
                   . html_writer::tag('strong', groups_get_group_name($currentgroupid));
    } else {
        $groupname = html_writer::tag('strong', get_string('allparticipants'));
    }
}
$usergraph = get_config('sliclquestions', 'usergraph');
if ($usergraph) {
    $charttype = $sliclquestions->chart_type;
    if ($charttype) {
        $PAGE->requires->js('/mod/sliclquestions/javascript/RGraph/RGraph.common.core.js');
        switch($charttype) {
            case 'bipolar':
                $PAGE->requires->js('/mod/sliclquestions/javascript/RGraph/RGraph.bipolar.js');
                break;
            case 'hbar':
                $PAGE->requires->js('/mod/sliclquestions/javascript/RGraph/RGraph.hbar.js');
                break;
            case 'radar':
                $PAGE->requires->js('/mod/sliclquestions/javascript/RGraph/RGraph.radar.js');
                break;
            case 'rose':
                $PAGE->requires->js('/mod/sliclquestions/javascript/RGraph/RGraph.rose.js');
                break;
            case 'vprogress':
                $PAGE->requires->js('/mod/sliclquestions/javascript/RGraph/RGraph.vprogres.js');
                break;
        }
    }
}
switch($act) {

    case 'dresp':           // Delete individual respons? Ask for confirmation
        require_capability('mod/sliclquestions:deleteresponses', $context);
        if (empty($survey)) {
            print_error('surveynotexists', 'sliclquestions');
        } elseif ($survey->course != $course->id) {
            print_error('surveyowner', 'sliclquestions');
        } elseif (!$rid || !is_numeric($rid)) {
            print_error('invalid_response', 'sliclquestions');
        } elseif (!($resp = $DB->get_record('sliclquestions_response', array('id' => $rid)))) {
            print_error('invalidresponserecord', 'sliclquestions');
        }
        $ruser = false;
        if ($user = $DB->get_record('user', array('id' => $resp->userid))) {
            $ruser = fullname($user);
        } else {
            $ruser = '- ' . get_string('unknown', 'sliclquestions') . ' -';
        }
        $PAGE->set_title(get_string('deletingresp', 'sliclquestions'));
        $PAGE->set_heading(format_string($course->fullname));
        echo $OUTPUT->header();
        $SESSION->sliclquestions->current_tab = 'deleteresp';
        include('tabs.php');
        $timesubmitted = html_writer::empty_tag('br')
                       . get_string('submitted', 'sliclquestions')
                       . '&nbsp;'
                       . userdate($resp->submitted);
        $msg = html_writer::div(get_string('confirmdelresp', 'sliclquestions', $ruser . $timesubmitted),
                                'warning centerpara');
        $buttonyes = new single_button(new moodle_url('report.php', array('action'             => 'dvresp',
                                                                          'rid'                => $rid,
                                                                          'individualresponse' => 1,
                                                                          'instance'           => $id,
                                                                          'group'              => $currentgroupid)),
                                       get_string('yes'),
                                       'post');
        $buttonno  = new single_button(new moodle_url('report.php', array('action'             => 'vresp',
                                                                          'rid'                => $rid,
                                                                          'individualresponse' => 1,
                                                                          'instance'           => $id,
                                                                          'group'              => $currentgroupid)),
                                       get_string('no'),
                                       'get');
        echo html_writer::tag('p', '&nbsp;')
           . html_writer::div(get_string('confirmdekeeed', 'sliclquestions'), 'warning centerpara')
           . $OUTPUT->confirm($msg, $buttonyes, $buttonno)
           . $OUTPUT->footer($course);
        break;

    case 'delallresp':      // Delete all responses ? Ask for comfirmation

        break;

    case 'dvresp':          // Delete single response

        break;

    case 'dvallresp':       // Delete all responses in SLiCL Questions (or group)

        break;

    case 'dwnpg':           // Download page options

        break;

    case 'dcsv':            // Doenload responses data as text (csv( format

        break;

    case 'vall':            // View all responses
    case 'vallasort':       // View all responses sorted in ascending order
    case 'vallarsort':      // View all responses sorted in descending order

        break;

    case 'vresp':           // View by reponse
    default:
        if (empty($survey)) {
            print_error('surveynotexists', 'sliclquestions');
        } elseif ($survey->course != $course->id) {
            print_error('surveyowner', 'sliclquestions');
        }
        $ruser = false;
        $noresponses = false;
        if ($byresponse || $rid) {
            if ($groupmode > 0) {
                switch($currentgroupid) {
                    case 0:
                        $resps = $respsallparticipants;
                        break;
                    default:
                        $sql = 'SELECT r.*'
                             . ' FROM {sliclquestions_response} r, {group_members} g'
                             . ' WHERE r.userid=g.userid AND r.survey_id=? AND g.groupid=?';
                }
            }
        }
        break;
}
