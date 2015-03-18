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
 * Filename : fbsettings_form
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/sliclquestions/lib.php');

class sliclquestions_feedback_form extends moodleform {

    protected $_feedbacks;

    public function definition() {
        global $sliclquestions, $DB, $SESSION;
        $currentsection   = $SESSION->sliclquestions->currentfbsection;
        $sectionid   = $this->_customdata['sectionid'];
        $feedbacksections = $sliclquestions->survey->feedbacksections;
        $this->_feedbacks = $DB->get_records('sliclquestions_feedback',
                array('section_id' => $sectionid),
                'minscore DESC');
        $this->context = $sliclquestions->context;
        $mform    =& $this->_form;

        if ($feedbacksections == 1) {
            $feedbackheading = get_string('feedbackglobalheading', 'sliclquestions');
            $feedbackmessages = get_string('feedbackglobalmessages', 'sliclquestions');
        } else {
            $feedbackheading = get_string('feedbacksectionheading', 'sliclquestions', $currentsection.'/'.$feedbacksections);
            $feedbackmessages = get_string('feedbackmessages', 'sliclquestions', $currentsection.'/'.$feedbacksections);
        }

        $mform->addElement('header', 'contenthdr', $feedbackheading);

        $questions = $sliclquestions->questions;
        $fbsection = $DB->get_record('sliclquestions_fb_sections',
                        array('survey_id' => $sliclquestions->survey->id, 'section' => $currentsection));
        $questionslist = '';
        if (isset($fbsection->scorecalculation)) {
            $scorecalculation = unserialize($fbsection->scorecalculation);
            $questionslist = '<ul style="float: left;">';
            foreach ($scorecalculation as $qid => $key) {
                $questionslist .= '<li>'.$questions[$qid]->name.'</li>';
            }
            $questionslist .= '</ul>';
        }
        $mform->addElement('static', 'questionsinsectionlist', get_string('questionsinsection', 'sliclquestions'), $questionslist);
        $mform->addElement('text', 'sectionlabel', get_string('feedbacksectionlabel', 'sliclquestions'),
                        array('size' => '50', 'maxlength' => '50'));
        $mform->setType('sectionlabel', PARAM_TEXT);
        $mform->addRule('sectionlabel', null, 'required', null, 'client');
        $mform->addHelpButton('sectionlabel', 'feedbacksectionlabel', 'sliclquestions');

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext' => true);
        $mform->addElement('editor', 'sectionheading', get_string('feedbacksectionheadingtext', 'sliclquestions'),
                        null, $editoroptions);
        $mform->setType('info', PARAM_RAW);

        $mform->addHelpButton('sectionheading', 'feedbackheading', 'sliclquestions');

        // FEEDBACK FIELDS.

        $mform->addElement('header', 'feedbackhdr', $feedbackmessages);
        $mform->addHelpButton('feedbackhdr', 'feedback', 'sliclquestions');

        $mform->addElement('static', 'scoreboundarystatic1',
                        get_string('feedbackscoreboundary', 'sliclquestions'), '100%');

        $repeatarray = array();
        $repeatedoptions = array();

        $repeatarray[] = $mform->createElement('editor', 'feedbacktext',
                        get_string('feedback', 'sliclquestions'), null, array('maxfiles' => EDITOR_UNLIMITED_FILES,
                                        'noclean' => true, 'context' => $sliclquestions->context));
        $repeatarray[] = $mform->createElement('text', 'feedbackboundaries',
                        get_string('feedbackscoreboundary', 'sliclquestions'), array('size' => 10));
        $repeatedoptions['feedbacklabel']['type'] = PARAM_RAW;
        $repeatedoptions['feedbacktext']['type'] = PARAM_RAW;
        $repeatedoptions['feedbackboundaries']['type'] = PARAM_RAW;

        $numfeedbacks = max(count($this->_feedbacks) * 1, 3);

        $nextel = $this->repeat_elements($repeatarray, $numfeedbacks - 1,
                        $repeatedoptions, 'boundary_repeats', 'boundary_add_fields', 2,
                        get_string('feedbackaddmorefeedbacks', 'sliclquestions'), true);

        // Put some extra elements in before the button.
        $mform->insertElementBefore($mform->createElement('editor',
                "feedbacktext[$nextel]", get_string('feedback', 'sliclquestions'), null,
                array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true,
                        'context' => $sliclquestions->context)),
                'boundary_add_fields');
        $mform->insertElementBefore($mform->createElement('static',
                        'scoreboundarystatic2', get_string('feedbackscoreboundary', 'sliclquestions'), '0%'),
                        'boundary_add_fields');

        // Hidden fields.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'sid', 0);
        $mform->setType('sid', PARAM_INT);

        // Buttons.
        if ($currentsection < $feedbacksections) {
            $currentsection ++;
            $sectionsnav = ' ('.$currentsection.'/'.$feedbacksections.')';
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton',
                            get_string('feedbacknextsection', 'sliclquestions', $sectionsnav));
        } else {
            $buttonarray[] = &$mform->createElement('submit', 'savesettings', get_string('savesettings', 'sliclquestions'));
        }

        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function data_preprocessing(&$toform) {
        if (count($this->_feedbacks)) {
            $key = 0;
            foreach ($this->_feedbacks as $feedback) {
                $draftid = file_get_submitted_draft_itemid('feedbacktext['.$key.']');
                $toform['feedbacktext['.$key.']']['text'] = file_prepare_draft_area(
                        $draftid,               // Draftid.
                        $this->context->id,     // Context.
                        'mod_sliclquestions',    // Component.
                        'feedback',             // Filarea.
                        !empty($feedback->id) ? (int) $feedback->id : null, // Itemid.
                        null,
                        $feedback->feedbacktext // Text.
                );
                $toform['feedbacktext['.$key.']']['format'] = 1;
                $toform['feedbacklabel['.$key.']'] = $feedback->feedbacklabel;
                $toform['feedbacktext['.$key.']']['itemid'] = $draftid;

                if ($feedback->minscore > 0) {
                    $toform['feedbackboundaries['.$key.']'] = (100.0 * $feedback->minscore / 100 ) . '%';
                }
                $key++;
            }
        }
    }
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Check the boundary value is a number or a percentage, and in range.
        $i = 0;
        while (!empty($data['feedbackboundaries'][$i] )) {

            $boundary = trim($data['feedbackboundaries'][$i]);
            if (strlen($boundary) > 0 && $boundary[strlen($boundary) - 1] == '%') {
                $boundary = trim(substr($boundary, 0, -1));
                if (is_numeric($boundary)) {
                    $boundary = $boundary * 100 / 100.0;
                } else {
                    $errors["feedbackboundaries[$i]"] =
                    get_string('feedbackerrorboundaryformat', 'quiz', $i + 1);
                }
            }
            if (is_numeric($boundary) && $boundary <= 0) {
                $errors["feedbackboundaries[$i]"] =
                get_string('feedbackerrorboundaryoutofrange', 'sliclquestions', $i + 1);
            }
            if (is_numeric($boundary) && $i > 0 &&
                            $boundary >= $data['feedbackboundaries'][$i - 1]) {
                $errors["feedbackboundaries[$i]"] =
                get_string('feedbackerrororder', 'sliclquestions', $i + 1);
            }
            $data['feedbackboundaries'][$i] = $boundary;
            $i += 1;
        }
        $numboundaries = $i;

        // Check there is nothing in the remaining unused fields.
        if (!empty($data['feedbackboundaries'])) {
            for ($i = $numboundaries; $i < count($data['feedbackboundaries']); $i += 1) {
                if (!empty($data['feedbackboundaries'][$i] ) &&
                                trim($data['feedbackboundaries'][$i] ) != '') {
                    $errors["feedbackboundaries[$i]"] =
                    get_string('feedbackerrorjunkinboundary', 'sliclquestions', $i + 1);
                }
            }
        }
        for ($i = $numboundaries + 1; $i < count($data['feedbacktext']); $i += 1) {
            if (!empty($data['feedbacktext'][$i]['text']) &&
                            trim($data['feedbacktext'][$i]['text'] ) != '') {
                    $errors["feedbacktext[$i]"] =
                        get_string('feedbackerrorjunkinfeedback', 'sliclquestions', $i + 1);
            }
        }
        return $errors;
    }

    /**
     * Load in existing data as form defaults. Usually new entry defaults are stored directly in
     * form definition (new entry form); this function is used to load in data where values
     * already exist and data is being edited (edit entry form).
     *
     * @param mixed $default_values object or array of default values
     */
    public function set_data($defaultvalues) {
        if (is_object($defaultvalues)) {
            $defaultvalues = (array)$defaultvalues;
        }
        $this->data_preprocessing($defaultvalues);
        parent::set_data($defaultvalues);
    }
}