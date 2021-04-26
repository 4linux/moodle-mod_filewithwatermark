<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/filewithwatermark/locallib.php');
require_once($CFG->dirroot.'/mod/filewithwatermark/classes/fileutil.php');


/**
 * Filewithwatermark configuration form
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_filewithwatermark_mod_form extends moodleform_mod {

    function definition() {

        global $CFG, $DB, $OUTPUT;

        $mform =& $this->_form;
        $config = get_config('filewithwatermark');

        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name field
        $mform->addElement('text', 'name', get_string('name', 'filewithwatermark'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Standard elements
        $this->standard_intro_elements();
        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);

        // Filemanager field
        $filemanager_options = array();
        $filemanager_options['accepted_types'] = 'pdf';
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = -1;
        $filemanager_options['mainfile'] = true;

        $mform->addElement('filemanager', 'files', get_string('selectfiles'), null, $filemanager_options);

        //-------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('appearance'));

        if ($this->current->instance) {
            $options = fileutil::get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = fileutil::get_displayoptions(explode(',', $config->displayoptions));
        }

        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'filewithwatermark'), $options);
            $mform->setDefault('display', $config->display);
            $mform->addHelpButton('display', 'displayselect', 'filewithwatermark');
        }

        $mform->addElement('checkbox', 'showsize', get_string('showsize', 'filewithwatermark'));
        $mform->setDefault('showsize', $config->showsize);
        $mform->addHelpButton('showsize', 'showsize', 'filewithwatermark');
        $mform->addElement('checkbox', 'showtype', get_string('showtype', 'filewithwatermark'));
        $mform->setDefault('showtype', $config->showtype);
        $mform->addHelpButton('showtype', 'showtype', 'filewithwatermark');
        $mform->addElement('checkbox', 'showdate', get_string('showdate', 'filewithwatermark'));
        $mform->setDefault('showdate', $config->showdate);
        $mform->addHelpButton('showdate', 'showdate', 'filewithwatermark');

        if (array_key_exists(fileutil::$DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'filewithwatermark'), array('size'=>3));
            if (count($options) > 1) {
                $mform->hideIf('popupwidth', 'display', 'noteq', fileutil::$DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);
            $mform->setAdvanced('popupwidth', true);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'filewithwatermark'), array('size'=>3));
            if (count($options) > 1) {
                $mform->hideIf('popupheight', 'display', 'noteq', fileutil::$DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
            $mform->setAdvanced('popupheight', true);
        }

        if (array_key_exists(fileutil::$DISPLAY_AUTO, $options) or
            array_key_exists(fileutil::$DISPLAY_EMBED, $options) or
            array_key_exists(fileutil::$DISPLAY_FRAME, $options)) {
            $mform->addElement('checkbox', 'printintro', get_string('printintro', 'filewithwatermark'));
            $mform->hideIf('printintro', 'display', 'eq', fileutil::$DISPLAY_POPUP);
            $mform->hideIf('printintro', 'display', 'eq', fileutil::$DISPLAY_DOWNLOAD);
            $mform->hideIf('printintro', 'display', 'eq', fileutil::$DISPLAY_OPEN);
            $mform->hideIf('printintro', 'display', 'eq', fileutil::$DISPLAY_NEW);
            $mform->setDefault('printintro', $config->printintro);
        }

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function definition_after_data() {
        if ($this->current->instance and $this->current->tobemigrated) {

            return;
        }

        parent::definition_after_data();
    }

    function data_preprocessing(&$default_values)
    {

        if ($this->current->instance and !$this->current->tobemigrated) {
            $draftitemid = file_get_submitted_draft_itemid('files');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_filewithwatermark', 'content', 0, array('subdirs'=>true));
            $default_values['files'] = $draftitemid;
        }

        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
            if (!empty($displayoptions['showsize'])) {
                $default_values['showsize'] = $displayoptions['showsize'];
            } else {
                // Must set explicitly to 0 here otherwise it will use system
                // default which may be 1.
                $default_values['showsize'] = 0;
            }
            if (!empty($displayoptions['showtype'])) {
                $default_values['showtype'] = $displayoptions['showtype'];
            } else {
                $default_values['showtype'] = 0;
            }
            if (!empty($displayoptions['showdate'])) {
                $default_values['showdate'] = $displayoptions['showdate'];
            } else {
                $default_values['showdate'] = 0;
            }
        }

    }

    function validation($data, $files) {

        global $USER;

        $pdfversions = [
            'PDF\-1\.0',
            'PDF\-1\.1',
            'PDF\-1\.2',
            'PDF\-1\.3',
            'PDF\-1\.4'
        ];

        $errors = parent::validation($data, $files);

        $usercontext = context_user::instance($USER->id);

        $fs = get_file_storage();

        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['files'], 'sortorder, id', false);
        $invalidversion = false;

        foreach ($files as $file) {
            $content = $file->get_content();

            if (!preg_match_all("/(" . join("|", $pdfversions) . ")/", $content)) {
                $errors['files'] = get_string('versionnotallowed', 'filewithwatermark');
                $invalidversion = true;
                break;
            }
        }

        if ($invalidversion) {
            return $errors;
        }

        if (!$files) {
            $errors['files'] = get_string('required');
            return $errors;
        }

        if (count($files) === 1) {
            return $errors;
        }

        $hasMainFiles = false;

        foreach ($files as $file) {
            if ($file->get_sortorder() === 1) {
                $hasMainFiles = true;
                break;
            }
        }

        if (!$hasMainFiles) {
            $file = reset($files);
            file_set_sortorder(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename(),
                1
            );
        }

        return $errors;

    }

}