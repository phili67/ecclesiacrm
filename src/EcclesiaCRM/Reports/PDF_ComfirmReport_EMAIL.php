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

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReportTCPDF;
use EcclesiaCRM\Emails\FamilyVerificationEmail;
use EcclesiaCRM\Emails\PersonVerificationEmail;
use EcclesiaCRM\Token;
use EcclesiaCRM\TokenPassword;
use EcclesiaCRM\TokenQuery;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\LoggerUtils;

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;

use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use Family;
use Propel\Runtime\ActiveQuery\Criteria;

class EmailPDF_ConfirmReport extends ChurchInfoReportTCPDF
{
    private $incrY;
    private $leftX;
    public $_PersonCustom;
    
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
        $this->_PersonCustom[(int)$order] = (int)$use;
    }

    public function GetPersonCustomField($order) {
        return $this->_PersonCustom[$order];
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
    private $numCustomFields;
    private $dataCol;
    private $dataWid;
    private $ormCustomFields;
    private $sCustomFieldTypeID;
    private $sCustomFieldName;
    private $count_email;
    private $fontSize;


    // Constructor
    public function __construct($fams = NULL, $persons = NULL, $fontSize = 10)
    {
        $this->incrY = SystemConfig::getValue('incrementY') + 0.5;
        $this->familiesEmailed = 0;
        $this->fams = $fams;
        $this->persons = $persons;
        $this->fontSize = $fontSize;
    }

    private function renderFamily ($fam, $minAge, $maxAge, $classList, $personCustomFields) {
        // Instantiate the directory class and build the report.
        $pdf = new EmailPDF_ConfirmReport();

        foreach ($personCustomFields as $customField) {
            $pdf->AddPersonCustomField($customField['order'], $customField['custom']);
        }

        $emaillist = [];

        $curY = $pdf->StartNewPage($fam->getId(), $fam->getName(), $fam->getAddress1(), $fam->getAddress2(), $fam->getCity(), $fam->getState(), $fam->getZip(), $fam->getCountry(), 'family');
        $curY += $this->incrY;

        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Family Name'));
        $pdf->SetFont('Times', '', 10);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $fam->getName());
        $curY += $this->incrY;
        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Address') . ' 1');
        $pdf->SetFont('Times', '', 10);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $fam->getAddress1());
        $curY += $this->incrY;
        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Address') . ' 2');
        $pdf->SetFont('Times', '', 10);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $fam->getAddress2());
        $curY += $this->incrY;
        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('City, State, Zip'));
        $pdf->SetFont('Times', '', 10);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, ($fam->getCity() . ', ' . $fam->getState() . '  ' . $fam->getZip()));
        $curY += $this->incrY;
        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Home Phone'));
        $pdf->SetFont('Times', '', 10);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $fam->getHomePhone());
        $curY += $this->incrY;
        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Send Newsletter'));
        $pdf->SetFont('Times', '', 10);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $fam->getSendNewsLetter());
        $curY += $this->incrY;

        // Missing the following information from the Family record:
        // Wedding date (if present) - need to figure how to do this with sensitivity
        // Family e-mail address

        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Anniversary Date'));
        $pdf->SetFont('Times', '', 10);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, (!is_null($fam->getWeddingDate())?OutputUtils::FormatDate($fam->getWeddingDate()->format('Y-m-d')):""));
        $curY += $this->incrY;

        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Family Email'));
        $pdf->SetFont('Times', '', 10);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $fam->getEmail());
        if (!empty($fam_Email)) {
            array_push($emaillist, $fam_Email);
        }

        $curY += $this->incrY;
        $curY += $this->incrY;


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

        $pdf->SetFont('Times', 'B', 10);
        $pdf->WriteAtCell($XName, $curY, $XGender - $XName, _('Member Name'));
        $pdf->WriteAtCell($XGender, $curY, $XRole - $XGender, _('M/F'));
        $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Adult/Child'));
        $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, _('Email'));
        $pdf->WriteAtCell($XBirthday, $curY, $XCellPhone - $XBirthday, _('Birthday'));
        $pdf->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, _('Cell Phone'));
        $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, _('Member/Friend'));
        $pdf->SetFont('Times', '', 10);
        $curY += $this->incrY;

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
            if (($curY + $this->numCustomFields * $this->incrY) > 260) {
                $curY = $pdf->StartLetterPage($fam->getID(), $fam->getName(), $fam->getAddress1(), $fam->getAddress2(), $fam->getCity(), $fam->getState(), $fam->getZip(), $fam->getCountry());
                $pdf->SetFont('Times', 'B', 10);
                $pdf->WriteAtCell($XName, $curY, $XGender - $XName, _('Member Name'));
                $pdf->WriteAtCell($XGender, $curY, $XRole - $XGender, _('M/F'));
                $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Adult/Child'));
                $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, _('Email'));
                $pdf->WriteAtCell($XBirthday, $curY, $XCellPhone - $XBirthday, _('Birthday'));
                $pdf->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, _('Cell Phone'));
                $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, _('Member/Friend'));
                $pdf->SetFont('Times', '', 10);
                $curY += $this->incrY;
            }
            $iPersonID = $aMember->getId();
            $pdf->SetFont('Times', 'B', 10);
            $pdf->WriteAtCell($XName, $curY, $XGender - $XName, $aMember->getFirstName() . ' ' . $aMember->getMiddleName() . ' ' . $aMember->getLastName());
            $pdf->SetFont('Times', '', 10);
            $genderStr = ($aMember->getGender() == 1 ? 'M' : 'F');
            $pdf->WriteAtCell($XGender, $curY, $XRole - $XGender, $genderStr);
            $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, $aMember->getFamRole());
            $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, $aMember->getEmail());
            if (!empty($aMember->getEmail())) {
                array_push($emaillist, $aMember->getEmail());
            }
            if ($aMember->getBirthYear()) {
                $birthdayStr = $aMember->getBirthMonth() . '/' . $aMember->getBirthDay() . '/' . $aMember->getBirthYear();
            } else {
                $birthdayStr = '';
            }
            $pdf->WriteAtCell($XBirthday, $curY, $XCellPhone - $XBirthday, $birthdayStr);
            $pdf->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, $aMember->getCellPhone());
            $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, $aMember->getClassName());
            $curY += $this->incrY;
            // Missing the following information for the personal record: ??? Is this the place to put this data ???
            // Work Phone
            $pdf->WriteAtCell($XWorkPhone, $curY, $XRight - $XWorkPhone, _('Work Phone') . ':' . $aMember->getWorkPhone());
            $curY += $this->incrY;
            $curY += $this->incrY;

            // *** All custom fields ***
            // Get the list of custom person fields


            $xSize = 40;
            if ($this->numCustomFields > 0) {

                $rawQry = PersonCustomQuery::create();
                foreach ($this->ormCustomFields as $customField) {
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

                foreach ($this->ormCustomFields as $customField) {
                    if ($pdf->GetPersonCustomField($customField->getCustomOrder()) == 0) continue;

                    if ($this->sCustomFieldName[$customField->getCustomOrder() - 1]) {
                        $currentFieldData = trim($aCustomData[$customField->getCustomField()]);

                        $currentFieldData = OutputUtils::displayCustomField($customField->getTypeId(), trim($aCustomData[$customField->getCustomField()]), $customField->getCustomSpecial(), false);

                        $OutStr = $this->sCustomFieldName[$customField->getCustomOrder() - 1] . ' : ' . $currentFieldData . '    ';
                        $pdf->WriteAtCell($xInc, $curY, $xSize, $this->sCustomFieldName[$customField->getCustomOrder() - 1]);
                        if ($currentFieldData == '') {
                            $pdf->SetFont('Times', 'B', 10);
                            $pdf->WriteAtCell($xInc + $xSize, $curY, $xSize, '');
                            $pdf->SetFont('Times', '', 10);
                        } else {
                            $pdf->WriteAtCell($xInc + $xSize, $curY, $xSize, $currentFieldData);
                        }
                        $numWide += 1; // increment the number of columns done
                        $xInc += (2 * $xSize); // Increment the X position by about 1/2 page width
                        if (($numWide % 2) == 0) { // 2 columns
                            $xInc = $XName; // Reset margin
                            $curY += $this->incrY;
                        }
                    }
                }

                //$pdf->WriteAt($XName,$curY,$OutStr);
                //$curY += (2 * SystemConfig::getValue("incrementY"));
            }
            $curY += 2 * $this->incrY;

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

                $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $groupStr);
                $curY += 2 * $this->incrY;
            }
        }

        if ($curY > 183) { // This insures the trailer information fits continuously on the page (3 inches of "footer"
            $curY = $pdf->StartLetterPage($fam->getID(), $fam->getName(), $fam->getAddress1(), $fam->getAddress2(), $fam->getCity(), $fam->getState(), $fam->getZip(), $fam->getCountry());
        }
        $pdf->FinishPage($curY);

        if (count($emaillist) > 0) {
            // now we are pending for a confirmation from the family !!! The Done
            $family = FamilyQuery::create()->findOneById($fam->getId());
            $family->setConfirmReport('Pending');
            $family->save();

            header('Pragma: public');  // Needed for IE when using a shared SSL certificate

            ob_end_clean();
            $doc = $pdf->Output('ConfirmReportEmail-' . $fam->getID() . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.pdf', 'S');

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
            //if ($fam->getID() == 274) {

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
            /*} else {
                LoggerUtils::getAppLogger()->info("No fam ".$this->count_email. " : ".$fam->getName()." STime : ".$sleepTime);
            }*/

            return $this->familiesEmailed;
        }
    }

    private function renderPerson ($person, $customFields) {
        // Instantiate the directory class and build the report.
        $pdf = new EmailPDF_ConfirmReport();

        foreach ($customFields as $customField) {
            $pdf->AddPersonCustomField($customField['order'], $customField['custom']);
        }

        $emaillist = [];

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
        $curY = $pdf->StartNewPage($person->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(),$family->getState(), $family->getZip(), $family->getCountry(), 'person');

        $curY += $this->incrY;  

        // place the first table
        $pdf->SetFont('Times', 'B', $this->fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Name'));
        $pdf->SetFont('Times', '', $this->fontSize);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $person->getlastName());
        $curY += $this->incrY;
        // place the first table
        $pdf->SetFont('Times', 'B', $this->fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('First Name'));
        $pdf->SetFont('Times', '', $this->fontSize);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $person->getFirstName());
        $curY += $this->incrY;
        $pdf->SetFont('Times', 'B', $this->fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Address 1'));
        $pdf->SetFont('Times', '', $this->fontSize);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $family->getAddress1());
        $curY += $this->incrY;
        $pdf->SetFont('Times', 'B', $this->fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('City, State, Zip'));
        $pdf->SetFont('Times', '', $this->fontSize);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, ($family->getCity().', '.$family->getState().'  '.$family->getZip()));
        $curY += $this->incrY;
        $pdf->SetFont('Times', 'B', $this->fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Address 2'));
        $pdf->SetFont('Times', '', $this->fontSize);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $family->getAddress2());
        $curY += $this->incrY;
        $pdf->SetFont('Times', 'B', $this->fontSize);
        $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Home Phone'));
        $pdf->SetFont('Times', '', $this->fontSize);
        $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, $person->getHomePhone());

        // Missing the following information from the Family record:
        // Wedding date (if present) - need to figure how to do this with sensitivity
        // Family e-mail address
        if ($person->getFmrId() == 1 or $person->getFmrId() == 2) {
            $curY += $this->incrY;    
            $pdf->SetFont('Times', 'B', $this->fontSize);
            $pdf->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $this->dataCol - SystemConfig::getValue('leftX'), _('Anniversary Date'));
            $pdf->SetFont('Times', '', $this->fontSize);
            $pdf->WriteAtCell($this->dataCol, $curY, $this->dataWid, OutputUtils::FormatDate((!is_null($family->getWeddingdate())?$family->getWeddingdate()->format('Y-m-d'):'')));
            $curY += $this->incrY;
        }

        $curY += $this->incrY;    
        $curY += $this->incrY;        

        $pdf->SetFont('Times', 'B', $this->fontSize);
        $pdf->WriteAtCell($XName, $curY, $XGender - $XName, _('Member Name'));
        $pdf->WriteAtCell($XGender, $curY, $XRole - $XGender, _('M/F'));
        $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Adult/Child'));
        $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, _('Email'));
        $pdf->WriteAtCell($XBirthday, $curY, $XHideAge - $XBirthday, _('Birthday'));
        $pdf->SetFont('Times', 'B', 5);
        $pdf->WriteAtCell($XHideAge, $curY, $XCellPhone - $XHideAge, _('Hide Age'), "LTR");
        $pdf->SetFont('Times', 'B', $this->fontSize);            
        $pdf->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, substr(_('Cell phone'),0,10).".");
        $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, _('Work Phone'));
        $pdf->SetFont('Times', '', $this->fontSize);
        $curY += $this->incrY;

        $iPersonID = $person->getId();
        $pdf->SetFont('Times', 'B', $this->fontSize);
        $pdf->WriteAtCell($XName, $curY, $XGender - $XName, $person->getFirstName().' '.$person->getMiddleName().' '.$person->getLastName());
        $pdf->SetFont('Times', '', $this->fontSize);
        $genderStr = ($person->getGender() == 1 ? 'M' : 'F');
        $pdf->WriteAtCell($XGender, $curY, $XRole - $XGender, $genderStr);
        $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, $person->getFamRole());
        $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, $person->getEmail());
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

        $pdf->WriteAtCell($XBirthday, $curY, $XHideAge - $XBirthday, $birthdayStr);
        $pdf->WriteAtCell($XHideAge, $curY, $XCellPhone - $XHideAge, $hideAgeStr);
        $pdf->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, $person->getCellPhone());
        $pdf->WriteAtCell($XClassification, $curY, $XRight - $XClassification, $person->getWorkPhone());

        $curY += $this->incrY;
        $curY += $this->incrY;
        
        // Missing the following information for the personal record: ??? Is this the place to put this data ???
        // Work Phone
        $pdf->SetFont('Times', 'B', $this->fontSize);
        $pdf->WriteAtCell($XName, $curY, $XEmail - $XGender, _('Send Newsletter'), "0");
        $pdf->WriteAtCell($XGender, $curY, $XBirthday - $XEmail, "", "0");
        if ($person->getSendNewsletter() == 'FALSE') {
            $pdf->CheckBox('newsletterPerson'.$person->getId(), 5, false, array(), array(), 'No', $XGender, $curY);
        } else {
            $pdf->CheckBox('newsletterPerson'.$person->getId(), 5, true, array(), array(), 'Yes', $XGender, $curY);
        }

        
        $pdf->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Classification'), "0", "R");
        $pdf->SetFont('Times', '', $this->fontSize);
        $pdf->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, $person->getClassName(), "0");

        $curY += $this->incrY;
        $curY += $this->incrY;

        // *** All custom fields ***
        // Get the list of custom person fields

        $xSize = 40;
        if ($this->numCustomFields > 0) {
            // Get the custom field data for this person.
            $rawQry = PersonCustomQuery::create();
            foreach ($this->ormCustomFields as $custField) {
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
            foreach ($this->ormCustomFields as $custField) {

                if ($pdf->GetPersonCustomField($custField->getCustomOrder()) == 0) continue;

                if ($this->sCustomFieldName[$custField->getCustomOrder() - 1]) {
                    $currentFieldData = trim($aCustomData[$custField->getCustomField()]);

                    $currentFieldData = OutputUtils::displayCustomField($custField->getTypeId(), trim($aCustomData[$custField->getCustomField()]), $custField->getCustomSpecial(), false);

                    if ($this->sCustomFieldTypeID[$custField->getCustomOrder() - 1] == 1) {
                        $pdf->SetFont('Times', 'B', $this->fontSize);
                        $pdf->WriteAtCell($xInc, $curY, $xSize, $this->sCustomFieldName[$custField->getCustomOrder() - 1]);
                        $pdf->SetFont('Times', '', $this->fontSize);
                        $pdf->WriteAtCell($xInc + $xSize, $curY, $xSize, "");
                        if (is_null($currentFieldData) or $currentFieldData  == '' or $currentFieldData == 'FALSE') {
                            $pdf->CheckBox('props'.$custField->getId(), 5, false, array(), array(), 'No', $xInc + $xSize, $curY);
                        } else {
                            $pdf->CheckBox('props'.$custField->getId(), 5, true, array(), array(), 'Yes', $xInc + $xSize, $curY);
                        }
                    } else {                    
                        $OutStr = $this->sCustomFieldName[$custField->getCustomOrder() - 1].' : '.$currentFieldData.'    ';
                        $pdf->SetFont('Times', 'B', $this->fontSize);
                        $pdf->WriteAtCell($xInc, $curY, $xSize, $this->sCustomFieldName[$custField->getCustomOrder() - 1]);

                        $pdf->SetFont('Times', '', $this->fontSize);
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
                        $curY += $this->incrY;
                    }
                }
            }
            //$pdf->WriteAt($XName,$curY,$OutStr);
            //$curY += (2 * SystemConfig::getValue("incrementY"));
        }
        $curY += 2 * $this->incrY;

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
            $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $groupStr);
            $curY += 2 * $this->incrY;
        }

        $pdf->FinishPage($curY);    

        // end of pdf page !!!
        
        if ($person->getEmail() != '') {
            $emaillist = [$person->getEmail()];
        } else {
            $emaillist = [$family->getEmail()];
        }

        if (count($emaillist) > 0) {
            // now we are pending for a confirmation from the person !!! The Done
            $per = PersonQuery::create()->findOneById($person->getId());
            $per->setConfirmReport('Pending');
            $per->save();

            header('Pragma: public');  // Needed for IE when using a shared SSL certificate

            ob_end_clean();
            $doc = $pdf->Output('ConfirmReportEmail-' . $person->getID() . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.pdf', 'S');

            $subject = $person->getLastName() . ' Family Information Review';

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
            //if ($fam->getID() == 274) {

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

                LoggerUtils::getAppLogger()->info("family ".$this->count_email. " : ".$person->getLastName()." STime : ".$sleepTime . "mail : ".$emails[0]);

                $mail = new PersonVerificationEmail($emails, $person->getFirstName(), $person->getLastName(), $token->getToken(), $emails, $password);
                $filename = 'ConfirmReportEmail-' . $person->getLastName() . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.pdf';
                $mail->addStringAttachment($doc, $filename);

                if (($personEmailSent = $mail->send())) {
                    $this->personsEmailed = $this->familiesEmailed + 1;
                } else {
                    LoggerUtils::getAppLogger()->error($mail->getError());
                }
            /*} else {
                LoggerUtils::getAppLogger()->info("No fam ".$this->count_email. " : ".$fam->getName()." STime : ".$sleepTime);
            }*/

            return $this->personsEmailed;
        }
    }

    public function renderAndSend($exportType, $minAge, $maxAge, $classList, $customPersonFields)
    {
        if ($exportType == 'family') {
            LoggerUtils::getAppLogger()->info("start : mailing to families");
            $familyEmailSent = false;

            // Get the list of custom person fields
            $this->ormCustomFields = PersonCustomMasterQuery::create()
                ->orderByCustomOrder()
                ->find();

            $this->numCustomFields = $this->ormCustomFields->count();

            if ($this->numCustomFields > 0) {
                $iFieldNum = 0;
                foreach ($this->ormCustomFields as $ormCustomField) {
                    $this->sCustomFieldName[$iFieldNum] = $ormCustomField->getCustomName();
                    $iFieldNum += 1;
                }
            }

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
            foreach ($ormFamilies as $fam) {
                $familyEmailSent = $this->renderFamily($fam, $minAge, $maxAge, $classList, $customPersonFields);
            }

            LoggerUtils::getAppLogger()->info("end : mailing to families ");

            return $familyEmailSent;
        } else if ($exportType = 'person') {
            LoggerUtils::getAppLogger()->info("start : mailing to persons");
            $familyEmailSent = false;

            // Get the list of custom person fields
            $this->ormCustomFields = PersonCustomMasterQuery::create()
                ->orderByCustomOrder()
                ->find();

            $this->numCustomFields = $this->ormCustomFields->count();

            if ($this->numCustomFields > 0) {
                $iFieldNum = 0;
                foreach ($this->ormCustomFields as $ormCustomField) {
                    $this->sCustomFieldName[$iFieldNum] = $ormCustomField->getCustomName();
                    $this->sCustomFieldTypeID[$iFieldNum] = $ormCustomField->getTypeId();
                    $iFieldNum += 1;
                }
            }

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
                $personEmailSent = $this->renderPerson($person, $customPersonFields);
            }

            LoggerUtils::getAppLogger()->info("end : mailing to persons ");

            return $personEmailSent;
        }
    }
}
