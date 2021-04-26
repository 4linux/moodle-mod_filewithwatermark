<?php

/**
 * Filewithwatermark module admin settings and defaults
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->dirroot.'/mod/filewithwatermark/classes/fileutil.php');

if ($ADMIN->fulltree) {

    $displayoptions = \fileutil::get_displayoptions(array(fileutil::$DISPLAY_AUTO,
                                                           fileutil::$DISPLAY_EMBED,
                                                           fileutil::$DISPLAY_FRAME,
                                                           fileutil::$DISPLAY_DOWNLOAD,
                                                           fileutil::$DISPLAY_OPEN,
                                                           fileutil::$DISPLAY_NEW,
                                                           fileutil::$DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(fileutil::$DISPLAY_AUTO,
                                   fileutil::$DISPLAY_EMBED,
                                   fileutil::$DISPLAY_DOWNLOAD,
                                   fileutil::$DISPLAY_OPEN,
                                   fileutil::$DISPLAY_POPUP,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('filewithwatermark/framesize',
        get_string('framesize', 'filewithwatermark'), get_string('configframesize', 'filewithwatermark'), 130, PARAM_INT));
    $settings->add(new admin_setting_configmultiselect('filewithwatermark/displayoptions',
        get_string('displayoptions', 'filewithwatermark'), get_string('configdisplayoptions', 'filewithwatermark'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('filewithwatermarkmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('filewithwatermark/printintro',
        get_string('printintro', 'filewithwatermark'), get_string('printintroexplain', 'filewithwatermark'), 1));
    $settings->add(new admin_setting_configselect('filewithwatermark/display',
        get_string('displayselect', 'filewithwatermark'), get_string('displayselectexplain', 'filewithwatermark'), fileutil::$DISPLAY_AUTO,
        $displayoptions));
    $settings->add(new admin_setting_configcheckbox('filewithwatermark/showsize',
        get_string('showsize', 'filewithwatermark'), get_string('showsize_desc', 'filewithwatermark'), 0));
    $settings->add(new admin_setting_configcheckbox('filewithwatermark/showtype',
        get_string('showtype', 'filewithwatermark'), get_string('showtype_desc', 'filewithwatermark'), 0));
    $settings->add(new admin_setting_configcheckbox('filewithwatermark/showdate',
        get_string('showdate', 'filewithwatermark'), get_string('showdate_desc', 'filewithwatermark'), 0));
    $settings->add(new admin_setting_configtext('filewithwatermark/popupwidth',
        get_string('popupwidth', 'filewithwatermark'), get_string('popupwidthexplain', 'filewithwatermark'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('filewithwatermark/popupheight',
        get_string('popupheight', 'filewithwatermark'), get_string('popupheightexplain', 'filewithwatermark'), 450, PARAM_INT, 7));
}
