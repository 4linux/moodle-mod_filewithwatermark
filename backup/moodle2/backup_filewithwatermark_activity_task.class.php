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
 * Defines backup_filewithwatermark_activity_task class
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @category backup
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/filewithwatermark/backup/moodle2/backup_filewithwatermark_stepslib.php');

/**
 * Provides the steps to perform one complete backup of the filewithwatermark instance
 */
class backup_filewithwatermark_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the filewithwatermark.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_filewithwatermark_activity_structure_step('filewithwatermark_structure', 'filewithwatermark.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of filewithwatermarks.
        $search="/(".$base."\/mod\/filewithwatermark\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@FILEWITHWATERMARKINDEX*$2@$', $content);

        // Link to filewithwatermark view by moduleid.
        $search = "/(".$base."\/mod\/filewithwatermark\/view.php\?id\=)([0-9]+)/";

        // for their new module.
        $content = preg_replace($search, '$@FILEWITHWATERMARKVIEWBYID*$2@$', $content);

        return $content;
    }
}
