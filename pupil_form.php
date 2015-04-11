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
 * Created  : 23 Mar 2015
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class sliclquestions_pupil_form extends moodleform
{
    public function definition()
    {
        $mform =& $this->_form;

        $mform->addElement('text', 'forename', get_string('firstname'));
        $mform->setType('forename', PARAM_ALPHA);
        $mform->addRule('forename', get_string('forename_error', 'sliclquestions'), 'required');

        $mform->addElement('text', 'surname', get_string('lastname'));
        $mform->setType('surname', PARAM_ALPHA);
        $mform->addRule('surname', get_string('surname_error', 'sliclquestions'), 'required');

        $mform->addElement('select', 'sex', get_string('sex', 'sliclquestions'), array('f' => get_string('sex_female', 'sliclquestions'),
                                                                                       'm' => get_string('sex_male', 'sliclquestions')));
        $mform->setType('sex', PARAM_ALPHA);
        $mform->setDefault('sex', 'm');

        $mform->addElement('select', 'year_id', get_string('schoolyear', 'sliclquestions'), array(3 => '3',
                                                                                                  4 => '4'));
        $mform->setType('year_id', PARAM_INT);

        $mform->addElement('text', 'class_id', get_string('class_id', 'sliclquestions'));
        $mform->setType('class_id', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('class_id', 'class_id', 'sliclquestions');

        $mform->addElement('select', 'kpi_level', get_string('kpilevel', 'sliclquestions'), array(1 => '1',
                                                                                                  2 => '2',
                                                                                                  3 => '3',
                                                                                                  4 => '4'));
        $mform->setType('kpi_level', PARAM_INT);
        $mform->setDefault('kpi_level', 2);

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action', 0);
        $mform->setType('action', PARAM_ALPHA);

        $mform->addElement('hidden', 'pid', 0);
        $mform->setType('pid', PARAM_INT);

        $this->add_action_buttons();
    }

    public function validation($data, $files)
    {
        return array();
    }
}
