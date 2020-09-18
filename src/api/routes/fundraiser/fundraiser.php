<?php

// Routes
use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\DonatedItemQuery;
use EcclesiaCRM\DonatedItem;
use EcclesiaCRM\SessionUser;
use Propel\Runtime\Propel;
use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\PaddleNumQuery;

use EcclesiaCRM\Map\PersonTableMap;

use EcclesiaCRM\Utils\InputUtils;

$app->group('/fundraiser', function () {

    $this->post('/{FundRaiserID:[0-9]+}', 'getAllFundraiserForID' );
    $this->post('/replicate', 'replicateFundraiser' );
    $this->post('/donatedItemSubmit', 'donatedItemSubmitFundraiser' );
    $this->delete('/donateditem', 'deleteDonatedItem' );

    // FindFundRaiser.php
    $this->post('/findFundRaiser/{fundRaiserID:[0-9]+}/{startDate}/{endDate}', 'findFundRaiser');

    // paddlenum
    $this->delete('/paddlenum', 'deletePaddleNum');
    $this->get('/paddlenum/list/{fundRaiserID:[0-9]+}', 'getPaddleNumList');

});

function getPaddleNumList(Request $request, Response $response, array $args)
{
    if ($args['fundRaiserID']) {
        $ormPaddleNumes = PaddleNumQuery::create()
            ->usePersonQuery()
            ->addAsColumn('BuyerFirstName', PersonTableMap::COL_PER_FIRSTNAME)
            ->addAsColumn('BuyerLastName', PersonTableMap::COL_PER_LASTNAME)
            ->endUse()
            ->orderByNum()
            ->findByFrId($args['fundRaiserID']);

        return $response->withJSON(['PaddleNumItems' => $ormPaddleNumes->toArray()]);
    }

    return $response->withJSON(['PaddleNumItems' => []]);
}

function findFundRaiser(Request $request, Response $response, array $args)
{
    if ($args['startDate'] != '-1' || $args['endDate'] != '-1') {
        if (isset ($args['fundRaiserID']) && $args['fundRaiserID'] != "0") {
            $ormDep = FundRaiserQuery::create()
                ->filterByDate(array("min" => $args['startDate'] . " 00:00:00", "max" => $args['endDate'] . " 23:59:59"))
                ->findById($args['fundRaiserID']);
        } else {
            $ormDep = FundRaiserQuery::create()
                ->filterByDate(array("min" => $args['startDate'] . " 00:00:00", "max" => $args['endDate'] . " 23:59:59"))
                ->find();
        }

        return $response->withJSON(['FundRaiserItems' => $ormDep->toArray()]);
    }

    if (isset ($args['fundRaiserID']) && $args['fundRaiserID'] != "0") {
        $ormDep = FundRaiserQuery::create()
                ->findById($args['fundRaiserID']);
    } else {
        $ormDep = FundRaiserQuery::create()
                ->find();
    }

    return $response->withJSON(['FundRaiserItems' => $ormDep->toArray()]);
}

function getAllFundraiserForID(Request $request, Response $response, array $args)
{

    $sSQL = "SELECT di_ID, di_Item, di_multibuy,
	                a.per_FirstName as donorFirstName, a.per_LastName as donorLastName,
	                b.per_FirstName as buyerFirstName, b.per_LastName as buyerLastName,
	                di_title, di_sellprice, di_estprice, di_materialvalue, di_minimum
	         FROM donateditem_di
	         LEFT JOIN person_per a ON di_donor_ID=a.per_ID
	         LEFT JOIN person_per b ON di_buyer_ID=b.per_ID
	         WHERE di_FR_ID = '" . $args['FundRaiserID'] . "' ORDER BY di_multibuy,SUBSTR(di_item,1,1),cast(SUBSTR(di_item,2) as unsigned integer),SUBSTR(di_item,4)";

    $connection = Propel::getConnection();

    $pdoDonatedItems = $connection->prepare($sSQL);
    $pdoDonatedItems->execute();

    return $response->withJSON(['DonatedItems' => $pdoDonatedItems->fetchAll(\PDO::FETCH_ASSOC)]);
}

function replicateFundraiser(Request $request, Response $response, array $args)
{
    $input = (object)$request->getParsedBody();

    if (isset ($input->DonatedItemID) && isset($input->count)) {

        $iDonatedItemID = $input->DonatedItemID;
        $iCount = $input->count;

        $item = DonatedItemQuery::create()
            ->findOneById($iDonatedItemID);


        if (is_null($item)) {
            return $response->withJSON(['status' => "failed"]);
        }

        $startItem = $item->getItem();

        if (strlen($startItem) == 2) { // replicated items will sort better if they have a two-digit number
            $letter = mb_substr($startItem, 0, 1);
            $number = mb_substr($startItem, 1, 1);
            $startItem = $letter . '0' . $number;
        }

        $letterNum = ord('a');

        for ($i = 0; $i < $iCount; $i++) {
            $newItem = new DonatedItem ();

            $newItem->setItem($startItem . chr($letterNum));
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

        return $response->withJSON(['status' => "success"]);
    }

    return $response->withJSON(['status' => "failed"]);
}

function deleteDonatedItem(Request $request, Response $response, array $args)
{
    $input = (object)$request->getParsedBody();

    if (isset ($input->FundRaiserID) && isset($input->DonatedItemID)) {

        $di = DonatedItemQuery::create()
            ->filterByFrId($input->FundRaiserID)
            ->findOneById($input->DonatedItemID);

        if (!is_null($di)) {
            $di->delete();

            return $response->withJSON(['status' => "success"]);
        }
    }

    return $response->withJSON(['status' => "failed"]);
}

function donatedItemSubmitFundraiser(Request $request, Response $response, array $args)
{
    $input = (object)$request->getParsedBody();


    if (isset ($input->currentFundraiser) && isset($input->currentDonatedItemID)
        && isset ($input->Item) && isset($input->Multibuy)
        && isset ($input->Donor) && isset($input->Title)
        && isset ($input->EstPrice) && isset($input->MaterialValue)
        && isset ($input->MinimumPrice) //&& isset($input->Buyer)
        && isset ($input->SellPrice) && isset($input->Description)
        && isset ($input->PictureURL)) {

        $sItem = InputUtils::FilterString($input->Item);
        $bMultibuy = InputUtils::FilterInt($input->Multibuy);
        $iDonor = InputUtils::FilterInt($input->Donor);
        $iBuyer = InputUtils::FilterInt($input->Buyer);
        $sTitle = InputUtils::FilterString($input->Title);
        $sDescription = InputUtils::FilterHTML($input->Description);
        $nSellPrice = InputUtils::FilterFloat($input->SellPrice);
        $nEstPrice = InputUtils::FilterFloat($input->EstPrice);
        $nMaterialValue = InputUtils::FilterFloat($input->MaterialValue);
        $nMinimumPrice = InputUtils::FilterFloat($input->MinimumPrice);
        $sPictureURL = InputUtils::FilterString($input->PictureURL);

        if ($input->currentDonatedItemID < 0) {
            $donatedItem = new DonatedItem();

            $donatedItem->setFrId($input->currentFundraiser);
            $donatedItem->setItem($sItem);
            $donatedItem->setMultibuy($bMultibuy);
            $donatedItem->setDonorId($iDonor);
            $donatedItem->setBuyerId($iBuyer);
            $donatedItem->setTitle($sTitle);
            $donatedItem->setDescription(html_entity_decode($sDescription));
            $donatedItem->setSellprice($nSellPrice);
            $donatedItem->setEstprice($nEstPrice);
            $donatedItem->setMaterialValue($nMaterialValue);
            $donatedItem->setMinimum($nMinimumPrice);
            $donatedItem->setPicture(Propel::getConnection()->quote($sPictureURL));
            $donatedItem->setEnteredby(SessionUser::getUser()->getPersonId());
            $donatedItem->setEntereddate(date('YmdHis'));

            $donatedItem->save();
        } else {
            $donatedItem = DonatedItemQuery::create()
                ->findOneById($input->currentDonatedItemID);

            $donatedItem->setFrId($input->currentFundraiser);
            $donatedItem->setItem($sItem);
            $donatedItem->setMultibuy($bMultibuy);
            if ($iDonor != 0)
                $donatedItem->setDonorId($iDonor);
            if ($iBuyer != 0)
                $donatedItem->setBuyerId($iBuyer);
            $donatedItem->setTitle(html_entity_decode($sTitle));
            $donatedItem->setDescription(html_entity_decode($sDescription));
            $donatedItem->setSellprice($nSellPrice);
            $donatedItem->setEstprice($nEstPrice);
            $donatedItem->setMaterialValue($nMaterialValue);
            $donatedItem->setMinimum($nMinimumPrice);
            $donatedItem->setPicture($sPictureURL);
            $donatedItem->setEnteredby(SessionUser::getUser()->getPersonId());
            $donatedItem->setEntereddate(date('YmdHis'));

            $donatedItem->save();
        }

        return $response->withJSON(['status' => "success"]);
    }

    return $response->withJSON(['status' => "failed"]);
}

function deletePaddleNum(Request $request, Response $response, array $args)
{
    $input = (object)$request->getParsedBody();

    if ( isset($input->fundraiserID) && isset ($input->pnID) ) {
        $pn = PaddleNumQuery::create()
            ->filterById($input->pnID)
            ->findOneByFrId($input->fundraiserID);

        if (!is_null($pn)) {
            $pn->delete();
            return $response->withJSON(['status' => "success"]);
        }
    }

    return $response->withJSON(['status' => "failed"]);
}


