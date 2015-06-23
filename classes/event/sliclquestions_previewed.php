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
 * Filename : sliclquestions_previewed
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 23 Jun 2015
 */

namespace mod_sliclquestions\event;

defined('MOODLE_INTERNAL') || die();

class sliclquestions_previewed extends \core\event\base
{
    public static function get_name()
    {
        return get_string('event_previewed', 'mod_sliclquestions');
    }

    public function get_description()
    {
        return sprintf(get_string('event_previed_desc', 'mod_sliclquestions'),
                       $this->userid,
                       $this->contextinstanceid);
    }

    public function get_url()
    {
        return new moodle_url('/mod/sliclquestions/preview.php',
                              array('id' => $this->contextinstanceid));
    }


    protected function init()
    {
        $this->data['objecttable'] = 'sliclquestions';
        $this->data['crud']        = 'r';
        $this->data['edulevel']    = self::LEVEL_OTHER;
    }
}
