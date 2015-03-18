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
 * Filename : settings_form
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 18 Mar 2015
 */

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class sliclquestions_settings_form extends moodleform {

    public function definition() {
        global $sliclquestions, $sliclquestionsrealms, $CFG;

        $mform    =& $this->_form;

        $mform->addElement('header', 'contenthdr', get_string('contentoptions', 'sliclquestions'));

        $capabilities = sliclquestions_load_capabilities($sliclquestions->cm->id);
        if (!$capabilities->createtemplates) {
            unset($sliclquestionsrealms['template']);
        }
        if (!$capabilities->createpublic) {
            unset($sliclquestionsrealms['public']);
        }
        if (isset($sliclquestionsrealms['public']) || isset($sliclquestionsrealms['template'])) {
            $mform->addElement('select', 'realm', get_string('realm', 'sliclquestions'), $sliclquestionsrealms);
            $mform->setDefault('realm', $sliclquestions->survey->realm);
            $mform->addHelpButton('realm', 'realm', 'sliclquestions');
        } else {
            $mform->addElement('hidden', 'realm', 'private');
        }
        $mform->setType('realm', PARAM_RAW);

        $mform->addElement('text', 'title', get_string('title', 'sliclquestions'), array('size' => '60'));
        $mform->setDefault('title', $sliclquestions->survey->title);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addHelpButton('title', 'title', 'sliclquestions');

        $mform->addElement('text', 'subtitle', get_string('subtitle', 'sliclquestions'), array('size' => '60'));
        $mform->setDefault('subtitle', $sliclquestions->survey->subtitle);
        $mform->setType('subtitle', PARAM_TEXT);
        $mform->addHelpButton('subtitle', 'subtitle', 'sliclquestions');

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext' => true);
        $mform->addElement('editor', 'info', get_string('additionalinfo', 'sliclquestions'), null, $editoroptions);
        $mform->setDefault('info', $sliclquestions->survey->info);
        $mform->setType('info', PARAM_RAW);
        $mform->addHelpButton('info', 'additionalinfo', 'sliclquestions');

        $mform->addElement('header', 'submithdr', get_string('submitoptions', 'sliclquestions'));

        $mform->addElement('text', 'thanks_page', get_string('url', 'sliclquestions'), array('size' => '60'));
        $mform->setType('thanks_page', PARAM_TEXT);
        $mform->setDefault('thanks_page', $sliclquestions->survey->thanks_page);
        $mform->addHelpButton('thanks_page', 'url', 'sliclquestions');

        $mform->addElement('static', 'confmes', get_string('confalts', 'sliclquestions'));
        $mform->addHelpButton('confmes', 'confpage', 'sliclquestions');

        $mform->addElement('text', 'thank_head', get_string('headingtext', 'sliclquestions'), array('size' => '30'));
        $mform->setType('thank_head', PARAM_TEXT);
        $mform->setDefault('thank_head', $sliclquestions->survey->thank_head);

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext' => true);
        $mform->addElement('editor', 'thank_body', get_string('bodytext', 'sliclquestions'), null, $editoroptions);
        $mform->setType('thank_body', PARAM_RAW);
        $mform->setDefault('thank_body', $sliclquestions->survey->thank_body);

        $mform->addElement('text', 'email', get_string('email', 'sliclquestions'), array('size' => '75'));
        $mform->setType('email', PARAM_TEXT);
        $mform->setDefault('email', $sliclquestions->survey->email);
        $mform->addHelpButton('email', 'sendemail', 'sliclquestions');

        // TODO $maxsections should be a site option of sliclquestions.
        $defaultsections = 10;
        // We cannot have more sections than available (required) questions with a choice value.
        $nbquestions = 0;
        foreach ($sliclquestions->questions as $question) {
            $qtype = $question->type_id;
            $qname = $question->name;
            $required = $question->required;
            // Question types accepted for feedback; QUESRATE ok except noduplicates.
            if (($qtype == QUESRADIO || $qtype == QUESDROP || ($qtype == QUESRATE && $question->precise != 2))
                            && $required == 'y' && $qname != '') {
                foreach ($question->choices as $choice) {
                    if (isset($choice->value) && $choice->value != null && $choice->value != 'NULL') {
                        $nbquestions ++;
                        break;
                    }
                }
            }
            if ($qtype == QUESYESNO && $required == 'y' && $qname != '') {
                $nbquestions ++;
            }
        }

        // Questionnaire Feedback Sections and Messages.
        if ($nbquestions != 0) {
            $maxsections = min ($nbquestions, $defaultsections);
            $feedbackoptions = array();
            $feedbackoptions[0] = get_string('feedbacknone', 'sliclquestions');
            $mform->addElement('header', 'submithdr', get_string('feedbackoptions', 'sliclquestions'));
            $feedbackoptions[1] = get_string('feedbackglobal', 'sliclquestions');
            for ($i = 2; $i <= $maxsections; ++$i) {
                $feedbackoptions[$i] = get_string('feedbacksections', 'sliclquestions', $i);
            }
            $mform->addElement('select', 'feedbacksections', get_string('feedbackoptions', 'sliclquestions'), $feedbackoptions);
            $mform->setDefault('feedbacksections', $sliclquestions->survey->feedbacksections);
            $mform->addHelpButton('feedbacksections', 'feedbackoptions', 'sliclquestions');

            $options = array('0' => get_string('no'), '1' => get_string('yes'));
            $mform->addElement('select', 'feedbackscores', get_string('feedbackscores', 'sliclquestions'), $options);
            $mform->addHelpButton('feedbackscores', 'feedbackscores', 'sliclquestions');

            // Is the RGraph library enabled at level site?
            if ($CFG->sliclquestions_usergraph) {
                $chartgroup = array();
                $charttypes = array (null => get_string('none'),
                        'bipolar' => get_string('chart:bipolar', 'sliclquestions'),
                        'vprogress' => get_string('chart:vprogress', 'sliclquestions'));
                $chartgroup[] = $mform->createElement('select', 'chart_type_global',
                        get_string('chart:type', 'sliclquestions').' ('.
                                get_string('feedbackglobal', 'sliclquestions').')', $charttypes);
                if ($sliclquestions->survey->feedbacksections == 1) {
                    $mform->setDefault('chart_type_global', $sliclquestions->survey->chart_type);
                }
                $mform->disabledIf('chart_type_global', 'feedbacksections', 'eq', 0);
                $mform->disabledIf('chart_type_global', 'feedbacksections', 'neq', 1);

                $charttypes = array (null => get_string('none'),
                        'bipolar' => get_string('chart:bipolar', 'sliclquestions'),
                        'hbar' => get_string('chart:hbar', 'sliclquestions'),
                        'rose' => get_string('chart:rose', 'sliclquestions'));
                $chartgroup[] = $mform->createElement('select', 'chart_type_two_sections',
                        get_string('chart:type', 'sliclquestions').' ('.
                                get_string('feedbackbysection', 'sliclquestions').')', $charttypes);
                if ($sliclquestions->survey->feedbacksections > 1) {
                    $mform->setDefault('chart_type_two_sections', $sliclquestions->survey->chart_type);
                }
                $mform->disabledIf('chart_type_two_sections', 'feedbacksections', 'neq', 2);

                $charttypes = array (null => get_string('none'),
                        'bipolar' => get_string('chart:bipolar', 'sliclquestions'),
                        'hbar' => get_string('chart:hbar', 'sliclquestions'),
                        'radar' => get_string('chart:radar', 'sliclquestions'),
                        'rose' => get_string('chart:rose', 'sliclquestions'));
                $chartgroup[] = $mform->createElement('select', 'chart_type_sections',
                        get_string('chart:type', 'sliclquestions').' ('.
                                get_string('feedbackbysection', 'sliclquestions').')', $charttypes);
                if ($sliclquestions->survey->feedbacksections > 1) {
                    $mform->setDefault('chart_type_sections', $sliclquestions->survey->chart_type);
                }
                $mform->disabledIf('chart_type_sections', 'feedbacksections', 'eq', 0);
                $mform->disabledIf('chart_type_sections', 'feedbacksections', 'eq', 1);
                $mform->disabledIf('chart_type_sections', 'feedbacksections', 'eq', 2);

                $mform->addGroup($chartgroup, 'chartgroup',
                        get_string('chart:type', 'sliclquestions'), null, false);
                $mform->addHelpButton('chartgroup', 'chart:type', 'sliclquestions');
            }
            $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext' => true);
            $mform->addElement('editor', 'feedbacknotes', get_string('feedbacknotes', 'sliclquestions'), null, $editoroptions);
            $mform->setType('feedbacknotes', PARAM_RAW);
            $mform->setDefault('feedbacknotes', $sliclquestions->survey->feedbacknotes);
            $mform->addHelpButton('feedbacknotes', 'feedbacknotes', 'sliclquestions');

            $mform->addElement('submit', 'feedbackeditbutton', get_string('feedbackeditsections', 'sliclquestions'));
            $mform->disabledIf('feedbackeditbutton', 'feedbacksections', 'eq', 0);
        }

        // Hidden fields.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'sid', 0);
        $mform->setType('sid', PARAM_INT);
        $mform->addElement('hidden', 'name', '');
        $mform->setType('name', PARAM_TEXT);
        $mform->addElement('hidden', 'owner', '');
        $mform->setType('owner', PARAM_RAW);

        // Buttons.

        $submitlabel = get_string('savechangesanddisplay');
        $submit2label = get_string('savechangesandreturntocourse');
        $mform = $this->_form;

        // Elements in a row need a group.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', $submit2label);
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}