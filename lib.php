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
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/filewithwatermark/vendor/autoload.php');
require_once($CFG->dirroot.'/mod/filewithwatermark/classes/pdfeditor.php');
require_once($CFG->dirroot.'/mod/filewithwatermark/classes/fileutil.php');

/**
 * List of features supported in Filewithwatermark module
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function filewithwatermark_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Add new instance
 *
 * @param $data
 * @param Object $filewithwatermark file data
 * @return int filewithwatermark id
 */
function filewithwatermark_add_instance($data, $filewithwatermark) {
    global $CFG, $DB;

    require_once("$CFG->dirroot/mod/filewithwatermark/locallib.php");

    $cmid = $data->coursemodule;
    $data->timemodified = time();

    filewithwatermark_set_display_options($data);

    $data->id = $DB->insert_record('filewithwatermark', $data);
    $DB->set_field('course_modules', 'instance', $data->id, array('id' => $cmid));
    filewithwatermark_set_mainfile($data);

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'filewithwatermark', $data->id, $completiontimeexpected);

    return $data->id;

}

/**
 * Update a instance
 *
 * @param Object $filewithwatermark filewithwatermark data
 * @return int filewithwatermark id
 */
function filewithwatermark_update_instance($data) {
    global $CFG, $DB;

    require_once("$CFG->dirroot/mod/filewithwatermark/locallib.php");

    $data->timemodified = time();
    $data->id           = $data->instance;
    $data->revision++;

    filewithwatermark_set_display_options($data);

    $DB->update_record('filewithwatermark', $data);
    filewithwatermark_set_mainfile($data);

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'filewithwatermark', $data->id, $completiontimeexpected);

    return true;
}

/**
 * Delete a instance
 *
 * @param int $id filewithwatermark id
 * @return bool
 */
function filewithwatermark_delete_instance($id) {
    global $DB;

    if (!$filewithwatermark = $DB->get_record('filewithwatermark', array('id'=>$id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('filewithwatermark', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'filewithwatermark', $id, null);

    // note: all context files are deleted automatically

    $DB->delete_records('filewithwatermark', array('id'=>$filewithwatermark->id));

    return true;

}

/**
 * Serves the filewithwatermark files.
 *
 * @package  mod_filewithwatermark
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function filewithwatermark_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if (!has_capability('mod/filewithwatermark:view', $context)) {
        return false;
    }

    if ($filearea !== 'content') {
        return false;
    }

    array_shift($args); // ignore revision - designed to prevent caching problems only

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = rtrim("/$context->id/mod_filewithwatermark/$filearea/0/$relativepath", '/');

    do {
        if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
            if ($fs->get_file_by_hash(sha1("$fullpath/."))) {
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.htm"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.html"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/Default.htm"))) {
                    break;
                }
            }
            $filewithwatermark = $DB->get_record('filewithwatermark', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
            if ($filewithwatermark->legacyfiles != \mod_filewithwatermark\fileutil::$LEGACYFILES_ACTIVE) {
                return false;
            }
            if (!$file = \mod_filewithwatermark\fileutil::try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_filewithwatermark', 'content', 0)) {
                return false;
            }
            // file migrate - update flag
            $filewithwatermark->legacyfileslast = time();
            $DB->update_record('filewithwatermark', $filewithwatermark);
        }
    } while (false);

    filewithwatermark_add_watermark($file, $forcedownload);

}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $filewithwatermark   filewithwatermark object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function filewithwatermark_view($filewithwatermark, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $filewithwatermark->id
    );

    $event = \mod_filewithwatermark\event\course_module_viewed::create($params);

    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('filewithwatermark', $filewithwatermark);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Updates display options based on form input.
 *
 * Shared code used by filewithwatermark_add_instance and filewithwatermark_update_instance.
 *
 * @param object $data Data object
 */
function filewithwatermark_set_display_options($data) {
    $displayoptions = array();
    if ($data->display == \mod_filewithwatermark\fileutil::$DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(\mod_filewithwatermark\fileutil::$DISPLAY_AUTO, \mod_filewithwatermark\fileutil::$DISPLAY_EMBED, \mod_filewithwatermark\fileutil::$DISPLAY_FRAME))) {
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    if (!empty($data->showsize)) {
        $displayoptions['showsize'] = 1;
    }
    if (!empty($data->showtype)) {
        $displayoptions['showtype'] = 1;
    }
    if (!empty($data->showdate)) {
        $displayoptions['showdate'] = 1;
    }
    $data->displayoptions = serialize($displayoptions);
}

/**
 * Add watermark to a file
 *
 * @param stored_file $file
 * @param bool $forcedownload
 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
 * @throws \setasign\Fpdi\PdfParser\PdfParserException
 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
 * @return stored_file Stored file with watermark
 */
function filewithwatermark_add_watermark($file, $forcedownload) {

    $filepath = filewithwatermark_create_tempdir();

    $filename = filewithwatermark_generate_filename($file, $filepath);

    $pdf_editor = filewithwatermark_create_watermarkedfile($file, $filepath, $filename);

    try{

        if ($forcedownload) {
            $pdf_editor->Output('D', $filename);
        } else {
            send_content_uncached($pdf_editor->Output('S'), $filename);
        }
    } catch (Exception $exception) {
        echo get_string('cannotgeneratewatermark', 'mod_filewithwatermark');
    }

}

/**
 * Create a watermarked file
 *
 * @param stdClass $file
 * @param string $filepath
 * @param string $filename
 * @return pdfeditor
 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
 * @throws \setasign\Fpdi\PdfParser\PdfParserException
 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
 */

function filewithwatermark_create_watermarkedfile($file, $filepath, $filename) {

    $pdf_editor = new \mod_filewithwatermark\pdfeditor();

    try {
        $filestream = fopen($filepath . $filename, 'a+');

        fwrite($filestream, $file->get_content());

        fclose($filestream);

        $countPage = $pdf_editor->setSourceFile($filepath . $filename);

        for ($i = 1; $i <= $countPage; $i++) {

            $pageId = $pdf_editor->importPage($i);

            $specs = $pdf_editor->getTemplateSize($pageId);

            $pdf_editor->addPage($specs['orientation']);

            $pdf_editor->useImportedPage($pageId);

        }

        unlink($filepath . $filename);

        return $pdf_editor;

    } catch (Exception $e) {
        echo get_string('cannotgeneratewatermark', 'mod_filewithwatermark');
    }
}

/**
 * Generate a unique file name
 *
 * @param stdClass $file
 * @param string $filepath
 * @return string
 */
function filewithwatermark_generate_filename($file, $filepath) {
    $filename =  uniqid("filewithwatermark", true) . $file->get_filename();;

    if (file_exists($filepath . $filename)) {
        $filename = uniqid("filewithwatermark", true) . $file->get_filename();
    }

    return $filename;

}

/**
 * Create a folder and give permission on temp dir
 *
 * @return string
 */
function filewithwatermark_create_tempdir() {
    global $CFG;

    $tempdir = $CFG->tempdir . '/filewithwatermark';

    if (!file_exists( $tempdir)) {
        mkdir($tempdir, 0777, true);
    }

    return $tempdir . '/' ;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_filewithwatermark_core_calendar_provide_event_action(calendar_event $event,
                                                         \core_calendar\action_factory $factory, $userid = 0) {

    global $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['filewithwatermark'][$event->instance];

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_url('/mod/filewithwatermark/view.php', ['id' => $cm->id]),
        1,
        true
    );
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param stdClass $coursemodule
 * @return cached_cm_info info
*/
function filewithwatermark_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/filelib.php");
    require_once("$CFG->dirroot/mod/filewithwatermark/locallib.php");
    require_once($CFG->libdir.'/completionlib.php');

    $context = context_module::instance($coursemodule->id);

    if (!$filewithwatermark = $DB->get_record('filewithwatermark', array('id'=>$coursemodule->instance),
        'id, name, display, displayoptions, revision, intro, introformat')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $filewithwatermark->name;
    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('filewithwatermark', $filewithwatermark, $coursemodule->id, false);
    }

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_filewithwatermark', 'content', 0, 'sortorder DESC, id ASC', false, 0, 0, 1);
    if (count($files) >= 1) {
        $mainfile = reset($files);
        $info->icon = file_file_icon($mainfile, 24);
        $filewithwatermark->mainfile = $mainfile->get_filename();
    }

    $display = filewithwatermark_get_final_display_type($filewithwatermark);

    if ($display == \mod_filewithwatermark\fileutil::$DISPLAY_POPUP) {
        $fullurl = "$CFG->wwwroot/mod/filewithwatermark/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($filewithwatermark->displayoptions) ? array() : unserialize($filewithwatermark->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

    } else if ($display == \mod_filewithwatermark\fileutil::$DISPLAY_NEW) {
        $fullurl = "$CFG->wwwroot/mod/filewithwatermark/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fullurl'); return false;";

    }

    if (($filedetails = filewithwatermark_get_file_details($filewithwatermark, $coursemodule)) && empty($filedetails['isref'])) {
        $displayoptions = @unserialize($filewithwatermark->displayoptions);
        $displayoptions['filedetails'] = $filedetails;
        $info->customdata = serialize($displayoptions);
    } else {
        $info->customdata = $filewithwatermark->displayoptions;
    }

    return $info;
}

/**
 * Called when viewing course page. Shows extra details after the link if
 * enabled.
 *
 * @param cm_info $cm Course module information
 */
function filewithwatermark_cm_info_view(cm_info $cm) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/filewithwatermark/locallib.php');

    $resource = (object)array('displayoptions' => $cm->customdata);
    $details = filewithwatermark_get_optional_details($resource, $cm);
    if ($details) {
        $cm->set_after_link(' ' . html_writer::tag('span', $details,
                array('class' => 'resourcelinkdetails')));
    }
}