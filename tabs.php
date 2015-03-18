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
 * Filename : tabs
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

/**
 * prints the tabbed bar
 *
 * @author Mike Churchward
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package sliclquestions
 */
global $DB, $SESSION;
$tabs = array();
$row  = array();
$inactive = array();
$activated = array();
if (!isset($SESSION->sliclquestions)) {
    $SESSION->sliclquestions = new stdClass();
}
$currenttab = $SESSION->sliclquestions->current_tab;

// If this sliclquestions has a survey, get the survey and owner.
// In a sliclquestions instance created "using" a PUBLIC sliclquestions, prevent anyone from editing settings, editing questions,
// viewing all responses...except in the course where that PUBLIC sliclquestions was originally created.

$courseid = $sliclquestions->course->id;
if ($survey = $DB->get_record('sliclquestions_survey', array('id' => $sliclquestions->sid))) {
    $owner = (trim($survey->owner) == trim($courseid));
} else {
    $survey = false;
    $owner = true;
}
if ($sliclquestions->capabilities->manage  && $owner) {
    $row[] = new tabobject('settings', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/qsettings.php?'.
            'id='.$sliclquestions->cm->id), get_string('advancedsettings'));
}

if ($sliclquestions->capabilities->editquestions && $owner) {
    $row[] = new tabobject('questions', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/questions.php?'.
            'id='.$sliclquestions->cm->id), get_string('questions', 'sliclquestions'));
}

if ($sliclquestions->capabilities->preview && $owner) {
    if (!empty($sliclquestions->questions)) {
        $row[] = new tabobject('preview', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/preview.php?'.
                        'id='.$sliclquestions->cm->id), get_string('preview_label', 'sliclquestions'));
    }
}

$usernumresp = $sliclquestions->count_submissions($USER->id);

if ($sliclquestions->capabilities->readownresponses && ($usernumresp > 0)) {
    $argstr = 'instance='.$sliclquestions->id.'&user='.$USER->id.'&group='.$currentgroupid;
    if ($usernumresp == 1) {
        $argstr .= '&byresponse=1&action=vresp';
        $yourrespstring = get_string('yourresponse', 'sliclquestions');
    } else {
        $yourrespstring = get_string('yourresponses', 'sliclquestions');
    }
    $row[] = new tabobject('myreport', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/myreport.php?'.
                           $argstr), $yourrespstring);

    if ($usernumresp > 1 && in_array($currenttab, array('mysummary', 'mybyresponse', 'myvall', 'mydownloadcsv'))) {
        $inactive[] = 'myreport';
        $activated[] = 'myreport';
        $row2 = array();
        $argstr2 = $argstr.'&action=summary';
        $row2[] = new tabobject('mysummary', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/myreport.php?'.$argstr2),
                                get_string('summary', 'sliclquestions'));
        $argstr2 = $argstr.'&byresponse=1&action=vresp';
        $row2[] = new tabobject('mybyresponse', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/myreport.php?'.$argstr2),
                                get_string('viewindividualresponse', 'sliclquestions'));
        $argstr2 = $argstr.'&byresponse=0&action=vall&group='.$currentgroupid;
        $row2[] = new tabobject('myvall', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/myreport.php?'.$argstr2),
                                get_string('myresponses', 'sliclquestions'));
        if ($sliclquestions->capabilities->downloadresponses) {
            $argstr2 = $argstr.'&action=dwnpg';
            $link  = $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2);
            $row2[] = new tabobject('mydownloadcsv', $link, get_string('downloadtext'));
        }
    } else if (in_array($currenttab, array('mybyresponse', 'mysummary'))) {
        $inactive[] = 'myreport';
        $activated[] = 'myreport';
    }
}

$numresp = $sliclquestions->count_submissions();
// Number of responses in currently selected group (or all participants etc.).
if (isset($SESSION->sliclquestions->numselectedresps)) {
    $numselectedresps = $SESSION->sliclquestions->numselectedresps;
} else {
    $numselectedresps = $numresp;
}

// If sliclquestions is set to separate groups, prevent user who is not member of any group
// to view All responses.
$canviewgroups = true;
$groupmode = groups_get_activity_groupmode($cm, $course);
if ($groupmode == 1) {
    $canviewgroups = groups_has_membership($cm, $USER->id);
}
$canviewallgroups = has_capability('moodle/site:accessallgroups', $context);

if (($canviewallgroups || ($canviewgroups && $sliclquestions->capabilities->readallresponseanytime))
                && $numresp > 0 && $owner && $numselectedresps > 0) {
    $argstr = 'instance='.$sliclquestions->id;
    $row[] = new tabobject('allreport', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.
                           $argstr.'&action=vall'), get_string('viewallresponses', 'sliclquestions'));
    if (in_array($currenttab, array('vall', 'vresp', 'valldefault', 'vallasort', 'vallarsort', 'deleteall', 'downloadcsv',
                                     'vrespsummary', 'individualresp', 'printresp', 'deleteresp'))) {
        $inactive[] = 'allreport';
        $activated[] = 'allreport';
        if ($currenttab == 'vrespsummary' || $currenttab == 'valldefault') {
            $inactive[] = 'vresp';
        }
        $row2 = array();
        $argstr2 = $argstr.'&action=vall&group='.$currentgroupid;
        $row2[] = new tabobject('vall', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                get_string('summary', 'sliclquestions'));
        if ($sliclquestions->capabilities->viewsingleresponse) {
            $argstr2 = $argstr.'&byresponse=1&action=vresp&group='.$currentgroupid;
            $row2[] = new tabobject('vrespsummary', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                get_string('viewbyresponse', 'sliclquestions'));
            if ($currenttab == 'individualresp' || $currenttab == 'deleteresp') {
                $argstr2 = $argstr.'&byresponse=1&action=vresp';
                $row2[] = new tabobject('vresp', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                        get_string('viewindividualresponse', 'sliclquestions'));
            }
        }
    }
    if (in_array($currenttab, array('valldefault',  'vallasort', 'vallarsort', 'deleteall', 'downloadcsv'))) {
        $activated[] = 'vall';
        $row3 = array();

        $argstr2 = $argstr.'&action=vall&group='.$currentgroupid;
        $row3[] = new tabobject('valldefault', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                get_string('order_default', 'sliclquestions'));
        if ($currenttab != 'downloadcsv' && $currenttab != 'deleteall') {
            $argstr2 = $argstr.'&action=vallasort&group='.$currentgroupid;
            $row3[] = new tabobject('vallasort', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                    get_string('order_ascending', 'sliclquestions'));
            $argstr2 = $argstr.'&action=vallarsort&group='.$currentgroupid;
            $row3[] = new tabobject('vallarsort', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                    get_string('order_descending', 'sliclquestions'));
        }
        if ($sliclquestions->capabilities->deleteresponses) {
            $argstr2 = $argstr.'&action=delallresp&group='.$currentgroupid;
            $row3[] = new tabobject('deleteall', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                    get_string('deleteallresponses', 'sliclquestions'));
        }

        if ($sliclquestions->capabilities->downloadresponses) {
            $argstr2 = $argstr.'&action=dwnpg&group='.$currentgroupid;
            $link  = $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2);
            $row3[] = new tabobject('downloadcsv', $link, get_string('downloadtext'));
        }
    }

    if (in_array($currenttab, array('individualresp', 'printresp', 'deleteresp'))) {
        $inactive[] = 'vresp';
        $activated[] = 'vresp';
        $inactive[] = 'printresp';

        $row3 = array();

        // New way to output popup print window for 2.0.
        $linkname = get_string('print', 'sliclquestions');
        $url = '/mod/sliclquestions/print.php?qid='.$sliclquestions->id.'&amp;rid='.$rid.
               '&amp;courseid='.$course->id.'&amp;sec=1';
        $title = get_string('printtooltip', 'sliclquestions');
        $options = array('menubar' => true, 'location' => false, 'scrollbars' => true,
                        'resizable' => true, 'height' => 600, 'width' => 800);
        $name = 'popup';
        $link = new moodle_url($url);
        $action = new popup_action('click', $link, $name, $options);
        $actionlink = $OUTPUT->action_link($link, $linkname, $action, array('title' => $title));
        $row3[] = new tabobject('printresp', '', $actionlink);

        if ($sliclquestions->capabilities->deleteresponses) {
            $argstr2 = $argstr.'&action=dresp&rid='.$rid.'&individualresponse=1';
            $row3[] = new tabobject('deleteresp', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                            get_string('deleteresp', 'sliclquestions'));
        }
    }
} else if ($canviewgroups && $sliclquestions->capabilities->readallresponses && ($numresp > 0) && $canviewgroups &&
           ($sliclquestions->resp_view == QUESTIONNAIRE_STUDENTVIEWRESPONSES_ALWAYS ||
            ($sliclquestions->resp_view == QUESTIONNAIRE_STUDENTVIEWRESPONSES_WHENCLOSED
                && $sliclquestions->is_closed()) ||
            ($sliclquestions->resp_view == QUESTIONNAIRE_STUDENTVIEWRESPONSES_WHENANSWERED
                && $usernumresp > 0 )) &&
           $sliclquestions->is_survey_owner()) {
    $argstr = 'instance='.$sliclquestions->id.'&sid='.$sliclquestions->sid;
    $row[] = new tabobject('allreport', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.
                           $argstr.'&action=vall&group='.$currentgroupid), get_string('viewallresponses', 'sliclquestions'));
    if (in_array($currenttab, array('valldefault',  'vallasort', 'vallarsort', 'deleteall', 'downloadcsv'))) {
        $inactive[] = 'vall';
        $activated[] = 'vall';
        $row2 = array();
        $argstr2 = $argstr.'&action=vall&group='.$currentgroupid;
        $row2[] = new tabobject('valldefault', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                get_string('summary', 'sliclquestions'));
        $inactive[] = $currenttab;
        $activated[] = $currenttab;
        $row3 = array();
        $argstr2 = $argstr.'&action=vall&group='.$currentgroupid;
        $row3[] = new tabobject('valldefault', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                get_string('order_default', 'sliclquestions'));
        $argstr2 = $argstr.'&action=vallasort&group='.$currentgroupid;
        $row3[] = new tabobject('vallasort', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                get_string('order_ascending', 'sliclquestions'));
        $argstr2 = $argstr.'&action=vallarsort&group='.$currentgroupid;
        $row3[] = new tabobject('vallarsort', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                get_string('order_descending', 'sliclquestions'));
        if ($sliclquestions->capabilities->deleteresponses) {
            $argstr2 = $argstr.'&action=delallresp';
            $row2[] = new tabobject('deleteall', $CFG->wwwroot.htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2),
                                    get_string('deleteallresponses', 'sliclquestions'));
        }

        if ($sliclquestions->capabilities->downloadresponses) {
            $argstr2 = $argstr.'&action=dwnpg';
            $link  = htmlspecialchars('/mod/sliclquestions/report.php?'.$argstr2);
            $row2[] = new tabobject('downloadcsv', $link, get_string('downloadtext'));
        }
        if (count($row2) <= 1) {
            $currenttab = 'allreport';
        }
    }
}

if ($sliclquestions->capabilities->viewsingleresponse && ($canviewallgroups || $canviewgroups)) {
    $nonrespondenturl = new moodle_url('/mod/sliclquestions/show_nonrespondents.php', array('id' => $sliclquestions->cm->id));
    $row[] = new tabobject('nonrespondents',
                    $nonrespondenturl->out(),
                    get_string('show_nonrespondents', 'sliclquestions'));
}

if ((count($row) > 1) || (!empty($row2) && (count($row2) > 1))) {
    $tabs[] = $row;

    if (!empty($row2) && (count($row2) > 1)) {
        $tabs[] = $row2;
    }

    if (!empty($row3) && (count($row3) > 1)) {
        $tabs[] = $row3;
    }

    print_tabs($tabs, $currenttab, $inactive, $activated);

}