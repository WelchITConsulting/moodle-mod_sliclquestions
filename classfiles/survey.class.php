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
 * Filename : survey
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 22 Jun 2015
 */

require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/sliclquestions.class.php');

class sliclquestions_survey
{
    static private $_instance;

    static public function get_instance(&$course, &$context, &$survey, &$url, &$params)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new sliclquestions_survey($course, $context, $survey, $url, $params);
        }
        return self::$_instance;
    }

    public function __construct(&$course, &$context, sliclquestions &$survey,
                                moodle_url &$url, &$params)
    {
        global $DB, $OUTPUT, $USER;

        $survey->render_page_header();
        if (!$survey->is_open()) {
            notice(get_string('notopen', 'sliclquestions', userdate($survey->opendate)), $url);
        } elseif ($survey->is_closed()) {
            notice(get_string('closed', 'sliclquestions', userdate($survey->closedate)), $url);
        } elseif (!$survey->user_is_eligible($USER->id)) {
            if ($survey->questions) {
                notice(get_string('noteligible', 'sliclquestions'), $url);
            }
        } else {
            $select = 'survey_id = ' . $survey->id
                    . ' AND userid = ' . $USER->id;
            $resume = $DB->get_record_select('sliclquestions_response', $select, null) !== false;
            if ($resume) {
                $complete = get_string('resumesurvey', 'sliclquestions');
            } else {
                $complete = get_string('answerquestions', 'sliclquestions');
            }
            if ($survey->questions) {
                $complete = html_writer::tag('strong', $complete);
                echo html_writer::link(new moodle_url('/mod/sliclquestions/complete.php', array('id' => $survey->cm->id)),
                                       $complete);
            }
            $OUTPUT->footer();
        }
        if (!$survey->questions) {
            echo html_writer::tag('p', get_string('noneinuse', 'sliclquestions'));
        }
        if ($survey->capabilities->editquestions && !$survey->questions) {
            $str = html_writer::tag('strong', get_string('addquestions', 'sliclquestions'));
            echo html_writer::link(new moodle_url('/mod/sliclquestions/questions.php', array('id' => $survey->cm->id)),
                                   $str);
        }
    }
}
