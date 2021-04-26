<?php

/**
 * The mod_filewithwatermark PDF editor class.
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/mod/filewithwatermark/classes/pdfrotate.php');

class pdfeditor extends \pdfrotate
{
    /**
     * Add watermark on both sides (left and right) for each page
     */
    function Header()
    {
        global $USER;

        $text = utf8_decode("{$USER->firstname} {$USER->lastname} / {$USER->email}");

        $this->SetFont('Arial','',14);
        $this->SetTextColor(0,0,0);
        $this->rotated_text(8,30,$text,270);
        $this->GetPageWidth();
    }

    /**
     * Rotate a text
     * @param $x
     * @param $y
     * @param $txt
     * @param $angle
     */
    function rotated_text($x,$y,$txt,$angle)
    {
        //Text rotated around its origin
        $this->rotate($angle,$x,$y);
        $this->Text($x,$y,$txt);
        $this->rotate(0);
    }

    /**
     * Rotate a image
     * @param $file
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     * @param $angle
     */
    function rotated_image($file,$x,$y,$w,$h,$angle)
    {
        //Image rotated around its upper-left corner
        $this->rotate($angle,$x,$y);
        $this->Image($file,$x,$y,$w,$h);
        $this->rotate(0);
    }

}