<?php
/*******************************************************************************
 *
 *  filename    : DonatedItemReplicate.php
 *  last change : 2015-01-01
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2015 Michael Wilt
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\DonatedItemQuery;
use EcclesiaCRM\DonatedItem;

$iFundRaiserID = $_SESSION['iCurrentFundraiser'];
$iDonatedItemID = InputUtils::LegacyFilterInputArr($_GET, 'DonatedItemID', 'int');
$iCount = InputUtils::LegacyFilterInputArr($_GET, 'Count', 'int');

$sLetter = 'a';

$item = DonatedItemQuery::create()
    ->findOneById($iDonatedItemID);

$startItem = $item->getItem();

if (strlen($startItem) == 2) { // replicated items will sort better if they have a two-digit number
    $letter = mb_substr($startItem, 0, 1);
    $number = mb_substr($startItem, 1, 1);
    $startItem = $letter.'0'.$number;
}

$letterNum = ord('a');

for ($i = 0; $i < $iCount; $i++) {
    $newItem = new DonatedItem ();

    $newItem->setItem($startItem.chr($letterNum));
    $newItem->setFrId($item->getFrId());
    $newItem->setDonorId($item->getDonorID());
    $newItem->setMultibuy($item->getMultibuy());
    $newItem->setTitle($item->getTitle());
    $newItem->setDescription($item->getDescription());
    $newItem->setSellprice($item->getSellprice());
    $newItem->setEstprice($item->getEstprice());
    $newItem->setMinimum($item->getMinimum());
    $newItem->setMaterialValue($item->getMaterialValue());
    $newItem->setEnteredby(SessionUser::getUser()->getPersonId());
    $newItem->setEntereddate(date('YmdHis'));
    $newItem->setPicture($item->getPicture());

    $newItem->save();

    $letterNum += 1;
}
RedirectUtils::Redirect("FundRaiserEditor.php?FundRaiserID=$iFundRaiserID");
