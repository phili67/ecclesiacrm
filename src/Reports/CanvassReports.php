<?php
/*******************************************************************************
 *
 *  filename    : /Include/CanvassUtilities.php
 *  last change : 2013-02-22
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2013 Michael Wilt
  *
 ******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\PDF_CanvassBriefingReport;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\CanvassUtilities;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\CanvassDataQuery;
use EcclesiaCRM\GroupQuery;

use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\CanvassDataTableMap;
use EcclesiaCRM\Map\GroupTableMap;


use Propel\Runtime\ActiveQuery\Criteria;

//Get the Fiscal Year ID out of the querystring
$iFYID = InputUtils::LegacyFilterInput($_GET['FYID'], 'int');
$sWhichReport = InputUtils::LegacyFilterInput($_GET['WhichReport']);

function TopPledgersLevel($iFYID, $iPercent)
{
    // Get pledges for this fiscal year, highest first
    $ormPledges = PledgeQuery::create()
        ->filterByPledgeorpayment("Pledge")
        ->filterByFyid($iFYID)
        ->orderByAmount(Criteria::DESC)
        ->find();

    $pledgeCount = $ormPledges->count();

    $res = 0.0;

    if ($pledgeCount > 0) {
        $res = ($ormPledges->toArray())[$pledgeCount * $iPercent / 100]['Amount'];
    }

    return $res;
}

function CanvassProgressReport($iFYID)
{
    // Instantiate the directory class and build the report.
    $pdf = new PDF_CanvassBriefingReport();

    $curY = 10;

    $pdf->SetFont('Times', '', 22);
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _('Canvass Progress Report').' '.date(SystemConfig::getValue("sDateFormatLong")));
    $pdf->SetFont('Times', '', 14);

    $curY += 10;

    $pdf->SetFont('Times', '', 12);
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchName'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchAddress'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchCity').', '.SystemConfig::getValue('sChurchState').'  '.SystemConfig::getValue('sChurchZip'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchPhone').'  '.SystemConfig::getValue('sChurchEmail'));
    $curY += 10;
    $pdf->SetFont('Times', '', 14);

    $nameX = 20;
    $doneX = 70;
    $toDoX = 85;
    $percentX = 110;

    $pdf->SetFont('Times', 'B', 14);
    $pdf->WriteAt($nameX, $curY, _('Name'));
    $pdf->WriteAt($doneX, $curY, _('Done'));
    $pdf->WriteAt($toDoX, $curY, _('Assigned'));
    $pdf->WriteAt($percentX, $curY, _('Percent'));
    $pdf->SetFont('Times', '', 14);

    $curY += 6;

    $totalToDo = 0;
    $totalDone = 0;

    // Get all the canvassers
    $canvassGroups = ['Canvassers', 'BraveCanvassers'];

    foreach ($canvassGroups as $cgName) {
        $canvassers = CanvassUtilities::CanvassGetCanvassers($cgName);
        if ( is_null($canvassers) ) {
            continue;
        }

        foreach ($canvassers as $canvasser) {
            $canvassees = FamilyQuery::create()->findByCanvasser($canvasser->getId());

            $thisCanvasserToDo = $canvassees->count();
            $thisCanvasserDone = 0;

            foreach ($canvassees as $canvassee) {
                $canvassData = CanvassDataQuery::create()->findByFamilyId($canvassee->getId());

                if ($canvassData->count() == 1) {
                    ++$thisCanvasserDone;
                }
            }

            $totalToDo += $thisCanvasserToDo;
            $totalDone += $thisCanvasserDone;

            // Write the status output line for this canvasser
            $pdf->WriteAt($nameX, $curY, $canvasser->getFirstName().' '.$canvasser->getLastName());
            $pdf->WriteAt($doneX, $curY, $thisCanvasserDone);
            $pdf->WriteAt($toDoX, $curY, $thisCanvasserToDo);
            if ($thisCanvasserToDo > 0) {
                $percentStr = sprintf('%.0f%%', ($thisCanvasserDone / $thisCanvasserToDo) * 100);
            } else {
                $percentStr = 'N/A';
            }
            $pdf->WriteAt($percentX, $curY, $percentStr);
            $curY += 6;

        }
    }

    // Summary status
    $pdf->SetFont('Times', 'B', 14);

    $pdf->WriteAt($nameX, $curY, _('Total'));
    $pdf->WriteAt($doneX, $curY, $totalDone);
    $pdf->WriteAt($toDoX, $curY, $totalToDo);
    $percentStr = sprintf('%.0f%%', ($totalDone / $totalToDo) * 100);
    $pdf->WriteAt($percentX, $curY, $percentStr);

    $pdf->Output('CanvassProgress'.date(SystemConfig::getValue("sDateFormatLong")).'.pdf', 'D');
}

function CanvassBriefingSheets($iFYID)
{
    // Instantiate the directory class and build the report.
    $pdf = new PDF_CanvassBriefingReport();

    $aQuestions = file('CanvassQuestions.txt');
    $iNumQuestions = count($aQuestions);

    // Get all the families which need canvassing
    $ormFamilies = FamilyQuery::create()
        ->filterByOkToCanvass("TRUE")
        ->filterByCanvasser(0,Criteria::GREATER_THAN)
        ->addJoin(FamilyTableMap::COL_FAM_CANVASSER,PersonTableMap::COL_PER_ID, Criteria::LEFT_JOIN)
        ->addAsColumn('CanvasserFirstName', PersonTableMap::COL_PER_FIRSTNAME)
        ->addAsColumn('CanvasserLastName', PersonTableMap::COL_PER_LASTNAME)
        ->orderByCanvasser()
        ->orderByName()
        ->find();

    // OK

    $topPledgeLevel = TopPledgersLevel($iFYID, 20); // mjw fix this- percentage should be a config option
    $canvasserX = 160;

    $topY = 20;
    $memberNameX = SystemConfig::getValue('leftX');
    $memberGenderX = $memberNameX + 30;
    $memberRoleX = $memberGenderX + 15;
    $memberAgeX = $memberRoleX + 30;
    $memberClassX = $memberAgeX + 20;
    $memberCellX = $memberClassX + 20;
    $memberEmailX = $memberCellX + 25;

    //while ($aFamily = mysqli_fetch_array($rsFamilies)) {
    foreach ($ormFamilies as $family) {
        $curY = $topY;

        $pdf->SetFont('Times', '', 22);

        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $family->getName());

        $pdf->SetFont('Times', '', 16);
        $pdf->PrintRightJustified($canvasserX, $curY, _('Canvasser').': '.$family->getCanvasserFirstName().' '.$family->getCanvasserLastName());

        $curY += 8;

        $pdf->SetFont('Times', '', 14);

        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $pdf->MakeSalutation($family->getId()));
        $curY += 5;
        if ($family->getAddress1() != '') {
            $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $family->getAddress1());
            $curY += 5;
        }
        if ($family->getAddress2() != '') {
            $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $family->getAddress2());
            $curY += 5;
        }
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $family->getCity().', '.$family->getState().'  '.$family->getZip());
        $curY += 5;
        if ($family->getCountry() != '' && $family->getCountry() != 'United States' && $family->getCountry() != 'USA') {
            $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $family->getCountry());
            $curY += 5;
        }
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $pdf->StripPhone($family->getHomePhone()));
        $curY += 5;

        // Get pledges for this fiscal year, this family
        $ormPledges = PledgeQuery::create()
            ->filterByFyid($iFYID)
            ->filterByPledgeorpayment("Pledge")
            ->filterByFamId($family->getId())
            ->orderByAmount(Criteria::DESC)
            ->find();

        $sPledgeStatus = '';
        if ($ormPledges->count() == 0) {
            $sPledgeStatus .= _('Did not pledge');
        } else {
            // we get the first pledge : ie the more important
            if ($ormPledges->toArray()[0]['Amount'] >= $topPledgeLevel) {
                $sPledgeStatus .= _('Top pledger');
            } else {
                $sPledgeStatus .= _('Pledged');
            }
        }

        $curY += SystemConfig::getValue('incrementY');

        $pdf->SetFont('Times', '', 12);
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _('Pledge status').': ');
        $pdf->SetFont('Times', 'B', 12);
        $pdf->WriteAt(SystemConfig::getValue('leftX') + 25, $curY, $sPledgeStatus);
        $pdf->SetFont('Times', '', 12);

        $curY += 2 * SystemConfig::getValue('incrementY');

        //Get the family members for this family
        $ormFamilyMembers = PersonQuery::create()
            ->addAlias('cls', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_ID), 1)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('sClassName', ListOptionTableMap::alias('cls', ListOptionTableMap::COL_LST_OPTIONNAME))
            ->addAlias('fmr', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array(PersonTableMap::COL_PER_FMR_ID, ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("fmr",ListOptionTableMap::COL_LST_ID), 2)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('OptionSequence', ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONSEQUENCE))
            ->addAsColumn('sFamRole', ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONNAME))
            ->filterByFamId($family->getId())
            ->orderBy('OptionSequence')
            ->find();

        $pdf->SetFont('Times', 'B', 10);

        $pdf->WriteAt($memberNameX, $curY, _('Name'));
        $pdf->WriteAt($memberGenderX, $curY, _('M/F'));
        $pdf->WriteAt($memberRoleX, $curY, _('Role'));
        $pdf->WriteAt($memberAgeX, $curY, _('Age'));
        $pdf->WriteAt($memberClassX, $curY, _('Member'));
        $pdf->WriteAt($memberCellX, $curY, _('Cell Phone'));
        $pdf->WriteAt($memberEmailX, $curY, _('Email'));
        $curY += SystemConfig::getValue('incrementY');

        $pdf->SetFont('Times', '', 10);

        foreach ($ormFamilyMembers as $aFamilyMember) {
            if ($aFamilyMember->getGender() == 1) {
                $sGender = 'M';
            } else {
                $sGender = 'F';
            }
            $sAge = OutputUtils::FormatAge($aFamilyMember->getBirthMonth(), $aFamilyMember->getBirthDay(), $aFamilyMember->getBirthYear(), $aFamilyMember->getFlags());
            $pdf->WriteAt($memberNameX, $curY, $aFamilyMember->getFirstName().' '.$aFamilyMember->getLastName());
            $pdf->WriteAt($memberGenderX, $curY, $sGender);
            $pdf->WriteAt($memberRoleX, $curY, $aFamilyMember->getsFamRole());
            $pdf->WriteAt($memberAgeX, $curY, $sAge);
            $pdf->WriteAt($memberClassX, $curY, $aFamilyMember->getsClassName());
            $pdf->WriteAt($memberCellX, $curY, $pdf->StripPhone($aFamilyMember->getCellPhone()));
            $pdf->WriteAt($memberEmailX, $curY, $aFamilyMember->getEmail());
            $curY += SystemConfig::getValue('incrementY');
        }

        // Go back around to get group affiliations
        if ($ormFamilyMembers->count() > 0) {
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
                    $groupStr = _('Assigned groups for ').$aMember->getFirstName().' '.$aMember->getLastName().': ';

                    $countGroups = 0;
                    foreach ($ormAssignedGroups as $aGroup) {
                        $groupStr .= $aGroup->getName().' ('.$aGroup->getRoleName().') ';
                        if ($countGroups == 0) {
                            $curY += SystemConfig::getValue('incrementY');
                        }

                        if (++$countGroups >= 2) {
                            $countGroups = 0;
                            $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $groupStr);
                            $groupStr = '        ';
                        }
                    }
                    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $groupStr);
                }

            }
        }
        $curY += 2 * SystemConfig::getValue('incrementY');
        $spaceLeft = 275 - $curY;
        $spacePerQuestion = $spaceLeft / $iNumQuestions;
        for ($i = 0; $i < $iNumQuestions; $i++) {
            $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, ($i + 1).'. '.$aQuestions[$i]);
            $curY += $spacePerQuestion;
        }

        $pdf->AddPage();
    }

    $pdf->Output('CanvassBriefing'.date(SystemConfig::getValue("sDateFormatLong")).'.pdf', 'D');
}

function CanvassSummaryReport($iFYID)
{
    // Instantiate the directory class and build the report.
    $pdf = new PDF_CanvassBriefingReport();

    $pdf->SetMargins(20, 20);

    $curY = 10;

    $pdf->SetFont('Times', '', 22);

    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _('Canvass Summary Report').' '.date(SystemConfig::getValue("sDateFormatLong")));

    $pdf->SetFont('Times', '', 14);

    $curY += 10;

    $pdf->SetFont('Times', '', 12);
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchName'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchAddress'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchCity').', '.SystemConfig::getValue('sChurchState').'  '.SystemConfig::getValue('sChurchZip'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchPhone').'  '.SystemConfig::getValue('sChurchEmail'));
    $curY += 10;
    $pdf->SetFont('Times', '', 14);

    $pdf->SetAutoPageBreak(1);

    $pdf->Write(5, "\n\n");

    $ormCanvassDatas = CanvassDataQuery::create()
        ->findByFyid($iFYID);

    foreach ([_('Positive'), _('Critical'), _('Insightful'), _('Financial'), _('Suggestion'), _('WhyNotInterested')] as $colName) {
        $pdf->SetFont('Times', 'B', 14);

        $pdf->Write(5, OutputUtils::translate_text_fpdf($colName).' '._('Comments')."\n");
        //		$pdf->WriteAt (SystemConfig::getValue("leftX"), $curY, $colName . " Comments");
        $pdf->SetFont('Times', '', 12);
        foreach ($ormCanvassDatas as $aDatum) {
            $str = '';
            switch ($colName) {
                case _('Positive'):
                    $str = $aDatum->getPositive();
                    break;
                case _('Critical'):
                    $str = $aDatum->getCritical();
                    break;
                case _('Insightful'):
                    $str = $aDatum->getInsightful();
                    break;
                case _('Financial'):
                    $str = $aDatum->getFinancial();
                    break;
                case _('Suggestion'):
                    $str = $aDatum->getSuggestion();
                    break;
                case _('WhyNotInterested'):
                    $str = $aDatum->getWhyNotInterested();
                    break;
            }

            if ($str != '') {
                $pdf->Write(4, OutputUtils::translate_text_fpdf($str)."\n\n");
                //				$pdf->WriteAt (SystemConfig::getValue("leftX"), $curY, $str);
//				$curY += SystemConfig::getValue("incrementY");
            }
        }
    }

    $pdf->Output('CanvassSummary'.date(SystemConfig::getValue("sDateFormatLong")).'.pdf', 'D');
}

function CanvassNotInterestedReport($iFYID)
{
    // Instantiate the directory class and build the report.
    $pdf = new PDF_CanvassBriefingReport();

    $pdf->SetMargins(20, 20);

    $curY = 10;

    $pdf->SetFont('Times', '', 22);
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _('Canvass Not Interested Report').' '.date(SystemConfig::getValue("sDateFormatLong")));
    $pdf->SetFont('Times', '', 14);

    $curY += 10;

    $pdf->SetFont('Times', '', 12);
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchName'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchAddress'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchCity').', '.SystemConfig::getValue('sChurchState').'  '.SystemConfig::getValue('sChurchZip'));
    $curY += SystemConfig::getValue('incrementY');
    $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sChurchPhone').'  '.SystemConfig::getValue('sChurchEmail'));
    $curY += 10;
    $pdf->SetFont('Times', '', 14);

    $pdf->SetAutoPageBreak(1);

    $pdf->Write(5, "\n\n");

    $ormCanvasDatas = CanvassDataQuery::create()
        ->addJoin(CanvassDataTableMap::COL_CAN_FAMID, FamilyTableMap::COL_FAM_ID, Criteria::LEFT_JOIN)
        ->addAsColumn('FamName', FamilyTableMap::COL_FAM_NAME)
        ->filterByFyid($iFYID)
        ->filterByNotInterested(1)
        ->find();

    $pdf->SetFont('Times', '', 12);
    foreach ($ormCanvasDatas as $aDatum) {
        $str = sprintf("%s : %s\n", $aDatum->getFamName(), $aDatum->getWhyNotInterested());
        $pdf->Write(4, OutputUtils::translate_text_fpdf($str)."\n\n");
    }

    header('Pragma: public');  // Needed for IE when using a shared SSL certificate
    $pdf->Output('CanvassNotInterested'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
}

if ($sWhichReport == 'Briefing') {
    CanvassBriefingSheets($iFYID);
}

if ($sWhichReport == 'Progress') {
    CanvassProgressReport($iFYID);
}

if ($sWhichReport == 'Summary') {
    CanvassSummaryReport($iFYID);
}

if ($sWhichReport == 'NotInterested') {
    CanvassNotInterestedReport($iFYID);
}
