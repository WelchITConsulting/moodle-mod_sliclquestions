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
 * Created  : 09 Jun 2015
 */

require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

class sliclquestions
{
    private $id = -1;
    private $course = '';
    private $name = '';
    private $intro = '';
    private $introformat = 0;
    private $questype = 0;

    private $opendate = 0;
    private $closedate = 0;
    private $timecreated = 0;
    private $timemodified = 0;
    private $cm = 0;
    private $context = null;

    public function __construct($id, $sliclquestions, &$course, &$cm)
    {
        global $DB;

        if ($id) {
            $sliclquestions = $DB->get_record('sliclquestions', array('id' => $id));
        }
        if (is_object($sliclquestions)) {
            $properties = get_object_vars($sliclquestions);
            foreach($properties as $k => $v) {
                $this->$k = $v;
            }
        }
        $this->course = $course;
        $this->cm = $cm;
        if (!empty($cm) && !empty($this->id)) {
            $this->context = context_module::instance($cm->id);
        }
    }

    public function get_type()
    {
        return $this->questype;
    }
}
