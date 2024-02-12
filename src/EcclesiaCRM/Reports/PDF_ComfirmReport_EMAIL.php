<?php

/*******************************************************************************
 *
 *  filename    : EcclesiaCRM/Reports/PDF_ComfirmReport_EMAIL.php
 *  last change : 2024-01-31 Philippe Logel
 *  description : Create emails with all the confirmation letters asking member
 *                families to verify the information in the database.
 *
 *  Test : http://url/Reports/ConfirmReportEmail.php?familyId=274
 *
 ******************************************************************************/

namespace EcclesiaCRM\Reports;

use EcclesiaCRM\Reports\ChurchInfoReportTCPDF;
use EcclesiaCRM\Emails\FamilyVerificationEmail;
use EcclesiaCRM\Emails\PersonVerificationEmail;

use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\Base\FamilyCustomMasterQuery;
use EcclesiaCRM\Base\FamilyCustomQuery;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Token;
use EcclesiaCRM\TokenPassword;
use EcclesiaCRM\TokenQuery;

use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Service\ConfirmReportService;
use Propel\Runtime\ActiveQuery\Criteria;

class EmailPDF_ConfirmReport extends ChurchInfoReportTCPDF
{
    private $incrY;
    private $leftX;
    public $_PersonCustom;
    public $_FamilyCustom;
    
    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);
        $this->leftX = 10;
        $this->SetFont('Times', '', 10);
        $this->SetMargins(10, 20);

        $this->SetAutoPageBreak(false);

        $this->incrY = SystemConfig::getValue('incrementY') + 0.5;
    }

    public function AddPersonCustomField($order, $use)
    {
        $this->_PersonCustom[(int)$order] = $use;
    }

    public function GetPersonCustomField($order) {
        if (!array_key_exists($order, $this->_PersonCustom)) return  0;

        return $this->_PersonCustom[$order];
    }

    public function AddFamilyCustomField($order, $use)
    {
        $this->_FamilyCustom[(int)$order] = $use;
    }

    public function GetFamilyCustomField($order) {
        if (!array_key_exists($order, $this->_FamilyCustom)) return  0;

        return $this->_FamilyCustom[$order];
    }

    private function StartNewPageFamily($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country)
    {
        $curY = $this->StartLetterPage($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country);
        $curY += 2 * $this->incrY;
        $blurb = SystemConfig::getValue('sConfirm1');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
        $curY += 2 * $this->incrY;

        return $curY;
    }

    private function StartNewPagePerson($person_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country)
    {
        $curY = $this->StartLetterPage($person_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, '', 'person');
        $curY += 2 * $this->incrY;
        $blurb = SystemConfig::getValue('sConfirm1');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
        $curY += 2 * $this->incrY;

        return $curY;
    }

    public function StartNewPage ($id, $fam_name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $type='family') {
        if ($type == 'family') {
            $curY = $this->StartNewPageFamily($id, $fam_name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country);
        } else {
            $curY = $this->StartNewPagePerson($id, $fam_name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country);
        }
        return $curY;
    }

    public function FinishPage($curY)
    {
        $curY += 2 * $this->incrY;
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm2'));

        $curY += 3 * $this->incrY;
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm3'));
        $curY += 2 * $this->incrY;
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm4'));

        if (SystemConfig::getValue('sConfirm5') != '') {
            $curY += 1 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm5'));
            $curY += 1 * $this->incrY;
        }
        if (SystemConfig::getValue('sConfirm6') != '') {
            $curY += 1 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm6'));
        }

        $curY += 2 * $this->incrY;
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSincerely') . ",");
        $curY += 1 * $this->incrY;
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSigner'));
    }
}

class EmailUsers
{
    private $familiesEmailed;
    private $personsEmailed;
    private $fams;
    private $persons;
    private $incrY;
    private $curY;
    private $numPersonCustomFields;
    private $ormPersonCustomFields;
    private $sPersonCustomFieldTypeID;
    private $sPersonCustomFieldName;
    private $numFamilyCustomFields;
    private $ormFamilyCustomFields;
    private $sFamilyCustomFieldTypeID;
    private $sFamilyCustomFieldName;
    private $dataCol;
    private $dataWid;
    private $count_email;
    private $fontSize;
    private $pdf;


    // Constructor
    public function __construct($fams = NULL, $persons = NULL, $fontSize = 8)
    {
        $this->incrY = SystemConfig::getValue('incrementY') + 0;//0.5;
        $this->familiesEmailed = $this->personsEmailed = 0;
        $this->fams = $fams;
        $this->persons = $persons;
        $this->fontSize = $fontSize;
        $this->personsEmailed = [];
        $this->familiesEmailed = [];

        // Get the list of custom person fields
        $this->ormPersonCustomFields = PersonCustomMasterQuery::create()
            ->orderByCustomOrder()
            ->find();

        $this->numPersonCustomFields = $this->ormPersonCustomFields->count();

        if ($this->numPersonCustomFields > 0) {
            $iFieldNum = 0;
            foreach ($this->ormPersonCustomFields as $ormCustomField) {
                $this->sPersonCustomFieldName[$iFieldNum] = $ormCustomField->getCustomName();
                $this->sPersonCustomFieldTypeID[$iFieldNum] = $ormCustomField->getTypeId();
                $iFieldNum += 1;
            }
        }

        // Get the list of custom family fields
        $this->ormFamilyCustomFields = FamilyCustomMasterQuery::create()
            ->orderByCustomOrder()
            ->find();

        $this->numFamilyCustomFields = $this->ormFamilyCustomFields->count();

        if ($this->numFamilyCustomFields > 0) {
            $iFieldNum = 0;
            foreach ($this->ormFamilyCustomFields as $ormCustomField) {
                $this->sFamilyCustomFieldName[$iFieldNum] = $ormCustomField->getCustomName();
                $this->sFamilyCustomFieldTypeID[$iFieldNum] = $ormCustomField->getTypeId();
                $iFieldNum += 1;
            }
        }
    }

    
    private function renderFamily ($fam, $minAge, $maxAge, $classList) : array {
        $personCustomFields = ConfirmReportService::getSelectedCustomPersonFields();
        $familyCustomFields = ConfirmReportService::getSelectedCustomFamilyFields();

        foreach ($personCustomFields as $customField) {
            $this->pdf->AddPersonCustomField($customField['order'], $customField['custom']);
        }

        foreach ($familyCustomFields as $customField) {
            $this->pdf->AddFamilyCustomField($customField['order'], $customField['custom']);
        }

        $emaillist = [];

        $this->curY = $this->pdf->StartNewPage($fam->getId(), $fam->getName(), $fam->getAddress1(), $fam->getAddress2(), $fam->getCity(), $fam->getState(), $fam->getZip(), $fam->getCountry(), 'family');
        $this->curY += $this->incrY;

        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Family Name'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $fam->getName());
        $this->curY += $this->incrY;
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Address') . ' 1');
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $fam->getAddress1());
        $this->curY += $this->incrY;
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Address') . ' 2');
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $fam->getAddress2());
        $this->curY += $this->incrY;
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('City, State, Zip'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, ($fam->getCity() . ', ' . $fam->getState() . '  ' . $fam->getZip()));
        $this->curY += $this->incrY;
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Home Phone'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $fam->getHomePhone());
        $this->curY += $this->incrY;
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Send Newsletter'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $fam->getSendNewsLetter());
        $this->curY += $this->incrY;

        // Missing the following information from the Family record:
        // Wedding date (if present) - need to figure how to do this with sensitivity
        // Family e-mail address

        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Anniversary Date'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, (!is_null($fam->getWeddingDate())?OutputUtils::FormatDate($fam->getWeddingDate()->format('Y-m-d')):""));
        $this->curY += $this->incrY;

        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Family Email'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $fam->getEmail());
        $this->curY += $this->incrY;

        // family custom fields :         
        $rawQry = FamilyCustomQuery::create();
        foreach ($this->ormFamilyCustomFields as $customField) {
            $rawQry->withColumn($customField->getCustomField());
        }

        if (!is_null($rawQry->findOneByFamId($fam->getId()))) {
            $aCustomData = $rawQry->findOneByFamId($fam->getId())->toArray();
        }

        foreach ($this->ormFamilyCustomFields as $customField) {
            if ($this->pdf->GetFamilyCustomField($customField->getCustomOrder()) == 0) continue;

            if ($this->sFamilyCustomFieldName[$customField->getCustomOrder() - 1]) {
                $currentFieldData = trim($aCustomData[$customField->getCustomField()]);

                $currentFieldData = OutputUtils::displayCustomField($customField->getTypeId(), trim($aCustomData[$customField->getCustomField()]), $customField->getCustomSpecial(), false);

                $this->pdf->SetFont('Times', 'B', $this->fontSize);
                $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), $this->sFamilyCustomFieldName[$customField->getCustomOrder() - 1]);
                $this->pdf->SetFont('Times', '', $this->fontSize);
                
                if ($currentFieldData == '') {
                    $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $fam->getEmail());
                } else {
                    $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $currentFieldData);                
                }
                $this->curY += $this->incrY;
            }

        }
        $this->curY += $this->incrY;
        $this->curY += $this->incrY;

        if (!empty($fam_Email)) {
            array_push($emaillist, $fam_Email);
        }

        $this->curY += $this->incrY;
        $this->curY += $this->incrY;


        $ormFamilyMembers = PersonQuery::create()
            ->addAlias('cls', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_ID), 1)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('ClassName', ListOptionTableMap::alias('cls', ListOptionTableMap::COL_LST_OPTIONNAME))
            ->addAlias('fmr', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array(PersonTableMap::COL_PER_FMR_ID, ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("fmr", ListOptionTableMap::COL_LST_ID), 2)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('FamRole', ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONNAME))
            ->filterByFamId($fam->getId())
            ->orderByFmrId();

        if ($classList != "*") {
            $ormFamilyMembers->filterByClsId($classList);
        }
    
        if ($minAge != 0 or $maxAge != 130) {
            $ormFamilyMembers->where('DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL ' . $minAge . ' YEAR) <= CURDATE() AND DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL (' . $maxAge . '+1) YEAR) >= CURDATE()');
        }
    
        $ormFamilyMembers->find();

        $XName = 10;
        $XGender = 50;
        $XRole = 60;
        $XEmail = 90;
        $XBirthday = 135;
        $XCellPhone = 155;
        $XClassification = 180;
        $XWorkPhone = 155;
        $XRight = 208;

        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell($XName, $this->curY, $XGender - $XName, _('Member Name'));
        $this->pdf->WriteAtCell($XGender, $this->curY, $XRole - $XGender, _('M/F'));
        $this->pdf->WriteAtCell($XRole, $this->curY, $XEmail - $XRole, _('Adult/Child'));
        $this->pdf->WriteAtCell($XEmail, $this->curY, $XBirthday - $XEmail, _('Email'));
        $this->pdf->WriteAtCell($XBirthday, $this->curY, $XCellPhone - $XBirthday, _('Birthday'));
        $this->pdf->WriteAtCell($XCellPhone, $this->curY, $XClassification - $XCellPhone, _('Cell Phone'));
        $this->pdf->WriteAtCell($XClassification, $this->curY, $XRight - $XClassification, _('Member/Friend'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->curY += $this->incrY;        

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
                ->findOneByFamId($fam->getId());
            $ormFamilyMembers = [$member];
        }

        $numFamilyMembers = 0;

        foreach ($ormFamilyMembers as $aMember) {
            if (is_null ($aMember)) continue;

            $numFamilyMembers++; // add one to the people count
            // Make sure the person data will display with adequate room for the trailer and group information
            if (($this->curY + $this->numPersonCustomFields * $this->incrY) > 260) {
                $this->curY = $this->pdf->StartLetterPage($fam->getID(), $fam->getName(), $fam->getAddress1(), $fam->getAddress2(), $fam->getCity(), $fam->getState(), $fam->getZip(), $fam->getCountry());
                $this->pdf->SetFont('Times', 'B', $this->fontSize);
                $this->pdf->WriteAtCell($XName, $this->curY, $XGender - $XName, _('Member Name'));
                $this->pdf->WriteAtCell($XGender, $this->curY, $XRole - $XGender, _('M/F'));
                $this->pdf->WriteAtCell($XRole, $this->curY, $XEmail - $XRole, _('Adult/Child'));
                $this->pdf->WriteAtCell($XEmail, $this->curY, $XBirthday - $XEmail, _('Email'));
                $this->pdf->WriteAtCell($XBirthday, $this->curY, $XCellPhone - $XBirthday, _('Birthday'));
                $this->pdf->WriteAtCell($XCellPhone, $this->curY, $XClassification - $XCellPhone, _('Cell Phone'));
                $this->pdf->WriteAtCell($XClassification, $this->curY, $XRight - $XClassification, _('Member/Friend'));
                $this->pdf->SetFont('Times', '', $this->fontSize);
                $this->curY += $this->incrY;
            }
            $iPersonID = $aMember->getId();
            $this->pdf->SetFont('Times', 'B', $this->fontSize);
            $this->pdf->WriteAtCell($XName, $this->curY, $XGender - $XName, $aMember->getFirstName() . ' ' . $aMember->getMiddleName() . ' ' . $aMember->getLastName());
            $this->pdf->SetFont('Times', '', $this->fontSize);
            $genderStr = ($aMember->getGender() == 1 ? 'M' : 'F');
            $this->pdf->WriteAtCell($XGender, $this->curY, $XRole - $XGender, $genderStr);
            $this->pdf->WriteAtCell($XRole, $this->curY, $XEmail - $XRole, $aMember->getFamRole());
            $this->pdf->WriteAtCell($XEmail, $this->curY, $XBirthday - $XEmail, $aMember->getEmail());
            if (!empty($aMember->getEmail())) {
                array_push($emaillist, $aMember->getEmail());
            }
            if ($aMember->getBirthYear()) {
                $birthdayStr = $aMember->getBirthMonth() . '/' . $aMember->getBirthDay() . '/' . $aMember->getBirthYear();
            } else {
                $birthdayStr = '';
            }
            $this->pdf->WriteAtCell($XBirthday, $this->curY, $XCellPhone - $XBirthday, $birthdayStr);
            $this->pdf->WriteAtCell($XCellPhone, $this->curY, $XClassification - $XCellPhone, $aMember->getCellPhone());
            $this->pdf->WriteAtCell($XClassification, $this->curY, $XRight - $XClassification, $aMember->getClassName());
            $this->curY += $this->incrY;
            // Missing the following information for the personal record: ??? Is this the place to put this data ???
            // Work Phone
            $this->pdf->WriteAtCell($XWorkPhone, $this->curY, $XRight - $XWorkPhone, _('Work Phone') . ':' . $aMember->getWorkPhone());
            $this->curY += $this->incrY;
            $this->curY += $this->incrY;

            // *** All custom fields ***
            // Get the list of custom person fields


            $xSize = 40;
            if ($this->numPersonCustomFields > 0) {

                $rawQry = PersonCustomQuery::create();
                foreach ($this->ormPersonCustomFields as $customField) {
                    $rawQry->withColumn($customField->getCustomField());
                }

                if (!is_null($rawQry->findOneByPerId($aMember->getId()))) {
                    $aCustomData = $rawQry->findOneByPerId($aMember->getId())->toArray();
                }

                $xInc = $XName; // Set the starting column for Custom fields
                // Here is where we determine if space is available on the current page to
                // display the custom data and still get the ending on the page
                // Calculations (without groups) show 84 mm is needed.
                // For the Letter size of 279 mm, this says that curY can be no bigger than 195 mm.
                // Leaving 12 mm for a bottom margin yields 183 mm.
                $numWide = 0; // starting value for columns

                foreach ($this->ormPersonCustomFields as $customField) {
                    if ($this->pdf->GetPersonCustomField($customField->getCustomOrder()) == 0) continue;

                    if ($this->sPersonCustomFieldName[$customField->getCustomOrder() - 1]) {
                        $currentFieldData = trim($aCustomData[$customField->getCustomField()]);

                        $currentFieldData = OutputUtils::displayCustomField($customField->getTypeId(), trim($aCustomData[$customField->getCustomField()]), $customField->getCustomSpecial(), false);

                        $OutStr = $this->sPersonCustomFieldName[$customField->getCustomOrder() - 1] . ' : ' . $currentFieldData . '    ';
                        $this->pdf->WriteAtCell($xInc, $this->curY, $xSize, $this->sPersonCustomFieldName[$customField->getCustomOrder() - 1]);
                        if ($currentFieldData == '') {
                            $this->pdf->SetFont('Times', 'B', $this->fontSize);
                            $this->pdf->WriteAtCell($xInc + $xSize, $this->curY, $xSize, '');
                            $this->pdf->SetFont('Times', '', $this->fontSize);
                        } else {
                            $this->pdf->WriteAtCell($xInc + $xSize, $this->curY, $xSize, $currentFieldData);
                        }
                        $numWide += 1; // increment the number of columns done
                        $xInc += (2 * $xSize); // Increment the X position by about 1/2 page width
                        if (($numWide % 2) == 0) { // 2 columns
                            $xInc = $XName; // Reset margin
                            $this->curY += $this->incrY;
                        }
                    }
                }

                //$this->pdf->WriteAt($XName,$this->curY,$OutStr);
                //$this->curY += (2 * SystemConfig::getValue("incrementY"));
            }
            $this->curY += 2 * $this->incrY;

            // Get the Groups this Person is assigned to
            $ormAssignedGroups = GroupQuery::create()
                ->leftJoinPerson2group2roleP2g2r()
                ->addAlias('role', ListOptionTableMap::TABLE_NAME)
                ->addMultipleJoin(array(
                        array('person2group2role_p2g2r.RoleId', ListOptionTableMap::alias('role', ListOptionTableMap::COL_LST_OPTIONID)),
                        array(ListOptionTableMap::Alias("role", ListOptionTableMap::COL_LST_ID), GroupTableMap::COL_GRP_ROLELISTID)
                    )
                    , Criteria::LEFT_JOIN)
                ->addAsColumn('RoleName', ListOptionTableMap::alias('role', ListOptionTableMap::COL_LST_OPTIONNAME))
                ->where('person2group2role_p2g2r.PersonId = ' . $aMember->getId())
                ->orderByName()
                ->find();

            if ($ormAssignedGroups->count() > 0) {
                $groupStr = _("Assigned groups for")." " . $aMember->getFirstName() . ' ' . $aMember->getLastName() . ': ';

                foreach ($ormAssignedGroups as $group) {
                    $groupStr .= $group->getName() . ' (' . _($group->getRoleName()) . ') ';
                }

                $this->pdf->WriteAt(SystemConfig::getValue('leftX'), $this->curY, $groupStr);
                $this->curY += 2 * $this->incrY;
            }
        }

        if ($this->curY > 183) { // This insures the trailer information fits continuously on the page (3 inches of "footer"
            $this->curY = $this->pdf->StartLetterPage($fam->getID(), $fam->getName(), $fam->getAddress1(), $fam->getAddress2(), $fam->getCity(), $fam->getState(), $fam->getZip(), $fam->getCountry());
        }  
        
        return $emaillist;
    }

    private function renderPerson ($person) {
        $customFields = ConfirmReportService::getSelectedCustomPersonFields();

        foreach ($customFields as $customField) {
            $this->pdf->AddPersonCustomField($customField['order'], $customField['custom']);
        }

        $family = FamilyQuery::create()->findOneById($person->getFamId());

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

        $cnt = 0;

        $cnt += 1;
        $this->curY = $this->pdf->StartNewPage($person->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(),$family->getState(), $family->getZip(), $family->getCountry(), 'person');

        $this->curY += $this->incrY;  

        // place the first table
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Name'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $person->getlastName());
        $this->curY += $this->incrY;
        // place the first table
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('First Name'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $person->getFirstName());
        $this->curY += $this->incrY;
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Address 1'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $family->getAddress1());
        $this->curY += $this->incrY;
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('City, State, Zip'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, ($family->getCity().', '.$family->getState().'  '.$family->getZip()));
        $this->curY += $this->incrY;
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Address 2'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $family->getAddress2());
        $this->curY += $this->incrY;
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Home Phone'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, $person->getHomePhone());

        // Missing the following information from the Family record:
        // Wedding date (if present) - need to figure how to do this with sensitivity
        // Family e-mail address
        if ($person->getFmrId() == 1 or $person->getFmrId() == 2) {
            $this->curY += $this->incrY;    
            $this->pdf->SetFont('Times', 'B', $this->fontSize);
            $this->pdf->WriteAtCell(SystemConfig::getValue('leftX'), $this->curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Anniversary Date'));
            $this->pdf->SetFont('Times', '', $this->fontSize);
            $this->pdf->WriteAtCell($this->dataCol, $this->curY, $this->dataWid, OutputUtils::FormatDate((!is_null($family->getWeddingdate())?$family->getWeddingdate()->format('Y-m-d'):'')));
            $this->curY += $this->incrY;
        }

        $this->curY += $this->incrY;    
        $this->curY += $this->incrY;        

        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell($XName, $this->curY, $XGender - $XName, _('Member Name'));
        $this->pdf->WriteAtCell($XGender, $this->curY, $XRole - $XGender, _('M/F'));
        $this->pdf->WriteAtCell($XRole, $this->curY, $XEmail - $XRole, _('Adult/Child'));
        $this->pdf->WriteAtCell($XEmail, $this->curY, $XBirthday - $XEmail, _('Email'));
        $this->pdf->WriteAtCell($XBirthday, $this->curY, $XHideAge - $XBirthday, _('Birthday'));
        $this->pdf->SetFont('Times', 'B', 5);
        $this->pdf->WriteAtCell($XHideAge, $this->curY, $XCellPhone - $XHideAge, _('Hide Age'), "LTR");
        $this->pdf->SetFont('Times', 'B', $this->fontSize);            
        $this->pdf->WriteAtCell($XCellPhone, $this->curY, $XClassification - $XCellPhone, substr(_('Cell phone'),0,10).".");
        $this->pdf->WriteAtCell($XClassification, $this->curY, $XRight - $XClassification, _('Work Phone'));
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->curY += $this->incrY;

        $iPersonID = $person->getId();
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell($XName, $this->curY, $XGender - $XName, $person->getFirstName().' '.$person->getMiddleName().' '.$person->getLastName());
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $genderStr = ($person->getGender() == 1 ? 'M' : 'F');
        $this->pdf->WriteAtCell($XGender, $this->curY, $XRole - $XGender, $genderStr);
        $this->pdf->WriteAtCell($XRole, $this->curY, $XEmail - $XRole, $person->getFamRole());
        $this->pdf->WriteAtCell($XEmail, $this->curY, $XBirthday - $XEmail, $person->getEmail());
        if ($person->getBirthYear()) {
            $theDate = new \DateTime($person->getBirthYear().'-'.$person->getBirthMonth().'-'.$person->getBirthDay(), new \DateTimeZone(SystemConfig::getValue('sTimeZone')));
            $birthdayStr = $theDate->format(SystemConfig::getValue("sDatePickerFormat"));
        } elseif ($person->getBirthMonth()) {
            $birthdayStr = $person->getBirthMonth().'-'.$person->getBirthDay();
        } else {
            $birthdayStr = '';
        }
        //If the "HideAge" check box is true, then create a Yes/No representation of the check box.
        if ($person->getFlags()) {
            $hideAgeStr = _('Yes');
        } else {
            $hideAgeStr = _('No');
        }

        $this->pdf->WriteAtCell($XBirthday, $this->curY, $XHideAge - $XBirthday, $birthdayStr);
        $this->pdf->WriteAtCell($XHideAge, $this->curY, $XCellPhone - $XHideAge, $hideAgeStr);
        $this->pdf->WriteAtCell($XCellPhone, $this->curY, $XClassification - $XCellPhone, $person->getCellPhone());
        $this->pdf->WriteAtCell($XClassification, $this->curY, $XRight - $XClassification, $person->getWorkPhone());

        $this->curY += $this->incrY;
        $this->curY += $this->incrY;
        
        // Missing the following information for the personal record: ??? Is this the place to put this data ???
        // Work Phone
        $this->pdf->SetFont('Times', 'B', $this->fontSize);
        $this->pdf->WriteAtCell($XName, $this->curY, $XEmail - $XGender, _('Send Newsletter'), "0");
        $this->pdf->WriteAtCell($XGender, $this->curY, $XBirthday - $XEmail, "", "0");
        if ($person->getSendNewsletter() == 'FALSE') {
            $this->pdf->CheckBox('newsletterPerson'.$person->getId(), 5, false, array(), array(), 'No', $XGender, $this->curY);
        } else {
            $this->pdf->CheckBox('newsletterPerson'.$person->getId(), 5, true, array(), array(), 'Yes', $XGender, $this->curY);
        }

        
        $this->pdf->WriteAtCell($XRole, $this->curY, $XEmail - $XRole, _('Classification'), "0", "R");
        $this->pdf->SetFont('Times', '', $this->fontSize);
        $this->pdf->WriteAtCell($XEmail, $this->curY, $XBirthday - $XEmail, $person->getClassName(), "0");

        $this->curY += $this->incrY;
        $this->curY += $this->incrY;

        // *** All custom fields ***
        // Get the list of custom person fields

        $xSize = 40;
        if ($this->numPersonCustomFields > 0) {
            // Get the custom field data for this person.
            $rawQry = PersonCustomQuery::create();
            foreach ($this->ormPersonCustomFields as $custField) {
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
            foreach ($this->ormPersonCustomFields as $custField) {

                if ($this->pdf->GetPersonCustomField($custField->getCustomOrder()) == 0) continue;

                if ($this->sPersonCustomFieldName[$custField->getCustomOrder() - 1]) {
                    $currentFieldData = trim($aCustomData[$custField->getCustomField()]);

                    $currentFieldData = OutputUtils::displayCustomField($custField->getTypeId(), trim($aCustomData[$custField->getCustomField()]), $custField->getCustomSpecial(), false);

                    if ($this->sPersonCustomFieldTypeID[$custField->getCustomOrder() - 1] == 1) {
                        $this->pdf->SetFont('Times', 'B', $this->fontSize);
                        $this->pdf->WriteAtCell($xInc, $this->curY, $xSize, $this->sPersonCustomFieldName[$custField->getCustomOrder() - 1]);
                        $this->pdf->SetFont('Times', '', $this->fontSize);
                        $this->pdf->WriteAtCell($xInc + $xSize, $this->curY, $xSize, "");
                        if (is_null($currentFieldData) or $currentFieldData  == '' or $currentFieldData == 'FALSE') {
                            $this->pdf->CheckBox('props'.$custField->getId(), 5, false, array(), array(), 'No', $xInc + $xSize, $this->curY);
                        } else {
                            $this->pdf->CheckBox('props'.$custField->getId(), 5, true, array(), array(), 'Yes', $xInc + $xSize, $this->curY);
                        }
                    } else {                    
                        $OutStr = $this->sPersonCustomFieldName[$custField->getCustomOrder() - 1].' : '.$currentFieldData.'    ';
                        $this->pdf->SetFont('Times', 'B', $this->fontSize);
                        $this->pdf->WriteAtCell($xInc, $this->curY, $xSize, $this->sPersonCustomFieldName[$custField->getCustomOrder() - 1]);

                        $this->pdf->SetFont('Times', '', $this->fontSize);
                        if ($currentFieldData == '') {
                            $this->pdf->WriteAtCell($xInc + $xSize, $this->curY, $xSize, '');                        
                        } else {
                            $this->pdf->WriteAtCell($xInc + $xSize, $this->curY, $xSize, $currentFieldData);
                        }
                    }                    
                    
                    $numWide += 1;    // increment the number of columns done
                    $xInc += (2 * $xSize);    // Increment the X position by about 1/2 page width
                    if (($numWide % 2) == 0) { // 2 columns
                        $xInc = $XName;    // Reset margin
                        $this->curY += $this->incrY;
                    }
                }
            }
            //$this->pdf->WriteAt($XName,$this->curY,$OutStr);
            //$this->curY += (2 * SystemConfig::getValue("incrementY"));
        }
        $this->curY += 2 * $this->incrY;

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
                ->where('person2group2role_p2g2r.PersonId = '.$person->getId())
                ->orderByName()
                ->find();


        if ($ormAssignedGroups->count() > 0) {
            $groupStr = _("Assigned groups for")." ".$person->getFirstName().' '.$person->getLastName().': ';

            foreach ($ormAssignedGroups as $group) {
                $groupStr .= $group->getName().' ('._($group->getRoleName()).') ';
            }
            $this->pdf->WriteAt(SystemConfig::getValue('leftX'), $this->curY, $groupStr);
            $this->curY += 2 * $this->incrY;
        }        
    }

    //
    // by default : 
    //
    // - the ages are chosen between 0 to 130
    // - all classList : "*"
    //
    public function renderAndSend($exportType, $minAge=0, $maxAge=130, $classList="*")
    {
        if ($exportType == 'family') {
            LoggerUtils::getAppLogger()->info("start : mailing to families");
            $familyEmailSent = false;

            // Get all the families not deactivated
            $ormFamilies = FamilyQuery::create()
                ->usePersonQuery()
                   ->filterByEmail('', \Propel\Runtime\ActiveQuery\Criteria::NOT_EQUAL)
                ->endUse()
                ->filterByDateDeactivated(NULL)
                ->groupById()
                ->orderByName();

            if ( !is_null($this->fams) ) {
                $ormFamilies->filterById($this->fams);
            }

            $ormFamilies->find();

            $this->dataCol = 55;
            $this->dataWid = 65;

            $this->count_email = 1;
            $this->familiesEmailed = 0;
            foreach ($ormFamilies as $fam) {
                // Instantiate the directory class and build the report.
                $this->pdf = new EmailPDF_ConfirmReport();
                $emaillist = $this->renderFamily($fam, $minAge, $maxAge, $classList);
                $this->pdf->FinishPage($this->curY);

                if (count($emaillist) > 0) {
                    // now we are pending for a confirmation from the family !!! The Done
                    $family = FamilyQuery::create()->findOneById($fam->getId());
                    $family->setConfirmReport('Pending');
                    $family->save();

                    header('Pragma: public');  // Needed for IE when using a shared SSL certificate

                    ob_end_clean();
                    $doc = $this->pdf->Output('ConfirmReportEmail-' . $fam->getID() . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.pdf', 'S');

                    $subject = $fam->getName() . ' Family Information Review';

                    if ($_GET['updated']) {
                        $subject .= $subject . ' ** Updated **';
                    }

                    /* this part is for google condition : https://support.google.com/mail/answer/81126 */
                    if ($this->count_email >= 0 and $this->count_email < 30) {
                        $sleepTime = 5;
                    } elseif ($this->count_email >= 30 and $this->count_email < 60) {
                        $sleepTime = 4;
                    } elseif ($this->count_email >= 60 and $this->count_email < 90) {
                        $sleepTime = 3;
                    } elseif ($this->count_email >= 90 and $this->count_email < 120) {
                        $sleepTime = 2;
                    } elseif ($this->count_email >= 120 and $this->count_email < 150) {
                        $sleepTime = 1;
                    } else {
                        $sleepTime = 0;
                    }

                    $this->count_email++;

                    if ($this->count_email > 1) {
                        sleep($sleepTime);
                    }
                    /* end of part : https://support.google.com/mail/answer/81126 */                    

                    TokenQuery::create()->filterByType("verifyFamily")->filterByReferenceId($fam->getId())->delete();
                    $token = new Token();
                    $token->build("verifyFamily", $fam->getId());
                    $token->save();

                    $tokenPassword = new TokenPassword();

                    $password = MiscUtils::random_password(8);

                    $tokenPassword->setTokenId($token->getPrimaryKey());
                    $tokenPassword->setPassword(md5($password));
                    $tokenPassword->setMustChangePwd(false);

                    $tokenPassword->save();

                    // we search the headPeople
                    $headPeople = $fam->getHeadPeople();

                    $emails = [];

                    foreach ($headPeople as $headPerson) {
                        $emails[] = $headPerson->getEmail();
                    }

                    // in the case there isn't any headPeople
                    if (count($emails) == 0) {
                        $emails = $fam->getEmails();
                    }

                    LoggerUtils::getAppLogger()->info("family ".$this->count_email. " : ".$fam->getName()." STime : ".$sleepTime . "mail : ".$emails[0]);

                    $mail = new FamilyVerificationEmail($emails, $fam->getName(), $token->getToken(), $emails, $password);
                    $filename = 'ConfirmReportEmail-' . $fam->getName() . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.pdf';
                    $mail->addStringAttachment($doc, $filename);

                    if (($familyEmailSent = $mail->send())) {
                        $this->familiesEmailed = $this->familiesEmailed + 1;
                    } else {
                        LoggerUtils::getAppLogger()->error($mail->getError());
                    }
                }
            }

            LoggerUtils::getAppLogger()->info("end : mailing to families ");

            return $this->familiesEmailed;
        } else if ($exportType = 'person') {
            LoggerUtils::getAppLogger()->info("start : mailing to persons");
            $familyEmailSent = false;

            // Get all the families not deactivated
            $persons = PersonQuery::create()
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
                ->orderByFmrId();

            if ($classList != "*") {
                $persons->filterByClsId($classList);
            }

            if ($minAge != 0 or $maxAge != 130) {
                $persons->where('DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL ' . $minAge . ' YEAR) <= CURDATE() AND DATE_ADD(CONCAT('.PersonTableMap::COL_PER_BIRTHYEAR.',"-",'.PersonTableMap::COL_PER_BIRTHMONTH.',"-",'.PersonTableMap::COL_PER_BIRTHDAY.'),INTERVAL (' . $maxAge . '+1) YEAR) >= CURDATE()');
            }
            

            if ( !is_null($this->persons) ) {
                $persons->filterById($this->persons);
            }

            $persons->find();

            $this->dataCol = 55;
            $this->dataWid = 65;

            $this->count_email = 1;
            foreach ($persons as $person) {
                // Instantiate the directory class and build the report.
                $this->pdf = new EmailPDF_ConfirmReport();
                $personEmailSent = $this->renderPerson($person);
                $this->pdf->FinishPage($this->curY);    

                // end of pdf page !!!
                
                if ($person->getEmail() != '') {
                    $emaillist = [$person->getEmail()];
                } else {
                    $emaillist = [$person->getFamily()->getEmail()];
                }

                if (count($emaillist) > 0) {
                    // now we are pending for a confirmation from the person !!! The Done
                    $per = PersonQuery::create()->findOneById($person->getId());
                    $per->setConfirmReport('Pending');
                    $per->save();

                    header('Pragma: public');  // Needed for IE when using a shared SSL certificate

                    ob_end_clean();
                    $doc = $this->pdf->Output('ConfirmReportEmail-' . $person->getID() . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.pdf', 'S');

                    $subject = $person->getLastName() . ' Person Information Review';

                    if ($_GET['updated']) {
                        $subject .= $subject . ' ** Updated **';
                    }

                    /* this part is for google condition : https://support.google.com/mail/answer/81126 */
                    if ($this->count_email >= 0 and $this->count_email < 30) {
                        $sleepTime = 5;
                    } elseif ($this->count_email >= 30 and $this->count_email < 60) {
                        $sleepTime = 4;
                    } elseif ($this->count_email >= 60 and $this->count_email < 90) {
                        $sleepTime = 3;
                    } elseif ($this->count_email >= 90 and $this->count_email < 120) {
                        $sleepTime = 2;
                    } elseif ($this->count_email >= 120 and $this->count_email < 150) {
                        $sleepTime = 1;
                    } else {
                        $sleepTime = 0;
                    }

                    $this->count_email++;

                    if ($this->count_email > 1) {
                        sleep($sleepTime);
                    }
                    /* end of part : https://support.google.com/mail/answer/81126 */                

                    TokenQuery::create()->filterByType("verifyPerson")->filterByReferenceId($person->getId())->delete();
                    $token = new Token();
                    $token->build("verifyPerson", $person->getId());
                    $token->save();

                    $tokenPassword = new TokenPassword();

                    $password = MiscUtils::random_password(8);

                    $tokenPassword->setTokenId($token->getPrimaryKey());
                    $tokenPassword->setPassword(md5($password));
                    $tokenPassword->setMustChangePwd(false);

                    $tokenPassword->save();

                    $emails = [$person->getEmail()];

                    LoggerUtils::getAppLogger()->info("Person ".$this->count_email. " : ".$person->getLastName()." STime : ".$sleepTime . "mail : ".$emails[0]);

                    $mail = new PersonVerificationEmail($emails, $person->getFirstName(), $person->getLastName(), $token->getToken(), $emails, $password);
                    $filename = 'ConfirmReportEmail-' . $person->getLastName() . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.pdf';
                    $mail->addStringAttachment($doc, $filename);

                    if (($this->personsEmailed = $mail->send())) {
                        $this->personsEmailed[] = $this->familiesEmailed + 1;
                    } else {
                        LoggerUtils::getAppLogger()->error($mail->getError());
                    }
                }                
            }

            LoggerUtils::getAppLogger()->info("end : mailing to persons ");

            return $this->personsEmailed;
        }
    }
}
