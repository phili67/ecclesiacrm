<?php
/*******************************************************************************
*
*  filename    : Reports/ConfirmReport.php
*  last change : 2020-10-04 Philippe Logel
*  description : Creates a PDF with all the confirmation letters asking member
*                families to verify the information in the database.

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReport;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Base\PersonCustomQuery;
use EcclesiaCRM\GroupQuery;

use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\GroupTableMap;

use Propel\Runtime\ActiveQuery\Criteria;

class PDF_ConfirmReport extends ChurchInfoReport
{
    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);
        $this->leftX = 10;
        $this->SetFont('Times', '', 10);
        $this->SetMargins(10, 20);

        $this->SetAutoPageBreak(false);
    }

    public function StartNewPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country)
    {
        $curY = $this->StartLetterPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, 'graphic');
        $curY += 2 * SystemConfig::getValue('incrementY');
        $blurb = SystemConfig::getValue('sConfirm1');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
        $curY += 2 * SystemConfig::getValue('incrementY');

        return $curY;
    }

    public function FinishPage($curY)
    {
        $curY += 2 * SystemConfig::getValue('incrementY');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm2'));

        $curY += 3 * SystemConfig::getValue('incrementY');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm3'));
        $curY += 2 * SystemConfig::getValue('incrementY');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm4'));

        if (SystemConfig::getValue('sConfirm5') != '') {
            $curY += 2 * SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm5'));
            $curY += 2 * SystemConfig::getValue('incrementY');
        }
        if (SystemConfig::getValue('sConfirm6') != '') {
            $curY += 2 * SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm6'));
        }
        //If the Reports Settings Menu's SystemConfig::getValue("sConfirmSigner") is set, then display the closing statement.  Hide it otherwise.
        if (SystemConfig::getValue('sConfirmSigner')) {
            $curY += 4 * SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSincerely').',');
            $curY += 4 * SystemConfig::getValue('incrementY');
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSigner'));
        }
    }
}

if (!SessionUser::getUser()->isCreateDirectoryEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

// Instantiate the directory class and build the report.
$pdf = new PDF_ConfirmReport();
$filename = 'ConfirmReport'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf';

// Get the list of custom person fields
$ormCustomFields = PersonCustomMasterQuery::create()
    ->orderByCustomOrder()
    ->find();

$numCustomFields = $ormCustomFields->count();

$sCustomFieldName = [];

if ( $ormCustomFields->count() > 0) {
    $iFieldNum = 0;
    foreach ($ormCustomFields as $customField) {
        $sCustomFieldName[$iFieldNum] = $customField->getCustomName();
        $iFieldNum+=1;
    }
}


$ormFamilies = FamilyQuery::create();

if ($_GET['familyId']) {
    $families = explode(",", $_GET['familyId']);
    $ormFamilies->filterById($families);
}

// Get all the families
$ormFamilies->orderByName()->find();

$sSubQuery = ' 1 ';
if ($_GET['familyId']) {
    $sSubQuery = ' fam_id in ('.$_GET['familyId'].') ';
}

$dataCol = 55;
$dataWid = 65;

// Loop through families
foreach ($ormFamilies as $family) {
    //If this is a report for a single family, name the file accordingly.
    if ($_GET['familyId']) {
        $filename = 'ConfirmReport-'.$family->getName().'.pdf';
    }

    $curY = $pdf->StartNewPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(),
        $family->getState(), $family->getZip(), $family->getCountry());
    $curY += SystemConfig::getValue('incrementY');

    $pdf->SetFont('Times', 'B', 10);
    $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Family Name'));
    $pdf->SetFont('Times', '', 10);
    $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getName());
    $curY += SystemConfig::getValue('incrementY');
    $pdf->SetFont('Times', 'B', 10);
    $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 1'));
    $pdf->SetFont('Times', '', 10);
    $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress1());
    $curY += SystemConfig::getValue('incrementY');
    $pdf->SetFont('Times', 'B', 10);
    $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 2'));
    $pdf->SetFont('Times', '', 10);
    $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress2());
    $curY += SystemConfig::getValue('incrementY');
    $pdf->SetFont('Times', 'B', 10);
    $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('City, State, Zip'));
    $pdf->SetFont('Times', '', 10);
    $pdf->WriteAtCell($dataCol, $curY, $dataWid, ($family->getCity().', '.$family->getState().'  '.$family->getZip()));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->SetFont('Times', 'B', 10);
    $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Home Phone'));
    $pdf->SetFont('Times', '', 10);
    $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getHomePhone());
    $curY += SystemConfig::getValue('incrementY');
    $pdf->SetFont('Times', 'B', 10);
    $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Send Newsletter'));
    $pdf->SetFont('Times', '', 10);
    $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getSendNewsletter());
    $curY += SystemConfig::getValue('incrementY');

    // Missing the following information from the Family record:
    // Wedding date (if present) - need to figure how to do this with sensitivity
    // Family e-mail address

    $pdf->SetFont('Times', 'B', 10);
    $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Anniversary Date'));
    $pdf->SetFont('Times', '', 10);
    $pdf->WriteAtCell($dataCol, $curY, $dataWid, OutputUtils::FormatDate((!is_null($family->getWeddingdate())?$family->getWeddingdate()->format('Y-m-d'):'')));
    $curY += SystemConfig::getValue('incrementY');

    $pdf->SetFont('Times', 'B', 10);
    $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Family Email'));
    $pdf->SetFont('Times', '', 10);
    $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getEmail());
    $curY += SystemConfig::getValue('incrementY');
    $curY += SystemConfig::getValue('incrementY');

    //Get the family members for this family
    $ormFamilyMembers = PersonQuery::create()
        ->addAlias('cls', ListOptionTableMap::TABLE_NAME)
        ->addMultipleJoin(array(
                array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONID)),
                array(ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_ID), 1)
            )
            , Criteria::LEFT_JOIN)
        ->addAsColumn('ClassName', ListOptionTableMap::alias('cls', ListOptionTableMap::COL_LST_OPTIONNAME))
        ->addAlias('fmr', ListOptionTableMap::TABLE_NAME)
        ->addMultipleJoin(array(
                array(PersonTableMap::COL_PER_FMR_ID, ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONID)),
                array(ListOptionTableMap::Alias("fmr",ListOptionTableMap::COL_LST_ID), 2)
            )
            , Criteria::LEFT_JOIN)
        ->addAsColumn('FamRole', ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONNAME))
        ->filterByFamId($family->getId())
        ->orderByFmrId()
        ->find();

    $XName = 10;
    $XGender = 40;
    $XRole = 50;
    $XEmail = 80;
    $XBirthday = 125;
    $XHideAge = 145;
    $XCellPhone = 155;
    $XClassification = 180;
    $XWorkPhone = 155;
    $XRight = 208;

    $pdf->SetFont('Times', 'B', 8);
    $pdf->WriteAtCell($XName, $curY, $XGender - $XName, _('Member Name'));
    $pdf->WriteAtCell($XGender, $curY, $XRole - $XGender, _('M/F'));
    $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Adult/Child'));
    $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, _('Email'));
    $pdf->WriteAtCell($XBirthday, $curY, $XHideAge - $XBirthday, _('Birthday'));
    $pdf->WriteAtCell($XHideAge, $curY, $XCellPhone - $XHideAge, substr(_('Hide Age'),0,5));
    $pdf->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, substr(_('Cell phone'),0,13).".");
    $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, _('Member/Friend'));
    $pdf->SetFont('Times', '', 10);
    $curY += SystemConfig::getValue('incrementY');

    $numFamilyMembers = 0;
    //while ($aMember = mysqli_fetch_array($rsFamilyMembers)) {
    foreach ($ormFamilyMembers as $fMember) {
        $numFamilyMembers++;    // add one to the people count

        // Make sure the person data will display with adequate room for the trailer and group information
        if (($curY + $numCustomFields * SystemConfig::getValue('incrementY')) > 260) {
            $curY = $pdf->StartLetterPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(), $family->getState(), $family->getZip(), $family->getCountry());
            $pdf->SetFont('Times', 'B', 8);
            $pdf->WriteAtCell($XName, $curY, $XGender - $XName, _('Member Name'));
            $pdf->WriteAtCell($XGender, $curY, $XRole - $XGender, _('M/F'));
            $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Adult/Child'));
            $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, _('Email'));
            $pdf->WriteAtCell($XBirthday, $curY, $XHideAge - $XBirthday, _('Birthday'));
            $pdf->WriteAtCell($XHideAge, $curY, $XCellPhone - $XHideAge, substr(_('Hide Age'),0,5));
            $pdf->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, substr(_('Cell phone'),0,15).".");
            $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, _('Member/Friend'));
            $pdf->SetFont('Times', '', 10);
            $curY += SystemConfig::getValue('incrementY');
        }
        $iPersonID = $fMember->getId();
        $pdf->SetFont('Times', 'B', 8);
        $pdf->WriteAtCell($XName, $curY, $XGender - $XName, $fMember->getFirstName().' '.$fMember->getMiddleName().' '.$fMember->getLastName());
        $pdf->SetFont('Times', '', 8);
        $genderStr = ($fMember->getGender() == 1 ? 'M' : 'F');
        $pdf->WriteAtCell($XGender, $curY, $XRole - $XGender, $genderStr);
        $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, $fMember->getFamRole());
        $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, $fMember->getEmail());
        if ($fMember->getBirthYear()) {
            $theDate = new DateTime($fMember->getBirthYear().'-'.$fMember->getBirthMonth().'-'.$fMember->getBirthDay(), new DateTimeZone(SystemConfig::getValue('sTimeZone')));
            $birthdayStr = $theDate->format(SystemConfig::getValue("sDatePickerFormat"));
        } elseif ($fMember->getBirthMonth()) {
            $birthdayStr = $fMember->getBirthMonth().'-'.$fMember->getBirthDay();
        } else {
            $birthdayStr = '';
        }
        //If the "HideAge" check box is true, then create a Yes/No representation of the check box.
        if ($fMember->getFlags()) {
            $hideAgeStr = _('Yes');
        } else {
            $hideAgeStr = _('No');
        }

        $pdf->WriteAtCell($XBirthday, $curY, $XHideAge - $XBirthday, $birthdayStr);
        $pdf->WriteAtCell($XHideAge, $curY, $XCellPhone - $XHideAge, $hideAgeStr);
        $pdf->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, $fMember->getCellPhone());
        $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, $fMember->getClassName());
        $curY += SystemConfig::getValue('incrementY');
        // Missing the following information for the personal record: ??? Is this the place to put this data ???
        // Work Phone
        $pdf->WriteAtCell($XWorkPhone, $curY, $XRight - $XWorkPhone, _('Work Phone').':'.$fMember->getWorkPhone());
        $curY += SystemConfig::getValue('incrementY');
        $curY += SystemConfig::getValue('incrementY');

        // *** All custom fields ***
        // Get the list of custom person fields

        $xSize = 40;
        if ($numCustomFields > 0) {
            // Get the custom field data for this person.
            $rawQry = PersonCustomQuery::create();
            foreach ($ormCustomFields as $custField) {
                $rawQry->withColumn($custField->getCustomField());
            }

            if (!is_null($rawQry->findOneByPerId($iPersonID))) {
                $aCustomData = $rawQry->findOneByPerId($iPersonID)->toArray();
            }

            //$numCustomData = $aCustomData);
            $OutStr = '';
            $xInc = $XName;    // Set the starting column for Custom fields
            // Here is where we determine if space is available on the current page to
            // display the custom data and still get the ending on the page
            // Calculations (without groups) show 84 mm is needed.
            // For the Letter size of 279 mm, this says that curY can be no bigger than 195 mm.
            // Leaving 12 mm for a bottom margin yields 183 mm.
            $numWide = 0;    // starting value for columns
            foreach ($ormCustomFields as $custField) {
                if ($sCustomFieldName[$custField->getCustomOrder() - 1]) {
                    $currentFieldData = trim($aCustomData[$custField->getCustomField()]);

                    $currentFieldData = OutputUtils::displayCustomField($custField->getTypeId(), trim($aCustomData[$custField->getCustomField()]), $custField->getCustomSpecial(), false);

                    $OutStr = $sCustomFieldName[$custField->getCustomOrder() - 1].' : '.$currentFieldData.'    ';
                    $pdf->WriteAtCell($xInc, $curY, $xSize, $sCustomFieldName[$custField->getCustomOrder() - 1]);
                    if ($currentFieldData == '') {
                        $pdf->SetFont('Times', 'B', 6);
                        $pdf->WriteAtCell($xInc + $xSize, $curY, $xSize, '');
                        $pdf->SetFont('Times', '', 10);
                    } else {
                        $pdf->WriteAtCell($xInc + $xSize, $curY, $xSize, $currentFieldData);
                    }
                    $numWide += 1;    // increment the number of columns done
                    $xInc += (2 * $xSize);    // Increment the X position by about 1/2 page width
                    if (($numWide % 2) == 0) { // 2 columns
                        $xInc = $XName;    // Reset margin
                        $curY += SystemConfig::getValue('incrementY');
                    }
                }
            }
            //$pdf->WriteAt($XName,$curY,$OutStr);
            //$curY += (2 * SystemConfig::getValue("incrementY"));
        }
        $curY += 2 * SystemConfig::getValue('incrementY');
    }
    //

    $curY += SystemConfig::getValue('incrementY');

    if (($curY + 2 * $numFamilyMembers * SystemConfig::getValue('incrementY')) >= 260) {
        $curY = $pdf->StartLetterPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(), $family->getState(), $family->getZip(), $family->getCountry());
    }

    $ormFamilyMembers = PersonQuery::create()
        ->filterByFamId($family->getId())
        ->orderByFmrId()
        ->find();

    foreach ($ormFamilyMembers as $aMember) {
        // Get the Groups this Person is assigned to
        $ormAssignedGroups = GroupQuery::create()
            ->leftJoinPerson2group2roleP2g2r()
            ->withColumn('person2group2role_p2g2r.PersonId', 'memberCount')
            ->addAlias('role', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array('person2group2role_p2g2r.RoleId', ListOptionTableMap::alias('role', ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("role",ListOptionTableMap::COL_LST_ID), GroupTableMap::COL_GRP_ROLELISTID)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('RoleName', ListOptionTableMap::alias('role', ListOptionTableMap::COL_LST_OPTIONNAME))
            ->where('person2group2role_p2g2r.PersonId = '.$aMember->getId())
            ->orderByName()
            ->find();


        if ($ormAssignedGroups->count() > 0) {
            $groupStr = _("Assigned groups for")." ".$aMember->getFirstName().' '.$aMember->getLastName().': ';

            foreach ($ormAssignedGroups as $group) {
                $groupStr .= $group->getName().' ('._($group->getRoleName()).') ';
            }
            $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $groupStr);
            $curY += 2 * SystemConfig::getValue('incrementY');
        }

    }

    if ($curY > 183) {    // This insures the trailer information fits continuously on the page (3 inches of "footer"
        $curY = $pdf->StartLetterPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(), $family->getState(), $family->getZip(), $family->getCountry());
    }
    $pdf->FinishPage($curY);
}

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output($filename, 'D');
} else {
    $pdf->Output();
}
