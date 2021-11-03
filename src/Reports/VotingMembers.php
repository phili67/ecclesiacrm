<?php
/*******************************************************************************
*
*  filename    : Reports/VotingMembers.php
*  last change : 2019-10-07
*  description : Creates a PDF with names of voting members for a particular fiscal year
*  updated by : Philippe Logel
*
******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;

use EcclesiaCRM\Reports\ChurchInfoReportTCPDF;

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\PersonQuery;

use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;

use Propel\Runtime\ActiveQuery\Criteria;


//Get the Fiscal Year ID out of the querystring
$iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');
$_SESSION['idefaultFY'] = $iFYID; // Remember the chosen FYID
$iRequireDonationYears = InputUtils::LegacyFilterInput($_POST['RequireDonationYears'], 'int');
$output = InputUtils::LegacyFilterInput($_POST['output']);

class PDF_VotingMembers extends ChurchInfoReportTCPDF
{
    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);

        $this->SetFont('Times', '', 10);
        $this->SetMargins(20, 20);

        $this->SetAutoPageBreak(false);
        $this->AddPage();
    }
}

$pdf = new PDF_VotingMembers();

$topY = 10;
$curY = $topY;

$pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, (_('Voting members ').MiscUtils::MakeFYString($iFYID)));
$curY += 10;

$votingMemberCount = 0;

// Get all the families
$families = FamilyQuery::Create()
                ->orderByName()
                ->find();

// Loop through families
foreach ($families as $family) {
    // Get pledge date ranges
    $donation = 'no';
    if ($iRequireDonationYears > 0) {
        $startdate = $iFYID + 1995 - $iRequireDonationYears;
        $startdate .= '-'.SystemConfig::getValue('iFYMonth').'-'.'01';
        // With the Filter : plg_date >= '$startdate' AND plg_date < '$enddate' and with propel you get  plg_date >= '$startdate' AND plg_date <= '$enddate'
        // That's why the end date are rewritten from
        // $enddate = $iFYID + 1995 + 1;
        // $enddate .= '-'.SystemConfig::getValue('iFYMonth').'-'.'01';
        // to
        $enddate = $iFYID + 1995 + (((SystemConfig::getValue('iFYMonth')-1)==0)?0:1);
        $enddate .= '-'.(((SystemConfig::getValue('iFYMonth')-1)==0)?12:SystemConfig::getValue('iFYMonth')).'-'.(((SystemConfig::getValue('iFYMonth')-1)==0)?'31':'01');

        // Get payments only
        $pledges = PledgeQuery::Create()
                    ->filterByFamId ($family->getId())
                    ->filterByPledgeorpayment ('Payment')
                    ->filterByDate(array('min' => $startdate,'max' => $enddate))
                    ->find();

        if ($pledges->count()) {
            $donation = 'yes';
        }
    }

    if (($iRequireDonationYears == 0) || $donation == 'yes') {
        $pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, $family->getName());


        $famMembers = PersonQuery::Create()
            ->addMultipleJoin(array(array(PersonTableMap::COL_PER_CLS_ID,ListOptionTableMap::COL_LST_OPTIONID),
                array(ListOptionTableMap::COL_LST_ID,1)),
                Criteria::INNER_JOIN)
            ->filterByFamId($family->getId())
            ->_and()->Where(ListOptionTableMap::COL_LST_OPTIONNAME." = '"._('Member')."'")
            ->find();

        if ($famMembers->count() == 0) {
            $curY += 5;
        }

        foreach ($famMembers as $member) {
            $pdf->WriteAt(SystemConfig::getValue('leftX') + 30, $curY, ($member->getFirstName().' '.$member->getLastName()));
            $curY += 5;
            if ($curY > 245) {
                $pdf->AddPage();
                $curY = $topY;
            }
            $votingMemberCount += 1;
        }
        if ($curY > 245) {
            $pdf->AddPage();
            $curY = $topY;
        }
    }
}

$curY += 5;
$pdf->WriteAt(SystemConfig::getValue('leftX'), $curY, _("Number of Voting Members").":".$votingMemberCount);

header('Pragma: public');  // Needed for IE when using a shared SSL certificate
ob_end_clean();
if (SystemConfig::getValue('iPDFOutputType') == 1) {
    $pdf->Output('VotingMembers'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf', 'D');
} else {
    $pdf->Output();
}
