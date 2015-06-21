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
        $mform->addRule('questype', get_string('null'), 'nonzero', null, 'client');
        $mform->addElement('editor', 'page', get_string('content', 'sliclquestions'), null, sliclquestions_get_editor_options($this->context));
        $mform->addRule('page', get_string('required'), 'required', null, 'client');

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






















//        global $sliclquestionstypes, $sliclquestionsrespondents, $sliclquestionsresponseviewers, $sliclquestionsrealms, $autonumbering;
//        $mform->addElement('header', 'sliclquestionshdr', get_string('responseoptions', 'sliclquestions'));
//
//        $mform->addElement('select', 'qtype', get_string('qtype', 'sliclquestions'), $sliclquestionstypes);
//        $mform->addHelpButton('qtype', 'qtype', 'sliclquestions');
//
//        $mform->addElement('hidden', 'cannotchangerespondenttype');
//        $mform->setType('cannotchangerespondenttype', PARAM_INT);
//        $mform->addElement('select', 'respondenttype', get_string('respondenttype', 'sliclquestions'), $sliclquestionsrespondents);
//        $mform->addHelpButton('respondenttype', 'respondenttype', 'sliclquestions');
//        $mform->disabledIf('respondenttype', 'cannotchangerespondenttype', 'eq', 1);
//
//        $mform->addElement('select', 'resp_view', get_string('responseview', 'sliclquestions'), $sliclquestionsresponseviewers);
//        $mform->addHelpButton('resp_view', 'responseview', 'sliclquestions');
//
//        $options = array('0' => get_string('no'), '1' => get_string('yes'));
//        $mform->addElement('select', 'resume', get_string('resume', 'sliclquestions'), $options);
//        $mform->addHelpButton('resume', 'resume', 'sliclquestions');
//
//        $options = array('0' => get_string('no'), '1' => get_string('yes'));
//        $mform->addElement('select', 'navigate', get_string('navigate', 'sliclquestions'), $options);
//        $mform->addHelpButton('navigate', 'navigate', 'sliclquestions');
//
//        $mform->addElement('select', 'autonum', get_string('autonumbering', 'sliclquestions'), $autonumbering);
//        $mform->addHelpButton('autonum', 'autonumbering', 'sliclquestions');
//        // Default = autonumber both questions and pages.
//        $mform->setDefault('autonum', 3);
//
//        // Removed potential scales from list of grades. CONTRIB-3167.
//        $grades[0] = get_string('nograde');
//        for ($i = 100; $i >= 1; $i--) {
//            $grades[$i] = $i;
//        }
//        $mform->addElement('select', 'grade', get_string('grade', 'sliclquestions'), $grades);
//
//        if (empty($sliclquestions->sid)) {
//            if (!isset($sliclquestions->id)) {
//                $sliclquestions->id = 0;
//            }
//
//            $mform->addElement('header', 'contenthdr', get_string('contentoptions', 'sliclquestions'));
//            $mform->addHelpButton('contenthdr', 'createcontent', 'sliclquestions');
//
//            $mform->addElement('radio', 'create', get_string('createnew', 'sliclquestions'), '', 'new-0');
//
//            // Retrieve existing private sliclquestionss from current course.
//            $surveys = sliclquestions_get_survey_select($sliclquestions->id, $COURSE->id, 0, 'private');
//            if (!empty($surveys)) {
//                $prelabel = get_string('useprivate', 'sliclquestions');
//                foreach ($surveys as $value => $label) {
//                    $mform->addElement('radio', 'create', $prelabel, $label, $value);
//                    $prelabel = '';
//                }
//            }
//            // Retrieve existing template sliclquestionss from this site.
//            $surveys = sliclquestions_get_survey_select($sliclquestions->id, $COURSE->id, 0, 'template');
//            if (!empty($surveys)) {
//                $prelabel = get_string('usetemplate', 'sliclquestions');
//                foreach ($surveys as $value => $label) {
//                    $mform->addElement('radio', 'create', $prelabel, $label, $value);
//                    $prelabel = '';
//                }
//            } else {
//                $mform->addElement('static', 'usetemplate', get_string('usetemplate', 'sliclquestions'),
//                                '('.get_string('notemplatesurveys', 'sliclquestions').')');
//            }
//
//            // Retrieve existing public sliclquestionss from this site.
//            $surveys = sliclquestions_get_survey_select($sliclquestions->id, $COURSE->id, 0, 'public');
//            if (!empty($surveys)) {
//                $prelabel = get_string('usepublic', 'sliclquestions');
//                foreach ($surveys as $value => $label) {
//                    $mform->addElement('radio', 'create', $prelabel, $label, $value);
//                    $prelabel = '';
//                }
//            } else {
//                $mform->addElement('static', 'usepublic', get_string('usepublic', 'sliclquestions'),
//                                   '('.get_string('nopublicsurveys', 'sliclquestions').')');
//            }
//
//            $mform->setDefault('create', 'new-0');
//        }

        //----------------------------------------------------------------------
        $this->standard_coursemodule_elements();

        //----------------------------------------------------------------------
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('page');
            $defaultvaluesp['page'] = array('format' => $defaultvalues['contentformat'],
                                            'text'   => $defaultvalues['content'],
                                            'itemid' => $draftitemid);
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
//        // Prevent sliclquestions set to "anonymous" to be reverted to "full name".
//        $defaultvalues['cannotchangerespondenttype'] = 0;
//        if (!empty($defaultvalues['respondenttype']) && $defaultvalues['respondenttype'] == "anonymous") {
//            // If this sliclquestions has responses.
//            $numresp = $DB->count_records('sliclquestions_response',
//                            array('survey_id' => $defaultvalues['sid'], 'complete' => 'y'));
//            if ($numresp) {
//                $defaultvalues['cannotchangerespondenttype'] = 1;
//            }
//        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }

//    public function add_completion_rules() {
//        $mform =& $this->_form;
//        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'sliclquestions'));
//        return array('completionsubmit');
//    }
//
//    public function completion_rule_enabled($data) {
//        return !empty($data['completionsubmit']);
//    }
}