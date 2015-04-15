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
 * Filename : lib
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */


function sliclquestions_add_instance($sliclquestions)
{
    global $DB;
}

function sliclquestions_update_instance($sliclquestions)
{
    global $DB;

    if (!empty($sliclquestions->id) && !empty($sliclquestions->realm)) {
        $DB->set_field('sliclquestions_survey', 'realm', $sliclquestions->realm, array('id' => $sliclquestions->id));
    }

    $sliclquestions->timemodified = time();
    $sliclquestions->id = $sliclquestions->instance;

    // Add the events for the date settings for the item to the calendar
    sliclquestions_set_events($sliclquestions);

    // Update the records and return the results
    return $DB->update_record('sliclquestions', $sliclquestions);
}

/*
// Given an object containing all the necessary data,
// (defined by the form in mod.html) this function
// will update an existing instance with new data.
function sliclquestions_update_instance($sliclquestions) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/sliclquestions/locallib.php');

    // Check the realm and set it to the survey if its set.
    if (!empty($sliclquestions->sid) && !empty($sliclquestions->realm)) {
        $DB->set_field('sliclquestions_survey', 'realm', $sliclquestions->realm, array('id' => $sliclquestions->sid));
    }

    $sliclquestions->timemodified = time();
    $sliclquestions->id = $sliclquestions->instance;

    // May have to add extra stuff in here.
    if (empty($sliclquestions->useopendate)) {
        $sliclquestions->opendate = 0;
    }
    if (empty($sliclquestions->useclosedate)) {
        $sliclquestions->closedate = 0;
    }

    if ($sliclquestions->resume == '1') {
        $sliclquestions->resume = 1;
    } else {
        $sliclquestions->resume = 0;
    }

    // Field sliclquestions->navigate used for branching sliclquestionss. Starting with version 2.5.5.
    /* if ($sliclquestions->navigate == '1') {
        $sliclquestions->navigate = 1;
    } else {
        $sliclquestions->navigate = 0;
    } */

    // Get existing grade item.
    sliclquestions_grade_item_update($sliclquestions);

    sliclquestions_set_events($sliclquestions);

    return $DB->update_record("sliclquestions", $sliclquestions);
}*/






/**
 * Delete an instance of the sliclquestions and all dependant records from the
 * database.
 *
 * @param int $id The ID for the sliclquestions instanc to be deleted
 * @return boolean The result of the record deletion
 */
function sliclquestions_delete_instance($id)
{
    global $DB;

    // Check that the id is a valid instance of the sliclquestions questionnaire
    if ($sliclquestions = $DB->get_record('sliclquestions', array('id' => $id))) {
        return false;
    }

    // Check for and delete any survey and response records
    if ($survey = $DB->get_record('sliclquestions_survey', array('id' => $sliclquestions->sid))) {
        if (!sliclquestions_delete_survey($sliclquestions->sid, $sliclquestions->id)) {
            return false;
        }
    }

    // Check for and remove any entries from the calendar
    if ($events = $DB->get_records('event', array('modulename' => 'sliclquestions',
                                                  'instance'   => $sliclquestions->id))) {
        foreach($events as $event) {
            $event->delete();
        }
    }

    // Remove the instance and return the result
    return $DB->delete_records('sliclquestions', array('id' => $id));
}








define('SLICLQUESTIONS_RESETFORM_RESET', 'sliclquestions_reset_data_');
define('SLICLQUESTIONS_RESETFORM_DROP', 'sliclquestions_drop_sliclquestions_');

function sliclquestions_supports($feature) {
    switch($feature) {
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * @return array all other caps used in module
 */
function sliclquestions_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

function sliclquestions_add_instance($sliclquestions) {
    // Given an object containing all the necessary data,
    // (defined by the form in mod.html) this function
    // will create a new instance and return the id number
    // of the new instance.
    global $COURSE, $DB, $CFG;
    require_once($CFG->dirroot.'/mod/sliclquestions/sliclquestions.class.php');
    require_once($CFG->dirroot.'/mod/sliclquestions/locallib.php');

    // Check the realm and set it to the survey if it's set.

    if (empty($sliclquestions->sid)) {
        // Create a new survey.
        $cm = new Object();
        $qobject = new sliclquestions(0, $sliclquestions, $COURSE, $cm);

        if ($sliclquestions->create == 'new-0') {
            $sdata = new Object();
            $sdata->name = $sliclquestions->name;
            $sdata->realm = 'private';
            $sdata->title = $sliclquestions->name;
            $sdata->subtitle = '';
            $sdata->info = '';
            $sdata->theme = ''; // Theme is deprecated.
            $sdata->thanks_page = '';
            $sdata->thank_head = '';
            $sdata->thank_body = '';
            $sdata->email = '';
            $sdata->feedbacknotes = '';
            $sdata->owner = $COURSE->id;
            if (!($sid = $qobject->survey_update($sdata))) {
                print_error('couldnotcreatenewsurvey', 'sliclquestions');
            }
        } else {
            $copyid = explode('-', $sliclquestions->create);
            $copyrealm = $copyid[0];
            $copyid = $copyid[1];
            if (empty($qobject->survey)) {
                $qobject->add_survey($copyid);
                $qobject->add_questions($copyid);
            }
            // New sliclquestionss created as "use public" should not create a new survey instance.
            if ($copyrealm == 'public') {
                $sid = $copyid;
            } else {
                $sid = $qobject->sid = $qobject->survey_copy($COURSE->id);
                // All new sliclquestionss should be created as "private".
                // Even if they are *copies* of public or template sliclquestionss.
                $DB->set_field('sliclquestions_survey', 'realm', 'private', array('id' => $sid));
            }
        }
        $sliclquestions->sid = $sid;
    }

    $sliclquestions->timemodified = time();

    // May have to add extra stuff in here.
    if (empty($sliclquestions->useopendate)) {
        $sliclquestions->opendate = 0;
    }
    if (empty($sliclquestions->useclosedate)) {
        $sliclquestions->closedate = 0;
    }

    if ($sliclquestions->resume == '1') {
        $sliclquestions->resume = 1;
    } else {
        $sliclquestions->resume = 0;
    }

    // Field sliclquestions->navigate used for branching sliclquestionss. Starting with version 2.5.5.
    /* if ($sliclquestions->navigate == '1') {
        $sliclquestions->navigate = 1;
    } else {
        $sliclquestions->navigate = 0;
    } */

    if (!$sliclquestions->id = $DB->insert_record("sliclquestions", $sliclquestions)) {
        return false;
    }

    sliclquestions_set_events($sliclquestions);

    return $sliclquestions->id;
}

// Return a small object with summary information about what a
// user has done with a given particular instance of this module
// Used for user activity reports.
// $return->time = the time they did it
// $return->info = a short text description.
function sliclquestions_user_outline($course, $user, $mod, $sliclquestions) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

    $result = new stdClass();
    if ($responses = sliclquestions_get_user_responses($sliclquestions->sid, $user->id, $complete = true)) {
        $n = count($responses);
        if ($n == 1) {
            $result->info = $n.' '.get_string("response", "sliclquestions");
        } else {
            $result->info = $n.' '.get_string("responses", "sliclquestions");
        }
        $lastresponse = array_pop($responses);
        $result->time = $lastresponse->submitted;
    } else {
            $result->info = get_string("noresponses", "sliclquestions");
    }
        return $result;
}

// Print a detailed representation of what a  user has done with
// a given particular instance of this module, for user activity reports.
function sliclquestions_user_complete($course, $user, $mod, $sliclquestions) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

    if ($responses = sliclquestions_get_user_responses($sliclquestions->sid, $user->id, $complete = false)) {
        foreach ($responses as $response) {
            if ($response->complete == 'y') {
                echo get_string('submitted', 'sliclquestions').' '.userdate($response->submitted).'<br />';
            } else {
                echo get_string('attemptstillinprogress', 'sliclquestions').' '.userdate($response->submitted).'<br />';
            }
        }
    } else {
        print_string('noresponses', 'sliclquestions');
    }

    return true;
}

// Given a course and a time, this module should find recent activity
// that has occurred in sliclquestions activities and print it out.
// Return true if there was output, or false is there was none.
function sliclquestions_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');
    return false;  //  True if anything was printed, otherwise false.
}

// Function to be run periodically according to the moodle cron
// This function searches for things that need to be done, such
// as sending out mail, toggling flags etc ...
function sliclquestions_cron () {
    global $CFG;
    require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

    return sliclquestions_cleanup();
}

// Must return an array of grades for a given instance of this module,
// indexed by user.  It also returns a maximum allowed grade.
function sliclquestions_grades($sliclquestionsid) {
    return null;
}

/**
 * Return grade for given user or all users.
 *
 * @param int $sliclquestionsid id of assignment
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function sliclquestions_get_user_grades($sliclquestions, $userid=0) {
    global $DB;
    $params = array();
    $usersql = '';
    if (!empty($userid)) {
        $usersql = "AND u.id = ?";
        $params[] = $userid;
    }

    $sql = "SELECT a.id, u.id AS userid, r.grade AS rawgrade, r.submitted AS dategraded, r.submitted AS datesubmitted
            FROM {user} u, {sliclquestions_attempts} a, {sliclquestions_response} r
            WHERE u.id = a.userid AND a.qid = $sliclquestions->id AND r.id = a.rid $usersql";
    return $DB->get_records_sql($sql, $params);
}

/**
 * Update grades by firing grade_updated event
 *
 * @param object $assignment null means all assignments
 * @param int $userid specific user only, 0 mean all
 */
function sliclquestions_update_grades($sliclquestions=null, $userid=0, $nullifnone=true) {
    global $CFG, $DB;

    if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
        require_once($CFG->libdir.'/gradelib.php');
    }

    if ($sliclquestions != null) {
        if ($graderecs = sliclquestions_get_user_grades($sliclquestions, $userid)) {
            $grades = array();
            foreach ($graderecs as $v) {
                if (!isset($grades[$v->userid])) {
                    $grades[$v->userid] = new stdClass();
                    if ($v->rawgrade == -1) {
                        $grades[$v->userid]->rawgrade = null;
                    } else {
                        $grades[$v->userid]->rawgrade = $v->rawgrade;
                    }
                    $grades[$v->userid]->userid = $v->userid;
                } else if (isset($grades[$v->userid]) && ($v->rawgrade > $grades[$v->userid]->rawgrade)) {
                    $grades[$v->userid]->rawgrade = $v->rawgrade;
                }
            }
            sliclquestions_grade_item_update($sliclquestions, $grades);
        } else {
            sliclquestions_grade_item_update($sliclquestions);
        }

    } else {
        $sql = "SELECT q.*, cm.idnumber as cmidnumber, q.course as courseid
                  FROM {sliclquestions} q, {course_modules} cm, {modules} m
                 WHERE m.name='sliclquestions' AND m.id=cm.module AND cm.instance=q.id";
        if ($rs = $DB->get_recordset_sql($sql)) {
            foreach ($rs as $sliclquestions) {
                if ($sliclquestions->grade != 0) {
                    sliclquestions_update_grades($sliclquestions);
                } else {
                    sliclquestions_grade_item_update($sliclquestions);
                }
            }
            $rs->close();
        }
    }
}

/**
 * Create grade item for given sliclquestions
 *
 * @param object $sliclquestions object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function sliclquestions_grade_item_update($sliclquestions, $grades = null) {
    global $CFG;
    if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
        require_once($CFG->libdir.'/gradelib.php');
    }

    if (!isset($sliclquestions->courseid)) {
        $sliclquestions->courseid = $sliclquestions->course;
    }

    if ($sliclquestions->cmidnumber != '') {
        $params = array('itemname' => $sliclquestions->name, 'idnumber' => $sliclquestions->cmidnumber);
    } else {
        $params = array('itemname' => $sliclquestions->name);
    }

    if ($sliclquestions->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $sliclquestions->grade;
        $params['grademin']  = 0;

    } else if ($sliclquestions->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$sliclquestions->grade;

    } else if ($sliclquestions->grade == 0) { // No Grade..be sure to delete the grade item if it exists.
        $grades = null;
        $params = array('deleted' => 1);

    } else {
        $params = null; // Allow text comments only.
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/sliclquestions', $sliclquestions->courseid, 'mod', 'sliclquestions',
                    $sliclquestions->id, 0, $grades, $params);
}

/**
 * This function returns if a scale is being used by one book
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 * @param $bookid int
 * @param $scaleid int
 * @return boolean True if the scale is used by any journal
 */
function sliclquestions_scale_used ($bookid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of book
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any journal
 */
function sliclquestions_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Serves the sliclquestions attachments. Implements needed access control ;-)
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - justsend the file
 */
function sliclquestions_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    $fileareas = array('intro', 'info', 'thankbody', 'question', 'feedbacknotes');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $componentid = (int)array_shift($args);

    if ($filearea != 'question') {
        if (!$survey = $DB->get_record('sliclquestions_survey', array('id' => $componentid))) {
            return false;
        }
    } else {
        if (!$question = $DB->get_record('sliclquestions_question', array('id' => $componentid))) {
            return false;
        }
    }

    if (!$sliclquestions = $DB->get_record('sliclquestions', array('id' => $cm->instance))) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_sliclquestions/$filearea/$componentid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, true); // Download MUST be forced - security!
}
/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $sliclquestionsnode The node to add module settings to
 */
function sliclquestions_extend_settings_navigation(settings_navigation $settings,
        navigation_node $sliclquestionsnode) {

    global $PAGE, $DB, $USER, $CFG;
    $individualresponse = optional_param('individualresponse', false, PARAM_INT);
    $rid = optional_param('rid', false, PARAM_INT); // Response id.
    $currentgroupid = optional_param('group', 0, PARAM_INT); // Group id.

    require_once($CFG->dirroot.'/mod/sliclquestions/sliclquestions.class.php');

    $context = $PAGE->cm->context;
    $cmid = $PAGE->cm->id;
    $cm = $PAGE->cm;
    $course = $PAGE->course;

    if (! $sliclquestions = $DB->get_record("sliclquestions", array("id" => $cm->instance))) {
        print_error('invalidcoursemodule');
    }

    $courseid = $course->id;
    $sliclquestions = new sliclquestions(0, $sliclquestions, $course, $cm);

    if ($survey = $DB->get_record('sliclquestions_survey', array('id' => $sliclquestions->sid))) {
        $owner = (trim($survey->owner) == trim($courseid));
    } else {
        $survey = false;
        $owner = true;
    }

    // On view page, currentgroupid is not yet sent as an optional_param, so get it.
    $groupmode = groups_get_activity_groupmode($cm, $course);
    if ($groupmode > 0 && $currentgroupid == 0) {
        $currentgroupid = groups_get_activity_group($sliclquestions->cm);
        if (!groups_is_member($currentgroupid, $USER->id)) {
            $currentgroupid = 0;
        }
    }

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $sliclquestionsnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false and array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('mod/sliclquestions:manage', $context) && $owner) {
        $url = '/mod/sliclquestions/qsettings.php';
        $node = navigation_node::create(get_string('advancedsettings'),
                new moodle_url($url, array('id' => $cmid)),
                navigation_node::TYPE_SETTING, null, 'advancedsettings',
                new pix_icon('t/edit', ''));
        $sliclquestionsnode->add_node($node, $beforekey);
    }

    if (has_capability('mod/sliclquestions:editquestions', $context) && $owner) {
        $url = '/mod/sliclquestions/questions.php';
        $node = navigation_node::create(get_string('questions', 'sliclquestions'),
                new moodle_url($url, array('id' => $cmid)),
                navigation_node::TYPE_SETTING, null, 'questions',
                new pix_icon('t/edit', ''));
        $sliclquestionsnode->add_node($node, $beforekey);
    }

    if (has_capability('mod/sliclquestions:preview', $context)) {
        $url = '/mod/sliclquestions/preview.php';
        $node = navigation_node::create(get_string('preview_label', 'sliclquestions'),
                new moodle_url($url, array('id' => $cmid)),
                navigation_node::TYPE_SETTING, null, 'preview',
                new pix_icon('t/preview', ''));
        $sliclquestionsnode->add_node($node, $beforekey);
    }

    if ($sliclquestions->user_can_take($USER->id)) {
        $url = '/mod/sliclquestions/complete.php';
        $node = navigation_node::create(get_string('answerquestions', 'sliclquestions'),
                new moodle_url($url, array('id' => $cmid)),
                navigation_node::TYPE_SETTING, null, '',
                new pix_icon('i/info', 'answerquestions'));
        $sliclquestionsnode->add_node($node, $beforekey);
    }
    $usernumresp = $sliclquestions->count_submissions($USER->id);

    if ($sliclquestions->capabilities->readownresponses && ($usernumresp > 0)) {
        $url = '/mod/sliclquestions/myreport.php';

        if ($usernumresp > 1) {
            $node = navigation_node::create(get_string('yourresponses', 'sliclquestions'),
                    new moodle_url($url, array('instance' => $sliclquestions->id,
                                    'userid' => $USER->id, 'byresponse' => 0, 'action' => 'summary', 'group' => $currentgroupid)),
                    navigation_node::TYPE_SETTING, null, 'yourresponses');
            $myreportnode = $sliclquestionsnode->add_node($node, $beforekey);

            $summary = $myreportnode->add(get_string('summary', 'sliclquestions'),
                    new moodle_url('/mod/sliclquestions/myreport.php',
                            array('instance' => $sliclquestions->id, 'userid' => $USER->id,
                                            'byresponse' => 0, 'action' => 'summary', 'group' => $currentgroupid)));
            $byresponsenode = $myreportnode->add(get_string('viewindividualresponse', 'sliclquestions'),
                    new moodle_url('/mod/sliclquestions/myreport.php',
                            array('instance' => $sliclquestions->id, 'userid' => $USER->id,
                                            'byresponse' => 1, 'action' => 'vresp', 'group' => $currentgroupid)));
            $allmyresponsesnode = $myreportnode->add(get_string('myresponses', 'sliclquestions'),
                    new moodle_url('/mod/sliclquestions/myreport.php',
                            array('instance' => $sliclquestions->id, 'userid' => $USER->id,
                                            'byresponse' => 0, 'action' => 'vall', 'group' => $currentgroupid)));
            if ($sliclquestions->capabilities->downloadresponses) {
                $downloadmyresponsesnode = $myreportnode->add(get_string('downloadtext'),
                        new moodle_url('/mod/sliclquestions/report.php',
                            array('instance' => $sliclquestions->id, 'user' => $USER->id,
                                            'action' => 'dwnpg', 'group' => $currentgroupid)));
            }
        } else {
            $node = navigation_node::create(get_string('yourresponse', 'sliclquestions'),
                            new moodle_url($url, array('instance' => $sliclquestions->id,
                                            'userid' => $USER->id,
                                            'byresponse' => 1, 'action' => 'vresp', 'group' => $currentgroupid)),
                            navigation_node::TYPE_SETTING, null, 'yourresponse');
            $myreportnode = $sliclquestionsnode->add_node($node, $beforekey);
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
    // and is not a non-editing teacher to view All responses.
    $canviewgroups = true;
    $groupmode = groups_get_activity_groupmode($cm, $course);
    if ($groupmode == 1) {
        $canviewgroups = groups_has_membership($cm, $USER->id);
    }
    $canviewallgroups = has_capability('moodle/site:accessallgroups', $context);
    if (( (
            // Teacher or non-editing teacher (if can view all groups).
            $canviewallgroups ||
            // Non-editing teacher (with canviewallgroups capability removed), if member of a group.
            ($canviewgroups && $sliclquestions->capabilities->readallresponseanytime))
            && $numresp > 0 && $owner && $numselectedresps > 0) ||
            $sliclquestions->capabilities->readallresponses && ($numresp > 0) && $canviewgroups &&
            ($sliclquestions->resp_view == SLICLQUESTIONS_STUDENTVIEWRESPONSES_ALWAYS ||
                    ($sliclquestions->resp_view == SLICLQUESTIONS_STUDENTVIEWRESPONSES_WHENCLOSED
                            && $sliclquestions->is_closed()) ||
                    ($sliclquestions->resp_view == SLICLQUESTIONS_STUDENTVIEWRESPONSES_WHENANSWERED
                            && $usernumresp > 0)) &&
            $sliclquestions->is_survey_owner()) {

        $url = '/mod/sliclquestions/report.php';
        $node = navigation_node::create(get_string('viewallresponses', 'sliclquestions'),
                new moodle_url($url, array('instance' => $sliclquestions->id, 'action' => 'vall')),
                navigation_node::TYPE_SETTING, null, 'vall');
        $reportnode = $sliclquestionsnode->add_node($node, $beforekey);

        if ($sliclquestions->capabilities->viewsingleresponse) {
            $summarynode = $reportnode->add(get_string('summary', 'sliclquestions'),
                    new moodle_url('/mod/sliclquestions/report.php',
                            array('instance' => $sliclquestions->id, 'action' => 'vall')));
        } else {
            $summarynode = $reportnode;
        }
        $defaultordernode = $summarynode->add(get_string('order_default', 'sliclquestions'),
                new moodle_url('/mod/sliclquestions/report.php',
                        array('instance' => $sliclquestions->id, 'action' => 'vall', 'group' => $currentgroupid)));
        $ascendingordernode = $summarynode->add(get_string('order_ascending', 'sliclquestions'),
                new moodle_url('/mod/sliclquestions/report.php',
                        array('instance' => $sliclquestions->id, 'action' => 'vallasort', 'group' => $currentgroupid)));
        $descendingordernode = $summarynode->add(get_string('order_descending', 'sliclquestions'),
                new moodle_url('/mod/sliclquestions/report.php',
                        array('instance' => $sliclquestions->id, 'action' => 'vallarsort', 'group' => $currentgroupid)));

        if ($sliclquestions->capabilities->deleteresponses) {
            $deleteallnode = $summarynode->add(get_string('deleteallresponses', 'sliclquestions'),
                    new moodle_url('/mod/sliclquestions/report.php',
                            array('instance' => $sliclquestions->id, 'action' => 'delallresp', 'group' => $currentgroupid)));
        }

        if ($sliclquestions->capabilities->downloadresponses) {
            $downloadresponsesnode = $summarynode->add(get_string('downloadtextformat', 'sliclquestions'),
                    new moodle_url('/mod/sliclquestions/report.php',
                            array('instance' => $sliclquestions->id, 'action' => 'dwnpg', 'group' => $currentgroupid)));
        }
        if ($sliclquestions->capabilities->viewsingleresponse) {
            $byresponsenode = $reportnode->add(get_string('viewbyresponse', 'sliclquestions'),
                new moodle_url('/mod/sliclquestions/report.php',
                    array('instance' => $sliclquestions->id, 'action' => 'vresp', 'byresponse' => 1, 'group' => $currentgroupid)));

            $viewindividualresponsenode = $byresponsenode->add(get_string('view', 'sliclquestions'),
                new moodle_url('/mod/sliclquestions/report.php',
                    array('instance' => $sliclquestions->id, 'action' => 'vresp', 'byresponse' => 1, 'group' => $currentgroupid)));

            if ($individualresponse) {
                $deleteindividualresponsenode = $byresponsenode->add(get_string('deleteresp', 'sliclquestions'),
                    new moodle_url('/mod/sliclquestions/report.php',
                        array('instance' => $sliclquestions->id, 'action' => 'dresp', 'byresponse' => 1,
                            'rid' => $rid, 'group' => $currentgroupid, 'individualresponse' => 1)));
            }
        }
    }
    if ($sliclquestions->capabilities->viewsingleresponse && ($canviewallgroups || $canviewgroups)) {
        $url = '/mod/sliclquestions/show_nonrespondents.php';
        $node = navigation_node::create(get_string('show_nonrespondents', 'sliclquestions'),
                new moodle_url($url, array('id' => $cmid)),
                navigation_node::TYPE_SETTING, null, 'nonrespondents');
        $nonrespondentsnode = $sliclquestionsnode->add_node($node, $beforekey);

    }
}

// Any other sliclquestions functions go here.  Each of them must have a name that
// starts with sliclquestions_.

function sliclquestions_get_view_actions() {
    return array('view', 'view all');
}

function sliclquestions_get_post_actions() {
    return array('submit', 'update');
}

function sliclquestions_get_recent_mod_activity(&$activities, &$index, $timestart,
                $courseid, $cmid, $userid = 0, $groupid = 0) {

    global $CFG, $COURSE, $USER, $DB;
    require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id' => $courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];
    $sliclquestions = $DB->get_record('sliclquestions', array('id' => $cm->instance));

    $context = context_module::instance($cm->id);
    $grader = has_capability('mod/sliclquestions:viewsingleresponse', $context);

    // If this is a copy of a public sliclquestions whose original is located in another course,
    // current user (teacher) cannot view responses.
    if ($grader && $survey = $DB->get_record('sliclquestions_survey', array('id' => $sliclquestions->sid))) {
        // For a public sliclquestions, look for the original public sliclquestions that it is based on.
        if ($survey->realm == 'public' && $survey->owner != $course->id) {
            // For a public sliclquestions, look for the original public sliclquestions that it is based on.
            $originalsliclquestions = $DB->get_record('sliclquestions',
                            array('sid' => $survey->id, 'course' => $survey->owner));
            $cmoriginal = get_coursemodule_from_instance("sliclquestions", $originalsliclquestions->id, $survey->owner);
            $contextoriginal = context_course::instance($survey->owner, MUST_EXIST);
            if (!has_capability('mod/sliclquestions:viewsingleresponse', $contextoriginal)) {
                $tmpactivity = new stdClass();
                $tmpactivity->type = 'sliclquestions';
                $tmpactivity->cmid = $cm->id;
                $tmpactivity->cannotview = true;
                $tmpactivity->anonymous = false;
                $activities[$index++] = $tmpactivity;
                return $activities;
            }
        }
    }

    if ($userid) {
        $userselect = "AND u.id = :userid";
        $params['userid'] = $userid;
    } else {
        $userselect = '';
    }

    if ($groupid) {
        $groupselect = 'AND gm.groupid = :groupid';
        $groupjoin   = 'JOIN {groups_members} gm ON  gm.userid=u.id';
        $params['groupid'] = $groupid;
    } else {
        $groupselect = '';
        $groupjoin   = '';
    }

    $params['timestart'] = $timestart;
    $params['sliclquestionsid'] = $sliclquestions->sid;

    $ufields = user_picture::fields('u', null, 'useridagain');
    if (!$attempts = $DB->get_records_sql("
                    SELECT qr.*,
                    {$ufields}
                    FROM {sliclquestions_response} qr
                    JOIN {user} u ON u.id = qr.username
                    $groupjoin
                    WHERE qr.submitted > :timestart
                    AND qr.survey_id = :sliclquestionsid
                    $userselect
                    $groupselect
                    ORDER BY qr.submitted ASC", $params)) {
        return;
    }

    $accessallgroups = has_capability('moodle/site:accessallgroups', $context);
    $viewfullnames   = has_capability('moodle/site:viewfullnames', $context);
    $groupmode       = groups_get_activity_groupmode($cm, $course);

    if (is_null($modinfo->groups)) {
        // Load all my groups and cache it in modinfo.
        $modinfo->groups = groups_get_user_groups($course->id);
    }

    $usersgroups = null;
    $aname = format_string($cm->name, true);
    $userattempts = array();
    foreach ($attempts as $attempt) {
        if ($sliclquestions->respondenttype != 'anonymous') {
            if (!isset($userattempts[$attempt->lastname])) {
                $userattempts[$attempt->lastname] = 1;
            } else {
                $userattempts[$attempt->lastname]++;
            }
        }
        if ($attempt->username != $USER->id) {
            if (!$grader) {
                // View complete individual responses permission required.
                continue;
            }

            if ($groupmode == SEPARATEGROUPS and !$accessallgroups) {
                if (is_null($usersgroups)) {
                    $usersgroups = groups_get_all_groups($course->id,
                    $attempt->userid, $cm->groupingid);
                    if (is_array($usersgroups)) {
                        $usersgroups = array_keys($usersgroups);
                    } else {
                         $usersgroups = array();
                    }
                }
                if (!array_intersect($usersgroups, $modinfo->groups[$cm->id])) {
                    continue;
                }
            }
        }

        $tmpactivity = new stdClass();

        $tmpactivity->type       = 'sliclquestions';
        $tmpactivity->cmid       = $cm->id;
        $tmpactivity->cminstance = $cm->instance;
        // Current user is admin - or teacher enrolled in original public course.
        if (isset($cmoriginal)) {
            $tmpactivity->cminstance = $cmoriginal->instance;
        }
        $tmpactivity->cannotview = false;
        $tmpactivity->anonymous  = false;
        $tmpactivity->name       = $aname;
        $tmpactivity->sectionnum = $cm->sectionnum;
        $tmpactivity->timestamp  = $attempt->submitted;
        $tmpactivity->groupid    = $groupid;
        if (isset($userattempts[$attempt->lastname])) {
            $tmpactivity->nbattempts = $userattempts[$attempt->lastname];
        }

        $tmpactivity->content = new stdClass();
        $tmpactivity->content->attemptid = $attempt->id;

        $userfields = explode(',', user_picture::fields());
        $tmpactivity->user = new stdClass();
        foreach ($userfields as $userfield) {
            if ($userfield == 'id') {
                $tmpactivity->user->{$userfield} = $attempt->username;
            } else {
                if (!empty($attempt->{$userfield})) {
                    $tmpactivity->user->{$userfield} = $attempt->{$userfield};
                } else {
                    $tmpactivity->user->{$userfield} = null;
                }
            }
        }
        if ($sliclquestions->respondenttype != 'anonymous') {
            $tmpactivity->user->fullname  = fullname($attempt, $viewfullnames);
        } else {
            $tmpactivity->user = '';
            unset ($tmpactivity->user);
            $tmpactivity->anonymous = true;
        }
        $activities[$index++] = $tmpactivity;
    }
}

/**
 * Prints all users who have completed a specified sliclquestions since a given time
 *
 * @global object
 * @param object $activity
 * @param int $courseid
 * @param string $detail not used but needed for compability
 * @param array $modnames
 * @return void Output is echo'd
 */
function sliclquestions_print_recent_mod_activity($activity, $courseid, $detail, $modnames) {
    global $CFG, $OUTPUT;

    // If the sliclquestions is "anonymous", then $activity->user won't have been set, so do not display respondent info.
    if ($activity->anonymous) {
        $stranonymous = ' ('.get_string('anonymous', 'sliclquestions').')';
        $activity->nbattempts = '';
    } else {
        $stranonymous = '';
    }
    // Current user cannot view responses to public sliclquestions.
    if ($activity->cannotview) {
        $strcannotview = get_string('cannotviewpublicresponses', 'sliclquestions');
    }
    echo html_writer::start_tag('div');
    echo html_writer::start_tag('span', array('class' => 'clearfix',
                    'style' => 'margin-top:0px; background-color: white; display: inline-block;'));

    if (!$activity->anonymous && !$activity->cannotview) {
        echo html_writer::tag('div', $OUTPUT->user_picture($activity->user, array('courseid' => $courseid)),
                        array('style' => 'float: left; padding-right: 10px;'));
    }
    if (!$activity->cannotview) {
        echo html_writer::start_tag('div');
        echo html_writer::start_tag('div');

        $urlparams = array('action' => 'vresp', 'instance' => $activity->cminstance,
                        'group' => $activity->groupid, 'rid' => $activity->content->attemptid, 'individualresponse' => 1);

        $context = context_module::instance($activity->cmid);
        if (has_capability('mod/sliclquestions:viewsingleresponse', $context)) {
            $report = 'report.php';
        } else {
            $report = 'myreport.php';
        }
        echo html_writer::tag('a', get_string('response', 'sliclquestions').' '.$activity->nbattempts.$stranonymous,
                        array('href' => new moodle_url('/mod/sliclquestions/'.$report, $urlparams)));
        echo html_writer::end_tag('div');
    } else {
        echo html_writer::start_tag('div');
        echo html_writer::start_tag('div');
        echo html_writer::tag('div', $strcannotview);
        echo html_writer::end_tag('div');
    }
    if (!$activity->anonymous  && !$activity->cannotview) {
        $url = new moodle_url('/user/view.php', array('course' => $courseid, 'id' => $activity->user->id));
        $name = $activity->user->fullname;
        $link = html_writer::link($url, $name);
        echo html_writer::start_tag('div', array('class' => 'user'));
        echo $link .' - '. userdate($activity->timestamp);
        echo html_writer::end_tag('div');
    }

    echo html_writer::end_tag('div');
    echo html_writer::end_tag('span');
    echo html_writer::end_tag('div');

    return;
}

/**
 * Prints sliclquestions summaries on 'My home' page
 *
 * Prints sliclquestions name, due date and attempt information on
 * sliclquestionss that have a deadline that has not already passed
 * and it is available for taking.
 *
 * @global object
 * @global stdClass
 * @global object
 * @uses CONTEXT_MODULE
 * @param array $courses An array of course objects to get sliclquestions instances from
 * @param array $htmlarray Store overview output array( course ID => 'sliclquestions' => HTML output )
 * @return void
 */
function sliclquestions_print_overview($courses, &$htmlarray) {
    global $USER, $CFG, $DB, $OUTPUT;

    require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

    if (!$sliclquestionss = get_all_instances_in_courses('sliclquestions', $courses)) {
        return;
    }

    // Get Necessary Strings.
    $strsliclquestions       = get_string('modulename', 'sliclquestions');
    $strnotattempted = get_string('noattempts', 'sliclquestions');
    $strattempted    = get_string('attempted', 'sliclquestions');
    $strsavedbutnotsubmitted = get_string('savedbutnotsubmitted', 'sliclquestions');

    $now = time();
    foreach ($sliclquestionss as $sliclquestions) {

        // The sliclquestions has a deadline.
        if ($sliclquestions->closedate != 0
                        // And it is before the deadline has been met.
                        and $sliclquestions->closedate >= $now
                        // And the sliclquestions is available.
                        and ($sliclquestions->opendate == 0 or $sliclquestions->opendate <= $now)) {
            if (!$sliclquestions->visible) {
                $class = ' class="dimmed"';
            } else {
                $class = '';
            }
            $str = $OUTPUT->box("$strsliclquestions:
                            <a$class href=\"$CFG->wwwroot/mod/sliclquestions/view.php?id=$sliclquestions->coursemodule\">".
                            format_string($sliclquestions->name).'</a>', 'name');

            // Deadline.
            $str .= $OUTPUT->box(get_string('closeson', 'sliclquestions', userdate($sliclquestions->closedate)), 'info');
            $select = 'qid = '.$sliclquestions->id.' AND userid = '.$USER->id;
            $attempts = $DB->get_records_select('sliclquestions_attempts', $select);
            $nbattempts = count($attempts);

            // Do not display a sliclquestions as due if it can only be sumbitted once and it has already been submitted!
            if ($nbattempts != 0 && $sliclquestions->qtype == SLICLQUESTIONSONCE) {
                continue;
            }

            // Attempt information.
            if (has_capability('mod/sliclquestions:manage', context_module::instance($sliclquestions->coursemodule))) {
                // Number of user attempts.
                $attempts = $DB->count_records('sliclquestions_attempts', array('id' => $sliclquestions->id));
                $str .= $OUTPUT->box(get_string('numattemptsmade', 'sliclquestions', $attempts), 'info');
            } else {
                if ($responses = sliclquestions_get_user_responses($sliclquestions->sid, $USER->id, $complete = false)) {
                    foreach ($responses as $response) {
                        if ($response->complete == 'y') {
                            $str .= $OUTPUT->box($strattempted, 'info');
                            break;
                        } else {
                            $str .= $OUTPUT->box($strsavedbutnotsubmitted, 'info');
                        }
                    }
                } else {
                    $str .= $OUTPUT->box($strnotattempted, 'info');
                }
            }
            $str = $OUTPUT->box($str, 'sliclquestions overview');

            if (empty($htmlarray[$sliclquestions->course]['sliclquestions'])) {
                $htmlarray[$sliclquestions->course]['sliclquestions'] = $str;
            } else {
                $htmlarray[$sliclquestions->course]['sliclquestions'] .= $str;
            }
        }
    }
}


/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the sliclquestions.
 *
 * @param $mform the course reset form that is being built.
 */
function sliclquestions_reset_course_form_definition($mform) {
    $mform->addElement('header', 'sliclquestionsheader', get_string('modulenameplural', 'sliclquestions'));
    $mform->addElement('advcheckbox', 'reset_sliclquestions',
                    get_string('removeallsliclquestionsattempts', 'sliclquestions'));
}

/**
 * Course reset form defaults.
 * @return array the defaults.
 */
function sliclquestions_reset_course_form_defaults($course) {
    return array('reset_sliclquestions' => 1);
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * sliclquestions responses for course $data->courseid, if $data->reset_sliclquestions_attempts is
 * set and true.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function sliclquestions_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/questionlib.php');
    require_once($CFG->dirroot.'/mod/sliclquestions/locallib.php');

    $componentstr = get_string('modulenameplural', 'sliclquestions');
    $status = array();

    if (!empty($data->reset_sliclquestions)) {
        $surveys = sliclquestions_get_survey_list($data->courseid, $type = '');

        // Delete responses.
        foreach ($surveys as $survey) {
            // Get all responses for this sliclquestions.
            $sql = "SELECT R.id, R.survey_id, R.submitted, R.username
                 FROM {sliclquestions_response} R
                 WHERE R.survey_id = ?
                 ORDER BY R.id";
            $resps = $DB->get_records_sql($sql, array($survey->id));
            if (!empty($resps)) {
                $sliclquestions = $DB->get_record("sliclquestions", array("sid" => $survey->id, "course" => $survey->owner));
                $sliclquestions->course = $DB->get_record("course", array("id" => $sliclquestions->course));
                foreach ($resps as $response) {
                    sliclquestions_delete_response($response, $sliclquestions);
                }
            }
            // Remove this sliclquestions's grades (and feedback) from gradebook (if any).
            $select = "itemmodule = 'sliclquestions' AND iteminstance = ".$survey->qid;
            $fields = 'id';
            if ($itemid = $DB->get_record_select('grade_items', $select, null, $fields)) {
                $itemid = $itemid->id;
                $DB->delete_records_select('grade_grades', 'itemid = '.$itemid);

            }
        }
        $status[] = array(
                        'component' => $componentstr,
                        'item' => get_string('deletedallresp', 'sliclquestions'),
                        'error' => false);

        $status[] = array(
                        'component' => $componentstr,
                        'item' => get_string('gradesdeleted', 'sliclquestions'),
                        'error' => false);
    }
    return $status;
}

/**
 * Obtains the automatic completion state for this sliclquestions based on the condition
 * in sliclquestions settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function sliclquestions_get_completion_state($course, $cm, $userid, $type) {
    global $CFG, $DB;

    // Get sliclquestions details.
    $sliclquestions = $DB->get_record('sliclquestions', array('id' => $cm->instance), '*', MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false.
    if ($sliclquestions->completionsubmit) {
        $params = array('userid' => $userid, 'qid' => $sliclquestions->id);
        return $DB->record_exists('sliclquestions_attempts', $params);
    } else {
        // Completion option is not enabled so just return $type.
        return $type;
    }
}