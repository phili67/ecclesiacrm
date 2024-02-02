<?php
/*******************************************************************************
*
*  filename    : Reports/ConfirmReport.php
*  last change : 2024-01-31 Philippe Logel
*  description : Creates a PDF with all the confirmation letters asking member
*                families to verify the information in the database.

******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReportTCPDF;
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
use EcclesiaCRM\Utils\InputUtils;
use Propel\Runtime\ActiveQuery\Criteria;

class PDF_ConfirmReport extends ChurchInfoReportTCPDF
{
    private $incrY;
    public $leftX;
    public $_PersonCustom;

    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);
        $this->leftX = 10;
        $this->incrY = SystemConfig::getValue('incrementY') + 0.5;
        $this->SetFont('Times', '', 10);
        $this->SetMargins(10, 20);

        $this->SetAutoPageBreak(false);
    }

    public function AddPersonCustomField($order, $use)
    {
        $this->_PersonCustom[$order] = $use;
    }

    public function GetPersonCustomField($order) {
        return $this->_PersonCustom[$order];
    }

    public function StartNewPage($ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $type)
    {
        $curY = $this->StartLetterPage($ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, 'graphic', $type);
        $curY += 2 * $this->incrY;
        $blurb = SystemConfig::getValue('sConfirm1');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
        $curY += 2 * $this->incrY;

        return $curY;
    }

    public function FinishPage($curY)
    {
        if (SystemConfig::getValue('sConfirm2') != '') {
            $curY += 1 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm2'));
        }

        if (SystemConfig::getValue('sConfirm3') != '') {
            $curY += 2 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm3'));
        }

        if (SystemConfig::getValue('sConfirm4') != '') {
            $curY += 2 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm4'));
        }

        if (SystemConfig::getValue('sConfirm5') != '') {
            $curY += 3 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm5'));
            $curY += 2 * $this->incrY;
        }
        if (SystemConfig::getValue('sConfirm6') != '') {
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm6'));
        }
        //If the Reports Settings Menu's SystemConfig::getValue("sConfirmSigner") is set, then display the closing statement.  Hide it otherwise.
        if (SystemConfig::getValue('sConfirmSigner')) {
            $curY += 2 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSincerely').',');
            $curY += 1 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSigner'));
        }
    }
}

if (!SessionUser::getUser()->isCreateDirectoryEnabled()) {
    RedirectUtils::Redirect('v2/dashboard');
    exit;
}

if (isset($_SESSION['POST_Datas'])) {
    $_POST = $_SESSION['POST_Datas'];
    unset($_SESSION['POST_Datas']);
}

$exportType = 'family';

if (isset($_POST['letterandlabelsnamingmethod'])) {
    $exportType = $_POST['letterandlabelsnamingmethod'];
}

$minAge = 18;
if (isset($_POST['minAge'])) {
    $minAge = InputUtils::FilterInt($_POST['minAge']);
}

$maxAge = 130;
if (isset($_POST['maxAge'])) {
    $maxAge = InputUtils::FilterInt($_POST['maxAge']);
}

$classList = "*";
if (isset($_POST['classList'])) {
    $classList = $_POST['classList'];
}

// Instantiate the directory class and build the report.
$pdf = new PDF_ConfirmReport();
$filename = 'ConfirmReport'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf';

// Get the list of custom person fields
$ormPersonCustomFields = PersonCustomMasterQuery::create()
    ->orderByCustomOrder()
    ->find();

$numCustomFields = $ormPersonCustomFields->count();

$sPersonCustomFieldName = [];
$sPersonCustomFieldTypeID = [];

if ( $ormPersonCustomFields->count() > 0) {
    $iFieldNum = 0;
    foreach ($ormPersonCustomFields as $customField) {
        $sPersonCustomFieldName[$iFieldNum] = $customField->getCustomName();
        $sPersonCustomFieldTypeID[$iFieldNum] = $customField->getTypeId();
        $iFieldNum+=1;

        $pdf->AddPersonCustomField( $customField->getCustomOrder(), isset($_POST["bCustomPerson".$customField->getCustomOrder()]) );
    }
}


$ormFamilies = FamilyQuery::create();
$ormFamilies->orderByName();

if ($_GET['familyId']) {
    $families = explode(",", $_GET['familyId']);
    $ormFamilies->filterById($families);
}

$ormFamilies->filterByDateDeactivated(NULL);

// Get all the families
$ormFamilies->find();

$dataCol = 55;
$dataWid = 65;

//$arr = $ormFamilies->toArray();

// Loop through families

$incrYAdd = 0.0;// +0.5
$fontSize = 8;

$incrY = SystemConfig::getValue('incrementY')+$incrYAdd;

$cnt = 0;

foreach ($ormFamilies as $family) {
    //If this is a report for a single family, name the file accordingly.
    if ($_GET['familyId']) {
        $filename = 'ConfirmReport-'.$family->getName().'.pdf';
    }

    if ($exportType == "family") {
        $cnt += 1;
        $curY = $pdf->StartNewPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(),$family->getState(), $family->getZip(), $family->getCountry(), $exportType);
        $curY += $incrY;
    
        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Family Name'));
        $pdf->SetFont('Times', '', $fontSize);
        $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getName());
        $curY += $incrY;
        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 1'));
        $pdf->SetFont('Times', '', $fontSize);
        $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress1());
        $curY += $incrY;
        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('City, State, Zip'));
        $pdf->SetFont('Times', '', $fontSize);
        $pdf->WriteAtCell($dataCol, $curY, $dataWid, ($family->getCity().', '.$family->getState().'  '.$family->getZip()));
        $curY += $incrY;
        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 2'));
        $pdf->SetFont('Times', '', $fontSize);
        $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress2());
        $curY += $incrY;
        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Home Phone'));
        $pdf->SetFont('Times', '', $fontSize);
        $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getHomePhone());
        $curY += $incrY;
        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Send Newsletter'));
        $pdf->SetFont('Times', '', $fontSize);

        $pdf->WriteAtCell($dataCol, $curY, $dataWid, "");
        if ($family->getSendNewsletter() == 'FALSE') {
            $pdf->CheckBox('newsletterFamily'.$family->getId(), 5, false, array(), array(), 'No', $dataCol, $curY);
        } else {
            $pdf->CheckBox('newsletterFamily'.$family->getId(), 5, true, array(), array(), 'Yes', $dataCol, $curY);
        }
        
        $curY += $incrY;

        // Missing the following information from the Family record:
        // Wedding date (if present) - need to figure how to do this with sensitivity
        // Family e-mail address

        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Anniversary Date'));
        $pdf->SetFont('Times', '', $fontSize);
        $pdf->WriteAtCell($dataCol, $curY, $dataWid, OutputUtils::FormatDate((!is_null($family->getWeddingdate())?$family->getWeddingdate()->format('Y-m-d'):'')));
        $curY += $incrY;

        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Family Email'));
        $pdf->SetFont('Times', '', $fontSize);
        $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getEmail());
        $curY += $incrY;
        $curY += $incrY;
    }

    //Get the family members for this family
    $ormFamilyMembers = PersonQuery::create()
        ->filterByDateDeactivated(NULL)
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
        ->orderByFmrId();

    if ($classList != "*") {
        $ormFamilyMembers->filterByClsId($classList);
    }

    if ($minAge != 0 or $maxAge != 130) {
        $ormFamilyMembers->where('DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL ' . $minAge . ' YEAR) <= CURDATE() AND DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL (' . $maxAge . '+1) YEAR) >= CURDATE()');
    }

    $ormFamilyMembers->find();

    if ($ormFamilyMembers->count() == 0) {
        $member = PersonQuery::create()
            ->filterByDateDeactivated(NULL)
            ->filterByClsId($classList)
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
            ->where('DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL ' . $minAge . ' YEAR) <= CURDATE() AND DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL (' . $maxAge . '+1) YEAR) >= CURDATE()')
        ->findOneByFamId($family->getId());
        $ormFamilyMembers = [$member];
    }

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

    $numFamilyMembers = 0;
    
    foreach ($ormFamilyMembers as $fMember) {
        if (is_null ($fMember)) continue;

        $numFamilyMembers++;    // add one to the people count

        // Make sure the person data will display with adequate room for the trailer and group information
        if (($curY + $numCustomFields * $incrY) > 260 and $exportType == "family") {
            $curY = $pdf->StartLetterPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(), $family->getState(), $family->getZip(), $family->getCountry(), "", $exportType);            
        } else if ($exportType == "person") {
            $cnt += 1;
            $curY = $pdf->StartNewPage($fMember->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(),$family->getState(), $family->getZip(), $family->getCountry(), $exportType);

            $curY += $incrY;  

            // place the first table
            $pdf->SetFont('Times', 'B', $fontSize);
            $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Name'));
            $pdf->SetFont('Times', '', $fontSize);
            $pdf->WriteAtCell($dataCol, $curY, $dataWid, $fMember->getlastName());
            $curY += $incrY;
            // place the first table
            $pdf->SetFont('Times', 'B', $fontSize);
            $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('First Name'));
            $pdf->SetFont('Times', '', $fontSize);
            $pdf->WriteAtCell($dataCol, $curY, $dataWid, $fMember->getFirstName());
            $curY += $incrY;
            $pdf->SetFont('Times', 'B', $fontSize);
            $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 1'));
            $pdf->SetFont('Times', '', $fontSize);
            $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress1());
            $curY += $incrY;
            $pdf->SetFont('Times', 'B', $fontSize);
            $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('City, State, Zip'));
            $pdf->SetFont('Times', '', $fontSize);
            $pdf->WriteAtCell($dataCol, $curY, $dataWid, ($family->getCity().', '.$family->getState().'  '.$family->getZip()));
            $curY += $incrY;
            $pdf->SetFont('Times', 'B', $fontSize);
            $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 2'));
            $pdf->SetFont('Times', '', $fontSize);
            $pdf->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress2());
            $curY += $incrY;
            $pdf->SetFont('Times', 'B', $fontSize);
            $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Home Phone'));
            $pdf->SetFont('Times', '', $fontSize);
            $pdf->WriteAtCell($dataCol, $curY, $dataWid, $fMember->getHomePhone());

            // Missing the following information from the Family record:
            // Wedding date (if present) - need to figure how to do this with sensitivity
            // Family e-mail address
            if ($fMember->getFmrId() == 1 or $fMember->getFmrId() == 2) {
                $curY += $incrY;    
                $pdf->SetFont('Times', 'B', $fontSize);
                $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Anniversary Date'));
                $pdf->SetFont('Times', '', $fontSize);
                $pdf->WriteAtCell($dataCol, $curY, $dataWid, OutputUtils::FormatDate((!is_null($family->getWeddingdate())?$family->getWeddingdate()->format('Y-m-d'):'')));
                $curY += $incrY;
            }

            $curY += $incrY;    
            $curY += $incrY;
        }

        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell($XName, $curY, $XGender - $XName, _('Member Name'));
        $pdf->WriteAtCell($XGender, $curY, $XRole - $XGender, _('M/F'));
        $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Adult/Child'));
        $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, _('Email'));
        $pdf->WriteAtCell($XBirthday, $curY, $XHideAge - $XBirthday, _('Birthday'));
        $pdf->SetFont('Times', 'B', 5);
        $pdf->WriteAtCell($XHideAge, $curY, $XCellPhone - $XHideAge, _('Hide Age'), "LTR");
        $pdf->SetFont('Times', 'B', $fontSize);            
        $pdf->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, substr(_('Cell phone'),0,10).".");
        $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, _('Work Phone'));
        $pdf->SetFont('Times', '', $fontSize);
        $curY += $incrY;

        $iPersonID = $fMember->getId();
        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell($XName, $curY, $XGender - $XName, $fMember->getFirstName().' '.$fMember->getMiddleName().' '.$fMember->getLastName());
        $pdf->SetFont('Times', '', $fontSize);
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
        $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, $fMember->getWorkPhone());

        $curY += $incrY;
        $curY += $incrY;
        
        // Missing the following information for the personal record: ??? Is this the place to put this data ???
        // Work Phone
        $pdf->SetFont('Times', 'B', $fontSize);
        $pdf->WriteAtCell($XName, $curY, $XEmail - $XGender, _('Send Newsletter'), "0");
        $pdf->WriteAtCell($XGender, $curY, $XBirthday - $XEmail, "", "0");
        if ($fMember->getSendNewsletter() == 'FALSE') {
            $pdf->CheckBox('newsletterPerson'.$fMember->getId(), 5, false, array(), array(), 'No', $XGender, $curY);
        } else {
            $pdf->CheckBox('newsletterPerson'.$fMember->getId(), 5, true, array(), array(), 'Yes', $XGender, $curY);
        }

        
        $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Classification'), "0", "R");
        $pdf->SetFont('Times', '', $fontSize);
        $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, $fMember->getClassName(), "0");

        $curY += $incrY;
        $curY += $incrY;

        // *** All custom fields ***
        // Get the list of custom person fields

        $xSize = 40;
        if ($numCustomFields > 0) {
            // Get the custom field data for this person.
            $rawQry = PersonCustomQuery::create();
            foreach ($ormPersonCustomFields as $custField) {
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
            foreach ($ormPersonCustomFields as $custField) {

                if ($pdf->GetPersonCustomField($custField->getCustomOrder()) == 0) continue;

                if ($sPersonCustomFieldName[$custField->getCustomOrder() - 1]) {
                    $currentFieldData = trim($aCustomData[$custField->getCustomField()]);

                    $currentFieldData = OutputUtils::displayCustomField($custField->getTypeId(), trim($aCustomData[$custField->getCustomField()]), $custField->getCustomSpecial(), false);

                    if ($sPersonCustomFieldTypeID[$custField->getCustomOrder() - 1] == 1) {
                        $pdf->SetFont('Times', 'B', $fontSize);
                        $pdf->WriteAtCell($xInc, $curY, $xSize, $sPersonCustomFieldName[$custField->getCustomOrder() - 1]);
                        $pdf->SetFont('Times', '', $fontSize);
                        $pdf->WriteAtCell($xInc + $xSize, $curY, $xSize, "");
                        if (is_null($currentFieldData) or $currentFieldData  == '' or $currentFieldData == 'FALSE') {
                            $pdf->CheckBox('props'.$custField->getId(), 5, false, array(), array(), 'No', $xInc + $xSize, $curY);
                        } else {
                            $pdf->CheckBox('props'.$custField->getId(), 5, true, array(), array(), 'Yes', $xInc + $xSize, $curY);
                        }
                    } else {                    
                        $OutStr = $sPersonCustomFieldName[$custField->getCustomOrder() - 1].' : '.$currentFieldData.'    ';
                        $pdf->SetFont('Times', 'B', $fontSize);
                        $pdf->WriteAtCell($xInc, $curY, $xSize, $sPersonCustomFieldName[$custField->getCustomOrder() - 1]);

                        $pdf->SetFont('Times', '', $fontSize);
                        if ($currentFieldData == '') {
                            $pdf->WriteAtCell($xInc + $xSize, $curY, $xSize, '');                        
                        } else {
                            $pdf->WriteAtCell($xInc + $xSize, $curY, $xSize, $currentFieldData);
                        }
                    }                    
                    
                    $numWide += 1;    // increment the number of columns done
                    $xInc += (2 * $xSize);    // Increment the X position by about 1/2 page width
                    if (($numWide % 2) == 0) { // 2 columns
                        $xInc = $XName;    // Reset margin
                        $curY += $incrY;
                    }
                }
            }
            //$pdf->WriteAt($XName,$curY,$OutStr);
            //$curY += (2 * SystemConfig::getValue("incrementY"));
        }
        $curY += 2 * $incrY;

        if ($exportType == "person") {
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
                ->where('person2group2role_p2g2r.PersonId = '.$fMember->getId())
                ->orderByName()
                ->find();


            if ($ormAssignedGroups->count() > 0) {
                $groupStr = _("Assigned groups for")." ".$fMember->getFirstName().' '.$fMember->getLastName().': ';

                foreach ($ormAssignedGroups as $group) {
                    $groupStr .= $group->getName().' ('._($group->getRoleName()).') ';
                }
                $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $groupStr);
                $curY += 2 * $incrY;
            }

            $pdf->FinishPage($curY);
        }
    }
    //

    $curY += $incrY;

    if (($curY + 2 * $numFamilyMembers * $incrY) >= 260 and $exportType == "family") {
        $curY = $pdf->StartLetterPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(), $family->getState(), $family->getZip(), $family->getCountry(), "", $exportType);
    }

    if ($exportType == "family") {
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
                $curY += 2 * $incrY;
            }

        }

        if ($curY > 183) {    // This insures the trailer information fits continuously on the page (3 inches of "footer"
            $curY = $pdf->StartLetterPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(), $family->getState(), $family->getZip(), $family->getCountry(), '', $exportType);
        }
        $pdf->FinishPage($curY);
    }
}

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
ob_end_clean();
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output($filename, 'D');
} else {
    $pdf->Output();
}