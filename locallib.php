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
 * Private Filewithwatermark module utility functions
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/filewithwatermark/lib.php");
require_once($CFG->dirroot.'/mod/filewithwatermark/classes/pdfeditor.php');
require_once($CFG->dirroot.'/mod/filewithwatermark/classes/fileutil.php');
require_once($CFG->dirroot.'/mod/filewithwatermark/classes/pdfextractor.php');
require_once($CFG->dirroot . '/mod/filewithwatermark/vendor/autoload.php');

/**
 * Set main file when have multiples files
 *
 * @param $data
 */
function filewithwatermark_set_mainfile($data) {
    global $DB;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $options = array('subdirs' => true, 'embed' => false);
        if ($data->display ==\mod_filewithwatermark\fileutil::$DISPLAY_EMBED) {
            $options['embed'] = true;
        }
        file_save_draft_area_files($draftitemid, $context->id, 'mod_filewithwatermark', 'content', 0, $options);
    }
    $files = $fs->get_area_files($context->id, 'mod_filewithwatermark', 'content', 0, 'sortorder', false);
    if (count($files) == 1) {
        // only one file attached, set it as main file automatically
        $file = reset($files);
        file_set_sortorder($context->id, 'mod_filewithwatermark', 'content', 0, $file->get_filepath(), $file->get_filename(), 1);
    }
}

/**
 * Print warning that file can not be found.
 *
 * @param object $filewithwatermark
 * @param object $cm
 * @param object $course
 * @return void, does not return
 */
function filewithwatermark_print_filenotfound($filewithwatermark, $cm, $course) {
    global $DB, $OUTPUT;

    filewithwatermark_print_header($filewithwatermark, $cm, $course);
    filewithwatermark_print_heading($filewithwatermark, $cm, $course);
    filewithwatermark_print_intro($filewithwatermark, $cm, $course);

    echo $OUTPUT->notification(get_string('filenotfound', 'filewithwatermark'));

    echo $OUTPUT->footer();
    die;
}

/**
 * Print filewithwatermark introduction.
 *
 * @param object $filewithwatermark
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function filewithwatermark_print_intro($filewithwatermark, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($filewithwatermark->displayoptions) ? array() : unserialize($filewithwatermark->displayoptions);

    $extraintro = filewithwatermark_get_optional_details($filewithwatermark, $cm);
    if ($extraintro) {
        // Put a paragaph tag around the details
        $extraintro = html_writer::tag('p', $extraintro, array('class' => 'resourcedetails'));
    }

    if ($ignoresettings || !empty($options['printintro']) || $extraintro) {
        $gotintro = trim(strip_tags($filewithwatermark->intro));
        if ($gotintro || $extraintro) {
            echo $OUTPUT->box_start('mod_introbox', 'resourceintro');
            if ($gotintro) {
                echo format_module_intro('filewithwatermark', $filewithwatermark, $cm->id);
            }
            echo $extraintro;
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Print filewithwatermark header.
 *
 * @param object $filewithwatermark
 * @param object $cm
 * @param object $course
 * @return void
 */
function filewithwatermark_print_header($filewithwatermark, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$filewithwatermark->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($filewithwatermark);
    echo $OUTPUT->header();
}

/**
 * Print filewithwatermark heading.
 *
 * @param object $filewithwatermark
 * @param object $cm
 * @param object $course
 * @param bool $notused This variable is no longer used
 * @return void
 */
function filewithwatermark_print_heading($filewithwatermark, $cm, $course, $notused = false) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($filewithwatermark->name), 2);
}

/**
 * Decide the best display format.
 *
 * @param object $filewithwatermark
 * @return int display type constant
 */
function filewithwatermark_get_final_display_type($filewithwatermark) {
    global $CFG, $PAGE;

    if ($filewithwatermark->display !=\mod_filewithwatermark\fileutil::$DISPLAY_AUTO) {
        return $filewithwatermark->display;
    }

    if (empty($filewithwatermark->mainfile)) {
        return\mod_filewithwatermark\fileutil::$DISPLAY_DOWNLOAD;
    } else {
        $mimetype = mimeinfo('type', $filewithwatermark->mainfile);
    }

    if (file_mimetype_in_typegroup($mimetype, 'archive')) {
        return\mod_filewithwatermark\fileutil::$DISPLAY_DOWNLOAD;
    }
    if (file_mimetype_in_typegroup($mimetype, array('web_image', '.htm', 'web_video', 'web_audio'))) {
        return\mod_filewithwatermark\fileutil::$DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return\mod_filewithwatermark\fileutil::$DISPLAY_OPEN;
}

/**
 * Display embedded filewithwatermark file.
 *
 * @param object $filewithwatermark
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function filewithwatermark_display_embed($filewithwatermark, $cm, $course, $file) {
    global $CFG, $PAGE, $OUTPUT;

    $clicktoopen = filewithwatermark_get_clicktoopen($file, $filewithwatermark->revision);

    $context = context_module::instance($cm->id);
    $moodleurl = moodle_url::make_pluginfile_url($context->id, 'mod_filewithwatermark', 'content', $filewithwatermark->revision,
        $file->get_filepath(), $file->get_filename());

    $title    = $filewithwatermark->name;

    $code = resourcelib_embed_pdf($moodleurl->out(), $title, $clicktoopen);

    filewithwatermark_print_header($filewithwatermark, $cm, $course);
    filewithwatermark_print_heading($filewithwatermark, $cm, $course);

    echo $code;

    filewithwatermark_print_intro($filewithwatermark, $cm, $course);

    echo $OUTPUT->footer();
    die;
}


/**
 * Display filewithwatermark frames.
 *
 * @param object $filewithwatermark
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function filewithwatermark_display_frame($filewithwatermark, $cm, $course, $file) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        filewithwatermark_print_header($filewithwatermark, $cm, $course);
        filewithwatermark_print_heading($filewithwatermark, $cm, $course);
        filewithwatermark_print_intro($filewithwatermark, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('filewithwatermark');
        $context = context_module::instance($cm->id);
        $path = '/'.$context->id.'/mod_filewithwatermark/content/'.$filewithwatermark->revision.$file->get_filepath().$file->get_filename();
        $fileurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
        $navurl = "$CFG->wwwroot/mod/filewithwatermark/view.php?id=$cm->id&amp;frameset=top";
        $title = strip_tags(format_string($course->shortname.': '.$filewithwatermark->name));
        $framesize = $config->framesize;
        $contentframetitle = s(format_string($filewithwatermark->name));
        $modulename = s(get_string('modulename','filewithwatermark'));
        $dir = get_string('thisdirection', 'langconfig');

        $file = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navurl" title="$modulename" />
    <frame src="$fileurl" title="$contentframetitle" />
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $file;
        die;
    }
}

/**
 * Gets optional details for a filewithwatermark, depending on filewithwatermark settings.
 *
 * Result may include the file size and type if those settings are chosen,
 * or blank if none.
 *
 * @param object $filewithwatermark Filewithwatermark table row (only property 'displayoptions' is used here)
 * @param object $cm Course-module table row
 * @return string Size and type or empty string if show options are not enabled
 */
function filewithwatermark_get_optional_details($filewithwatermark, $cm) {
    global $DB;

    $details = '';

    $options = empty($filewithwatermark->displayoptions) ? array() : @unserialize($filewithwatermark->displayoptions);
    if (!empty($options['showsize']) || !empty($options['showtype']) || !empty($options['showdate'])) {
        if (!array_key_exists('filedetails', $options)) {
            $filedetails = filewithwatermark_get_file_details($filewithwatermark, $cm);
        } else {
            $filedetails = $options['filedetails'];
        }
        $size = '';
        $type = '';
        $date = '';
        $langstring = '';
        $infodisplayed = 0;
        if (!empty($options['showsize'])) {
            if (!empty($filedetails['size'])) {
                $size = display_size($filedetails['size']);
                $langstring .= 'size';
                $infodisplayed += 1;
            }
        }
        if (!empty($options['showtype'])) {
            if (!empty($filedetails['type'])) {
                $type = $filedetails['type'];
                $langstring .= 'type';
                $infodisplayed += 1;
            }
        }
        if (!empty($options['showdate']) && (!empty($filedetails['modifieddate']) || !empty($filedetails['uploadeddate']))) {
            if (!empty($filedetails['modifieddate'])) {
                $date = get_string('modifieddate', 'mod_filewithwatermark', userdate($filedetails['modifieddate'],
                    get_string('strftimedatetimeshort', 'langconfig')));
            } else if (!empty($filedetails['uploadeddate'])) {
                $date = get_string('uploadeddate', 'mod_filewithwatermark', userdate($filedetails['uploadeddate'],
                    get_string('strftimedatetimeshort', 'langconfig')));
            }
            $langstring .= 'date';
            $infodisplayed += 1;
        }

        if ($infodisplayed > 1) {
            $details = get_string("filewithwatermarkdetails_{$langstring}", 'filewithwatermark',
                (object)array('size' => $size, 'type' => $type, 'date' => $date));
        } else {
            // Only one of size, type and date is set, so just append.
            $details = $size . $type . $date;
        }
    }

    return $details;
}

/**
 * Internal function - create click to open text with link
 *
 * @param $file
 * @param $revision
 * @param string $extra
 * @return mixed
 */
function filewithwatermark_get_clicktoopen($file, $revision, $extra='') {
    global $CFG;

    $filename = $file->get_filename();
    $path = '/'.$file->get_contextid().'/mod_filewithwatermark/content/'.$revision.$file->get_filepath().$file->get_filename();
    $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);

    $string = get_string('clicktoopen2', 'filewithwatermark', "<a href=\"$fullurl\" $extra>$filename</a>");

    return $string;
}

/**
 * Print filewithwatermark info and workaround link when JS not available.
 *
 * @param object $filewithwatermark
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function filewithwatermark_print_workaround($filewithwatermark, $cm, $course, $file) {
    global $CFG, $OUTPUT;

    filewithwatermark_print_header($filewithwatermark, $cm, $course);
    filewithwatermark_print_heading($filewithwatermark, $cm, $course, true);
    filewithwatermark_print_intro($filewithwatermark, $cm, $course, true);

    $filewithwatermark->mainfile = $file->get_filename();
    echo '<div class="resourceworkaround">';
    switch (filewithwatermark_get_final_display_type($filewithwatermark)) {
        case \mod_filewithwatermark\fileutil::$DISPLAY_POPUP:
            $path = '/'.$file->get_contextid().'/mod_filewithwatermark/content/'.$filewithwatermark->revision.$file->get_filepath().$file->get_filename();
            $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
            $options = empty($resource->displayoptions) ? array() : unserialize($filewithwatermark->displayoptions);
            $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
            $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
            $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
            $extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";
            echo filewithwatermark_get_clicktoopen($file, $filewithwatermark->revision, $extra);
            break;

        case \mod_filewithwatermark\fileutil::$DISPLAY_NEW:
            $extra = 'onclick="this.target=\'_blank\'"';
            echo filewithwatermark_get_clicktoopen($file, $filewithwatermark->revision, $extra);
            break;

        case \mod_filewithwatermark\fileutil::$DISPLAY_DOWNLOAD:
            echo filewithwatermark_get_clicktodownload($file, $filewithwatermark->revision);
            break;

        case \mod_filewithwatermark\fileutil::$DISPLAY_OPEN:
        default:
            echo filewithwatermark_get_clicktoopen($file, $filewithwatermark->revision);
            break;
    }
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Internal function - create click to open text with link
 *
 * @param $file
 * @param $revision
 * @return mixed
 */
function filewithwatermark_get_clicktodownload($file, $revision) {
    global $CFG;

    $filename = $file->get_filename();
    $path = '/'.$file->get_contextid().'/mod_filewithwatermark/content/'.$revision.$file->get_filepath().$file->get_filename();
    $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, true);

    $string = get_string('clicktodownload', 'filewithwatermark', "<a href=\"$fullurl\">$filename</a>");

    return $string;
}

/**
 * Gets details of the file to cache in course cache to be displayed using
 *
 * @param object $filewithwatermark Filewithwatermark table row (only property 'displayoptions' is used here)
 * @param object $cm Course-module table row
 * @return string Size and type or empty string if show options are not enabled
 */
function filewithwatermark_get_file_details($filewithwatermark, $cm) {
    $options = empty($filewithwatermark->displayoptions) ? array() : @unserialize($filewithwatermark->displayoptions);
    $filedetails = array();
    if (!empty($options['showsize']) || !empty($options['showtype']) || !empty($options['showdate'])) {
        $context = context_module::instance($cm->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_filewithwatermark', 'content', 0, 'sortorder DESC, id ASC', false);
        // For a typical file resource, the sortorder is 1 for the main file
        // and 0 for all other files. This sort approach is used just in case
        // there are situations where the file has a different sort order.
        $mainfile = $files ? reset($files) : null;
        if (!empty($options['showsize'])) {
            $filedetails['size'] = 0;
            foreach ($files as $file) {
                // This will also synchronize the file size for external files if needed.
                $filedetails['size'] += $file->get_filesize();
                if ($file->get_repository_id()) {
                    // If file is a reference the 'size' attribute can not be cached.
                    $filedetails['isref'] = true;
                }
            }
        }
        if (!empty($options['showtype'])) {
            if ($mainfile) {
                $filedetails['type'] = get_mimetype_description($mainfile);
                $filedetails['mimetype'] = $mainfile->get_mimetype();
                // Only show type if it is not unknown.
                if ($filedetails['type'] === get_mimetype_description('document/unknown')) {
                    $filedetails['type'] = '';
                }
            } else {
                $filedetails['type'] = '';
            }
        }
        if (!empty($options['showdate'])) {
            if ($mainfile) {
                // Modified date may be up to several minutes later than uploaded date just because
                // teacher did not submit the form promptly. Give teacher up to 5 minutes to do it.
                if ($mainfile->get_timemodified() > $mainfile->get_timecreated() + 5 * MINSECS) {
                    $filedetails['modifieddate'] = $mainfile->get_timemodified();
                } else {
                    $filedetails['uploadeddate'] = $mainfile->get_timecreated();
                }
                if ($mainfile->get_repository_id()) {
                    // If main file is a reference the 'date' attribute can not be cached.
                    $filedetails['isref'] = true;
                }
            } else {
                $filedetails['uploadeddate'] = '';
            }
        }
    }
    return $filedetails;
}

/**
 * Retrieve user data from file
 *
 * @param $file
 * @return array
 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
 * @throws \setasign\Fpdi\PdfParser\PdfParserException
 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
 */
function filewithwatermark_get_file_userdata($file) {

    $filepath = filewithwatermark_create_tempdir();

    $filename = filewithwatermark_generate_filename($file, $filepath);

    $pdf_editor = filewithwatermark_create_watermarkedfile($file, $filepath, $filename);

    $pdf_extractor = new pdfextractor();

    $content = $pdf_extractor->extract_text($pdf_editor->Output('S'));

    $contentarray = explode('/', $content);

    return [
        'name' => trim($contentarray[0]),
        'email' => trim($contentarray[1])
    ];
}
