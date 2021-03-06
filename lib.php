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
        case FEATURE_MOD_ARCHETYPE:             return MOD_ARCHETYPE_ASSIGNMENT;

        case FEATURE_BACKUP_MOODLE2:            return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS:   return false;
        case FEATURE_GRADE_HAS_GRADE:           return false;
        case FEATURE_GRADE_OUTCOMES:            return false;
        case FEATURE_GROUPS:                    return true;
        case FEATURE_GROUPINGS:                 return true;
        case FEATURE_MOD_INTRO:                 return true;
        case FEATURE_SHOW_DESCRIPTION:          return true;

        default:                                return null;
    }
}

function sliclquestions_add_instance($data, $mform = null)
{
    global $DB;

    $cmid = $data->coursemodule;

    $data->timemodified = time();
    $data->timecreated = $data->timemodified;

    if ($mform) {
        $data->content       = $data->page['text'];
        $data->contentformat = $data->page['format'];
    }
    $data->displayoptions = serialize(array('printheading' => $data->printheading,
                                            'printintro'   => $data->printintro));

    if (empty($data->useopendate)) {
        $data->opendate = 0;
    }
    if (empty($data->useclosedate)) {
        $data->closedate = 0;
    }

    // Create the instance in the datanase
    $data->id = $DB->insert_record('sliclquestions', $data);

    // Add the events for the date settings for the item to the calendar
    sliclquestions_set_events($data);

    $DB->set_field('course_modules', 'instance', $data->id, array('id' => $cmid));
    $context = context_module::instance($cmid);
    if ($mform && !empty($data->page['itemid'])) {
        $draftitemid = $data->page['itemid'];
        $data->content = file_save_draft_area_files($draftitemid, $context->id,
                                                    'mod_sliclquestions', 'content', 0,
                                                    sliclquestions_editor_options($context),
                                                    $data->content);
        $DB->update_record('sliclquestions', $data);
    }
    return $data->id;
}

function sliclquestions_update_instance($data, $mform)
{
    global $DB;

    $cmid                 = $data->coursemodule;
    $draftitemid          = $data->page['itemid'];
    $data->id             = $data->instance;
    $data->content        = $data->page['text'];
    $data->contentformat  = $data->page['format'];
    $data->timemodified   = time();
    $data->displayoptions = serialize(array('printheading' => $data->printheading,
                                            'printintro'   => $data->printintro));
    if (empty($data->useopendate)) {
        $data->opendate = 0;
    }
    if (empty($data->useclosedate)) {
        $data->closedate = 0;
    }

    $DB->update_record('sliclquestions', $data);
    // Add the events for the date settings for the item to the calendar
    sliclquestions_set_events($data);

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $data->content = file_save_draft_area_files($draftitemid, $context->id,
                                                    'mod_sliclquestions', 'content', 0,
                                                    sliclquestions_editor_options($context),
                                                    $data->content);
        $DB->update_record('sliclquestions', $data);
    }
    return true;
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

    // Remove all responses from the tables
    $response_types = array('');

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
