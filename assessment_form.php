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
 * Filename : assessment_form
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 05 Jul 2015
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/mod/sliclquestions/locallib.php');
require_once($CFG->dirroot . '/mod/sliclquestions/classfiles/sliclquestions.class.php');

class sliclquestions_assessment_form extends moodleform
{
    public function definition()
    {
        $mform =& $this->_form;

        $mform->addElement('html', html_writer::tag('h4', 'Pupil:'/* . $sliclstudent->name*/));
        $mform->addElement('html', html_writer::start_tag('fieldset', array('class' => 'slicl-container',
                                                                            'id'    => 'slicl-1')));
        $mform->addElement('html', html_writer::start_tag('legend', array('class' => 'slicl-legend')));
        $mform->addElement('html', html_writer::start_div('slicl-info'));
        $mform->addElement('html', html_writer::div('Question #', 'accesshide'));
        $mform->addElement('html', html_writer::tag('h2', '1', array('class' => 'slicl-number')));
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_tag('legend'));
        $mform->addElement('html', html_writer::start_div('slicl-content'));
        $mform->addElement('html', html_writer::start_div('slicl-question'));
        $mform->addElement('html', '<p>This pupils record indicates they are at the KPI level indicated below. If this has changed please alter this here but not in the original record.</p>');
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::start_div('slicl-answer'));

        $kpiarr = array();
        $kpiarr[] = &$mform->createElement('radio', 'kpi_level', '', '1', 1);
        $kpiarr[] = &$mform->createElement('radio', 'kpi_level', '', '2', 2);
        $kpiarr[] = &$mform->createElement('radio', 'kpi_level', '', '3', 3);
        $kpiarr[] = &$mform->createElement('radio', 'kpi_level', '', '4', 4);
        $mform->addGroup($kpiarr);

        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_tag('fieldset'));
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_tag('fieldset'));

        $mform->addElement('html', html_writer::tag('legend', get_string('kpi1', 'sliclquestions'), array('class' => 'kpi-1')));
        $mform->addElement('html', html_writer::tag('legend', get_string('kpi2', 'sliclquestions'), array('class' => 'kpi-2')));
        $mform->addElement('html', html_writer::tag('legend', get_string('kpi3', 'sliclquestions'), array('class' => 'kpi-3')));
        $mform->addElement('html', html_writer::tag('legend', get_string('kpi4', 'sliclquestions'), array('class' => 'kpi-4')));
        $mform->addElement('html', html_writer::start_tag('fieldset', array('class' => 'slicl-container',
                                                                            'id'    => 'slicl-2')));
        $mform->addElement('html', html_writer::start_tag('legend', array('class' => 'slicl-legend')));
        $mform->addElement('html', html_writer::start_div('slicl-info'));
        $mform->addElement('html', html_writer::div('Question #', 'accesshide'));
        $mform->addElement('html', html_writer::tag('h2', '2', array('class' => 'slicl-number')));
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_tag('legend'));
        $mform->addElement('html', html_writer::start_div('slicl-content'));
        $mform->addElement('html', html_writer::start_div('slicl-question'));
        $mform->addElement('html', '<p>Please provide the KPI measurements for this pupil in the following table:</p><p>1 - below ARE standard</p><p>2 - achieving ARE standard</p><p>3 - exceeding ARE standard</p>');
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::start_div('slicl-answer'));

        $html = '<span class="question"></span>'
              . '<span class="qn-elem-head">'
              . '<span class="qn-elem-head-3">1</span>&nbsp;'
              . '<span class="qn-elem-head-3">2</span>&nbsp;'
              . '<span class="qn-elem-head-3">3</span></span>';
        $mform->addElement('html', html_writer::div($html, 'slicl-row'));


        // KPI Level 1
        $mform->addElement('html', html_writer::start_div('kpi-1'));

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Begins to form lower-case letters in the correct direction, starting and finishing in the right place', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_1', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_1', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_1', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Writes sentences by: <ol><li>sequencing sentences to form short narratives; and</li><li>re-reading what has been written to check that it makes sense</li></ol>', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_2', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_2', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_2', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Spells words containing each of the 40+ phonemes already taught', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_3', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_3', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_3', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Names the letters of the alphabet in order', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_4', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_4', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_4', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Writes from memory simple sentences dictated by the teacher that include words using the GPCs and common exception words taught so far', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_5', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_5', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_5', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Introduces capital letters, full stops, question marks and exclamation marks to demarcate sentences', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_6', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_6', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi1_6', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        // End KPI Level 1 Elements
        $mform->addElement('html', html_writer::end_div());


        // KPI Level 2
        $mform->addElement('html', html_writer::start_div('kpi-2'));

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Writes capital letters and digits of the correct size, orientation and relationship to one another and to lower case letters', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_1', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_1', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_1', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Develops positive attitudes towards, and stamina for, writing, by writing for different purposes', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_2', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_2', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_2', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Considers what is going to be written before beginning by encapsulating what they want to say, sentence by sentence', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_3', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_3', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_3', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Makes simple additions, revisions and corrections to inform writing by: <ol><li>Proof-reading to check errors in spelling, grammar and punctuation,</li><li>segmenting spoken words into phonemes and representing these by graphemes, spelling any correctly,</li><li>learning new ways of spelling phonemes for which one or more spellings are already known: and learn some words with each spelling including a few common homophones</li></ol>', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_4', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_4', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_4', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses the suffixes \'er\', \'est\' in adjectives and \'ly\' to turn adjectives into adverbs', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_5', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_5', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_5', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Constructs subordination (using when, if, that, because) and co-ordination (using or, and, but)', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_6', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_6', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_6', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses the correct choice and consistent use of present tense and past tense throughout a written piece', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_7', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_7', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_7', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses capital letters, full stops, question marks and exclamation marks to demarcate sentences', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_8', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_8', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_8', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses commas to separate items in a list', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_9', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_9', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi2_9', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        // End KPI Level 2 Elements
        $mform->addElement('html', html_writer::end_div());


        // KPI Level 3
        $mform->addElement('html', html_writer::start_div('kpi-3'));

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Organises Paragraphs around a theme', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_1', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_1', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_1', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Proof-reads for spelling and punctuation errors', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_2', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_2', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_2', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses the forms \'a\' or \'an\' according to whether the next word begins with a consonant or vowel', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_3', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_3', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_3', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Expresses time, place and cause using conjunctions', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_4', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_4', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_4', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Introduces inverted comma to punctuate direct speech', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_5', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_5', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_5', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses headings and sub-headings to aid presentation', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_6', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_6', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_6', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses the present perfect form of verbs instead of the simple past eg \'He has gone out to play\'  in contrast to \'He went out to play\'', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_7', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_7', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi3_7', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        // End KPI Level 3 Elements
        $mform->addElement('html', html_writer::end_div());


        // KPI Level 4
        $mform->addElement('html', html_writer::start_div('kpi-4'));

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Organises paragraphs around a theme', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_1', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_1', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_1', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Proof-reads for spelling and punctuation errors', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_2', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_2', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_2', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses standard English forms of verb inflections instead of local spoken forms', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_3', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_3', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_3', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses fronted adverbials', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_4', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_4', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_4', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Can choose an appropriate pronoun or noun within and across sentences to aid cohesion and avoid repetition', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_5', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_5', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_5', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Uses inverted commas and other punctuation to indicate direct speech', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_6', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_6', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'kpi4_6', '', '', 3);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        // End KPI Level 4 Elements
        $mform->addElement('html', html_writer::end_div());


        // Close question wrapper
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_tag('fieldset'));


        // Personality data
        $mform->addElement('html', html_writer::start_tag('fieldset', array('class' => 'slicl-container',
                                                                            'id'    => 'slicl-3')));
        $mform->addElement('html', html_writer::start_tag('legend', array('class' => 'slicl-legend')));
        $mform->addElement('html', html_writer::start_div('slicl-info'));
        $mform->addElement('html', html_writer::div('Question #', 'accesshide'));
        $mform->addElement('html', html_writer::tag('h2', '3', array('class' => 'slicl-number')));
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_tag('legend'));
        $mform->addElement('html', html_writer::start_div('slicl-content'));
        $mform->addElement('html', html_writer::start_div('slicl-question'));
        $mform->addElement('html', '<p>Rate the pupils performance in the classroom in the following categories:</p><p>1 - Very poor, 2 - Poor, 3 - Neutral, 4 - Good, 5 - Very good</p>');
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::start_div('slicl-answer'));

        $mform->addElement('html', html_writer::start_div('personality'));

        $html = '<span class="question"></span>'
              . '<span class="qn-elem-head">'
              . '<span class="qn-elem-head-5">1</span>&nbsp;'
              . '<span class="qn-elem-head-5">2</span>&nbsp;'
              . '<span class="qn-elem-head-5">3</span>&nbsp;'
              . '<span class="qn-elem-head-5">4</span>&nbsp;'
              . '<span class="qn-elem-head-5">5</span></span>';
        $mform->addElement('html', html_writer::div($html, 'slicl-row'));

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Attendance', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'personality_1', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'personality_1', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'personality_1', '', '', 3);
        $kpiarr[] =& $mform->createElement('radio', 'personality_1', '', '', 4);
        $kpiarr[] =& $mform->createElement('radio', 'personality_1', '', '', 5);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Attitude and helpfullness', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'personality_2', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'personality_2', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'personality_2', '', '', 3);
        $kpiarr[] =& $mform->createElement('radio', 'personality_2', '', '', 4);
        $kpiarr[] =& $mform->createElement('radio', 'personality_2', '', '', 5);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Behaviour', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'personality_3', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'personality_3', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'personality_3', '', '', 3);
        $kpiarr[] =& $mform->createElement('radio', 'personality_3', '', '', 4);
        $kpiarr[] =& $mform->createElement('radio', 'personality_3', '', '', 5);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Engagement', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'personality_4', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'personality_4', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'personality_4', '', '', 3);
        $kpiarr[] =& $mform->createElement('radio', 'personality_4', '', '', 4);
        $kpiarr[] =& $mform->createElement('radio', 'personality_4', '', '', 5);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', html_writer::start_div('slicl-row'));
        $mform->addElement('html', html_writer::tag('span', 'Aspiration', array('class' => 'question')));
        $kpiarr = array();
        $kpiarr[] =& $mform->createElement('radio', 'personality_5', '', '', 1);
        $kpiarr[] =& $mform->createElement('radio', 'personality_5', '', '', 2);
        $kpiarr[] =& $mform->createElement('radio', 'personality_5', '', '', 3);
        $kpiarr[] =& $mform->createElement('radio', 'personality_5', '', '', 4);
        $kpiarr[] =& $mform->createElement('radio', 'personality_5', '', '', 5);
        $mform->addGroup($kpiarr);
        $mform->addElement('html', html_writer::end_div());

        // End Personality Elements
        $mform->addElement('html', html_writer::end_div());


        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_tag('fieldset'));


        // Additional comments box
        $mform->addElement('html', html_writer::start_tag('fieldset', array('class' => 'slicl-container',
                                                                            'id'    => 'slicl-4')));
        $mform->addElement('html', html_writer::start_tag('legend', array('class' => 'slicl-legend')));
        $mform->addElement('html', html_writer::start_div('slicl-info'));
        $mform->addElement('html', html_writer::div('Question #', 'accesshide'));
        $mform->addElement('html', html_writer::tag('h2', '4', array('class' => 'slicl-number')));
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_tag('legend'));
        $mform->addElement('html', html_writer::start_div('slicl-content'));
        $mform->addElement('html', html_writer::start_div('slicl-question'));
        $mform->addElement('html', '<p>Any other comments relevant to this child</p>');
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::start_div('slicl-answer'));
        $mform->addElement('textarea', 'q4', '', 'rows="5" cols="80"');
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_div());
        $mform->addElement('html', html_writer::end_tag('fieldset'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'pid');
        $mform->setType('pid', PARAM_INT);
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHA);

        $this->add_action_buttons();
    }
}
