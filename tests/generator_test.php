<?php

/**
 * PHPUnit data generator tests.
 *
 * @package mod_filewithwatermark
 * @category phpunit
 * @copyright 2013 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_filewithwatermark_generator_testcase extends advanced_testcase {
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
