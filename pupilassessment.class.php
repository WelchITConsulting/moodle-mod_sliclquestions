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
 * Filename : pupilassessment
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 22 Jun 2015
 */

require_once($CFG->dirroot . '/mod/sliclquestions/sliclquestions.class.php');

class mod_sliclquestions_pupil_assessment
{
    static private $_instance;

    static public function get_instance(&$sliclquestions, &$context, &$url, &$params)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new mod_sliclquestions_pupil_assessment($sliclquestions, $context, $url, $params);
        }
        return self::$_instance;
    }

    public function __construct(sliclquestions &$sliclquestions, &$context, &$url, &$params)
    {
        global $USER;

        if (!$sliclquestions->is_open()) {
            echo html_writer::div(get_string('notopen', 'sliclquestions', userdate($sliclquestions->opendate)), 'message');
        } elseif ($sliclquestions->is_closed()) {
            echo html_writer::div(get_string('closed', 'sliclquestions', userdate($sliclquestions->closedate)), 'message');
        } elseif ($sliclquestions->user_is_eligible($USER->id)) {
            if ($sliclquestions->questions) {
                echo html_writer::div(get_string('noteligible', 'sliclquestions'), 'message');
            }
        } elseif ($sliclquestions->user_can_take($USER-id)) {
            $select = 'survey_id = ' . $sliclquestions->id
                    . ' AND userid = ' . $USER->id;
            $resume = $DB->get_record_select('sliclquestions_response', $select, null) !== false;
            if ($resume) {
                $complete = get_string('resumesurvey', 'sliclquestions');
            } else {
                $complete = get_string('answerquestions', 'sliclquestions');
            }
            if ($sliclquestions->questions) {
                $complete = html_writer::tag('strong', $complete);
                echo html_writer::link(new moodle_url('/mod/sliclquestions/complete.php', array('id' => $sliclquestions->cm->id)),
                                       $complete);
            }
        }
        if (!$sliclquestions->questions) {
            echo html_writer::tag('p', get_string('noneinuse', 'sliclquestions'));
        }
        if ($sliclquestions->capabilities->editquestions && !$sliclquestions->questions) {
            $str = html_writer::tag('strong', get_string('addquestions', 'sliclquestions'));
            echo html_writer::link(new moodle_url('/mod/sliclquestions/questions.php', array('id' => $sliclquestions->cm->id)),
                                   $str);
        }
        echo '<p>end of module</p>';
    }
}
