<?php
/*******************************************************************************
*
*  filename    : Reports/GroupReport.php
*  last change : 2003-09-09
*  description : Creates a group-member directory
*
*  http://www.ecclesiacrm.com/
*  Copyright 2003  Chris Gebhardt, Jason York

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\PDF_GroupDirectory;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\GroupPropMasterQuery;
use Propel\Runtime\Propel;


$bOnlyCartMembers = $_POST['OnlyCart'];
$iGroupID = InputUtils::LegacyFilterInput($_POST['GroupID'], 'int');
$iMode = InputUtils::LegacyFilterInput($_POST['ReportModel'], 'int');

if ($iMode == 1) {
    $iRoleID = InputUtils::LegacyFilterInput($_POST['GroupRole'], 'int');
} else {
    $iRoleID = 0;
}

    $connection = Propel::getConnection();


    // Get the group name
    $group = GroupQuery::Create()->findOneById ($iGroupID);
    $sGroupName  = $group->getName();
    $iRoleListID = $group->getRoleListId();

    // Get the selected role name
    if ($iRoleID > 0) {
        $list      = ListOptionQuery::Create()->filterById ($iRoleListID)->findOneByOptionId($iRoleID);
        $sRoleName = $list->getOptionName();
    } elseif (isset($_POST['GroupRoleEnable'])) {
        $lists = ListOptionQuery::Create()->findById ($iRoleListID);

        foreach ($lists as $list) {
          $aRoleNames[$list->getOptionId()] = $list->getOptionName();
        }
    }

    $pdf = new PDF_GroupDirectory($sGroupName,$sRoleName);

    // See if this group has special properties.
    $props = GroupPropMasterQuery::Create()->orderByPropId()->findByGroupId ($iGroupID);
    $bHasProps = ($props->count() > 0);

    $sSQL = 'SELECT * FROM person_per
      LEFT JOIN family_fam ON per_fam_ID = fam_ID ';

    if ($bHasProps) {
        $sSQL .= 'LEFT JOIN groupprop_'.$iGroupID.' ON groupprop_'.$iGroupID.'.per_ID = person_per.per_ID ';
    }

    $sSQL .= 'LEFT JOIN person2group2role_p2g2r ON p2g2r_per_ID = person_per.per_ID
      WHERE p2g2r_grp_ID = '.$iGroupID;

    if ($iRoleID > 0) {
        $sSQL .= ' AND p2g2r_rle_ID = '.$iRoleID;
    }

    if ($bOnlyCartMembers && count($_SESSION['aPeopleCart']) > 0) {
        $sSQL .= ' AND person_per.per_ID IN ('.Cart::ConvertCartToString($_SESSION['aPeopleCart']).')';
    }

    $sSQL .= ' ORDER BY per_LastName';

    $statement = $connection->prepare($sSQL);
    $statement->execute();

    // This is used for the headings for the letter changes.
    // Start out with something that isn't a letter to force the first one to work
    // $sLastLetter = "0";

    while ($aRow = $statement->fetch( \PDO::FETCH_BOTH )) {
        $OutStr = '';

        $pdf->sFamily = OutputUtils::FormatFullName($aRow['per_Title'], $aRow['per_FirstName'], $aRow['per_MiddleName'], $aRow['per_LastName'], $aRow['per_Suffix'], 3);

        MiscUtils::SelectWhichAddress($sAddress1, $sAddress2, $aRow['per_Address1'], $aRow['per_Address2'], $aRow['fam_Address1'], $aRow['fam_Address2'], false);

        $sCity = MiscUtils::SelectWhichInfo($aRow['per_City'], $aRow['fam_City'], false);
        $sState = MiscUtils::SelectWhichInfo($aRow['per_State'], $aRow['fam_State'], false);
        $sZip = MiscUtils::SelectWhichInfo($aRow['per_Zip'], $aRow['fam_Zip'], false);
        $sHomePhone = MiscUtils::SelectWhichInfo($aRow['per_HomePhone'], $aRow['fam_HomePhone'], false);
        $sWorkPhone = MiscUtils::SelectWhichInfo($aRow['per_WorkPhone'], $aRow['fam_WorkPhone'], false);
        $sCellPhone = MiscUtils::SelectWhichInfo($aRow['per_CellPhone'], $aRow['fam_CellPhone'], false);
        $sEmail = MiscUtils::SelectWhichInfo($aRow['per_Email'], $aRow['fam_Email'], false);

        if (isset($_POST['GroupRoleEnable'])) {
            $OutStr = _('Role').': '.$aRoleNames[$aRow['p2g2r_rle_ID']]."\n";
        }

        if (isset($_POST['AddressEnable'])) {
            if (strlen($sAddress1)) {
                $OutStr .= $sAddress1."\n";
            }
            if (strlen($sAddress2)) {
                $OutStr .= $sAddress2."\n";
            }
            if (strlen($sCity)) {
                $OutStr .= $sCity.', '.$sState.' '.$sZip."\n";
            }
        }

        if (isset($_POST['HomePhoneEnable']) && strlen($sHomePhone)) {
            $TempStr = MiscUtils::ExpandPhoneNumber($sHomePhone, SystemConfig::getValue('sDefaultCountry'), $bWierd);
            $OutStr .= '  '._('Phone').': '.$TempStr."\n";
        }

        if (isset($_POST['WorkPhoneEnable']) && strlen($sWorkPhone)) {
            $TempStr = MiscUtils::ExpandPhoneNumber($sWorkPhone, SystemConfig::getValue('sDefaultCountry'), $bWierd);
            $OutStr .= '  '._('Work').': '.$TempStr."\n";
        }

        if (isset($_POST['CellPhoneEnable']) && strlen($sCellPhone)) {
            $TempStr = MiscUtils::ExpandPhoneNumber($sCellPhone, SystemConfig::getValue('sDefaultCountry'), $bWierd);
            $OutStr .= '  '._('Cell').': '.$TempStr."\n";
        }

        if (isset($_POST['EmailEnable']) && strlen($sEmail)) {
            $OutStr .= '  '._('Email').': '.$sEmail."\n";
        }

        if (isset($_POST['OtherEmailEnable']) && strlen($aRow['per_WorkEmail'])) {
            $OutStr .= '  '._('Other Email').': '.$aRow['per_WorkEmail'] .= "\n";
        }

        if ($bHasProps) {
            foreach ($props as $prop) {
                if (isset($_POST[$prop->getField().'enable'])) {
                    $currentData = trim($aRow[$prop->getField()]);
                    if (!empty($currentData)) {
                      $OutStr .= $prop->getName().': '.OutputUtils::displayCustomField($prop->getTypeId(), $currentData, $prop->getSpecial(),false)."\n";
                    }
                }
            }
        }

        // Count the number of lines in the output string
        $numlines = 1;
        $offset = 0;
        while ($result = strpos($OutStr, "\n", $offset)) {
            $offset = $result + 1;
            $numlines++;
        }

        //if ($numlines > 1)
        //{
        /* if (strtoupper($sLastLetter) != strtoupper(mb_substr($pdf->sFamily,0,1)))
        {
            $pdf->Check_Lines($numlines+2);
            $sLastLetter = strtoupper(mb_substr($pdf->sFamily,0,1));
            $pdf->Add_Header($sLastLetter);
        } */
        $pdf->Add_Record($pdf->sFamily, $OutStr, $numlines);
        // }
    }

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
ob_end_clean();
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('GroupDirectory-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
