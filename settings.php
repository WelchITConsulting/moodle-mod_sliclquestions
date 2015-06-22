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
 * Filename : settings
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 22 Jun 2015
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // Add the global print heading setting
    $settings->add(new admin_setting_configcheckbox('sliclquestions/printheading',
                                                    get_string('printheading', 'sliclquestions'),
                                                    get_string('printheadingexplain', 'sliclquestions'),
                                                    1));

    // Add the global print intro setting
    $settings->add(new admin_setting_configcheckbox('sliclquestions/printintro',
                                                    get_string('printintro', 'sliclquestions'),
                                                    get_string('printintroexplain', 'sliclquestions'),
                                                    0));
}