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
 * Filename : locallib
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once($CFG->libdir.'/eventslib.php');
require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->dirroot.'/mod/sliclquestions/classfiles/question.class.php');

// Constants

define('SLICLQUESTIONS_SURVEY', 1);
define('SLICLQUESTIONS_PUPILREGISTRATION', 2);
define('SLICLQUESTIONS_PUPILASSESSMENT', 3);

global $sliclquestions_types;

$sliclquestions_types = array(0                                => get_string('choosequestiontype', 'sliclquestions'),
                              SLICLQUESTIONS_SURVEY            => get_string('questype_survey', 'sliclquestions'),
                              SLICLQUESTIONS_PUPILREGISTRATION => get_string('questype_pupilregister', 'sliclquestions'),
                              SLICLQUESTIONS_PUPILASSESSMENT   => get_string('questype_pupilassess', 'sliclquestions'));



function sliclquestions_editor_options($context)
{
    global $CFG;
    return array('subdirs'      => 1,
                 'maxbytes'     => $CFG->maxbytes,
                 'maxfiles'     => 1,
                 'changeformat' => 1,
                 'context'      => $context,
                 'noclean'      => 1,
                 'trusttext'    => 0);
}

function sliclquestions_set_events($data)
{

}

function sliclquestions_search_form($course, $search)
{
    global $CFG, $OUPUT;
    return '<div class="sliclquestions-search"><form action="'
         . $CFG->wwwroot
         . '/mod/sliclquestions/search.php" style="display:inline">'
         . '<fieldset class="invisiblefieldset">'
         . $OUPUT->help_icon('search')
         . '<label class="accesshide" for="search">'
         . get_string('search', 'sliclquestions')
         . '</label><input id="search" name="search" type="text" size="18" value="'
         . s($search, true)
         . '" /><input name="id" type="hidden" value="'
         . $course->id
         . '" /></fieldset></form></div>';
}

function sliclquestions_choice_values($content)
{
    $contents = new stdClass();
    $contents->text    = '';
    $contents->image   = '';
    $contents->modname = '';
    $contents->title   = '';
    if ($count = preg_match('/[<img)\s .*(src="(.[^"]{1,})")/isxmU', $content, $matches)) {
        $contents->image = $matches[0];
        $imageurl = $matches[3];
        if (preg_match('/(title=.)([^"]{1,})/', $content, $matches) ||
                preg_match('/(alt=.)([^"]{1,})/', $content, $matches)) {
            $contents->title = $matches[2];
        } else {
            preg_match('/.*\/(.*)\..*$/', $imageurl, $matches);
            $contents->title =  $matches[1];
        }
        if (preg_match('/(.*)(<img.*)/', $content, $matches)) {
            $content = $matches[1];
        } else {
            return $contents;
        }
    }
    if (preg_match_all('/^(\d{1,2}=)(.*)$/', $content, $matches)) {
        $content = $matches[2][0];
    }
    $contents->text = $content;
    if ($pos = strpos($content, '::')) {
        $contents->text = substr($content, $pos + 2);
        $contents->modname = substr($content, 0, $pos);
    }
    return $contents;
}




//// Constants.
//
//define ('SLICLQUESTIONSUNLIMITED', 0);
//define ('SLICLQUESTIONSONCE', 1);
//define ('SLICLQUESTIONSDAILY', 2);
//define ('SLICLQUESTIONSWEEKLY', 3);
//define ('SLICLQUESTIONSMONTHLY', 4);
//
//define ('SLICLQUESTIONS_STUDENTVIEWRESPONSES_NEVER', 0);
//define ('SLICLQUESTIONS_STUDENTVIEWRESPONSES_WHENANSWERED', 1);
//define ('SLICLQUESTIONS_STUDENTVIEWRESPONSES_WHENCLOSED', 2);
//define ('SLICLQUESTIONS_STUDENTVIEWRESPONSES_ALWAYS', 3);
//
//define('SLICLQUESTIONS_MAX_EVENT_LENGTH', 5 * 24 * 60 * 60);   // 5 days maximum.
//
//define('SLICLQUESTIONS_DEFAULT_PAGE_COUNT', 20);
//
//global $sliclquestionstypes;
//$sliclquestionstypes = array (SLICLQUESTIONSUNLIMITED => get_string('qtypeunlimited', 'sliclquestions'),
//                              SLICLQUESTIONSONCE => get_string('qtypeonce', 'sliclquestions'),
//                              SLICLQUESTIONSDAILY => get_string('qtypedaily', 'sliclquestions'),
//                              SLICLQUESTIONSWEEKLY => get_string('qtypeweekly', 'sliclquestions'),
//                              SLICLQUESTIONSMONTHLY => get_string('qtypemonthly', 'sliclquestions'));
//
//global $sliclquestionsrespondents;
//$sliclquestionsrespondents = array ('fullname' => get_string('respondenttypefullname', 'sliclquestions'),
//                                    'anonymous' => get_string('respondenttypeanonymous', 'sliclquestions'));
//
//global $sliclquestionsrealms;
//$sliclquestionsrealms = array ('private' => get_string('private', 'sliclquestions'),
//                               'public' => get_string('public', 'sliclquestions'),
//                               'template' => get_string('template', 'sliclquestions'));
//
//global $sliclquestionsresponseviewers;
//$sliclquestionsresponseviewers =
//    array ( SLICLQUESTIONS_STUDENTVIEWRESPONSES_WHENANSWERED => get_string('responseviewstudentswhenanswered', 'sliclquestions'),
//            SLICLQUESTIONS_STUDENTVIEWRESPONSES_WHENCLOSED => get_string('responseviewstudentswhenclosed', 'sliclquestions'),
//            SLICLQUESTIONS_STUDENTVIEWRESPONSES_ALWAYS => get_string('responseviewstudentsalways', 'sliclquestions'));
//
//global $autonumbering;
//$autonumbering = array (0 => get_string('autonumberno', 'sliclquestions'),
//        1 => get_string('autonumberquestions', 'sliclquestions'),
//        2 => get_string('autonumberpages', 'sliclquestions'),
//        3 => get_string('autonumberpagesandquestions', 'sliclquestions'));
//
//function sliclquestions_check_date ($thisdate, $insert=false) {
//    $dateformat = get_string('strfdate', 'sliclquestions');
//    if (preg_match('/(%[mdyY])(.+)(%[mdyY])(.+)(%[mdyY])/', $dateformat, $matches)) {
//        $datepieces = explode($matches[2], $thisdate);
//        foreach ($datepieces as $datepiece) {
//            if (!is_numeric($datepiece)) {
//                return 'wrongdateformat';
//            }
//        }
//        $pattern = "/[^dmy]/i";
//        $dateorder = strtolower(preg_replace($pattern, '', $dateformat));
//        $countpieces = count($datepieces);
//        if ($countpieces == 1) { // Assume only year entered.
//            switch ($dateorder) {
//                case 'dmy': // Most countries.
//                case 'mdy': // USA.
//                    $datepieces[2] = $datepieces[0]; // year
//                    $datepieces[0] = '1'; // Assumed 1st month of year.
//                    $datepieces[1] = '1'; // Assumed 1st day of month.
//                    break;
//                case 'ymd': // ISO 8601 standard
//                    $datepieces[1] = '1'; // Assumed 1st month of year.
//                    $datepieces[2] = '1'; // Assumed 1st day of month.
//                    break;
//            }
//        }
//        if ($countpieces == 2) { // Assume only month and year entered.
//            switch ($dateorder) {
//                case 'dmy': // Most countries.
//                    $datepieces[2] = $datepieces[1]; // Year.
//                    $datepieces[1] = $datepieces[0]; // Month.
//                    $datepieces[0] = '1'; // Assumed 1st day of month.
//                    break;
//                case 'mdy': // USA
//                    $datepieces[2] = $datepieces[1]; // Year.
//                    $datepieces[0] = $datepieces[0]; // Month.
//                    $datepieces[1] = '1'; // Assumed 1st day of month.
//                    break;
//                case 'ymd': // ISO 8601 standard
//                    $datepieces[2] = '1'; // Assumed 1st day of month.
//                    break;
//            }
//        }
//        if (count($datepieces) > 1) {
//            if ($matches[1] == '%m') {
//                $month = $datepieces[0];
//            }
//            if ($matches[1] == '%d') {
//                $day = $datepieces[0];
//            }
//            if ($matches[1] == '%y') {
//                $year = strftime('%C').$datepieces[0];
//            }
//            if ($matches[1] == '%Y') {
//                $year = $datepieces[0];
//            }
//
//            if ($matches[3] == '%m') {
//                $month = $datepieces[1];
//            }
//            if ($matches[3] == '%d') {
//                $day = $datepieces[1];
//            }
//            if ($matches[3] == '%y') {
//                $year = strftime('%C').$datepieces[1];
//            }
//            if ($matches[3] == '%Y') {
//                $year = $datepieces[1];
//            }
//
//            if ($matches[5] == '%m') {
//                $month = $datepieces[2];
//            }
//            if ($matches[5] == '%d') {
//                $day = $datepieces[2];
//            }
//            if ($matches[5] == '%y') {
//                $year = strftime('%C').$datepieces[2];
//            }
//            if ($matches[5] == '%Y') {
//                $year = $datepieces[2];
//            }
//
//            $month = min(12, $month);
//            $month = max(1, $month);
//            if ($month == 2) {
//                $day = min(29, $day);
//            } else if ($month == 4 || $month == 6 || $month == 9 || $month == 11) {
//                $day = min(30, $day);
//            } else {
//                $day = min(31, $day);
//            }
//            $day = max(1, $day);
//            if (!$thisdate = gmmktime(0, 0, 0, $month, $day, $year)) {
//                return 'wrongdaterange';
//            } else {
//                if ($insert) {
//                    $thisdate = trim(userdate ($thisdate, '%Y-%m-%d', '1', false));
//                } else {
//                    $thisdate = trim(userdate ($thisdate, $dateformat, '1', false));
//                }
//            }
//            return $thisdate;
//        }
//    } else {
//        return ('wrongdateformat');
//    }
//}
//
//
//// A variant of Moodle's notify function, with a different formatting.
//function sliclquestions_notify($message) {
//    $message = clean_text($message);
//    $errorstart = '<div class="notifyproblem">';
//    $errorend = '</div>';
//    $output = $errorstart.$message.$errorend;
//    echo $output;
//}
//
//function sliclquestions_choice_values($content) {
//
//    // If we run the content through format_text first, any filters we want to use (e.g. multilanguage) should work.
//    // examines the content of a possible answer from radio button, check boxes or rate question
//    // returns ->text to be displayed, ->image if present, ->modname name of modality, image ->title.
//    $contents = new stdClass();
//    $contents->text = '';
//    $contents->image = '';
//    $contents->modname = '';
//    $contents->title = '';
//    // Has image.
//    if ($count = preg_match('/(<img)\s .*(src="(.[^"]{1,})")/isxmU', $content, $matches)) {
//        $contents->image = $matches[0];
//        $imageurl = $matches[3];
//        // Image has a title or alt text: use one of them.
//        if (preg_match('/(title=.)([^"]{1,})/', $content, $matches)
//             || preg_match('/(alt=.)([^"]{1,})/', $content, $matches) ) {
//            $contents->title = $matches[2];
//        } else {
//            // Image has no title nor alt text: use its filename (without the extension).
//            preg_match("/.*\/(.*)\..*$/", $imageurl, $matches);
//            $contents->title = $matches[1];
//        }
//        // Content has text or named modality plus an image.
//        if (preg_match('/(.*)(<img.*)/', $content, $matches)) {
//            $content = $matches[1];
//        } else {
//            // Just an image.
//            return $contents;
//        }
//    }
//
//    // Check for score value first (used e.g. by personality test feature).
//    $r = preg_match_all("/^(\d{1,2}=)(.*)$/", $content, $matches);
//    if ($r) {
//        $content = $matches[2][0];
//    }
//
//    // Look for named modalities.
//    $contents->text = $content;
//    // DEV JR from version 2.5, a double colon :: must be used here instead of the equal sign.
//    if ($pos = strpos($content, '::')) {
//        $contents->text = substr($content, $pos + 2);
//        $contents->modname = substr($content, 0, $pos);
//    }
//    return $contents;
//}
//
///**
// * Get the information about the standard sliclquestions JavaScript module.
// * @return array a standard jsmodule structure.
// */
//function sliclquestions_get_js_module() {
//    global $PAGE;
//    return array(
//            'name' => 'mod_sliclquestions',
//            'fullpath' => '/mod/sliclquestions/module.js',
//            'requires' => array('base', 'dom', 'event-delegate', 'event-key',
//                    'core_question_engine', 'moodle-core-formchangechecker'),
//            'strings' => array(
//                    array('cancel', 'moodle'),
//                    array('flagged', 'question'),
//                    array('functiondisabledbysecuremode', 'quiz'),
//                    array('startattempt', 'quiz'),
//                    array('timesup', 'quiz'),
//                    array('changesmadereallygoaway', 'moodle'),
//            ),
//    );
//}
//
///**
// * Get all the sliclquestions responses for a user
// */
//function sliclquestions_get_user_responses($surveyid, $userid, $complete=true) {
//    global $DB;
//    $andcomplete = '';
//    if ($complete) {
//        $andcomplete = " AND complete = 'y' ";
//    }
//    return $DB->get_records_sql ("SELECT *
//        FROM {sliclquestions_response}
//        WHERE survey_id = ?
//        AND username = ?
//        ".$andcomplete."
//        ORDER BY submitted ASC ", array($surveyid, $userid));
//}
//
///**
// * get the capabilities for the sliclquestions
// * @param int $cmid
// * @return object the available capabilities from current user
// */
//function sliclquestions_load_capabilities($cmid) {
//    static $cb;
//
//    if (isset($cb)) {
//        return $cb;
//    }
//
//    $context = sliclquestions_get_context($cmid);
//
//    $cb = new object;
//    $cb->view                   = has_capability('mod/sliclquestions:view', $context);
//    $cb->submit                 = has_capability('mod/sliclquestions:submit', $context);
//    $cb->viewsingleresponse     = has_capability('mod/sliclquestions:viewsingleresponse', $context);
//    $cb->downloadresponses      = has_capability('mod/sliclquestions:downloadresponses', $context);
//    $cb->deleteresponses        = has_capability('mod/sliclquestions:deleteresponses', $context);
//    $cb->manage                 = has_capability('mod/sliclquestions:manage', $context);
//    $cb->editquestions          = has_capability('mod/sliclquestions:editquestions', $context);
//    $cb->createtemplates        = has_capability('mod/sliclquestions:createtemplates', $context);
//    $cb->createpublic           = has_capability('mod/sliclquestions:createpublic', $context);
//    $cb->readownresponses       = has_capability('mod/sliclquestions:readownresponses', $context);
//    $cb->readallresponses       = has_capability('mod/sliclquestions:readallresponses', $context);
//    $cb->readallresponseanytime = has_capability('mod/sliclquestions:readallresponseanytime', $context);
//    $cb->printblank             = has_capability('mod/sliclquestions:printblank', $context);
//    $cb->preview                = has_capability('mod/sliclquestions:preview', $context);
//
//    $cb->viewhiddenactivities   = has_capability('moodle/course:viewhiddenactivities', $context, null, false);
//
//    return $cb;
//}
//
///**
// * returns the context-id related to the given coursemodule-id
// * @param int $cmid the coursemodule-id
// * @return object $context
// */
//function sliclquestions_get_context($cmid) {
//    static $context;
//
//    if (isset($context)) {
//        return $context;
//    }
//
//    if (!$context = context_module::instance($cmid)) {
//            print_error('badcontext');
//    }
//    return $context;
//}
//
//// This function *really* shouldn't be needed, but since sometimes we can end up with
//// orphaned surveys, this will clean them up.
//function sliclquestions_cleanup() {
//    global $DB;
//
//    // Find surveys that don't have sliclquestionss associated with them.
//    $sql = 'SELECT qs.* FROM {sliclquestions_survey} qs '.
//           'LEFT JOIN {sliclquestions} q ON q.sid = qs.id '.
//           'WHERE q.sid IS NULL';
//
//    if ($surveys = $DB->get_records_sql($sql)) {
//        foreach ($surveys as $survey) {
//            sliclquestions_delete_survey($survey->id, 0);
//        }
//    }
//    // Find deleted questions and remove them from database (with their associated choices, etc.).
//    return true;
//}
//
//function sliclquestions_record_submission(&$sliclquestions, $userid, $rid=0) {
//    global $DB;
//
//    $attempt['qid'] = $sliclquestions->id;
//    $attempt['userid'] = $userid;
//    $attempt['rid'] = $rid;
//    $attempt['timemodified'] = time();
//    return $DB->insert_record("sliclquestions_attempts", (object)$attempt, false);
//}
//
//function sliclquestions_delete_survey($sid, $sliclquestionsid) {
//    global $DB;
//    $status = true;
//    // Delete all survey attempts and responses.
//    if ($responses = $DB->get_records('sliclquestions_response', array('survey_id' => $sid), 'id')) {
//        foreach ($responses as $response) {
//            $status = $status && sliclquestions_delete_response($response);
//        }
//    }
//
//    // There really shouldn't be any more, but just to make sure...
//    $DB->delete_records('sliclquestions_response', array('survey_id' => $sid));
//    $DB->delete_records('sliclquestions_attempts', array('qid' => $sliclquestionsid));
//
//    // Delete all question data for the survey.
//    if ($questions = $DB->get_records('sliclquestions_question', array('survey_id' => $sid), 'id')) {
//        foreach ($questions as $question) {
//            $DB->delete_records('sliclquestions_quest_choice', array('question_id' => $question->id));
//        }
//        $status = $status && $DB->delete_records('sliclquestions_question', array('survey_id' => $sid));
//    }
//
//    // Delete all feedback sections and feedback messages for the survey.
//    if ($fbsections = $DB->get_records('sliclquestions_fb_sections', array('survey_id' => $sid), 'id')) {
//        foreach ($fbsections as $fbsection) {
//            $DB->delete_records('sliclquestions_feedback', array('section_id' => $fbsection->id));
//        }
//        $status = $status && $DB->delete_records('sliclquestions_fb_sections', array('survey_id' => $sid));
//    }
//
//    $status = $status && $DB->delete_records('sliclquestions_survey', array('id' => $sid));
//
//    return $status;
//}
//
//function sliclquestions_delete_response($response, $sliclquestions='') {
//    global $DB;
//    $status = true;
//    $cm = '';
//    $rid = $response->id;
//    // The sliclquestions_delete_survey function does not send the sliclquestions array.
//    if ($sliclquestions != '') {
//        $cm = get_coursemodule_from_instance("sliclquestions", $sliclquestions->id, $sliclquestions->course->id);
//    }
//
//    // Delete all of the response data for a response.
//    $DB->delete_records('sliclquestions_response_bool', array('response_id' => $rid));
//    $DB->delete_records('sliclquestions_response_date', array('response_id' => $rid));
//    $DB->delete_records('sliclquestions_resp_multiple', array('response_id' => $rid));
//    $DB->delete_records('sliclquestions_response_other', array('response_id' => $rid));
//    $DB->delete_records('sliclquestions_response_rank', array('response_id' => $rid));
//    $DB->delete_records('sliclquestions_resp_single', array('response_id' => $rid));
//    $DB->delete_records('sliclquestions_response_text', array('response_id' => $rid));
//
//    $status = $status && $DB->delete_records('sliclquestions_response', array('id' => $rid));
//    $status = $status && $DB->delete_records('sliclquestions_attempts', array('rid' => $rid));
//
//    if ($status && $cm) {
//        // Update completion state if necessary.
//        $completion = new completion_info($sliclquestions->course);
//        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $sliclquestions->completionsubmit) {
//            $completion->update_state($cm, COMPLETION_INCOMPLETE, $response->username);
//        }
//    }
//
//    return $status;
//}
//
//function sliclquestions_delete_responses($qid) {
//    global $DB;
//
//    $status = true;
//
//    // Delete all of the response data for a question.
//    $DB->delete_records('sliclquestions_response_bool', array('question_id' => $qid));
//    $DB->delete_records('sliclquestions_response_date', array('question_id' => $qid));
//    $DB->delete_records('sliclquestions_resp_multiple', array('question_id' => $qid));
//    $DB->delete_records('sliclquestions_response_other', array('question_id' => $qid));
//    $DB->delete_records('sliclquestions_response_rank', array('question_id' => $qid));
//    $DB->delete_records('sliclquestions_resp_single', array('question_id' => $qid));
//    $DB->delete_records('sliclquestions_response_text', array('question_id' => $qid));
//
//    $status = $status && $DB->delete_records('sliclquestions_response', array('id' => $qid));
//    $status = $status && $DB->delete_records('sliclquestions_attempts', array('rid' => $qid));
//
//    return $status;
//}
//
//function sliclquestions_get_survey_list($courseid=0, $type='') {
//    global $DB;
//
//    if ($courseid == 0) {
//        if (isadmin()) {
//            $sql = "SELECT id,name,owner,realm,status " .
//                   "{sliclquestions_survey} " .
//                   "ORDER BY realm,name ";
//            $params = null;
//        } else {
//            return false;
//        }
//    } else {
//        $castsql = $DB->sql_cast_char2int('s.owner');
//        if ($type == 'public') {
//            $sql = "SELECT s.id,s.name,s.owner,s.realm,s.status,s.title,q.id as qid,q.name as qname " .
//                   "FROM {sliclquestions} q " .
//                   "INNER JOIN {sliclquestions_survey} s ON s.id = q.sid AND ".$castsql." = q.course " .
//                   "WHERE realm = ? " .
//                   "ORDER BY realm,name ";
//            $params = array($type);
//        } else if ($type == 'template') {
//            $sql = "SELECT s.id,s.name,s.owner,s.realm,s.status,s.title,q.id as qid,q.name as qname " .
//                   "FROM {sliclquestions} q " .
//                   "INNER JOIN {sliclquestions_survey} s ON s.id = q.sid AND ".$castsql." = q.course " .
//                   "WHERE (realm = ?) " .
//                   "ORDER BY realm,name ";
//            $params = array($type);
//        } else if ($type == 'private') {
//            $sql = "SELECT s.id,s.name,s.owner,s.realm,s.status,q.id as qid,q.name as qname " .
//                "FROM {sliclquestions} q " .
//                "INNER JOIN {sliclquestions_survey} s ON s.id = q.sid " .
//                "WHERE owner = ? and realm = ? " .
//                "ORDER BY realm,name ";
//            $params = array($courseid, $type);
//
//        } else {
//            // Current get_survey_list is called from function sliclquestions_reset_userdata so we need to get a
//            // complete list of all sliclquestionss in current course to reset them.
//            $sql = "SELECT s.id,s.name,s.owner,s.realm,s.status,q.id as qid,q.name as qname " .
//                   "FROM {sliclquestions} q " .
//                    "INNER JOIN {sliclquestions_survey} s ON s.id = q.sid AND ".$castsql." = q.course " .
//                   "WHERE owner = ? " .
//                   "ORDER BY realm,name ";
//            $params = array($courseid);
//        }
//    }
//    return $DB->get_records_sql($sql, $params);
//}
//
//function sliclquestions_get_survey_select($instance, $courseid=0, $sid=0, $type='') {
//    global $OUTPUT, $DB;
//
//    $surveylist = array();
//
//    if ($surveys = sliclquestions_get_survey_list($courseid, $type)) {
//        $strpreview = get_string('preview_sliclquestions', 'sliclquestions');
//        foreach ($surveys as $survey) {
//            $originalcourse = $DB->get_record('course', array('id' => $survey->owner));
//            if (!$originalcourse) {
//                // This should not happen, but we found a case where a public survey
//                // still existed in a course that had been deleted, and so this
//                // code lead to a notice, and a broken link. Since that is useless
//                // we just skip surveys like this.
//                continue;
//            }
//
//            // Prevent creating a copy of a public sliclquestions IN THE SAME COURSE as the original.
//            if ($type == 'public' && $survey->owner == $courseid) {
//                continue;
//            } else {
//                $args = "sid={$survey->id}&popup=1";
//                if (!empty($survey->qid)) {
//                    $args .= "&qid={$survey->qid}";
//                }
//                $link = new moodle_url("/mod/sliclquestions/preview.php?{$args}");
//                $action = new popup_action('click', $link);
//                $label = $OUTPUT->action_link($link, $survey->qname.' ['.$originalcourse->fullname.']',
//                    $action, array('title' => $strpreview));
//                $surveylist[$type.'-'.$survey->id] = $label;
//            }
//        }
//    }
//    return $surveylist;
//}
//
//function sliclquestions_get_type ($id) {
//    switch ($id) {
//        case 1:
//            return get_string('yesno', 'sliclquestions');
//        case 2:
//            return get_string('textbox', 'sliclquestions');
//        case 3:
//            return get_string('essaybox', 'sliclquestions');
//        case 4:
//            return get_string('radiobuttons', 'sliclquestions');
//        case 5:
//            return get_string('checkboxes', 'sliclquestions');
//        case 6:
//            return get_string('dropdown', 'sliclquestions');
//        case 8:
//            return get_string('ratescale', 'sliclquestions');
//        case 9:
//            return get_string('date', 'sliclquestions');
//        case 10:
//            return get_string('numeric', 'sliclquestions');
//        case 100:
//            return get_string('sectiontext', 'sliclquestions');
//        case 99:
//            return get_string('sectionbreak', 'sliclquestions');
//        default:
//        return $id;
//    }
//}
//
///**
// * This creates new events given as opendate and closedate by $sliclquestions.
// * @param object $sliclquestions
// * @return void
// */
// /* added by JR 16 march 2009 based on lesson_process_post_save script */
//
//function sliclquestions_set_events($sliclquestions) {
//    // Adding the sliclquestions to the eventtable.
//    global $DB;
//    if ($events = $DB->get_records('event', array('modulename' => 'sliclquestions', 'instance' => $sliclquestions->id))) {
//        foreach ($events as $event) {
//            $event = calendar_event::load($event);
//            $event->delete();
//        }
//    }
//
//    // The open-event.
//    $event = new stdClass;
//    $event->description = $sliclquestions->name;
//    $event->courseid = $sliclquestions->course;
//    $event->groupid = 0;
//    $event->userid = 0;
//    $event->modulename = 'sliclquestions';
//    $event->instance = $sliclquestions->id;
//    $event->eventtype = 'open';
//    $event->timestart = $sliclquestions->opendate;
//    $event->visible = instance_is_visible('sliclquestions', $sliclquestions);
//    $event->timeduration = ($sliclquestions->closedate - $sliclquestions->opendate);
//
//    if ($sliclquestions->closedate and $sliclquestions->opendate and $event->timeduration <= SLICLQUESTIONS_MAX_EVENT_LENGTH) {
//        // Single event for the whole sliclquestions.
//        $event->name = $sliclquestions->name;
//        calendar_event::create($event);
//    } else {
//        // Separate start and end events.
//        $event->timeduration  = 0;
//        if ($sliclquestions->opendate) {
//            $event->name = $sliclquestions->name.' ('.get_string('sliclquestionsopens', 'sliclquestions').')';
//            calendar_event::create($event);
//            unset($event->id); // So we can use the same object for the close event.
//        }
//        if ($sliclquestions->closedate) {
//            $event->name = $sliclquestions->name.' ('.get_string('sliclquestionscloses', 'sliclquestions').')';
//            $event->timestart = $sliclquestions->closedate;
//            $event->eventtype = 'close';
//            calendar_event::create($event);
//        }
//    }
//}
//
///**
// * Get users who have not completed the sliclquestions
// *
// * @global object
// * @uses CONTEXT_MODULE
// * @param object $cm
// * @param int $group single groupid
// * @param string $sort
// * @param int $startpage
// * @param int $pagecount
// * @return object the userrecords
// */
//function sliclquestions_get_incomplete_users($cm, $sid,
//                $group = false,
//                $sort = '',
//                $startpage = false,
//                $pagecount = false) {
//
//    global $DB;
//
//    $context = context_module::instance($cm->id);
//
//    // First get all users who can complete this sliclquestions.
//    $cap = 'mod/sliclquestions:submit';
//    $fields = 'u.id, u.username';
//    if (!$allusers = get_users_by_capability($context,
//                    $cap,
//                    $fields,
//                    $sort,
//                    '',
//                    '',
//                    $group,
//                    '',
//                    true)) {
//        return false;
//    }
//    $allusers = array_keys($allusers);
//
//    // Nnow get all completed sliclquestionss.
//    $params = array('survey_id' => $sid, 'complete' => 'y');
//    $sql = "SELECT username FROM {sliclquestions_response} "
//                    . "WHERE survey_id = $sid AND complete = 'y' "
//    . "GROUP BY username ";
//
//    if (!$completedusers = $DB->get_records_sql($sql)) {
//        return $allusers;
//    }
//    $completedusers = array_keys($completedusers);
//    // Now strike all completedusers from allusers.
//    $allusers = array_diff($allusers, $completedusers);
//    // For paging I use array_slice().
//    if ($startpage !== false AND $pagecount !== false) {
//        $allusers = array_slice($allusers, $startpage, $pagecount);
//    }
//    return $allusers;
//}
//
///**
// * Called by HTML editor in showrespondents and Essay question. Based on question/essay/renderer.
// * Pending general solution to using the HTML editor outside of moodleforms in Moodle pages.
// */
//function sliclquestions_get_editor_options($context) {
//    return array(
//                    'subdirs' => 0,
//                    'maxbytes' => 0,
//                    'maxfiles' => -1,
//                    'context' => $context,
//                    'noclean' => 0,
//                    'trusttext' => 0
//    );
//}
//
//// Skip logic: we need to find out how many questions will actually be displayed on next page/section.
//function sliclquestions_nb_questions_on_page ($questionsinsliclquestions, $questionsinsection, $rid) {
//    global $DB;
//    $questionstodisplay = array();
//    foreach ($questionsinsection as $question) {
//        if ($question->dependquestion != 0) {
//            switch ($questionsinsliclquestions[$question->dependquestion]->type_id) {
//                case SLICLQUESYESNO:
//                    if ($question->dependchoice == 0) {
//                        $questiondependchoice = "'y'";
//                    } else {
//                        $questiondependchoice = "'n'";
//                    }
//                    $responsetable = 'response_bool';
//                    break;
//                default:
//                    $questiondependchoice = $question->dependchoice;
//                    $responsetable = 'resp_single';
//            }
//            $sql = 'SELECT * FROM {sliclquestions}_'.$responsetable.' WHERE response_id = '.$rid.
//            ' AND question_id = '.$question->dependquestion.' AND choice_id = '.$questiondependchoice;
//            if ($DB->get_record_sql($sql)) {
//                $questionstodisplay [] = $question->id;
//            }
//        } else {
//            $questionstodisplay [] = $question->id;
//        }
//    }
//    return $questionstodisplay;
//}
//
//function sliclquestions_get_dependencies($questions, $position) {
//    $dependencies = array();
//    $dependencies[''][0] = get_string('choosedots');
//
//    foreach ($questions as $question) {
//        if (($question->type_id == SLICLQUESRADIO || $question->type_id == SLICLQUESDROP || $question->type_id == SLICLQUESYESNO)
//                        && $question->position < $position) {
//            if (($question->type_id == SLICLQUESRADIO || $question->type_id == SLICLQUESDROP) && $question->name != '') {
//                foreach ($question->choices as $key => $choice) {
//                    $contents = sliclquestions_choice_values($choice->content);
//                    if ($contents->modname) {
//                        $choice->content = $contents->modname;
//                    } else if ($contents->title) { // Must be an image; use its title for the dropdown list.
//                        $choice->content = $contents->title;
//                    } else {
//                        $choice->content = $contents->text;
//                    }
//                    $dependencies[$question->name][$question->id.','.$key] = $question->name.'->'.$choice->content;
//                }
//            }
//            if ($question->type_id == SLICLQUESYESNO && $question->name != '') {
//                $dependencies[$question->name][$question->id.',0'] = $question->name.'->'.get_string('yes');
//                $dependencies[$question->name][$question->id.',1'] = $question->name.'->'.get_string('no');
//            }
//        }
//    }
//    return $dependencies;
//}
//
//// Get the parent of a child question.
//function sliclquestions_get_parent ($question) {
//    global $DB;
//    $qid = $question->id;
//    $parent = array();
//    $dependquestion = $DB->get_record('sliclquestions_question', array('id' => $question->dependquestion),
//                    $fields = 'id, position, name, type_id');
//    if (is_object($dependquestion)) {
//        $qdependchoice = '';
//        switch ($dependquestion->type_id) {
//            case QUESRADIO:
//            case QUESDROP:
//                $dependchoice = $DB->get_record('sliclquestions_quest_choice', array('id' => $question->dependchoice),
//                    $fields = 'id,content');
//                $qdependchoice = $dependchoice->id;
//                $dependchoice = $dependchoice->content;
//
//                $contents = sliclquestions_choice_values($dependchoice);
//                if ($contents->modname) {
//                    $dependchoice = $contents->modname;
//                }
//                break;
//            case QUESYESNO:
//                switch ($question->dependchoice) {
//                    case 0:
//                        $dependchoice = get_string('yes');
//                        $qdependchoice = 'y';
//                        break;
//                    case 1:
//                        $dependchoice = get_string('no');
//                        $qdependchoice = 'n';
//                        break;
//                }
//                break;
//        }
//        // Qdependquestion, parenttype and qdependchoice fields to be used in preview mode.
//        $parent [$qid]['qdependquestion'] = 'q'.$dependquestion->id;
//        $parent [$qid]['qdependchoice'] = $qdependchoice;
//        $parent [$qid]['parenttype'] = $dependquestion->type_id;
//        // Other fields to be used in Questions edit mode.
//        $parent [$qid]['position'] = $question->position;
//        $parent [$qid]['name'] = $question->name;
//        $parent [$qid]['content'] = $question->content;
//        $parent [$qid]['parentposition'] = $dependquestion->position;
//        $parent [$qid]['parent'] = $dependquestion->name.'->'.$dependchoice;
//    }
//    return $parent;
//}
//
//// Get parent position of all child questions in current sliclquestions.
//function sliclquestions_get_parent_positions ($questions) {
//    global $DB;
//    $parentpositions = array();
//    foreach ($questions as $question) {
//        $dependquestion = $question->dependquestion;
//        if ($dependquestion != 0) {
//            $childid = $question->id;
//            $parentpos = $questions[$dependquestion]->position;
//            $parentpositions[$childid] = $parentpos;
//        }
//    }
//    return $parentpositions;
//}
//
//// Get child position of all parent questions in current sliclquestions.
//function sliclquestions_get_child_positions ($questions) {
//    global $DB;
//    $childpositions = array();
//    foreach ($questions as $question) {
//        $dependquestion = $question->dependquestion;
//        if ($dependquestion != 0) {
//            $parentid = $questions[$dependquestion]->id;
//            if (!isset($firstchildfound[$parentid])) {
//                $firstchildfound[$parentid] = true;
//                $childpos = $question->position;
//                $childpositions[$parentid] = $childpos;
//            }
//        }
//    }
//    return $childpositions;
//}
//
//// Check if current sliclquestions contains child questions.
//function sliclquestions_has_dependencies($questions) {
//    foreach ($questions as $question) {
//        if ($question->dependquestion != 0) {
//            return true;
//            break;
//        }
//    }
//    return false;
//}
//
//// Check that the needed page breaks are present to separate child questions.
//function sliclquestions_check_page_breaks($sliclquestions) {
//    global $DB;
//    $msg = '';
//    // Store the new page breaks ids.
//    $newpbids = array();
//    $delpb = 0;
//    $sid = $sliclquestions->survey->id;
//    $questions = $DB->get_records('sliclquestions_question', array('survey_id' => $sid, 'deleted' => 'n'), 'id');
//    $positions = array();
//    foreach ($questions as $key => $qu) {
//        $positions[$qu->position]['question_id'] = $key;
//        $positions[$qu->position]['dependquestion'] = $qu->dependquestion;
//        $positions[$qu->position]['dependchoice'] = $qu->dependchoice;
//        $positions[$qu->position]['type_id'] = $qu->type_id;
//        $positions[$qu->position]['qname'] = $qu->name;
//        $positions[$qu->position]['qpos'] = $qu->position;
//    }
//    $count = count($positions);
//
//    for ($i = $count; $i > 0; $i--) {
//        $qu = $positions[$i];
//        $questionnb = $i;
//        if ($qu['type_id'] == SLICLQUESPAGEBREAK) {
//            $questionnb--;
//            // If more than one consecutive page breaks, remove extra one(s).
//            $prevqu = null;
//            $prevtypeid = null;
//            if ($i > 1) {
//                $prevqu = $positions[$i - 1];
//                $prevtypeid = $prevqu['type_id'];
//            }
//            // If $i == $count then remove that extra page break in last position.
//            if ($prevtypeid == SLICLQUESPAGEBREAK || $i == $count || $qu['qpos'] == 1) {
//                $qid = $qu['question_id'];
//                $delpb ++;
//                $msg .= get_string("checkbreaksremoved", "sliclquestions", $delpb).'<br />';
//                // Need to reload questions.
//                $questions = $DB->get_records('sliclquestions_question', array('survey_id' => $sid, 'deleted' => 'n'), 'id');
//                $DB->set_field('sliclquestions_question', 'deleted', 'y', array('id' => $qid, 'survey_id' => $sid));
//                $select = 'survey_id = '.$sid.' AND deleted = \'n\' AND position > '.
//                                $questions[$qid]->position;
//                if ($records = $DB->get_records_select('sliclquestions_question', $select, null, 'position ASC')) {
//                    foreach ($records as $record) {
//                        $DB->set_field('sliclquestions_question', 'position', $record->position - 1, array('id' => $record->id));
//                    }
//                }
//            }
//        }
//        // Add pagebreak between question child and not dependent question that follows.
//        if ($qu['type_id'] != SLICLQUESPAGEBREAK) {
//            $qname = $positions[$i]['qname'];
//            $j = $i - 1;
//            if ($j != 0) {
//                $prevtypeid = $positions[$j]['type_id'];
//                $prevdependquestion = $positions[$j]['dependquestion'];
//                $prevdependchoice = $positions[$j]['dependchoice'];
//                $prevdependquestionname = $positions[$j]['qname'];
//                $prevqname = $positions[$j]['qname'];
//                if (($prevtypeid != SLICLQUESPAGEBREAK && ($prevdependquestion != $qu['dependquestion']
//                                || $prevdependchoice != $qu['dependchoice']))
//                                || ($qu['dependquestion'] == 0 && $prevdependquestion != 0)) {
//                    $sql = 'SELECT MAX(position) as maxpos FROM {sliclquestions_question} '.
//                                    'WHERE survey_id = '.$sliclquestions->survey->id.' AND deleted = \'n\'';
//                    if ($record = $DB->get_record_sql($sql)) {
//                        $pos = $record->maxpos + 1;
//                    } else {
//                        $pos = 1;
//                    }
//                    $question = new Object();
//                    $question->survey_id = $sliclquestions->survey->id;
//                    $question->type_id = SLICLQUESPAGEBREAK;
//                    $question->position = $pos;
//                    $question->content = 'break';
//                    if (!($newqid = $DB->insert_record('sliclquestions_question', $question))) {
//                        return(false);
//                    }
//                    $newpbids[] = $newqid;
//                    $movetopos = $i;
//                    $sliclquestions = new sliclquestions($sliclquestions->id, null, $course, $cm);
//                    $sliclquestions->move_question($newqid, $movetopos);
//                }
//            }
//        }
//    }
//    if (empty($newpbids) && !$msg) {
//        $msg = get_string('checkbreaksok', 'sliclquestions');
//    } else if ($newpbids) {
//        $msg .= get_string('checkbreaksadded', 'sliclquestions').'&nbsp;';
//        $newpbids = array_reverse ($newpbids);
//        $sliclquestions = new sliclquestions($sliclquestions->id, null, $course, $cm);
//        foreach ($newpbids as $newpbid) {
//            $msg .= $sliclquestions->questions[$newpbid]->position.'&nbsp;';
//        }
//    }
//    return($msg);
//}
//
//// Get all descendants and choices for questions with descendants.
//function sliclquestions_get_descendants_and_choices ($questions) {
//    global $DB;
//    $questions = array_reverse($questions, true);
//    $qu = array();
//    foreach ($questions as $question) {
//        if ($question->dependquestion) {
//            $dq = $question->dependquestion;
//            $dc = $question->dependchoice;
//            $qid = $question->id;
//
//            $qu['descendants'][$dq][] = 'qn-'.$qid;
//            if (array_key_exists($qid, $qu['descendants'])) {
//                foreach ($qu['descendants'][$qid] as $q) {
//                    $qu['descendants'][$dq][] = $q;
//                }
//            }
//            $qu['choices'][$dq][$dc][] = 'qn-'.$qid;
//        }
//    }
//    return($qu);
//}
//
//// Get all descendants for a question to be deleted.
//function sliclquestions_get_descendants ($questions, $questionid) {
//    global $DB;
//    $questions = array_reverse($questions, true);
//    $qu = array();
//    foreach ($questions as $question) {
//        if ($question->dependquestion) {
//            $dq = $question->dependquestion;
//            $qid = $question->id;
//            $qpos = $question->position;
//            $qu[$dq][] = $qid;
//            if (array_key_exists($qid, $qu)) {
//                foreach ($qu[$qid] as $q) {
//                    $qu[$dq][] = $q;
//                }
//            }
//        }
//    }
//    $descendants = array();
//    if (isset($qu[$questionid])) {
//        foreach ($qu[$questionid] as $descendant) {
//            $childquestion = $questions[$descendant];
//            $descendants += sliclquestions_get_parent ($childquestion);
//        }
//        uasort($descendants, 'sliclquestions_cmp');
//    }
//    return($descendants);
//}
//
//// Function to sort descendants array in sliclquestions_get_descendants function.
//function sliclquestions_cmp($a, $b) {
//    if ($a == $b) {
//        return 0;
//    } else if ($a < $b) {
//        return -1;
//    } else {
//        return 1;
//    }
//}
