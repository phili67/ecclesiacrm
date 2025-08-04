<?php

namespace EcclesiaCRM\Reports;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\PersonCustomQuery;

class PDF_Directory extends ChurchInfoReportTCPDF
{
    // Private properties
    public $sRecordName = "";
    public $_Margin_Left = 16;        // Left Margin
    public $_Margin_Top = 0;         // Top margin
    public $_Char_Size = 10;        // Character size
    public $_Column = 0;
    public $_Font = 'Arial';
    public $_Gutter = 5;
    public $_LS = 4;
    public $sFamily;
    public $sLastName;
    public $_ColWidth = 58;
    public $_Custom;
    public $_NCols = 3;
    public $_PS = 'Letter';
    public $cMargin = 0;
    public $sSortBy = '';

    // Constructor
    public function __construct($nc = 1, $paper = 'letter', $fs = 10, $ls = 4)
    {
        parent::__construct('P', 'mm', $paper);
        parent::setPrintHeader(true);

        $this->_Char_Size = $fs;
        $this->_LS = $ls;

        $this->_Column = 0;
        $this->_Font = 'Times';
        $this->SetMargins(0, 0);

        $this->Set_Char_Size($this->_Char_Size);
        $this->SetAutoPageBreak(false);

        $this->_Margin_Left = 13;
        $this->_Margin_Top = 13;
        $this->_Custom = [];
        $this->_NCols = $nc;
        $this->_ColWidth = 190 / $nc - $this->_Gutter;
    }

    public function Header()
    {
        global $bDirUseTitlePage;

        if (($this->PageNo() > 1) || ($bDirUseTitlePage == false)) {
            //Select Arial bold 15
            $this->SetFont("Helvetica", 'B', 15);
            //Line break
            $this->Ln(7);
            //Move to the right
            $this->SetX($this->_Margin_Left);
            //Framed title
            $this->Cell($this->w - ($this->_Margin_Left * 2), 10, SystemConfig::getValue('sEntityName') . ' - ' . _('Directory'), 0, 0, 'C');
            $this->SetY(25);
        }
    }

    public function Footer()
    {
        global $bDirUseTitlePage;

        if (($this->PageNo() > 1) || ($bDirUseTitlePage == false)) {
            //Go to 1.7 cm from bottom
            $this->SetY(-17);
            //Select Arial italic 8
            $this->SetFont($this->_Font, 'I', 8);
            //Print centered page number
            $iPageNumber = $this->PageNo();
            if ($bDirUseTitlePage) {
                $iPageNumber--;
            }
            $this->Cell(0, 10, _('Page') . ' ' . $iPageNumber . '    ' . date(SystemConfig::getValue("sDateTimeFormat"), time()), 0, 0, 'C');  // in 2.6.0, create a new config for time formatting also
        }
    }

    public function TitlePage()
    {
        global $sDirectoryDisclaimer;
        //Select Arial bold 15
        $this->SetFont($this->_Font, 'B', 15);

        if (is_readable(SystemConfig::getValue('bDirLetterHead'))) {
            $this->Image(SystemConfig::getValue('bDirLetterHead'), 10, 5, 190);
        }

        //Line break
        $this->Ln(5);
        //Move to the right
        $this->MultiCell(197, 10, "\n\n\n" . SystemConfig::getValue('sEntityName') . "\n\n" . _('Directory') . "\n\n", 0, 'C');
        $this->Ln(5);
        $today = date(SystemConfig::getValue("sDateFormatLong"));
        $this->MultiCell(197, 10, $today . "\n\n", 0, 'C');

        $sContact = sprintf("%s\n%s, %s  %s\n\n%s\n\n", SystemConfig::getValue('sEntityAddress'),
            SystemConfig::getValue('sEntityCity'),
            SystemConfig::getValue('sEntityState'), SystemConfig::getValue('sEntityZip'),
            SystemConfig::getValue('sEntityPhone'));
        $this->MultiCell(197, 10, $sContact, 0, 'C');
        $this->Cell(10);
        $this->MultiCell(197, 10, $sDirectoryDisclaimer, 0, 'C');
        $this->AddPage();
    }

    // Sets the character size
    // This changes the line height too
    public function Set_Char_Size($pt)
    {
        if ($pt > 3) {
            $this->_Char_Size = $pt;
            $this->SetFont($this->_Font, '', $this->_Char_Size);
        }
    }

    public function AddCustomField($order, $use)
    {
        $this->_Custom[$order] = $use;
    }

    public function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }

        return $nl;
    }

    public function Check_Lines($numlines, $fid, $pid)
    {
        // Need to determine if we will extend beyoned 17mm from the bottom of
        // the page.

        $h = 0; // check image height.  id will be zero if not included
        $famimg = SystemURLs::getDocumentRoot().'/Images/Family/' . $fid . '.png';
        if (file_exists($famimg)) {
            $s = getimagesize($famimg);
            $h = ($this->_ColWidth / $s[0]) * $s[1];
        }

        $persimg = SystemURLs::getDocumentRoot().'/Images/Person/' . $pid . '.png';
        if (file_exists($persimg)) {
            $s = getimagesize($persimg);
            $h = ($this->_ColWidth / $s[0]) * $s[1];
        }

//      if ($this->GetY() + $h + $numlines * 5 > $this->h - 27)
        if ($this->GetY() + $h + $numlines * $this->_LS > $this->h - 27) {
            // Next Column or Page
            if ($this->_Column == $this->_NCols - 1) {
                $this->_Column = 0;
                $this->AddPage();
                $this->SetY(25);
            } else {
                $this->_Column++;
                $this->SetY(25);
            }
        }
    }

    // This function prints out the heading when a letter
    // changes.
    public function Add_Header($sLetter)
    {
        $this->Check_Lines(2, 0, 0);
        $this->SetTextColor(50);// color gray for the letter header//$this->SetTextColor(255);
        $this->SetFont($this->_Font, 'B', $this->_Char_Size);
        $_PosX = ($this->_Column * ($this->_ColWidth + $this->_Gutter)) + $this->_Margin_Left;
        $_PosY = $this->GetY();
        $this->SetXY($_PosX, $_PosY);
        $this->setFillColor(220,220,220);// light gray in background
        $this->Cell($this->_ColWidth, $this->_LS, $sLetter, 1, 1, 'C', 1);
        $this->SetTextColor(0);
        $this->SetFont($this->_Font, '', $this->_Char_Size);
        $this->SetY($this->GetY() + $this->_LS);
    }

    // This prints the family name in BOLD
    public function Print_Name($sName)
    {
        $this->SetFont("Helvetica", 'B', $this->_Char_Size);//'BU'
//        $_PosX = $this->_Column == 0 ? $this->_Margin_Left : $this->w - $this->_Margin_Left - $this->_ColWidth;
        $_PosX = ($this->_Column * ($this->_ColWidth + $this->_Gutter)) + $this->_Margin_Left;
        $_PosY = $this->GetY();
        $this->SetXY($_PosX, $_PosY);
//        $this->MultiCell($this->_ColWidth, 5, $sName);
        $this->Cell($this->_ColWidth, $this->_LS, $sName);
//        $this->SetY($_PosY + $this->NbLines($this->_ColWidth, $sName) * 5);
        $this->SetY($_PosY + $this->NbLines($this->_ColWidth, $sName) * $this->_LS);
        $this->SetFont($this->_Font, '', $this->_Char_Size);
    }

    public function sGetCustomString($rsCustomFields, $aRow)
    {
        $numCustomFields = $rsCustomFields->count();
        if ($numCustomFields > 0) {
            extract($aRow);

            $rawQry = PersonCustomQuery::create();
            foreach ($rsCustomFields as $customfield) {
                $rawQry->withColumn($customfield->getCustomField());
            }

            if (!is_null($rawQry->findOneByPerId($per_ID))) {
                $aCustomData = $rawQry->findOneByPerId($per_ID)->toArray();
            }

            $OutStr = '';
            foreach ($rsCustomFields as $rsCustomField) {
                $sCustom = 'bCustom' . $rsCustomField->getCustomOrder();
                if ($this->_Custom[$rsCustomField->getCustomOrder()]) {
                    $currentFieldData = OutputUtils::displayCustomField($rsCustomField->getTypeId(), $aCustomData[$rsCustomField->getCustomField()], $rsCustomField->getCustomSpecial(), false);

//                    $currentFieldData = trim($aCustomData[$custom_Field]);
                    if ($currentFieldData != '') {
                        $OutStr .= '   ' . $rsCustomField->getCustomName() . ': ' . $currentFieldData .= "\n";
                    }
                }
            }

            return $OutStr;
        } else {
            return '';
        }
    }

    public function getBirthdayString($bDirBirthday, $per_BirthMonth, $per_BirthDay, $per_BirthYear, $per_Flags)
    {
        if ($bDirBirthday && $per_BirthMonth && $per_BirthDay) {
            $formatString = SystemConfig::getValue("sDateFormatShort");
            if (!$per_BirthYear || $per_Flags) {  // if the year is not present, or the user does not want year shown
                $formatString = preg_replace('/(\W?[C|g|Y|y]\W?)/i', '', $formatString); //remove any Year data from the format string
            }
            return date($formatString, mktime(0, 0, 0, $per_BirthMonth, $per_BirthDay, $per_BirthYear));
        } else {
            return '';
        }
    }

    // This function formats the string for the family info
    public function sGetFamilyString($aRow)
    {
        global $bDirFamilyPhone;
        global $bDirFamilyWork;
        global $bDirFamilyCell;
        global $bDirFamilyEmail;
        global $bDirWedding;
        global $bDirAddress;

        extract($aRow);

        $sFamilyStr = '';

        if ($bDirAddress) {
            //            if (strlen($fam_Address1)) { $sFamilyStr .= $fam_Address1 . "\n";  }
//            if (strlen($fam_Address2)) { $sFamilyStr .= $fam_Address2 . "\n";  }
            if (strlen($fam_Address1)) {
                $sFamilyStr .= $fam_Address1;
            }
            if (strlen($fam_Address2)) {
                $sFamilyStr .= '  ' . $fam_Address2;
            }
            $sFamilyStr .= "\n";
            if (strlen($fam_City)) {
                $sFamilyStr .= $fam_City . ', ' . $fam_State . ' ' . $fam_Zip . "\n";
            }
        }

        if ($bDirFamilyPhone && strlen($fam_HomePhone)) {
            $sFamilyStr .= '   ' . _('Phone') . ': ' . MiscUtils::ExpandPhoneNumber($fam_HomePhone, $fam_Country, $bWierd) . "\n";
        }
        if ($bDirFamilyWork && strlen($fam_WorkPhone)) {
            $sFamilyStr .= '   ' . _('Work') . ': ' . MiscUtils::ExpandPhoneNumber($fam_WorkPhone, $fam_Country, $bWierd) . "\n";
        }
        if ($bDirFamilyCell && strlen($fam_CellPhone)) {
            $sFamilyStr .= '   ' . _('Cell') . ': ' . MiscUtils::ExpandPhoneNumber($fam_CellPhone, $fam_Country, $bWierd) . "\n";
        }
        if ($bDirFamilyEmail && strlen($fam_Email)) {
            $sFamilyStr .= '   ' . _('Email') . ': ' . $fam_Email . "\n";
        }
        if ($bDirWedding && ($fam_WeddingDate > 0)) {
            $sFamilyStr .= '   ' . _('Wedding') . ': ' . date(SystemConfig::getValue("sDateFormatShort"), strtotime($fam_WeddingDate)) . "\n";
        }

        return $sFamilyStr;
    }

    // This function formats the string for the head of household.
    // NOTE: This is used for the Head AND Spouse (called twice)
    public function sGetHeadString($rsCustomFields, $aHead)
    {
        global $bDirBirthday;
        global $bDirPersonalPhone;
        global $bDirPersonalWork;
        global $bDirPersonalCell;
        global $bDirPersonalEmail;
        global $bDirPersonalWorkEmail;

        extract($aHead);

        $sHeadStr = '';

        if (strlen($per_LastName) && (strtolower($per_LastName) != strtolower($this->sLastName))) {
            $bDifferentLastName = true;
        } else {
            $bDifferentLastName = false;
        }

        // First time build with last name, second time append spouse name.
        if (strlen($this->sRecordName)) {
            $this->sRecordName .= ' ' . _('and') . ' ' . $per_FirstName;
            if ($bDifferentLastName) {
                $this->sRecordName .= ' ' . $per_LastName;
            }
            if (strlen($per_Suffix)) {
                $this->sRecordName .= ' ' . $per_Suffix;
            }
        } else {
            $this->sRecordName = $this->sLastName . ', ' . $per_FirstName;
            if ($bDifferentLastName) {
                $this->sRecordName .= ' ' . $per_LastName;
            }
            if (strlen($per_Suffix)) {
                $this->sRecordName .= ' ' . $per_Suffix;
            }
        }

        $sHeadStr .= $per_FirstName;
        if ($bDifferentLastName) {
            $sHeadStr .= ' ' . $per_LastName;
        }
        if (strlen($per_Suffix)) {
            $sHeadStr .= ' ' . $per_Suffix;
        }

        $iTempLen = strlen($sHeadStr);

        $sHeadStr .= " " . $this->getBirthdayString($bDirBirthday, $per_BirthMonth, $per_BirthDay, $per_BirthYear, $per_Flags) . "\n";

        $sCountry = MiscUtils::SelectWhichInfo($per_Country, $fam_Country, false);

        if ($bDirPersonalPhone && strlen($per_HomePhone)) {
            $TempStr = MiscUtils::ExpandPhoneNumber($per_HomePhone, $sCountry, $bWierd);
            $sHeadStr .= '   ' . _('Phone') . ': ' . $TempStr .= "\n";
        }
        if ($bDirPersonalWork && strlen($per_WorkPhone)) {
            $TempStr = MiscUtils::ExpandPhoneNumber($per_WorkPhone, $sCountry, $bWierd);
            $sHeadStr .= '   ' . _('Work') . ': ' . $TempStr .= "\n";
        }
        if ($bDirPersonalCell && strlen($per_CellPhone)) {
            $TempStr = MiscUtils::ExpandPhoneNumber($per_CellPhone, $sCountry, $bWierd);
            $sHeadStr .= '   ' . _('Cell') . ': ' . $TempStr .= "\n";
        }
        if ($bDirPersonalEmail && strlen($per_Email)) {
            $sHeadStr .= '   ' . _('Email') . ': ' . $per_Email .= "\n";
        }
        if ($bDirPersonalWorkEmail && strlen($per_WorkEmail)) {
            $sHeadStr .= '   ' . _('Work/Other Email') . ': ' . $per_WorkEmail .= "\n";
        }

        $sHeadStr .= $this->sGetCustomString($rsCustomFields, $aHead);

        // If there is no additional information for either head or spouse, there is no
        // need to print the name in the sublist, they are already are in the heading.
        if (strlen($sHeadStr) == $iTempLen) {
            return '';
        } else {
            return $sHeadStr;
        }
    }

    // This function formats the string for other family member records
    public function sGetMemberString($aRow)
    {
        global $bDirPersonalPhone;
        global $bDirPersonalWork;
        global $bDirPersonalCell;
        global $bDirPersonalEmail;
        global $bDirPersonalWorkEmail;
        global $bDirBirthday;
        global $aChildren;

        extract($aRow);

        $sMemberStr = $per_FirstName;

        // Check to see if family member has different last name
        if (strlen($per_LastName) && ($per_LastName != $this->sLastName)) {
            $sMemberStr .= ' ' . $per_LastName;
        }
        if (strlen($per_Suffix)) {
            $sMemberStr .= ' ' . $per_Suffix;
        }

        $sMemberStr .= " " . $this->getBirthdayString($bDirBirthday, $per_BirthMonth, $per_BirthDay, $per_BirthYear, $per_Flags) . "\n";

        $sCountry = MiscUtils::SelectWhichInfo($per_Country, $fam_Country, false);

        if ($bDirPersonalPhone && strlen($per_HomePhone)) {
            $TempStr = MiscUtils::ExpandPhoneNumber($per_HomePhone, $sCountry, $bWierd);
            $sMemberStr .= '   ' . _('Phone') . ': ' . $TempStr .= "\n";
        }
        if ($bDirPersonalWork && strlen($per_WorkPhone)) {
            $TempStr = MiscUtils::ExpandPhoneNumber($per_WorkPhone, $sCountry, $bWierd);
            $sMemberStr .= '   ' . _('Work') . ': ' . $TempStr .= "\n";
        }
        if ($bDirPersonalCell && strlen($per_CellPhone)) {
            $TempStr = MiscUtils::ExpandPhoneNumber($per_CellPhone, $sCountry, $bWierd);
            $sMemberStr .= '   ' . _('Cell') . ': ' . $TempStr .= "\n";
        }
        if ($bDirPersonalEmail && strlen($per_Email)) {
            $sMemberStr .= '   ' . _('Email') . ': ' . $per_Email .= "\n";
        }
        if ($bDirPersonalWorkEmail && strlen($per_WorkEmail)) {
            $sMemberStr .= '   ' . _('Work/Other Email') . ': ' . $per_WorkEmail .= "\n";
        }

        return $sMemberStr;
    }

    // Number of lines is only for the $text parameter
    public function Add_Record($sName, $text, $numlines, $fid, $pid)
    {
        $this->Check_Lines($numlines, $fid, $pid);

        $this->Print_Name( $sName);

        $_PosX = ($this->_Column * ($this->_ColWidth + $this->_Gutter)) + $this->_Margin_Left;
        $_PosY = $this->GetY();

        $this->SetXY($_PosX, $_PosY);

        $dirimg = '';
        $famimg = SystemURLs::getDocumentRoot().'/Images/Family/' . $fid . '.png';
        if (file_exists($famimg)) {
            $dirimg = $famimg;
        }

        $perimg = SystemURLs::getDocumentRoot().'/Images/Person/' . $pid . '.png';
        if (file_exists($perimg)) {
            $dirimg = $perimg;
        }

        if ($dirimg != '') {
            $s = getimagesize($dirimg);
            $h = ($this->_ColWidth / $s[0]) * $s[1];
            $_PosY += 2;
            $this->Image($dirimg, $_PosX, $_PosY, $this->_ColWidth);
            $this->SetXY($_PosX, $_PosY + $h + 2);
        }

        $this->setCellHeightRatio(0.92);
        $this->MultiCell($this->_ColWidth, $this->_LS, $text, 0, 'L');
        $this->SetY($this->GetY() + $this->_LS);
    }
}
