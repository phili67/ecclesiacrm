<?php
/*******************************************************************************
 *
 *  filename    : Include/ReportsConfig.php
 *  last change : 2003-03-14
 *  description : Configure report generation
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2004-2012 Chris Gebhardt, Michael Wilt
 *
 ******************************************************************************/

namespace EcclesiaCRM\Reports;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ReportUtilities;
use TCPDF;

// This class definition contains a bunch of configuration stuff and utitilities
// that are useful to all the reports generated by ChurchInfo

// Load the FPDF library

class ChurchInfoReportTCPDF extends TCPDF
{
    //
    // Paper size for all PDF report documents
    // Sizes: A3, A4, A5, Letter, Legal, or a 2-element array for custom size
    // Sorry -- This should really be set in database, but it is needed before all the report settings
    // are read from the database.

    public $paperFormat = 'Letter';

    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        parent::setPrintHeader(false);
    }

    public function TextWithDirection($x, $y, $txt, $direction='R')
    {

        parent::StartTransform();
// Rotate 90 degrees counter-clockwise centered by ($x,$y) which is the lower left corner of the rectangle
        parent::Rotate(90, $x, $y);
        parent::Text($x, $y, $txt);
// Stop Transformation
        parent::StopTransform();

        /*return;

        if ($direction=='R')
            $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        elseif ($direction=='L')
            $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        elseif ($direction=='U')
            $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        elseif ($direction=='D')
            $s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
        else
            $s=sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));

        if ($this->ColorFlag)
            $s='q '.$this->TextColor.' '.$s.' Q';

        $this->_out($s);*/
    }

    public function _escape($txt)
    {
        return '';
    }

    public function setFont ($family, $style='', $size=null, $fontfile='', $subset='default', $out=true)
    {
        $lang = SystemConfig::getValue('sLanguage');
        if ($lang == 'ko_KR') {
            $family = 'cid0kr';
            $size = round($size * 0.8);
        } else if ($lang == 'uk_UA') {
            $this->setFontSubsetting(true);
            $family = 'freeserif';
            $size = round($size * 0.8);
        } else if ($lang == 'ja_JP') {
            $family = 'cid0jp';
            $size = round($size*0.8);
        } else if ($lang == 'ru_RU') {
            $this->setFontSubsetting(true);
            $family = 'freeserif';
            $size = round($size*0.6);
        } else if ($lang == 'zh_CN') {
            $family = 'cid0ct';
        } else if ($lang == 'el_GR') {
            $family = 'courier';
        } else if ($lang == 'ar_EG') {
            $family = 'aealarabiya';
        } else if ($lang == 'pl_PL') {
            $family = 'dejavusans';
            $size = round($size*0.8);
        } else if ($lang == 'ro_RO') {
            $family = 'dejavusans';
            $size = round($size*0.6);
        } else if ($lang == 'vi_VN') {
            $family = 'dejavusans';
            $size = round($size*0.7);
        } else if ($lang == 'th_TH') {
            $family = 'freeserif';
            $size = round($size*0.9);
        } else if ($lang == 'sq_AL') {
            $family = 'dejavusans';
            $size = round($size*0.8);
        }
        parent::setFont($family, $style, $size, $fontfile, $subset, $out);
    }

    public function StripPhone($phone)
    {
        if (mb_substr($phone, 0, 3) == SystemConfig::getValue('sHomeAreaCode')) {
            $phone = mb_substr($phone, 3, strlen($phone) - 3);
        }
        if (mb_substr($phone, 0, 5) == ('('.SystemConfig::getValue('sHomeAreaCode').')')) {
            $phone = mb_substr($phone, 5, strlen($phone) - 5);
        }
        if (mb_substr($phone, 0, 1) == '-') {
            $phone = mb_substr($phone, 1, strlen($phone) - 1);
        }
        if (strlen($phone) == 7) {
            // Fix the missing -
            $phone = mb_substr($phone, 0, 3).'-'.mb_substr($phone, 3, 4);
        }

        return $phone;
    }

    public function PrintRightJustified($x, $y, $str)
    {
        $str = str_replace('\n', chr(10), $str);

        $iLen = mb_strlen($str);
        $nMoveBy = 10 - 2 * $iLen;
        $this->SetXY($x + $nMoveBy, $y);
        $this->Write(SystemConfig::getValue('incrementY'), $str);
    }

    public function PrintRightJustifiedCell($x, $y, $wid, $str)
    {
        $str = str_replace('\n', chr(10), $str);
        $this->SetXY($x, $y);
        $this->Cell($wid, SystemConfig::getValue('incrementY'), $str, 1, 0, 'R');
    }

    public function PrintCenteredCell($x, $y, $wid, $str)
    {
        $str = str_replace('\n', chr(10), $str);
        $this->SetXY($x, $y);
        $this->Cell($wid, SystemConfig::getValue('incrementY'), $str, 1, 0, 'C');
    }

    public function WriteAt($x, $y, $str)
    {
        $str = str_replace('\n', chr(10), $str);

        $this->SetXY($x, $y);
        $this->Write(SystemConfig::getValue('incrementY'), $str);
    }

    public function WriteAtCell($x, $y, $wid, $str, $border=1, $align="L")
    {
        $str = str_replace('\n', chr(10), $str);
        $this->SetXY($x, $y);
        $this->MultiCell($wid, 4, $str, $border, $align);
    }

    public function StartLetterPage($ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $letterhead = '', $type = "family")
    {
        $this->AddPage();

        if ($letterhead == 'graphic' && is_readable(SystemConfig::getValue('bDirLetterHead'))) {
            $this->Image(SystemConfig::getValue('bDirLetterHead'), 12, 15, 185);
            $curY = 20 + (SystemConfig::getValue('incrementY') * 3) + 25;
            $this->WriteAt(170, $curY, date(SystemConfig::getValue("sDateFormatLong")));
        } elseif ($letterhead == 'none') {
            $curY = 20 + (SystemConfig::getValue('incrementY') * 3) + 25;
            $this->WriteAt(170, $curY, date(SystemConfig::getValue("sDateFormatLong")));
        } else {
            $dateX = 170;
            $dateY = 25;
            $this->WriteAt($dateX, $dateY, date(SystemConfig::getValue("sDateFormatLong")));
            $curY = 20;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchName'));
            $curY += SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchAddress'));
            $curY += SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchCity').', '.SystemConfig::getValue('sChurchState').'  '.SystemConfig::getValue('sChurchZip'));
            $curY += SystemConfig::getValue('incrementY');
            $curY += SystemConfig::getValue('incrementY'); // Skip another line before the phone/email
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchPhone').'  '.SystemConfig::getValue('sChurchEmail'));
            $curY += 25; // mm to move to the second window
        }

        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $this->MakeSalutation($ID, $type));
        $curY += SystemConfig::getValue('incrementY');
        if ($fam_Address1 != '') {
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $fam_Address1);
            $curY += SystemConfig::getValue('incrementY');
        }
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $fam_City.', '.$fam_State.'  '.$fam_Zip);
        $curY += SystemConfig::getValue('incrementY');
        if ($fam_Address2 != '') {
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $fam_Address2);
            $curY += SystemConfig::getValue('incrementY');
        }
        if ($fam_Country != '' && $fam_Country != SystemConfig::getValue('sDefaultCountry')) {
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $fam_Country);
            $curY += SystemConfig::getValue('incrementY');
        }
        $curY += 5.0; // mm to get away from the second window        
        return $curY;
    }

    public function MakeSalutation($ID, $type)
    {
        if ($type == "family") {
            return ReportUtilities::MakeSalutationUtilityFamily($ID);
        } else {
            return ReportUtilities::MakeSalutationUtilityPerson($ID);
        }
    }
}
