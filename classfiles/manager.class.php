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
 * Filename : manager
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 21 Jun 2015
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');
require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/sliclquestions.class.php');

class mod_sliclquestions_management_console
{
    static private $_instance;

    static public function get_instance(&$course, &$context, &$survey, &$url, &$params)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new mod_sliclquestions_management_console($course, $context, $survey, $url, $params);
        }
        return self::$_instance;
    }

    public function __construct(&$course, &$context, &$survey, &$url, &$params)
    {
        if ($survey->questype == SLICLQUESTIONS_PUPILREGISTRATION) {
            $this->pupil_registration_statistics($survey, $course, $context, $url);
        } elseif ($survey->questype == SLICLQUESTIONS_PUPILASSESSMENT) {
            $this->pupil_assessment($survey, $course, $context, $url);
        } elseif ($survey->questype == SLICLQUESTIONS_SURVEY) {
            $this->display_survey($course, $context, $survey, $url, $params);
        } else {
            notice(get_string('invalidquesttype', 'sliclquestions'), $url);
        }
    }









    private function pupil_assessment(&$course, &$context, &$survey, &$url, &$params)
    {
        if ($survey->capabilities->viewallresponse) {

        } elseif ($survey->capabilitis->viewownresponses) {

        }
    }

    private function display_survey(&$course, &$context, &$survey, &$url, &$params)
    {
        global $OUTPUT;

        if (!empty($params['act'])) {
            switch($params['act']) {

                // Display response
                case 'resp':
                    $uid = required_param('uid', PARAM_INT);
                    $this->show_response($uid, $survey);
                    break;

                // Display the non-respondents for the survey
                case 'noresp':
                    $this->show_non_respondents();
                    break;

                // Display a list of those who have responded
                default:
                    $this->show_respondents($survey, $params);
                    break;
            }
        } else {
            // Display a list of those who have responded
            $this->show_respondents($survey, $params);
        }
    }

    private function show_response($uid, &$survey)
    {
        global $DB, $OUTPUT, $PAGE;

        $user = $DB->get_record('user', array('id' => $uid));
        $response = $DB->get_record('sliclquestions_response', array('survey_id' => $survey->id,
                                                                     'userid'    => $user->id));
        $question = $DB->get_record('sliclquestions_question', array('survey_id' => $response->survey_id));
        $answer = $DB->get_record('sliclquestions_resp_text', array('responseid' => $response->id,
                                                                    'questionid' => $question->id));
//        $PAGE->set_pagelayout('popup');
        echo $survey->render_page_header()
           . $OUTPUT->box_start('sliclquestions-question')
           . html_writer::div($question->content)
           . $OUTPUT->box_end()
           . $OUTPUT->box_start('sliclquestions-answer')
           . html_writer::start_div('quote')
           . html_writer::tag('h2', fullname($user))
           . html_writer::end_div()
           . html_writer::div((!empty($answer->response) ? $answer->response : ''),
                              'quoted-text')
           . $OUTPUT->box_end()
           . $OUTPUT->footer();
        exit();
    }

    private function show_non_respondents()
    {
        global $DB, $CFG, $PAGE, $OUTPUT;

        $showall        = optional_param('showall', false, PARAM_INT);
        $currentgroupid = optional_param('grp', 0, PARAM_INT);
        $perpage        = optional_param('perpage', SLICLQUESTIONS_DEFAULT_PER_PAGE, PARAM_INT);
        $subject        = optional_param('subject', '', PARAM_CLEANHTML);
        $message        = optional_param('message', '', PARAM_CLEANHTML);
        $messageuser    = optional_param_array('messageuser', false, PARAM_INT);
        if (!isset($params['act'])) {
            $params['act'] = '';
        }
        if (isset($survey->cm->groupmode) && empty($course->groupmodeforce)) {
            $groupmode = $survey->cm->groupmode;
        } else {
            $groupmode = $course->groupmode;
        }
        $groupselect = groups_print_activity_menu($survey->cm, $url->out(), true);
        $mygroupid   = groups_get_activity_group($survey->cm);
        $baseurl     = new moodle_url('/mod/sliclquestions/view.php');
        $baseurl->params(array('id'      => $survey->cm->id,
                               'showall' => $showall));
        $tablecolumns = array('userpic', 'fullname');
        $extrafields  = get_extra_user_fields($context);
        $tableheaders = array(get_string('userpic'), get_string('fullnameuser'));
        if (in_array('email', $extrafields) || has_capability('moodle/course:viewhiddenuserfields', $context)) {
            $tablecolumns[] = 'email';
            $tableheaders[] = get_string('email');
        }
        if (!isset($hiddenfields['city'])) {
            $tablecolumns[] = 'city';
            $tableheaders[] = get_string('city');
        }
        if (!isset($hiddenfields['country'])) {
            $tablecolumns[] = 'country';
            $tableheaders[] = get_string('country');
        }
        if (!isset($hiddenfields['lastaccess'])) {
            $tablecolumns[] = 'lastaccess';
            $tableheaders[] = get_string('lastaccess');
        }
        if ($survey->capabilities->message) {
            $tablecolumns[] = 'select';
            $tableheaders[] = get_string('select');
        }
        $table = new flexible_table('sliclquestions-shownonrespondents-' . $course->id);
        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($baseurl);
        $table->sortable(true, 'lastname', SORT_DESC);
        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'showentrytable');
        $table->set_attribute('class', 'flexible generaltable generalbox');
        $table->set_control_variables(array(TABLE_VAR_SORT   => 'ssort',
                                            TABLE_VAR_IFIRST => 'sifirst',
                                            TABLE_VAR_ILAST  => 'silast',
                                            TABLE_VAR_PAGE   => 'spage'));
        $table->no_sorting('status');
        $table->no_sorting('select');
        $table->setup();
        if ($table->get_sql_sort()) {
            $sort = $table->get_sql_sort();
        } else {
            $sort = '';
        }
        if ($groupmode > 0) {
            if ($mygroupid > 0) {
                $usedgroupid = $mygroupid;
            } else {
                $usedgroupid = false;
            }
        } else {
            $usedgroupid = false;
        }
        $nonrespondents      = $this->get_incomplete_users($context, $survey->id. $usedgroupid);
        $countnonrespondents  = count($nonrespondents);
        $table->initialbars(false);
        if ($showall) {
            $startpage = false;
            $pagecount = false;
        } else {
            $table->pagesize($perpage, $countnonrespondents);
            $startpage = $table->get_page_start();
            $pagecount = $table->get_page_size();
        }
        $nonrespondents = $this->get_incomplete_users($context, $survey->id, $usedgroupid, $sort, $startpage, $pagecount);
        echo $survey->render_page_header()
           . (isset($groupselect) ? $groupselect : '')
           . html_writer::div('', 'clearer')
           . $OUTPUT->box_start('left-align');
        if (!$nonrespondents) {
            echo $OUTPUT->notification(get_string('noexistingparticipants', 'enrol'));
        } else {
            echo print_string('nonrespondents', 'sliclquestions')
               . ' ('
               . $countnonrespondents
               . ')'
               . html_writer::start_tag('form', array('class'  => 'mform',
                                                      'action' => '/mod/sliclquestions/view.php',
                                                      'method' => 'post',
                                                      'id'     => 'sliclquestions_sendmessageform'));
            foreach($nonrespondents as $nonrespondent) {
                $user = $DB->get_record('user', array('id' => $nonrespondent));
                $profilelink = html_writer::start_tag('strong')
                             . html_writer::tag('a',
                                                fullname($user),
                                                array('href' => $CFG->wwwroot
                                                              . '/user/view.php?id='
                                                              . $user->id
                                                              . '&amp;course='
                                                              . $course->id))
                             . html_writer::end_tag('strong');
                $data = array($OUTPUT->user_picture($user, array('courseid' => $course->id)),
                              $profilelink);
                if (in_array('email', $tablecolumns)) {
                    $data[] = $user->email;
                }
                if (!isset($hiddenfields['city'])) {
                    $data[] = $user->city;
                }
                $countries = get_string_manager()->get_list_of_countries();
                if (!isset($hidddenfields['country'])) {
                    $data[] = (!empty($user->country) ? $countries[$user->country] : '');
                }
                $datestring = new stdClass();
                $datestring->year  = get_string('year');
                $datestring->years = get_string('years');
                $datestring->day   = get_string('day');
                $datestring->days  = get_string('days');
                $datestring->hour  = get_string('hour');
                $datestring->hours = get_string('hours');
                $datestring->min   = get_string('min');
                $datestring->mins  = get_string('mins');
                $datestring->sec   = get_string('sec');
                $datestring->secs  = get_string('secs');
                if ($user->lastaccess) {
                    $lastaccess = format_time(time() - $user->lastaccess, $datestring);
                } else {
                    $lastaccess = get_string('never');
                }
                $data[] = $lastaccess;
                if ($survey->capabilities->message) {
                    $data[] = html_writer::empty_tag('input', array('type'  => 'checkbox',
                                                                    'class' => 'usercheckbox',
                                                                    'name'  => 'messageuser[]',
                                                                    'value' => $user->id,
                                                                    'alt'   => ''));
                }
                $table->add_data($data);
            }
            $table->print_html();
            $allurl = new moodle_url($baseurl);
            if ($showall) {
                $allurl->param('showall', 0);
                echo $OUTPUT->container(html_writer::link($allurl,
                                                          get_string('showperpage', '', SLICLQUESTIONS_DEFAULT_PER_PAGE)),
                                        array(), 'showall');
            } elseif (($countnonrespondents > 0) && ($perpage < $countnonrespondents)) {
                $allurl->param('showall', 1);
                echo $OUTPUT->container(html_writer::link($allurl,
                                                          get_string('showall', '', $countnonrespondents)),
                                        array(), 'showall');
            }
            if ($survey->capabilities->message) {
                echo $OUTPUT->box_start('mdl-align')
                   . html_writer::start_div('buttons')
                   . html_writer::empty_tag('input', array('type' => 'button',
                                                           'id'   => 'checkall',
                                                           'value' => get_string('selectall')))
                   . html_writer::empty_tag('input', array('type' => 'button',
                                                           'id'   => 'checknone',
                                                           'value' => get_string('deselectall')))
                   . html_writer::end_div()
                   . $OUTPUT->box_end()
                   . (($params['act'] == 'sendmessage') && !is_array($messageuser) ? $OUTPUT->notification(get_string('nousersselected', 'sliclquestions'))
                                                                                   : '');
            }
        }
        if ($survey->capabilities->message) {
            $editor = editors_get_preferred_editor();
            $editor->use_editor('message_id', sliclquestions_editor_options($context));
            $texteditor = html_writer::div(html_writer::tag('textarea', $message, array('id'   => 'message_id',
                                                                                        'name' => 'message',
                                                                                        'rows' => '10',
                                                                                        'cols' => '60')));
            $table = new html_table();
            $table->align = array('left', 'left');
            $table->data[] = array(html_writer::tag('strong', get_string('subject', 'sliclquestions')),
                                   html_writer::empty_tag('input', array('type'      => 'text',
                                                                         'id'        => 'sliclquestions_subject',
                                                                         'name'      => 'subject',
                                                                         'size'      => '65',
                                                                         'maxlength' => '255',
                                                                         'value'     => $subject)));
            $table->data[] = array(html_writer::tag('strong', get_string('messagebody')),
                                   $texteditor);
            echo html_writer::start_tag('fieldset', array('class' => 'clearfix'))
               . (($params['act'] == 'sendmessage') && (empty($subject) || empty($message)) ? $OUTPUT->notification(get_string('allfieldsrequired'))
                                                                                            : '')
               . html_writer::tag('legend',
                                  get_string('sendmessage', 'sliclquestions'),
                                  array('class' => 'ftoggler'))
               . html_writer::empty_tag('input', array('type' => 'hidden',
                                                       'name' => 'format',
                                                       'value' => FORMAT_HTML))
               . html_writer::table($table)
               . $OUTPUT->box_start('mdl-left')
               . html_writer::start_div('buttons')
               . html_writer::empty_tag('input', array('type'  => 'submit',
                                                       'name'  => 'send_message',
                                                       'value' => get_string('send', 'sliclquestions')))
               . html_writer::end_div()
               . $OUTPUT->box_end()
               . html_writer::empty_tag('input', array('type'  => 'hidden',
                                                       'name'  => 'sesskey',
                                                       'value' => sesskey()))
               . html_writer::empty_tag('input', array('type'  => 'hidden',
                                                       'name'  => 'act',
                                                       'value' => 'sendmessage'))
               . html_writer::empty_tag('input', array('type'  => 'hidden',
                                                       'name'  => 'id',
                                                       'value' => $survey->cm->id))
               . html_writer::end_tag('fieldset')
               . html_writer::end_tag('form');
            $PAGE->requires->js_init_call('M.mod_sliclquestions.init_sendmessage',
                                          null,
                                          false,
                                          array('name'     => 'mod_sliclquestions',
                                                'fullpath' => '/mod/sliclquestions/module.js'));
        }
        echo $OUTPUT->box_end()
           . $OUTPUT->footer();
        exit();
    }

    private function show_respondents(&$survey, &$params)
    {
        global $DB, $PAGE, $OUTPUT, $USER;

        $select = 'survey_id=' . $survey->id;
        if (!$survey->capabilities->viewallresponses) {
            $select .= ' AND userid=' . $USER->id;
        }
        $responses = $DB->get_records_select('sliclquestions_response', $select);
        if ($responses) {
            $table = new html_table();
            $table->head = array(get_string('respondents', 'sliclquestions'),
                                 get_string('dateresponded', 'sliclquestions'));
            $table->align = array('left', 'left');
            foreach($responses as $response) {
                $user = $DB->get_record('user', array('id' => $response->userid));
                $params['uid'] = $user->id;
                $params['act'] = 'resp';
                $userlink = html_writer::tag('a',
                                             fullname($user),
                                             array('href' => new moodle_url('/mod/sliclquestions/view.php', $params)));
                $table->data[] = array($userlink,
                                       userdate($response->submitted));
            }
        }
        echo $survey->render_page_header()
           . $OUTPUT->box_start('generalbox center clearfix')
           . html_writer::table($table)
           . $OUTPUT->box_end()
           . $OUTPUT->footer();
        exit();
    }

    private function pupil_registration_statistics(&$survey, &$course, $context, $url)
    {
        global $CFG, $DB, $OUTPUT;

        $sort  = optional_param('s', 'lastname', PARAM_ALPHA);
        $order = optional_param('o', 'ASC', PARAM_ALPHA);
        $firstnamesort = array('s' => 'firstname');
        if ($sort == 'firstname') {
            $firstnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $firstnamesort['o'] = 'ASC';
        }
        $lastnamesort = array('s' => 'lastname');
        if ($sort == 'lastname') {
            $lastnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $lastnamesort['o'] = 'ASC';
        }
        $nameheader = '<a href="' . $url->out(true, $firstnamesort) . '">'
                    . get_string('firstname') . '</a> / <a href="'
                    . $url->out(true, $lastnamesort) . '">'
                    . get_string('lastname') . '</a>';
        $table = new html_table();
        $table->head = array($nameheader,
                             get_string('pupilsfemale', 'sliclquestions'),
                             get_string('pupilsmale', 'sliclquestions'));
        $table->align = array('left', 'center', 'center');
        $totalmales = 0;
        $totalfemales = 0;
        $sql = 'SELECT DISTINCT CONCAT(ce.id,sr.sex) AS ind, ce.id, ce.firstname, ce.lastname, sr.sex, count(*) AS numrec'
             . ' FROM (SELECT u.id, u.firstname, u.lastname FROM {user} u, {role_assignments} ra,'
             . ' {role} r WHERE u.id = ra.userid AND ra.roleid = r.id'
             . ' AND r.shortname=? AND ra.contextid=?) AS ce '
             . ' LEFT OUTER JOIN {sliclquestions_students} sr ON ce.id=sr.teacher_id'
             . ' AND sr.survey_id=1 AND sr.deleteflag=0 GROUP BY ce.firstname,ce.lastname,sr.teacher_id,sr.sex'
             . ' ORDER BY ';
        if ($sort == 'firstname') {
            $sql .= 'ce.firstname '
                  . ($order == 'ASC' ? 'ASC' : 'DESC')
                  . ',ce.lastname ASC,sr.sex DESC';
        } else {
            $sql .= 'ce.lastname '
                  . ($order == 'ASC' ? 'ASC' : 'DESC')
                  . ',ce.firstname ASC,sr.sex DESC';
        }
        $context = context_course::instance($course->id);
        $results = $DB->get_records_sql($sql, array('sbenquirer',
                                                    $context->id));
        $data = array();
        foreach($results as $record) {
            if (!array_key_exists($record->id, $data)) {
                $data[$record->id] = array($record->firstname . ' ' . $record->lastname, 0, 0);
            }
            if ($record->sex == 'm') {
                $data[$record->id][2] = $record->numrec;
                $totalmales += $record->numrec;
            } elseif ($record->sex == 'f') {
                $data[$record->id][1] = $record->numrec;
                $totalfemales += $record->numrec;
            }
        }
        $table->data = $data;
        $totaltable = new html_table();
        $totaltable->head   = array('',
                                    get_string('pupilsfemale', 'sliclquestions'),
                                    get_string('pupilsmale', 'sliclquestions'),
                                    get_string('pupilstotal', 'sliclquestions'));
        $totaltable->align  = array('left', 'center', 'center', 'center');
        $totaltable->data[] = array(get_string('pupilsregistered', 'sliclquestions'),
                                    $totalfemales,
                                    $totalmales,
                                    ($totalfemales + $totalmales));

        // Output the list of pupils
        echo $survey->render_page_header()
           . $OUTPUT->box_start('generalbox center clearfix')
           . html_writer::tag('p', get_string('statsregisteredcontent', 'sliclquestions'))
           . html_writer::start_div('slicl-registered-pupils')
           . html_writer::table($totaltable)
           . html_writer::end_div()
           . html_writer::table($table)
           . $OUTPUT->box_end()
           . $OUTPUT->footer();
        exit();
    }

    private function pupil_assessment_statistics(&$survey, &$course, $context, $url)
    {
        global $CFG, $DB, $OUTPUT;

        $sort  = optional_param('s', 'lastname', PARAM_ALPHA);
        $order = optional_param('o', 'ASC', PARAM_ALPHA);
        $firstnamesort = array('s' => 'firstname');
        if ($sort == 'firstname') {
            $firstnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $firstnamesort['o'] = 'ASC';
        }
        $lastnamesort = array('s' => 'lastname');
        if ($sort == 'lastname') {
            $lastnamesort['o'] = ($order == 'ASC' ? 'DESC' : 'ASC');
        } else {
            $lastnamesort['o'] = 'ASC';
        }
        $nameheader = '<a href="' . $url->out(true, $firstnamesort) . '">'
                    . get_string('firstname') . '</a> / <a href="'
                    . $url->out(true, $lastnamesort) . '">'
                    . get_string('lastname') . '</a>';
        $table = new html_table();
        $table->head = array($nameheader,
                             get_string('pupilsfemale', 'sliclquestions'),
                             get_string('pupilsmale', 'sliclquestions'));
        $table->align = array('left', 'center', 'center');
        $totalmales           = 0;
        $totalassessedmales   = 0;
        $totalfemales         = 0;
        $totalassessedfemales = 0;
        $sql = 'SELECT DISTINCT CONCAT(ce.id,sr.sex) AS ind, ce.id, ce.firstname, ce.lastname, sr.sex, count(*) AS numrec'
             . ' FROM (SELECT u.id, u.firstname, u.lastname FROM {user} u, {role_assignments} ra,'
             . ' {role} r WHERE u.id = ra.userid AND ra.roleid = r.id'
             . ' AND r.shortname=? AND ra.contextid=?) AS ce '
             . ' LEFT OUTER JOIN {sliclquestions_students} sr ON ce.id=sr.teacher_id'
             . ' AND sr.survey_id=1 AND sr.deleteflag=0 GROUP BY ce.firstname,ce.lastname,sr.teacher_id,sr.sex'
             . ' ORDER BY ';
        if ($sort == 'firstname') {
            $sql .= 'ce.firstname '
                  . ($order == 'ASC' ? 'ASC' : 'DESC')
                  . ',ce.lastname ASC,sr.sex DESC';
        } else {
            $sql .= 'ce.lastname '
                  . ($order == 'ASC' ? 'ASC' : 'DESC')
                  . ',ce.firstname ASC,sr.sex DESC';
        }
        $context = context_course::instance($course->id);
        $results = $DB->get_records_sql($sql, array('sbenquirer',
                                                    $context->id));
        $data = array();
        foreach($results as $record) {
            $sql = 'SELECT COUNT(r.id) AS assessed'
                 . ' FROM {sliclquestions_response} r, {sliclquestions_students} s'
                 . ' WHERE s.id=r.pupilid AND s.deleteflag=0 AND r.userid=? AND r.survey_id=? AND s.sex=?';
            $assessed = (int)$DB->count_records_sql($sql, array($record->id, $survey->id, $record->sex));

            if (!array_key_exists($record->id, $data)) {
                $data[$record->id] = array($record->firstname . ' ' . $record->lastname, '', '');
            }
            if ($record->sex == 'm') {
                $data[$record->id][2]   = html_writer::tag('strong', $assessed, array('style' => 'color:#2fa4e7'))
                                        . ' (' .$record->numrec . ')';
                $totalmales             += $record->numrec;
                $totalassessedmales     += $assessed;
            } elseif ($record->sex == 'f') {
                $data[$record->id][1]   = html_writer::tag('strong', $assessed, array('style' => 'color:#2fa4e7'))
                                        . ' (' .$record->numrec . ')';
                $totalfemales           += $record->numrec;
                $totalassessedfemales   += $assessed;
            }
        }
        $table->data = $data;
        $totaltable = new html_table();
        $totaltable->head   = array('',
                                    get_string('pupilsfemale', 'sliclquestions'),
                                    get_string('pupilsmale', 'sliclquestions'),
                                    get_string('pupilstotal', 'sliclquestions'));
        $totaltable->align  = array('left', 'center', 'center', 'center');
        $totaltable->data[] = array(get_string('pupilsassessed', 'sliclquestions'),
                                    html_writer::tag('strong', $totalassessedfemales, array('style' => 'color:#2fa4e7')) . ' (' . $totalfemales . ')',
                                    html_writer::tag('strong', $totalassessedmales, array('style' => 'color:#2fa4e7')) . ' (' . $totalmales . ')',
                                    html_writer::tag('strong', ($totalassessedfemales + $totalassessedmales), array('style' => 'color:#2fa4e7'))
                                    . ' (' . ($totalfemales + $totalmales) . ')');

        // Output the list of pupils
        echo $survey->render_page_header()
           . $OUTPUT->box_start('generalbox center clearfix')
           . html_writer::tag('p', get_string('statsassessedcontent', 'sliclquestions'))
           . html_writer::start_div('slicl-registered-pupils')
           . html_writer::table($totaltable)
           . html_writer::end_div()
           . html_writer::table($table)
           . $OUTPUT->box_end()
           . $OUTPUT->footer();
        exit();
    }




    private function get_incomplete_users(&$context, $id, $group = false, $sort = '', $startpage = false, $pagecount = false)
    {
        global $DB;

        if (!$allusers = get_users_by_capability($context, 'mod/sliclquestions:submit',
                                                 'u.id,u.username', $sort, '', '',
                                                 $group, '', true)) {
            return false;
        }
        $allusers = array_keys($allusers);
        $sql = 'SELECT DISTINCT userid FROM {sliclquestions_response}'
             . ' WHERE survey_id=?';
        if (!$completedusers = $DB->get_records_sql($sql, array($id))) {
            return $allusers;
        }
        $completedusers = array_keys($completedusers);
        $allusers       = array_diff($allusers, $completedusers);
        if (($startpage !== false) && ($pagecount !== false)) {
            $allusers = array_slice($allusers, $startpage, $pagecount);
        }
        return $allusers;
    }

    private function get_creative_enquirers($context, $id, $group = false, $sort = '', $startpage = false, $pagecount = false)
    {
        global $DB;

        $sql = 'SELECT u.id, u.firstname, u.lastname FROM {user} u, {role_assignments} ra,'
             . ' {role} r WHERE u.id = ra.userid AND ra.roleid = r.id'
             . ' AND r.shortname=? AND ra.contextid=?';
        if (!$ces = $DB->get_records_sql($sql, array('sbenquirer', $context->id))) {
            return false;
        }
        $ces = array_keys($ces);
        $sql = 'SELECT DISTINCT userid FROM {sliclquestions_response}'
             . ' WHERE survey_id=?';
        if (!$completed = $DB->get_records_sql($sql, array($id))) {
            return $ces;
        }
        $completed = array_keys($completed);
        $ces       = array_diff($ces, $completed);
        if (($startpage !== false) && ($pagecount !== false)) {
            $ces = array_slice($ces, $startpage, $pagecount);
        }
        return $ces;
    }
}
