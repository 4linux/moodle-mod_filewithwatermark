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
 * Filewithwatermark events test cases.
 *
 * @package    mod_filewithwatermark
 * @copyright  2014 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
* Filewithwatermark events test cases.
*
* @package    mod_filewithwatermark
* @copyright  2014 Rajesh Taneja <rajesh@moodle.com>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*
*/
class mod_filewithwatermark_lib_testcase extends advanced_testcase {

    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/filewithwatermark/locallib.php');
        require_once($CFG->dirroot . '/mod/filewithwatermark/lib.php');
    }

    /**
     * Test resource_view
     * @return void
     */
    public function test_resource_view() {
        global $CFG;

        $CFG->enablecompletion = 1;
        $this->resetAfterTest();

        $this->setAdminUser();
        // Setup test data.
        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        $filewithwatermark = $this->getDataGenerator()->create_module('filewithwatermark', array('course' => $course->id),
            array('completion' => 2, 'completionview' => 1));
        $context = context_module::instance($filewithwatermark->cmid);
        $cm = get_coursemodule_from_instance('filewithwatermark', $filewithwatermark->id);

        // Trigger and capture the event.
        $sink = $this->redirectEvents();

        filewithwatermark_view($filewithwatermark, $course, $cm, $context);

        $events = $sink->get_events();

        // 2 additional events thanks to completion.
        $this->assertCount(3, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        $this->assertInstanceOf('\mod_filewithwatermark\event\course_module_viewed', $event);
        $this->assertEquals($context, $event->get_context());
        $moodleurl = new \moodle_url('/mod/filewithwatermark/view.php', array('id' => $cm->id));
        $this->assertEquals($moodleurl, $event->get_url());
        $this->assertEventContextNotUsed($event);
        $this->assertNotEmpty($event->get_name());

        // Check completion status.
        $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm);
        $this->assertEquals(1, $completiondata->completionstate);

    }

    /**
     * Test add watermark to a file
     *
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     */
    public function test_add_watermark() {
        global $DB, $SITE, $USER;

        $this->resetAfterTest(true);

        // Must be a non-guest user to create resources.
        $this->setAdminUser();

        // There are 0 resources initially.
        $this->assertEquals(0, $DB->count_records('filewithwatermark'));

        // Create the generator object and do standard checks.
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_filewithwatermark');

        $this->assertInstanceOf('mod_filewithwatermark_generator', $generator);

        // Create three instances in the site course.
        $filewithwatermark = $generator->create_instance(array('course' => $SITE->id));

        $context = context_module::instance($filewithwatermark->cmid);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_filewithwatermark', 'content', 0, 'sortorder DESC, id ASC', false);

        $this->assertEquals(1, count($files));

        $file = array_shift($files);

        $userdata = filewithwatermark_get_file_userdata($file);

        $this->assertEquals($userdata['email'], $USER->email);
        $this->assertEquals($userdata['name'], "{$USER->firstname} {$USER->lastname}");

    }

}