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

    $displayoptions = \mod_filewithwatermark\fileutil::get_displayoptions(array(
        \mod_filewithwatermark\fileutil::$DISPLAY_AUTO,
        \mod_filewithwatermark\fileutil::$DISPLAY_EMBED,
        \mod_filewithwatermark\fileutil::$DISPLAY_FRAME,
        \mod_filewithwatermark\fileutil::$DISPLAY_DOWNLOAD,
        \mod_filewithwatermark\fileutil::$DISPLAY_OPEN,
        \mod_filewithwatermark\fileutil::$DISPLAY_NEW,
        \mod_filewithwatermark\fileutil::$DISPLAY_POPUP,
    ));
    $defaultdisplayoptions = array(
        \mod_filewithwatermark\fileutil::$DISPLAY_AUTO,
        \mod_filewithwatermark\fileutil::$DISPLAY_EMBED,
        \mod_filewithwatermark\fileutil::$DISPLAY_DOWNLOAD,
        \mod_filewithwatermark\fileutil::$DISPLAY_OPEN,
        \mod_filewithwatermark\fileutil::$DISPLAY_POPUP,
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
        get_string('displayselect', 'filewithwatermark'), get_string('displayselectexplain', 'filewithwatermark'),\mod_filewithwatermark\fileutil::$DISPLAY_AUTO,
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
