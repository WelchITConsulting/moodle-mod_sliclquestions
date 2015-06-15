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
 * Filename : pupil_form
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 09 Jun 2015
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');

class mod_sliclquestions_pupil_form extends moodleform_mod
{
    protected function definition()
    {
        $mform =& $this->_form;

        $mform->addElement('text', 'forename', get_string('forename', 'sliclquestions'), array('size' => 64));
        $mform->setType('forename', PARAM_TEXT);
        $mform->addRule('forename', null, 'required', null, 'client');

        $mform->addElement('text', 'surname', get_string('surname', 'sliclquestions'), array('size' => 64));
        $mform->setType('surname', PARAM_TEXT);
        $mform->addRule('surname', null, 'required', null, 'client');

        $mform->addElement('select', 'sex', get_string('sex', 'sliclquestions'),
                           array('m' => get_string('male', 'sliclquestions'),
                                 'f' => get_string('female', 'sliclquestions')));
        $mform->setType('sex', PARAM_ALPHA);
        $mform->addRule('sex', null, 'required', null, 'client');

        $mform->addElement('select', 'year_id', get_string('schoolyear', 'sliclquestions'),
                           array('3' => '3',
                                 '4' => '4'));
        $mform->setType('year_id', PARAM_INT);
        $mform->addRule('year_id', null, 'required', null, 'client');

        $mform->addElement('text', 'class_id', get_string('classid', 'sliclquestions'));
        $mform->setType('class_id', PARAM_TEXT);
        $mform->addHelpButton('class_id', 'classid', 'sliclquestions');

        $mform->addElement('select', 'kpi_level', get_string('kpilevel', 'sliclquestions'),
                           array(1 => '1', 2 => '2', 3 => '3', 4 => '4'));
        $mform->setType('kpi_level', PARAM_INT);
        $mform->addRule('kpi_level', null, 'required', null, 'client');
        $mform->setDefault('kpi_level', 2);

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'act', 0);
        $mform->setType('act', PARAM_ALPHA);

        $mform->addElement('hidden', 'pid', 0);
        $mform->setType('pid', PARAM_INT);

        $mform->add_action_buttons();
    }
}