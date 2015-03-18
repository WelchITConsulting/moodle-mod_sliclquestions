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
 * Filename : restore_sliclquestions_activity_task
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 16 Mar 2015
 */

defined('MOODLE_INTERNAL') || die();

// Because it exists (must).
require_once($CFG->dirroot . '/mod/sliclquestions/backup/moodle2/restore_sliclquestions_stepslib.php');

/**
 * questionnaire restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_sliclquestions_activity_task extends restore_activity_task
{
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings()
    {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps()
    {
        // Choice only has one structure step.
        $this->add_step(new restore_sliclquestions_activity_structure_step('sliclquestions_structure', 'sliclquestions.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents()
    {
        return array(new restore_decode_content('sliclquestions',
                                                array('intro'),
                                               'sliclquestions'),
                     new restore_decode_content('questionnaire_survey',
                                                array('info', 'thank_head',
                                                'thank_body'),
                                                     'sliclquestions_survey'),
                     new restore_decode_content('sliclquestions_question',
                                                array('content'),
                                                'sliclquestions_question'));
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules()
    {
        return array(new restore_decode_rule('SLICLQUESTIONSVIEWBYID',
                                             '/mod/sliclquestions/view.php?id=$1',
                                             'course_module'),
                     new restore_decode_rule('SLICLQUESTIONSINDEX',
                                             '/mod/sliclquestions/index.php?id=$1',
                                             'course'));
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * questionnaire logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules()
    {
        return array(new restore_log_rule('sliclquestions',
                'add',
                'view.php?id={course_module}',
                '{sliclquestions}'),
                     new restore_log_rule('sliclquestions',
                                          'update',
                                          'view.php?id={course_module}',
                                          '{sliclquestions}'),
                     new restore_log_rule('sliclquestions',
                                          'view',
                                          'view.php?id={course_module}',
                                          '{sliclquestions}'),
                     new restore_log_rule('sliclquestions',
                                          'choose',
                                          'view.php?id={course_module}',
                                          '{sliclquestions}'),
                     new restore_log_rule('sliclquestions',
                                          'choose again',
                                          'view.php?id={course_module}',
                                          '{sliclquestions}'),
                     new restore_log_rule('sliclquestions',
                                          'report',
                                          'report.php?id={course_module}',
                                          '{sliclquestions}'));
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course()
    {
        return array(
                     new restore_log_rule('sliclquestions',
                                          'view all',
                                          'index?id={course}',
                                          null,
                                          null,
                                          null,
                                          'index.php?id={course}'),
                     new restore_log_rule('sliclquestions',
                                          'view all',
                                          'index.php?id={course}',
                                          null));
    }
}
