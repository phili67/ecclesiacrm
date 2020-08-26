<?php
/*******************************************************************************
 *
 *  filename    : AddDonors.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2009 Michael Wilt
 *
 * This script adds people who have donated but not registered as buyers to the
 * buyer list so they can get statements too.
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;

use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\Base\DonatedItemQuery;
use EcclesiaCRM\Base\PaddleNumQuery;
use EcclesiaCRM\PaddleNum;

use EcclesiaCRM\Map\DonatedItemTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\PaddleNumTableMap;

use Propel\Runtime\ActiveQuery\Criteria;


$linkBack = '';
if (array_key_exists('linkBack', $_GET)) {
    InputUtils::LegacyFilterInput($_GET['linkBack']);
}
$iFundRaiserID = InputUtils::LegacyFilterInput($_GET['FundRaiserID']);

if ($linkBack == '') {
    $linkBack = "PaddleNumList.php?FundRaiserID=$iFundRaiserID";
}

if ($iFundRaiserID > 0) {
    // Get the current fund raiser record
    $ormFRR = FundRaiserQuery::create()
            ->findOneById($iFundRaiserID);

    // Set current fundraiser
    $_SESSION['iCurrentFundraiser'] = $iFundRaiserID;
} else {
    RedirectUtils::Redirect($linkBack);
}

echo $iFundRaiserID;

// Get all the people listed as donors for this fundraiser
$ormDonors = DonatedItemQuery::create()
    ->addJoin(DonatedItemTableMap::COL_DI_DONOR_ID,PersonTableMap::COL_PER_ID,Criteria::LEFT_JOIN)
    ->addAsColumn('PersonID',PersonTableMap::COL_PER_ID)
    ->orderBy('PersonID')
    ->findByFrId($iFundRaiserID);

print_r($ormDonors->toArray());


$extraPaddleNum = 1;
$maxPN = PaddleNumQuery::create()
    ->addAsColumn('Max', 'MAX('.PaddleNumTableMap::COL_PN_NUM.")")
    ->findOneByFrId($iFundRaiserID);

if ( !is_null($maxPN) ) {
    $extraPaddleNum = $maxPN->getMax() + 1;
}


// Go through the donors, add buyer records for any who don't have one yet
foreach ($ormDonors as $donor) {
    $buyer = PaddleNumQuery::create()
        ->filterByFrId($iFundRaiserID)
        ->findOneByPerId( $donor->getDonorId() );

    if ( is_null($buyer) ) {
        $newPN = new PaddleNum();

        $newPN->setNum($extraPaddleNum++);
        $newPN->setFrId($iFundRaiserID);
        $newPN->setPerId($donor->getDonorId());

        $newPN->save();
    }
}

RedirectUtils::Redirect($linkBack);
