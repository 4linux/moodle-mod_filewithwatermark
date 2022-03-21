<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Define all the backup steps that will be used by the backup_filewithwatermark_activity_task
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @category backup
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete filewithwatermark structure for backup, with file and id annotations
 */
class backup_filewithwatermark_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        $userinfo = $this->get_setting_value('userinfo');

        $filewithwatermark = new backup_nested_element('filewithwatermark', array('id'), array(
            'name', 'intro', 'introformat',
            'legacyfiles', 'legacyfileslast', 'display',
            'displayoptions', 'filterfiles', 'revision', 'timemodified'));

        $filewithwatermark->set_source_table('filewithwatermark', array('id' => backup::VAR_ACTIVITYID));

        $filewithwatermark->annotate_files('mod_filewithwatermark', 'intro', null); // This file areas haven't itemid
        $filewithwatermark->annotate_files('mod_filewithwatermark', 'content', null); // This file areas haven't itemid

        return $this->prepare_activity_structure($filewithwatermark);
    }
}
