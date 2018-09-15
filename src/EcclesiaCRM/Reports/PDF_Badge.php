<?php

////////////////////////////////////////////////////
// PDF_Label
//
// Class to print labels in Avery or custom formats
//
//
// Copyright (C) 2003 Philippe Logel (LPA)
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
 * PDF_Label - PDF label editing.
 *
 * @author Laurent PASSEBECQ <lpasseb@numericable.fr>
 * @copyright 2003 Laurent PASSEBECQ
 **/

/*
*  InfoCentral modifications:
*    adjustment of label format parameters: 5160,
*
*/

namespace EcclesiaCRM\Reports;

class PDF_Badge extends PDF_Label
{
    // Constructor
    public function __construct($format, $posX = 1, $posY = 1, $unit = 'mm')
    {
        parent::__construct($format,$posX,$posY,$unit);
    }


    // Print a label
    public function Add_PDF_Badge($title, $LastName, $firstName, $group, $props='', $sFirstNameFontSize = 20,$image='../Images/scleft1.png', 
                                               $title_red=0, $title_gren=0, $title_blue=0,
                                               $back_red=255, $back_gren=255, $back_blue=255,
                                               $sImagePosition='Left')
    {
        // We are in a new page, then we must add a page
        if ($this->_COUNTX == 0 && $this->_COUNTY == 0) {
            $this->AddPage();
        }

        $_PosX = $this->_Margin_Left + ($this->_COUNTX * ($this->_Width + $this->_X_Space));
        $_PosY = $this->_Margin_Top + ($this->_COUNTY * ($this->_Height + $this->_Y_Space));

        $this->SetFillColor($back_red,$back_gren,$back_blue);
        $this->Rect($_PosX,$_PosY, $this->_Width, $this->_Height, F);
        
        if ($image != "../Images/" && file_exists($image)) {
          if ($sImagePosition == 'Left') {
            $this->Image($image,$_PosX, $_PosY,7,$this->_Height);
          } else if ($sImagePosition == 'Right') {
            $this->Image($image,$_PosX+$this->_Width-7, $_PosY,7,$this->_Height);
          } else {
            $this->Image($image,$_PosX, $_PosY,$this->_Width,$this->_Height);
          }
        }
        
        $this->SetFontSize (15);
        $this->SetTextColor ($title_red, $title_gren, $title_blue);
        $this->SetXY($_PosX, $_PosY );
        $this->Cell($this->_Width,10,iconv('UTF-8', 'ISO-8859-1', $title),0,0,'C');
        
        $this->SetFontSize ($sFirstNameFontSize);
        $this->SetTextColor (0,0,0);
        $this->SetXY($_PosX, $_PosY + $this->_Height/2 - $this->_Get_Height_Chars($sFirstNameFontSize));
        $this->Cell($this->_Width,10,iconv('UTF-8', 'ISO-8859-1', mb_strtoupper($firstName)),0,0,'C');

        $this->SetFontSize (12);
        $this->SetXY($_PosX, $_PosY + $this->_Height/4*3 - $this->_Get_Height_Chars(12));
        $this->Cell($this->_Width,10,iconv('UTF-8', 'ISO-8859-1', mb_strtoupper($LastName)),0,0,'C');
        
        $this->SetFontSize (4);
        $this->SetXY($_PosX+7, $_PosY + $this->_Height - 7);
        
        $this->MultiCell($this->_Width-14,2,iconv('UTF-8', 'ISO-8859-1', $props),0,($sImagePosition == 'Left')?'L':'R');
        
        $this->SetFontSize (8);
        $this->SetXY($_PosX+7, $_PosY + $this->_Height - 10);
        $this->Cell($this->_Width-14,10,iconv('UTF-8', 'ISO-8859-1', $group),0,0,($sImagePosition == 'Left')?'R':'L');
        
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
