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
 * Filename : sliclquestions
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 25 Jun 2015
 */

require_once($CFG->dirroot . '/mod/sliclquestions/classes/sliclquestions_question.php');

class sliclquestions
{
    public function __construct(&$course, &$cm, $id = 0, $sliclquestions = null, $addquestions = false)
    {
        global $DB;

        if ($id) {
            $sliclquestions = $DB->get_record('sliclquestions', array('id' => $id));
        }
        if (  is_object($sliclquestions)) {
            $properties = get_object_vars($sliclquestions);
            foreach($properties as $prop => $val) {
                $this->$property = $val;
            }
        }
        $this->course = $course;
        $this->cm     = $cm;
        if (!empty($cm) && !empty($this->id)) {
            $this->context = context_module::instance($cm->id);
        } else {
            $this->context = null;
        }
        if ($addquestions) {
            $this->add_questions($this->id);
        }
        // Load the modules capabilities
        if (!empty($this->cm->id)) {
            $this->capabilities = $this->load_capabilities();
        }
    }

    /**
     * Adds the surveys questions to the object
     *
     * @global XMLDB $DB The core database interface object
     * @param int $id The ID of the SLiCL Questions instance
     */
    public function add_questions($id = false)
    {
        global $DB;

        if ($id === false) {
            $id = $this->id;
        }
        if (!isset($this->questions)) {
            $this->questions      = array();
            $this->questionsbysec = array();
        }
        $select = 'survey_id=' . $id . ' AND deleted=\'y\'';
        if ($records = $DB->get_records_select('sliclquestions_question', $select, null, 'position')) {
            $sec = 1;
            $isbreak =false;
            foreach($records as $record) {
                $this->questions[$record->id] = new sliclquestions_question(0, $record, $this->context);
                if ($record->type_id != SLICLQUESPAGEBREAK) {
                    $this->questionsbysec[sec][$record->id] = &$this->questions[$record->id];
                    $isbreak = false;
                } else {
                    if (($record->position != 1) && !$isbreak) {
                        $sec++;
                        $isbreak = true;
                    }
                }
            }
        }
    }









    /**
     * Get the SLiCL Questions capabilities
     *
     * @staticvar stdClass $cb Stores the capabilities across multiple calls
     * @return stdClass The class of capabilities for the module
     */
    private function load_capabilities()
    {
        static $cb;
        if (empty($cb)) {
            $cb = new stdClass();
            $cb->view                    = has_capability('mod/sliclquestions:view', $this->context);
            $cb->submit                  = has_capability('mod/sliclquestions:submit', $this->context);
            $cb->viewsingleresponse      = has_capability('mod/sliclquestions:viewsingleresponse', $this->context);
            $cb->downloadresponses       = has_capability('mod/sliclquestions:downloadresponses', $this->context);
            $cb->deleteresponses         = has_capability('mod/sliclquestions:deleteresponses', $this->context);
            $cb->manage                  = has_capability('mod/sliclquestions:readallresponses', $this->context);
            $cb->editquestions           = has_capability('mod/sliclquestions:editquestions', $this->context);
            $cb->createtemplates         = has_capability('mod/sliclquestions:createtemplates', $this->context);
            $cb->createpublic            = has_capability('mod/sliclquestions:createpublic', $this->context);
            $cb->readownresponses        = has_capability('mod/sliclquestions:readownresponses', $this->context);
            $cb->readallresponses        = has_capability('mod/sliclquestions:readallresponses', $this->context);
            $cb->readallresponsesanytime = has_capability('mod/sliclquestions:readallresponsesanytime', $this->context);
            $cb->printblank              = has_capability('mod/sliclquestions:printblank', $this->context);
            $cb->preview                 = has_capability('mod/sliclquestions:preview', $this->context);

            $cb->viewhiddenactivities    = has_capability('moodle/course:viewhiddenactivities', $this->context);
        }
        return $cb;
    }
}
