<?php

/**
 * List of all resources with watermark in course
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_filewithwatermark\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strfilewithwatermark     = get_string('modulename', 'filewithwatermark');
$strfilewithwatermarks    = get_string('modulenameplural', 'filewithwatermark');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/filewithwatermark/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strfilewithwatermarks);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strfilewithwatermarks);
echo $OUTPUT->header();
echo $OUTPUT->heading($strfilewithwatermarks);

if (!$filewithwatermarks = get_all_instances_in_course('filewithwatermark', $course)) {
    notice(get_string('thereareno', 'moodle', $strfilewithwatermarks), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($filewithwatermarks as $filewithwatermark) {
    $cm = $modinfo->cms[$filewithwatermark->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($filewithwatermark->section !== $currentsection) {
            if ($filewithwatermark->section) {
                $printsection = get_section_name($course, $filewithwatermark->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $filewithwatermark->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($filewithwatermark->timemodified)."</span>";
    }

    $extra = empty($cm->extra) ? '' : $cm->extra;

    $class = $filewithwatermark->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed
    $table->data[] = array (
        $printsection,
        "<a $class $extra href=\"view.php?id=$cm->id\">".format_string($filewithwatermark->name)."</a>",
        format_module_intro('filewithwatermark', $filewithwatermark, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
