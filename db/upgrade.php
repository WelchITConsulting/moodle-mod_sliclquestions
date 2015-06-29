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
 * Filename : upgrade
 * Author   : John Welch <jwelch@welchitconsulting.co.uk>
 * Created  : 06 Jan 2015
 */

function xmldb_sliclquestions_upgrade($oldversion=0)
{
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015062500) {

        $table = new xmldb_table('sliclquestions_response');

        $field = new xmldb_field('userid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                               XMLDB_NOTNULL, null, 0, 'survey_id');
        $dbman->add_field($table, $field);

        unset($field);
        $field = new xmldb_field('grade');
        $dbman->drop_field($table, $field);

        unset($field);
        $field = new xmldb_field('username');
        $dbman->drop_field($table, $field);

        upgrade_mod_savepoint(true, '2015062500', 'sliclquestions');
    }

    if ($oldversion < 2015062503) {

        $table = new xmldb_table('sliclquestions_question');
        $field = new xmldb_field('result_id');
        $dbman->drop_field($table, $field);
        upgrade_mod_savepoint(true, 2015062503, 'sliclquestions');
    }

    if ($oldversion < 2015062900) {

        $table = new xmldb_table('sliclquestions_attempts');
        $dbman->drop_table($table);
        upgrade_mod_savepoint(true, 2015062900, 'sliclquestions');
    }

    return true;
}
