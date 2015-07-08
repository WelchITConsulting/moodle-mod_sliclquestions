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

    // Ability to view the module contents
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

    // Ability to submit completed questionnaires
    'mod/sliclquestions:submit' => array(

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

    // Ability to print a blank questionnaire
    'mod/sliclquestions:printblank' => array(

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

    // Ability to preview a questionnaire
    'mod/sliclquestions:preview' => array(
        'captype'            => 'read',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),

    // Ability to create and edit questionnairea
    'mod/sliclquestions:manage' => array(

        'captype'            => 'write',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),

    // Ability to submit a pupil assessment questionnaire
    'mod/sliclquestions:assesspupils' => array(
        'captype'            => 'read',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),

    // Ability to create a register of pupils
    'mod/sliclquestions:registerpupils' => array(
        'captype'            => 'read',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),

    // Ability to view individual responses to a questionnaire
    'mod/sliclquestions:viewstatistics' => array(
        'captype'            => 'read',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    ),

    // Ability to download responses in a CSV file
    'mod/sliclquestions:downloadresponses' => array(

        'captype'            => 'write',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'manager'        => CAP_ALLOW
        )
    ),

    // Ability to delete someone's (or own) previous responses
    'mod/sliclquestions:deleteresponses' => array(

        'captype'            => 'write',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'manager'        => CAP_ALLOW
        )
    ),

    // Ability to edit questionnaire questions
    'mod/sliclquestions:editquestions' => array(

        'captype'            => 'write',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'manager'        => CAP_ALLOW
        )
    ),

//    // Ability to create template questionnaires which can be copied, but not used
//    'mod/sliclquestions:createtemplates' => array(
//
//        'captype'            => 'write',
//        'contextlevel'       => CONTEXT_MODULE,
//        'legacy'             => array(
//            'manager'        => CAP_ALLOW
//        )
//    ),
//
//    // Ability to create public surveys which can be accessed from multiple places
//    'mod/sliclquestions:createpublic' => array(
//
//        'captype'            => 'write',
//        'contextlevel'       => CONTEXT_MODULE,
//        'legacy'             => array(
//            'coursecreator'  => CAP_ALLOW,
//            'manager'        => CAP_ALLOW
//        )
//    ),

    // Ability to read others previous responses to a questionnaire
    // Subject to the constraints on whether reponses can be viewed whilst the
    // questionnaire is still open or user has not yet responded themselves
    'mod/sliclquestions:viewallresponses' => array(
        'captype'            => 'read',
        'contextlevel'       => CONTEXT_MODULE,
        'legacy'             => array(
            'manager'        => CAP_ALLOW
        )
    ),

    // Ability to read own responses to a questionnaire
    'mod/sliclquestions:viewownresponses' => array(
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

//    // Ability to read others responses without the above checks
//    'mod/sliclquestions:readallresponseanytime' => array(
//
//        'captype'            => 'read',
//        'contextlevel'       => CONTEXT_MODULE,
//        'legacy'             => array(
//            'manager'        => CAP_ALLOW
//        )
//    ),

    // Ability to message students from a questionnaire
    'mod/sliclquestions:message' => array(
        'riskbit'            => RISK_SPAM,
        'captype'            => 'write',
        'contextlevel'       => CONTEXT_MODULE,
        'archtypes'          => array(
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    )
);
