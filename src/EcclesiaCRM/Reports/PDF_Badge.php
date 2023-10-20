<?php

////////////////////////////////////////////////////
// PDF_Badge
//
// Class to print labels in Avery or custom formats
//
// Copyright (C) 2023 Philippe Logel (LPA)
// Based on code by Steve Dillon (steved@mad.scientist.com)
//
//-------------------------------------------------------------------
// VERSIONS :
// 1.0  : Initial release
// 1.1  : +    : Added unit in the constructor
//          + : Now Positions start @ (1,1).. then the first image @top-left of a page is (1,1)
//          + : Added in the description of a label :
//                font-size    : defaut char size (can be changed by calling Set_Char_Size(xx);
//                paper-size    : Size of the paper for this sheet (thanx to Al Canton)
//                metric        : type of unit used in this description
//                              You can define your label properties in inches by setting metric to 'in'
//                              and printing in millimiter by setting unit to 'mm' in constructor.
//              Added some labels :
//                5160, 5161, 5162, 5163,5164 : thanx to Al Canton : acanton@adams-blake.com
//                8600                         : thanx to Kunal Walia : kunal@u.washington.edu
//          + : Added 3mm to the position of labels to avoid errors
////////////////////////////////////////////////////

/**
 * PDF_Badge - PDF Badge editing.
 *
 * @author Philippe LOGEL <philippe.loge@imathgeo.com>
 * @copyright 2023 Laurent PASSEBECQ & Philippe Logel
 **/

/*
*  InfoCentral modifications:
*    adjustment of label format parameters: 5160,
*
*/


namespace EcclesiaCRM\Reports;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;


class PDF_Badge extends PDF_Label
{
    // Constructor
    // the $view param to true set ever a A7 page
    public function __construct($format, $posX = 1, $posY = 1, $unit = 'mm', $view = false)
    {
        parent::__construct($format,$posX,$posY,$unit, $view);
    }

    public function create_QR_Code($groupID,$personId)
    {
        $writer = new PngWriter();

// Create QR code
        $qrCode = QrCode::create($groupID." ".$personId)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

// Create generic logo
        /*$logo = Logo::create(__DIR__.'/assets/symfony.png')
            ->setResizeToWidth(50);*/

// Create generic label
        $label = Label::create('EcclesiaCRM')
            ->setTextColor(new Color(0, 0, 0));
            //->setBackgroundColor(new Color(0, 0, 0));

        $result = $writer->write($qrCode, null, $label);

        // Save it to a file
        $result->saveToFile('../tmp_attach/qrcode_'.$groupID."_".$personId.'.png');

        return '../tmp_attach/qrcode_'.$groupID."_".$personId.'.png';
    }


    // Print a label
    public function Add_PDF_Badge($title, $titlePosition, $LastName, $firstName, $group,$groupPosition, $props='', 
                                    $sFirstNameFontSize = 20,$image='../Images/scleft1.png',
                                    $title_red=0, $title_gren=0, $title_blue=0,
                                    $back_red=255, $back_gren=255, $back_blue=255,
                                    $sImagePosition='Left',
                                    $groupID=-1,$personId=-1)
    {
        $lastNameFontSize = (int)$sFirstNameFontSize*0.8;
        
        // We are in a new page, then we must add a page
        if ($this->_COUNTX == 0 && $this->_COUNTY == 0) {
            $this->AddPage();
            $this->SetFillColor(255,255,255);
            $this->Rect(0, 0, $this->getPageWidth(),    $this->getPageHeight(), 'F');
        }

        $_PosX = $this->_Margin_Left + ($this->_COUNTX * ($this->_Width + $this->_X_Space));
        $_PosY = $this->_Margin_Top + ($this->_COUNTY * ($this->_Height + $this->_Y_Space));

        $this->SetFillColor($back_red,$back_gren,$back_blue);
        $this->Rect($_PosX,$_PosY, $this->_Width, $this->_Height, 'F');

        if ($image != "../Images/" && file_exists($image)) {
          if ($sImagePosition == 'Left') {
            $this->Image($image,$_PosX, $_PosY,7,$this->_Height);
          } else if ($sImagePosition == 'Right') {
            $this->Image($image,$_PosX+$this->_Width-7, $_PosY,7,$this->_Height);
          } else {
            $this->Image($image,$_PosX, $_PosY,$this->_Width,$this->_Height);
          }
        }

        $has_QR_Code = False;
        if ($groupID > 0 && $personId >= 0) {
            $has_QR_Code = True;
            $qr_code = $this->create_QR_Code($groupID, $personId);

            $this->Image($qr_code, $_PosX+9, $_PosY + $this->_Height*0.20, $this->_Height*0.60, $this->_Height*0.60);

            unlink ($qr_code);
        }

        $position = 'L';
        $addX = '0';
        switch ($sImagePosition) {
            case 'Left':
                $position = 'R';
                $addX = -5;
                break;
            case 'Right':
                $position = 'L';
                $addX = 5;
                break;
            default:
                $position = 'C';
                $addX = 0;
        }

        switch ($titlePosition) {
            case 'Left':
                $tposition = 'L';
                break;
            case 'Right':
                $tposition = 'R';
                break;
            default:
                $tposition = 'C';
        }

        switch ($groupPosition) {
            case 'Left':
                $gposition = 'L';
                break;
            case 'Right':
                $gposition = 'R';
                break;
            default:
                $gposition = 'C';
        }

        
        if (!$has_QR_Code) {
            $this->SetFontSize(15);
            $this->SetTextColor($title_red, $title_gren, $title_blue);
            $this->SetXY($_PosX, $_PosY);
            $this->Cell($this->_Width , 10,  $group, 0, 0, $gposition);

            $this->SetFontSize($sFirstNameFontSize);
            $this->SetTextColor(0, 0, 0);
            $this->SetXY($_PosX, $_PosY + $this->_Height / 2 - $this->_Get_Height_Chars($sFirstNameFontSize));
            $this->Cell($this->_Width, 10,  mb_strtoupper($firstName), 0, 0, 'C');

            $this->SetFontSize(12);
            $this->SetXY($_PosX, $_PosY + $this->_Height / 2 - $this->_Get_Height_Chars($sFirstNameFontSize) + $this->_Get_Height_Chars($lastNameFontSize) + 1);
            $this->Cell($this->_Width, 10, mb_strtoupper($LastName), 0, 0, 'C');

            $this->SetFontSize(4);
            $this->SetXY($_PosX, $_PosY + $this->_Height - 7);

            $this->MultiCell($this->_Width, 2, $props, 0, 'C');

            $this->SetFontSize (8);
            $this->SetXY($_PosX, $_PosY + $this->_Height - 8);
            $this->Cell($this->_Width , 10,  $title, 0, 0, $tposition);
        } else {
            $sFirstNameFontSize *= 0.75;
            $lastNameFontSize = $sFirstNameFontSize*0.8;
            
            $this->SetFontSize(13);
            $this->SetTextColor($title_red, $title_gren, $title_blue);
            $this->SetXY($_PosX, $_PosY);
            $this->Cell($this->_Width , 10,  $group, 0, 0, $gposition);

            $this->SetFontSize($sFirstNameFontSize);
            $this->SetTextColor(0, 0, 0);
            $this->SetXY($_PosX + 16, $_PosY + $this->_Height / 2 - $this->_Get_Height_Chars($sFirstNameFontSize));
            $this->Cell($this->_Width, 10, mb_strtoupper($firstName), 0, 0, 'C');

            $this->SetFontSize($lastNameFontSize);
            $this->SetXY($_PosX + 16, $_PosY + $this->_Height / 2 - $this->_Get_Height_Chars($sFirstNameFontSize) + $this->_Get_Height_Chars($lastNameFontSize) + 1);
            $this->Cell($this->_Width, 10, mb_strtoupper($LastName), 0, 0, 'C');

            $this->SetFontSize(4);
            $this->SetXY($_PosX + 16, $_PosY + $this->_Height - 7);

            $this->MultiCell($this->_Width, 2,  $props, 0, 'C');

            $this->SetFontSize (8);
            $this->SetXY($_PosX, $_PosY + $this->_Height - 8);
            $this->Cell($this->_Width , 10,  $title, 0, 0, $tposition);
        }
        
        // draw the border

        $this->Line($_PosX, $_PosY, $_PosX, $_PosY + $this->_Height);
        $this->Line($_PosX, $_PosY, $_PosX + $this->_Width, $_PosY);
        $this->Line($_PosX + $this->_Width-2, $_PosY, $_PosX + $this->_Width-2, $_PosY);
        $this->Line($_PosX , $_PosY + $this->_Height, $_PosX, $_PosY + $this->_Height);


        $this->_COUNTY++;

        if ($this->_COUNTY == $this->_Y_Number) {
            // End of column reached, we start a new one
            $this->_COUNTX++;
            $this->_COUNTY = 0;
        }

        if ($this->_COUNTX == $this->_X_Number) {
            // Page full, we start a new one
            $this->_COUNTX = 0;
            $this->_COUNTY = 0;
        }
    }
}
