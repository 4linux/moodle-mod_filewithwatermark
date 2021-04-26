<?php

/**
 * The mod_filewithwatermark PDF content rotate class.
 *
 * @package    mod_filewithwatermark
 * @copyright  2021 4Linux  {@link https://4linux.com.br/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

require_once( $CFG->dirroot . '/mod/filewithwatermark/vendor/setasign/fpdi/src/FpdiTrait.php');
require_once( $CFG->dirroot . '/mod/filewithwatermark/vendor/setasign/fpdi/src/FpdfTplTrait.php');
require_once( $CFG->dirroot . '/mod/filewithwatermark/vendor/setasign/fpdf/fpdf.php');
require_once( $CFG->dirroot . '/mod/filewithwatermark/vendor/setasign/fpdi/src/FpdfTpl.php');
require_once( $CFG->dirroot . '/mod/filewithwatermark/vendor/setasign/fpdi/src/Fpdi.php');

class pdfrotate extends setasign\Fpdi\Fpdi
{
    protected $angle=0;

    /**
     * Rotate the document
     *
     * @param $angle
     * @param int $x
     * @param int $y
     *
     */
    function rotate($angle,$x=-1,$y=-1)
    {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    /**
     * Set state to 1
     */
    function _endpage()
    {
        if($this->angle!=0)
        {
            $this->angle=0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

}