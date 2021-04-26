<?php
/**
 * The mod_filewithwatermark instance list viewed event.
 *
 * @package    mod_file_with_watermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_filewithwatermark\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_filewithwatermark instance list viewed event class.
 *
 * @package    mod_filewithwatermark
 * @since      Moodle 2.7
 * @copyright  2014 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_instance_list_viewed extends \core\event\course_module_instance_list_viewed {
    // No need for any code here as everything is handled by the parent class.
}
