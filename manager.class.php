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

class mod_sliclquestions_management_console
{
    static private $_instance;

    static public function get_instance($course, $cm, $survey, $url, $params)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new mod_sliclquestions_management_console($course, $cm, $survey, $url, $params);
        }
        return self::$_instance;
    }

    public function __construct($course, $cm, $survey, $url, $params)
    {
        echo "Management Console";
    }

    private function display_statistics()
    {

    }

    private function get_non_respondents()
    {

    }
}