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
 * Filename : restore_sliclquestions_stepslib
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 16 Mar 2015
 */

/**
 * Define all the restore steps that will be used by the restore_questionnaire_activity_task
 */

/**
 * Structure step to restore one questionnaire activity
 */
class restore_sliclquestions_activity_structure_step extends restore_activity_structure_step
{
    protected function define_structure()
    {
        $paths = array(new restore_path_element('sliclquestions',
                                                '/activity/sliclquestions'),
                       new restore_path_element('sliclquestions_survey',
                                                '/activity/sliclquestions/surveys/survey'),
                       new restore_path_element('sliclquestions_fb_sections',
                                                '/activity/sliclquestions/surveys/survey/fb_sections/fb_section'),
                       new restore_path_element('sliclquestions_feedback',
                                                '/activity/sliclquestions/surveys/survey/fb_sections/fb_section/feedbacks/feedback'),
                       new restore_path_element('sliclquestions_question',
                                                '/activity/sliclquestions/surveys/survey/questions/question'),
                       new restore_path_element('sliclquestions_quest_choice',
                                                '/activity/sliclquestions/surveys/survey/questions/question/quest_choices/quest_choice'));
        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo) {
            $paths[] = new restore_path_element('sliclquestions_attempt',
                                                '/activity/sliclquestions/attempts/attempt');
            $paths[] = new restore_path_element('sliclquestions_response',
                                                '/activity/sliclquestions/attempts/attempt/responses/response');
            $paths[] = new restore_path_element('sliclquestions_response_bool',
                                                '/activity/sliclquestions/attempts/attempt/responses/response/response_bools/response_bool');
            $paths[] = new restore_path_element('sliclquestions_response_date',
                                                '/activity/sliclquestions/attempts/attempt/responses/response/response_dates/response_date');
            $paths[] = new restore_path_element('sliclquestions_response_multiple',
                                                '/activity/sliclquestions/attempts/attempt/responses/response/response_multiples/response_multiple');
            $paths[] = new restore_path_element('sliclquestions_response_other',
                                                '/activity/sliclquestions/attempts/attempt/responses/response/response_others/response_other');
            $paths[] = new restore_path_element('sliclquestions_response_rank',
                                                '/activity/sliclquestion/attempts/attempt/responses/response/response_ranks/response_rank');
            $paths[] = new restore_path_element('sliclquestions_response_single',
                                                '/activity/sliclquestion/attempts/attempt/responses/response/response_singles/response_single');
            $paths[] = new restore_path_element('sliclquestions_response_text',
                                                '/activity/sliclquestions/attempts/attempt/responses/response/response_texts/response_text');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_sliclquestions($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the questionnaire record.
        $newitemid = $DB->insert_record('sliclquestions', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_sliclquestions_survey($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->owner = $this->get_courseid();

        // Insert the questionnaire_survey record.
        $newitemid = $DB->insert_record('sliclquestions_survey', $data);
        $this->set_mapping('sliclquestions_survey', $oldid, $newitemid, true);

        // Update the questionnaire record we just created with the new survey id.
        $DB->set_field('sliclquestions', 'sid', $newitemid, array('id' => $this->get_new_parentid('sliclquestions')));
    }

    protected function process_sliclquestions_question($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->survey_id = $this->get_new_parentid('sliclquestions_survey');

        if (isset($data->dependquestion)) {
            // Dependquestion.
            $data->dependquestion = $this->get_mappingid('sliclquestions_question', $data->dependquestion);

            // Dependchoice.
            // Only change mapping for RADIO and DROP question types, not for YESNO question.
            $dependquestion = $DB->get_record('sliclquestions_question', array('id' => $data->dependquestion), $fields = 'type_id');
            if (is_object($dependquestion)) {
                if ($dependquestion->type_id != 1) {
                    $data->dependchoice = $this->get_mappingid('sliclquestions_quest_choice', $data->dependchoice);
                }
            }
        }
        // Insert the questionnaire_question record.
        $newitemid = $DB->insert_record('sliclquestions_question', $data);
        $this->set_mapping('sliclquestions_question', $oldid, $newitemid, true);
    }

    protected function process_sliclquestions_fb_sections($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->survey_id = $this->get_new_parentid('sliclquestions_survey');

        // If this questionnaire has separate sections feedbacks.
        if (isset($data->scorecalculation)) {
            $scorecalculation = unserialize($data->scorecalculation);
            $newscorecalculation = array();
            foreach ($scorecalculation as $key => $qid) {
                $newqid = $this->get_mappingid('sliclquestions_question', $key);
                $newscorecalculation[$newqid] = null;
            }
            $data->scorecalculation = serialize($newscorecalculation);
        }
        // Insert the questionnaire_fb_sections record.
        $newitemid = $DB->insert_record('sliclquestions_fb_sections', $data);
        $this->set_mapping('sliclquestions_fb_sections', $oldid, $newitemid, true);
    }

    protected function process_sliclquestions_feedback($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->section_id = $this->get_new_parentid('sliclquestions_fb_sections');

        // Insert the questionnaire_feedback record.
        $newitemid = $DB->insert_record('sliclquestions_feedback', $data);
        $this->set_mapping('sliclquestions_feedback', $oldid, $newitemid, true);
    }

    protected function process_sliclquestions_quest_choice($data)
    {
        global $CFG, $DB;

        $data = (object)$data;

        // Replace the = separator with :: separator in quest_choice content. This fixes radio button options using old "value"="display" formats.
        require_once($CFG->dirroot.'/mod/sliclquestions/locallib.php');
        if (($data->value == null || $data->value == 'NULL') && !preg_match("/^([0-9]{1,3}=.*|!other=.*)$/", $data->content)) {
            $content = sliclquestions§_choice_values($data->content);
            if ($pos = strpos($content->text, '=')) {
                $data->content = str_replace('=', '::', $content->text);
            }
        }
        $oldid = $data->id;
        $data->question_id = $this->get_new_parentid('sliclquestions_question');
        if (isset($data->dependquestion)) {
            // Dependquestion.
            $data->dependquestion = $this->get_mappingid('sliclquestions_question', $data->dependquestion);

            // Dependchoice.
            // Only change mapping for RADIO and DROP question types, not for YESNO question.
            $dependquestion = $DB->get_record('sliclquestions_question',
                            array('id' => $data->dependquestion), $fields = 'type_id');
            if (is_object($dependquestion)) {
                if ($dependquestion->type_id != 1) {
                    $data->dependchoice = $this->get_mappingid('sliclquestions_quest_choice', $data->dependchoice);
                }
            }
        }
        // Insert the questionnaire_quest_choice record.
        $newitemid = $DB->insert_record('sliclquestions_quest_choice', $data);
        $this->set_mapping('sliclquestions_quest_choice', $oldid, $newitemid);
    }

    protected function process_sliclquestions_attempt($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->qid = $this->get_new_parentid('sliclquestions');
        $data->userid = $this->get_mappingid('user', $data->userid);

        // Insert the questionnaire_attempts record.
        $newitemid = $DB->insert_record('sliclquestions_attempts', $data);
        $this->set_mapping('sliclquestions_attempt', $oldid, $newitemid);
    }

    protected function process_sliclquestions_response($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->survey_id = $this->get_mappingid('sliclquestions_survey', $data->survey_id);
        $data->username = $this->get_mappingid('user', $data->username);

        // Insert the questionnaire_response record.
        $newitemid = $DB->insert_record('sliclquestions_response', $data);
        $this->set_mapping('sliclquestions_response', $oldid, $newitemid);

        // Update the questionnaire_attempts record we just created with the new response id.
        $DB->set_field('sliclquestions_attempts', 'rid', $newitemid,
                        array('id' => $this->get_new_parentid('sliclquestions_attempt')));
    }

    protected function process_sliclquestions_response_bool($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->response_id = $this->get_new_parentid('sliclquestions_response');
        $data->question_id = $this->get_mappingid('sliclquestions_question', $data->question_id);

        // Insert the questionnaire_response_bool record.
        $newitemid = $DB->insert_record('sliclquestions_response_bool', $data);
    }

    protected function process_sliclquestions_response_date($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->response_id = $this->get_new_parentid('sliclquestions_response');
        $data->question_id = $this->get_mappingid('sliclquestions_question', $data->question_id);

        // Insert the questionnaire_response_date record.
        $newitemid = $DB->insert_record('sliclquestions_response_date', $data);
    }

    protected function process_sliclquestions_response_multiple($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->response_id = $this->get_new_parentid('sliclquestions_response');
        $data->question_id = $this->get_mappingid('sliclquestions_question', $data->question_id);
        $data->choice_id = $this->get_mappingid('sliclquestions_quest_choice', $data->choice_id);

        // Insert the questionnaire_resp_multiple record.
        $newitemid = $DB->insert_record('sliclquestions_resp_multiple', $data);
    }

    protected function process_sliclquestions_response_other($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->response_id = $this->get_new_parentid('sliclquestions_response');
        $data->question_id = $this->get_mappingid('sliclquestions_question', $data->question_id);
        $data->choice_id = $this->get_mappingid('sliclquestions_quest_choice', $data->choice_id);

        // Insert the questionnaire_response_other record.
        $newitemid = $DB->insert_record('sliclquestions_response_other', $data);
    }

    protected function process_sliclquestions_response_rank($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->response_id = $this->get_new_parentid('sliclquestions_response');
        $data->question_id = $this->get_mappingid('sliclquestions_question', $data->question_id);
        $data->choice_id = $this->get_mappingid('sliclquestions_quest_choice', $data->choice_id);

        // Insert the questionnaire_response_rank record.
        $newitemid = $DB->insert_record('sliclquestions_response_rank', $data);
    }

    protected function process_sliclquestions_response_single($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->response_id = $this->get_new_parentid('sliclquestions_response');
        $data->question_id = $this->get_mappingid('sliclquestions_question', $data->question_id);
        $data->choice_id = $this->get_mappingid('sliclquestions_quest_choice', $data->choice_id);

        // Insert the questionnaire_resp_single record.
        $newitemid = $DB->insert_record('sliclquestions_resp_single', $data);
    }

    protected function process_sliclquestions_response_text($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->response_id = $this->get_new_parentid('sliclquestions_response');
        $data->question_id = $this->get_mappingid('sliclquestions_question', $data->question_id);

        // Insert the questionnaire_response_text record.
        $newitemid = $DB->insert_record('sliclquestions_response_text', $data);
    }

    protected function after_execute()
    {
        // Add questionnaire related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_sliclquestions', 'intro', null);
        $this->add_related_files('mod_sliclquestions', 'info', 'sliclquestions_survey');
        $this->add_related_files('mod_sliclquestions', 'thankbody', 'sliclquestions_survey');
        $this->add_related_files('mod_sliclquestions', 'feedbacknotes', 'sliclquestions_survey');
        $this->add_related_files('mod_sliclquestions', 'question', 'sliclquestions_question');
        $this->add_related_files('mod_sliclquestions', 'sectionheading', 'sliclquestions_fb_sections');
        $this->add_related_files('mod_sliclquestions', 'feedback', 'sliclquestions_feedback');
    }
}
