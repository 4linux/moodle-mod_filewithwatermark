<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * PHPUnit data generator tests.
 *
 * @package mod_filewithwatermark
 * @category phpunit
 * @copyright 2013 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * PHPUnit data generator tests.
 *
 * @package mod_filewithwatermark
 * @category phpunit
 * @copyright 2013 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_filewithwatermark_generator_testcase extends advanced_testcase {

    /**
     * Test create a new mock instance
     */
    public function test_generator() {
        global $DB, $SITE;

        $this->resetAfterTest(true);

        // Must be a non-guest user to create resources.
        $this->setAdminUser();

        // There are 0 resources initially.
        $this->assertEquals(0, $DB->count_records('filewithwatermark'));

        // Create the generator object and do standard checks.
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_filewithwatermark');

        $this->assertInstanceOf('mod_filewithwatermark_generator', $generator);

        // Create three instances in the site course.
        $generator->create_instance(array('course' => $SITE->id));
        $generator->create_instance(array('course' => $SITE->id));
        $filewithwatermark = $generator->create_instance(array('course' => $SITE->id));

        $this->assertEquals(3, $DB->count_records('filewithwatermark'));

        // Check the course-module is correct.
        $cm = get_coursemodule_from_instance('filewithwatermark', $filewithwatermark->id);
        $this->assertEquals($filewithwatermark->id, $cm->instance);
        $this->assertEquals('filewithwatermark', $cm->modname);
        $this->assertEquals($SITE->id, $cm->course);

        // Check the context is correct.
        $context = context_module::instance($cm->id);
        $this->assertEquals($filewithwatermark->cmid, $context->instanceid);

        // Check that generated resource module contains a file.
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_filewithwatermark', 'content', false, '', false);
        $this->assertEquals(1, count($files));
    }
}
