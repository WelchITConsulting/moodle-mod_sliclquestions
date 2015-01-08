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
 * Filename : access
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 06 Jan 2015
 */

$capabilities = array(

    // Ability to add a new questionnaire instance
    'mod/sliclquestions:addinstance' => array(

        'riskbitmask'        => RISK_XSS,
        'captype'            => 'write',
        'contextlevel'       => CONTEXT_COURSE,
        'archetypes'         => array(
            'manager'        => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),

    // Ability to see that a questionnaire exists along with some basic informtation
    'mod/sliclquestions:view' => array(

        'captype'            => 'read',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'student'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),

    // Ability to complete and submit a questionnaire
    'mod/sliclquestions:view' => array(

        'captype'            => 'write',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'student'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),
);
