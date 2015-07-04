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
 * Filename : mod_form
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/sliclquestions/locallib.php');

class mod_sliclquestions_mod_form extends moodleform_mod
{
    protected function definition()
    {
        global $CFG, $COURSE, $sliclquestions_types;

        $mform    =& $this->_form;

        $config = get_config('sliclquestions');

        //----------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'sliclquestions'), array('size' => '48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('description'));

        //----------------------------------------------------------------------
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'sliclquestions'));

        $mform->addElement('select', 'questype', get_string('questype', 'sliclquestions'), $sliclquestions_types);
        $mform->setType('questype', PARAM_INT);
        $mform->addRule('questype', null, 'required', null, 'client');
        $mform->addRule('questype', get_string('nonzeroerror', 'sliclquestions'), 'nonzero', null, 'client');
        $mform->addElement('select', 'register', get_string('pupilregister', 'sliclquestions'), sliclquestions_registers());
        $mform->setType('questype', PARAM_INT);
        $mform->addHelpButton('register', 'pupilregister');
        $mform->disabledIf('register', 'questype', 'neq', SLICLQUESTIONS_PUPILASSESSMENT);
        $mform->addElement('editor', 'page', get_string('content', 'sliclquestions'), null, sliclquestions_editor_options($this->context));
        $mform->addRule('page', get_string('required'), 'required', null, 'client');

        //----------------------------------------------------------------------
        $mform->addElement('header', 'displayoptionshdr', get_string('displayoptions', 'sliclquestions'));

        $mform->addElement('advcheckbox', 'printheading', get_string('printheading', 'sliclquestions'));
        $mform->setDefault('printheading', $config->printheading);
        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'sliclquestions'));
        $mform->setDefault('printintro', $config->printintro);

        //----------------------------------------------------------------------
        $mform->addElement('header', 'timinghdr', get_string('timing', 'form'));

        $enableopengroup = array();
        $enableopengroup[] =& $mform->createElement('checkbox', 'useopendate', get_string('opendate', 'sliclquestions'));
        $enableopengroup[] =& $mform->createElement('date_time_selector', 'opendate', '');
        $mform->addGroup($enableopengroup, 'enableopengroup', get_string('opendate', 'sliclquestions'), ' ', false);
        $mform->addHelpButton('enableopengroup', 'opendate', 'sliclquestions');
        $mform->disabledIf('enableopengroup', 'useopendate', 'notchecked');

        $enableclosegroup = array();
        $enableclosegroup[] =& $mform->createElement('checkbox', 'useclosedate', get_string('closedate', 'sliclquestions'));
        $enableclosegroup[] =& $mform->createElement('date_time_selector', 'closedate', '');
        $mform->addGroup($enableclosegroup, 'enableclosegroup', get_string('closedate', 'sliclquestions'), ' ', false);
        $mform->addHelpButton('enableclosegroup', 'closedate', 'sliclquestions');
        $mform->disabledIf('enableclosegroup', 'useclosedate', 'notchecked');

        //----------------------------------------------------------------------
        $this->standard_coursemodule_elements();

        //----------------------------------------------------------------------
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('page');
            $defaultvalues['page']['format'] = $defaultvalues['contentformat'];
            $defaultvalues['page']['text']   = file_prepare_draft_area($draftitemid, $this->context->id,
                                                                       'mod_sliclquestions', 'content', 0,
                                                                       sliclquestions_editor_options($this->context),
                                                                       $defaultvalues['content']);
            $defaultvalues['page']['itemid'] = $draftitemid;
        }
        if (empty($defaultvalues['opendate'])) {
            $defaultvalues['useopendate'] = 0;
        } else {
            $defaultvalues['useopendate'] = 1;
        }
        if (empty($defaultvalues['closedate'])) {
            $defaultvalues['useclosedate'] = 0;
        } else {
            $defaultvalues['useclosedate'] = 1;
        }
        if (!empty($defaultvalues['displayoptions'])) {
            $displayoptions = unserialize($defaultvalues['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $defaultvalues['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $defaultvalues['printheading'] = $displayoptions['printheading'];
            }
        }
    }
}