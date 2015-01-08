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
 * Filename : install
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 06 Jan 2015
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_sliclquestions_install()
{
    global $DB;

    // Initial creation of question type data

    $questtype                = new stdClass();
    $questtype->typeid        = 1;
    $questtype->type          = 'Yes/No';
    $questtype->haschoices    = 'n';
    $questtype->responsetable = 'resp_bool';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 2;
    $questtype->type          = 'Text Box';
    $questtype->haschoices    = 'n';
    $questtype->responsetable = 'resp_text';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 3;
    $questtype->type          = 'Essay Box';
    $questtype->haschoices    = 'n';
    $questtype->responsetable = 'resp_text';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 4;
    $questtype->type          = 'Radio Buttons';
    $questtype->haschoices    = 'y';
    $questtype->responsetable = 'resp_single';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 5;
    $questtype->type          = 'Check Boxes';
    $questtype->haschoices    = 'y';
    $questtype->responsetable = 'resp_multiple';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 6;
    $questtype->type          = 'Dropdown Box';
    $questtype->haschoices    = 'y';
    $questtype->responsetable = 'resp_single';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 8;
    $questtype->type          = 'Rate (scale 1..5)';
    $questtype->haschoices    = 'y';
    $questtype->responsetable = 'resp_rank';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 9;
    $questtype->type          = 'Date';
    $questtype->haschoices    = 'n';
    $questtype->responsetable = 'resp_date';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 10;
    $questtype->type          = 'Numeric';
    $questtype->haschoices    = 'n';
    $questtype->responsetable = 'resp_text';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 99;
    $questtype->type          = 'Page Break';
    $questtype->haschoices    = 'n';
    $questtype->responsetable = '';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);

    $questtype                = new stdClass();
    $questtype->typeid        = 100;
    $questtype->type          = 'Section Text';
    $questtype->haschoices    = 'n';
    $questtype->responsetable = '';
    $id = $DB->insert_record('sliclquestions_quest_type', $questtype);
}
