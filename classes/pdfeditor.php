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
 * The mod_filewithwatermark PDF editor class.
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_filewithwatermark;

use mod_filewithwatermark\pdfrotate;

class pdfeditor extends pdfrotate
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
     *
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
     *
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