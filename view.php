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
 * Activity view
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_once($CFG->dirroot.'/mod/filewithwatermark/lib.php');
require_once($CFG->dirroot.'/mod/filewithwatermark/locallib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot.'/mod/filewithwatermark/classes/pdfeditor.php');
require_once($CFG->dirroot.'/mod/filewithwatermark/classes/fileutil.php');


$id       = optional_param('id', 0, PARAM_INT); // Course Module ID
$r        = optional_param('r', 0, PARAM_INT);  // Filewithwatermark instance ID
$redirect = optional_param('redirect', 0, PARAM_BOOL);
$forceview = optional_param('forceview', 0, PARAM_BOOL);


if ($r) {
    if (!$filewithwatermark = $DB->get_record('filewithwatermark', array('id'=>$r))) {
        filewithwatermark_redirect_if_migrated($r, 0);
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('filewithwatermark', $filewithwatermark->id, $filewithwatermark->course, false, MUST_EXIST);

} else {
    if (!$cm = get_coursemodule_from_id('filewithwatermark', $id)) {
        filewithwatermark_redirect_if_migrated(0, $id);
        print_error('invalidcoursemodule');
    }
    $filewithwatermark = $DB->get_record('filewithwatermark', array('id'=>$cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/filewithwatermark:view', $context);

// Completion and trigger events.
filewithwatermark_view($filewithwatermark, $course, $cm, $context);

$PAGE->set_url('/mod/filewithwatermark/view.php', array('id' => $cm->id));

if ($filewithwatermark->tobemigrated) {
    filewithwatermark_print_tobemigrated($filewithwatermark, $cm, $course);
    die;
}

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_filewithwatermark', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!

if (count($files) < 1) {
    filewithwatermark_print_filenotfound($filewithwatermark, $cm, $course);
    die;
} else {
    $file = reset($files);
    unset($files);
}

$filewithwatermark->mainfile = $file->get_filename();
$displaytype = filewithwatermark_get_final_display_type($filewithwatermark);
if ($displaytype ==\mod_filewithwatermark\fileutil::$DISPLAY_OPEN || $displaytype ==\mod_filewithwatermark\fileutil::$DISPLAY_DOWNLOAD) {
    $redirect = true;
}

// Don't redirect teachers, otherwise they can not access course or module settings.
if ($redirect && !course_get_format($course)->has_view_page() &&
        (has_capability('moodle/course:manageactivities', $context) ||
        has_capability('moodle/course:update', context_course::instance($course->id)))) {
    $redirect = false;
}

if ($redirect && !$forceview) {
    // coming from course page or url index page
    // this redirect trick solves caching problems when tracking views ;-)
    $path = '/'.$context->id.'/mod_filewithwatermark/content/'.$filewithwatermark->revision.$file->get_filepath().$file->get_filename();

    $fullurl = moodle_url::make_file_url('/pluginfile.php', $path, $displaytype ==\mod_filewithwatermark\fileutil::$DISPLAY_DOWNLOAD);

    redirect($fullurl);
}

switch ($displaytype) {
    case\mod_filewithwatermark\fileutil::$DISPLAY_EMBED:
        filewithwatermark_display_embed($filewithwatermark, $cm, $course, $file);
        break;
    case\mod_filewithwatermark\fileutil::$DISPLAY_FRAME:
        filewithwatermark_display_frame($filewithwatermark, $cm, $course, $file);
        break;
    default:
        filewithwatermark_print_workaround($filewithwatermark, $cm, $course, $file);
        break;
}

