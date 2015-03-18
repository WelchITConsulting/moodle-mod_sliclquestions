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
 * Filename : myreport
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/sliclquestions/sliclquestions.class.php');

$instance = required_param('instance', PARAM_INT);   // Questionnaire ID.
$userid = optional_param('user', $USER->id, PARAM_INT);
$rid = optional_param('rid', null, PARAM_INT);
$byresponse = optional_param('byresponse', 0, PARAM_INT);
$action = optional_param('action', 'summary', PARAM_RAW);
$currentgroupid = optional_param('group', 0, PARAM_INT); // Groupid.

if (! $sliclquestions = $DB->get_record("sliclquestions", array("id" => $instance))) {
    print_error('incorrectsliclquestions', 'sliclquestions');
}
if (! $course = $DB->get_record("course", array("id" => $sliclquestions->course))) {
    print_error('coursemisconf');
}
if (! $cm = get_coursemodule_from_instance("sliclquestions", $sliclquestions->id, $course->id)) {
    print_error('invalidcoursemodule');
}

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
$sliclquestions->canviewallgroups = has_capability('moodle/site:accessallgroups', $context);
// Should never happen, unless called directly by a snoop...
if ( !has_capability('mod/sliclquestions:readownresponses', $context)
    || $userid != $USER->id) {
    print_error('Permission denied');
}
$url = new moodle_url($CFG->wwwroot.'/mod/sliclquestions/myreport.php', array('instance' => $instance));
if (isset($userid)) {
    $url->param('userid', $userid);
}
if (isset($byresponse)) {
    $url->param('byresponse', $byresponse);
}

if (isset($currentgroupid)) {
    $url->param('group', $currentgroupid);
}

if (isset($action)) {
    $url->param('action', $action);
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('sliclquestionsreport', 'sliclquestions'));
$PAGE->set_heading(format_string($course->fullname));

$sliclquestions = new sliclquestions(0, $sliclquestions, $course, $cm);
$sid = $sliclquestions->survey->id;
$courseid = $course->id;

// Tab setup.
if (!isset($SESSION->sliclquestions)) {
    $SESSION->sliclquestions = new stdClass();
}
$SESSION->sliclquestions->current_tab = 'myreport';

switch ($action) {
    case 'summary':
        if (empty($sliclquestions->survey)) {
            print_error('surveynotexists', 'sliclquestions');
        }
        $SESSION->sliclquestions->current_tab = 'mysummary';
        $select = 'survey_id = '.$sliclquestions->sid.' AND username = \''.$userid.'\' AND complete=\'y\'';
        $resps = $DB->get_records_select('sliclquestions_response', $select);
        if (!$resps = $DB->get_records_select('sliclquestions_response', $select)) {
            $resps = array();
        }
        $rids = array_keys($resps);
        if (count($resps) > 1) {
            $titletext = get_string('myresponsetitle', 'sliclquestions', count($resps));
        } else {
            $titletext = get_string('yourresponse', 'sliclquestions');
        }

        // Print the page header.
        echo $OUTPUT->header();

        // Print the tabs.
        include('tabs.php');

        echo $OUTPUT->heading($titletext);
        echo '<div class = "generalbox">';
        $sliclquestions->survey_results(1, 1, '', '', $rids, $USER->id);
        echo '</div>';

        // Finish the page.
        echo $OUTPUT->footer($course);
        break;

    case 'vall':
        if (empty($sliclquestions->survey)) {
            print_error('surveynotexists', 'sliclquestions');
        }
        $SESSION->sliclquestions->current_tab = 'myvall';
        $select = 'survey_id = '.$sliclquestions->sid.' AND username = \''.$userid.'\' AND complete=\'y\'';
        $sort = 'submitted ASC';
        $resps = $DB->get_records_select('sliclquestions_response', $select, $params = null, $sort);
        $titletext = get_string('myresponses', 'sliclquestions');

        // Print the page header.
        echo $OUTPUT->header();

        // Print the tabs.
        include('tabs.php');

        echo $OUTPUT->heading($titletext.':');
        $sliclquestions->view_all_responses($resps);

        // Finish the page.
        echo $OUTPUT->footer($course);
        break;

    case 'vresp':
        if (empty($sliclquestions->survey)) {
            print_error('surveynotexists', 'sliclquestions');
        }
        $SESSION->sliclquestions->current_tab = 'mybyresponse';
        if ($CFG->sliclquestions_usergraph) {
            $charttype = $sliclquestions->survey->chart_type;
            if ($charttype) {
                $PAGE->requires->js('/mod/sliclquestions/javascript/RGraph/RGraph.common.core.js');

                switch ($charttype) {
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
                        $PAGE->requires->js('/mod/sliclquestions/javascript/RGraph/RGraph.vprogress.js');
                        break;
                }
            }
        }
        $select = 'survey_id = '.$sliclquestions->sid.' AND username = \''.$userid.'\' AND complete=\'y\'';
        $sort = 'submitted ASC';
        $resps = $DB->get_records_select('sliclquestions_response', $select, $params = null, $sort);
        // All participants.
        $sql = "SELECT R.id, R.survey_id, R.submitted, R.username
         FROM {sliclquestions_response} R
         WHERE R.survey_id = ? AND
               R.complete='y'
         ORDER BY R.id";
        if (!($respsallparticipants = $DB->get_records_sql($sql, array($sid)))) {
            $respsallparticipants = array();
        }
        $select = 'survey_id = '.$sliclquestions->sid.' AND username = \''.$userid.'\' AND complete=\'y\'';
        $fields = "id,survey_id,submitted,username";
        $params = array();
        $respsuser = $DB->get_records_select('sliclquestions_response', $select, $params, $sort = '', $fields);
        $SESSION->sliclquestions->numrespsallparticipants = count ($respsallparticipants);
        $SESSION->sliclquestions->numselectedresps = $SESSION->sliclquestions->numrespsallparticipants;
        $iscurrentgroupmember = false;

        // Available group modes (0 = no groups; 1 = separate groups; 2 = visible groups).
        $groupmode = groups_get_activity_groupmode($cm, $course);
        if ($groupmode > 0) {
            // Check if current user is member of any group.
            $usergroups = groups_get_user_groups($courseid, $userid);
            $isgroupmember = count($usergroups[0]) > 0;
            // Check if current user is member of current group.
            $iscurrentgroupmember = groups_is_member($currentgroupid, $userid);

            if ($groupmode == 1) {
                $sliclquestionsgroups = groups_get_all_groups($course->id, $userid);
            }
            if ($groupmode == 2 || $sliclquestions->canviewallgroups) {
                $sliclquestionsgroups = groups_get_all_groups($course->id);
            }

            if (!empty($sliclquestionsgroups)) {
                $groupscount = count($sliclquestionsgroups);
                foreach ($sliclquestionsgroups as $key) {
                    $firstgroupid = $key->id;
                    break;
                }
                if ($groupscount === 0 && $groupmode == 1) {
                    $currentgroupid = 0;
                }
                if ($groupmode == 1 && !$sliclquestions->canviewallgroups && $currentgroupid == 0) {
                    $currentgroupid = $firstgroupid;
                }
                // If currentgroup is All Participants, current user is of course member of that "group"!
                if ($currentgroupid == 0) {
                    $iscurrentgroupmember = true;
                }
                // Current group members.
                $castsql = $DB->sql_cast_char2int('R.username');
                $sql = "SELECT R.id, R.survey_id, R.submitted, R.username
            FROM {sliclquestions_response} R,
                {groups_members} GM
             WHERE R.survey_id= ? AND
               R.complete='y' AND
               GM.groupid = ? AND " . $castsql . "=GM.userid
            ORDER BY R.id";
                if (!($currentgroupresps = $DB->get_records_sql($sql, array($sid, $currentgroupid)))) {
                    $currentgroupresps = array();
                }

            } else {
                // Groupmode = separate groups but user is not member of any group
                // and does not have moodle/site:accessallgroups capability -> refuse view responses.
                if (!$sliclquestions->canviewallgroups) {
                    $currentgroupid = 0;
                }
            }

            if ($currentgroupid > 0) {
                $groupname = get_string('group').' <strong>'.groups_get_group_name($currentgroupid).'</strong>';
            } else {
                $groupname = '<strong>'.get_string('allparticipants').'</strong>';
            }
        }

        $rids = array_keys($resps);
        if (!$rid) {
            // If more than one response for this respondent, display most recent response.
            $rid = end($rids);
        }
        $numresp = count($rids);
        if ($numresp > 1) {
            $titletext = get_string('myresponsetitle', 'sliclquestions', $numresp);
        } else {
            $titletext = get_string('yourresponse', 'sliclquestions');
        }

        $compare = false;
        // Print the page header.
        echo $OUTPUT->header();

        // Print the tabs.
        include('tabs.php');
        echo $OUTPUT->box_start();

        echo $OUTPUT->heading($titletext);

        if (count($resps) > 1) {
            $userresps = $resps;
            echo '<div style="text-align:center; padding-bottom:5px;">';
            $sliclquestions->survey_results_navbar_student ($rid, $userid, $instance, $userresps);
            echo '</div>';
        }
        $resps = array();
        // Determine here which "global" responses should get displayed for comparison with current user.
        // Current user is viewing his own group's results.
        if (isset($currentgroupresps)) {
            $resps = $currentgroupresps;
        }

        // Current user is viewing another group's results so we must add their own results to that group's results.

        if (!$iscurrentgroupmember) {
            $resps += $respsuser;
        }
        // No groups.
        if ($groupmode == 0 || $currentgroupid == 0) {
            $resps = $respsallparticipants;
        }
        $compare = true;
        $sliclquestions->view_response($rid, null, null, $resps, $compare, $iscurrentgroupmember,
                        $allresponses = false, $currentgroupid);
        if (isset($userresps) && count($userresps) > 1) {
            echo '<div style="text-align:center; padding-bottom:5px;">';
            $sliclquestions->survey_results_navbar_student ($rid, $userid, $instance, $userresps);
            echo '</div>';
        }
        echo $OUTPUT->box_end();
        // Finish the page.
        echo $OUTPUT->footer($course);
        break;

    case get_string('return', 'sliclquestions'):
    default:
        redirect('view.php?id='.$cm->id);
}
