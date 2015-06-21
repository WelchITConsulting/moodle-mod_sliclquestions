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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

function sliclquestions_supports($feature)
{
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:             return MOD_ARCHETYPE_RESOURCE;

        case FEATURE_BACKUP_MOODLE2:            return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS:   return false;
        case FEATURE_GRADE_HAS_GRADE:           return false;
        case FEATURE_GRADE_OUTCOMES:            return false;
        case FEATURE_GROUPS:                    return false;
        case FEATURE_GROUPINGS:                 return false;
        case FEATURE_MOD_INTRO:                 return true;
        case FEATURE_SHOW_DESCRIPTION:          return true;

        default:                                return null;
    }
}

function sliclquestions_add_instance($sliclquestions)
{
    global $DB;

    $time = time();
    $sliclquestions->timemodified = $time;
    $sliclquestions->timecreated = $time;

    if (!$id = $DB->add_record('sliclquestions', $sliclquestions)) {
        return false;
    }

    return $id;
}

function sliclquestions_update_instance($sliclquestions)
{
    global $DB;

//    if (!empty($sliclquestions->id) && !empty($sliclquestions->realm)) {
//        $DB->set_field('sliclquestions_survey', 'realm', $sliclquestions->realm, array('id' => $sliclquestions->id));
//    }

    $sliclquestions->timemodified = time();
    $sliclquestions->id = $sliclquestions->instance;

    // Add the events for the date settings for the item to the calendar
//    sliclquestions_set_events($sliclquestions);

    // Update the records and return the results
    return $DB->update_record('sliclquestions', $sliclquestions);
}

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
