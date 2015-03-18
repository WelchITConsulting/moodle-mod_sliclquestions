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
 * Filename : backup_sliclquestions_activity_task
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 16 Mar 2015
 */

require_once($CFG->dirroot . '/mod/sliclquestions/backup/moodle2/backup_sliclquestions_stepslib.php');
require_once($CFG->dirroot . '/mod/sliclquestions/backup/moodle2/backup_sliclquestions_settingslib.php');

/**
 * questionnaire backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_sliclquestions_activity_task extends backup_activity_task
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
        $this->add_step(new backup_sliclquestions_activity_structure_step('sliclquestions_structure', 'sliclquestions.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content)
    {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");
        // Link to the list of questionnaires.
        $search = "/(".$base."\/mod\/sliclquestions\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@SLICLQUESTIONSINDEX*$2@$', $content);

        // Link to questionnaire view by moduleid.
        $search = "/(".$base."\/mod\/sliclquestions\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@SLICLQUESTIONSVIEWBYID*$2@$', $content);

        return $content;
    }
}
