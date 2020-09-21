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
use EcclesiaCRM\PaddleNum;

use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Map\DonatedItemTableMap;
use EcclesiaCRM\Map\PaddleNumTableMap;

use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\Utils\InputUtils;

$app->group('/fundraiser', function () {

    $this->post('/{FundRaiserID:[0-9]+}', 'getAllFundraiserForID' );
    $this->post('/replicate', 'replicateFundraiser' );
    $this->post('/donatedItemSubmit', 'donatedItemSubmitFundraiser' );
    $this->delete('/donateditem', 'deleteDonatedItem' );

    // FindFundRaiser.php
    $this->post('/findFundRaiser/{fundRaiserID:[0-9]+}/{startDate}/{endDate}', 'findFundRaiser' );

// paddlenum
    $this->delete('/paddlenum', 'deletePaddleNum' );
    $this->post('/paddlenum/list/{fundRaiserID:[0-9]+}', 'getPaddleNumList' );
    $this->post('/paddlenum/add/donnors', 'addDonnors' );

/*
 * @! Returns a list of all the persons who are in the cart
 */
    $this->get('/paddlenum/persons/all/{fundRaiserID:[0-9]+}', "getAllPersonsNum" );
/*
 * @! Returns a list of all the persons who are in the cart
 */
    $this->post('/paddlenum/add', 'addPaddleNum' );

});

function addPaddleNum (Request $request, Response $response, array $args)
{
    $input = (object)$request->getParsedBody();

    if ( isset($input->PerID) && isset ($input->PaddleNumID) && isset($input->Num) && isset ($input->fundraiserID) ) {
        if ($input->PaddleNumID > 0) {
            $ormPaddleNum = PaddleNumQuery::create()
                ->findOneById($input->PaddleNumID);

            $iCurrentFundraiser = $ormPaddleNum->getFrId();
        } else {
            $iCurrentFundraiser = $input->fundraiserID;
        }

        // to get multibuy donated items
        $ormMultibuyItems = DonatedItemQuery::create()
            ->filterByMultibuy(1)
            ->findByFrId($input->CurrentFundraiser);


        if ($input->PerID > 0) {// Only with a person you can add a buyer
            foreach ($ormMultibuyItems as $multibuyItem) {
                $mbName = 'MBItem' . $multibuyItem->getId();

                $iMBCount = InputUtils::LegacyFilterInput($_POST[$mbName], 'int');
                if ($iMBCount > 0) { // count for this item is positive.  If a multibuy record exists, update it.  If not, create it.
                    $ormNumBought = MultibuyQuery::create()
                        ->filterByPerId($input->PerID)
                        ->findOneByItemId($multibuyItem->getId());

                    if (!is_null($ormNumBought)) {
                        $ormNumBought->setPerId($input->PerID);
                        $ormNumBought->setCount($iMBCount);
                        $ormNumBought->setItemId($multibuyItem->getId());
                        $ormNumBought->save();
                    } else {
                        $ormNumBought = new Multibuy();
                        $ormNumBought->setPerId($input->PerID);
                        $ormNumBought->setCount($iMBCount);
                        $ormNumBought->setItemId($multibuyItem->getId());
                        $ormNumBought->save();
                    }
                } else { // count is zero, if it was positive before there is a multibuy record that needs to be deleted
                    $ormNumBought = MultibuyQuery::create()
                        ->filterByPerId($input->PerID)
                        ->findOneByItemId($multibuyItem->getId());

                    if (!is_null($ormNumBought)) {
                        $ormNumBought->delete();
                    }
                }
            }

            // New PaddleNum
            if ($input->PaddleNumID != 0) {
                $paddNum = new PaddleNum();

                $paddNum->setFrId($iCurrentFundraiser);
                $paddNum->setNum($input->Num);
                $paddNum->setPerId($input->PerID);

                $paddNum->save();
                // Existing record (update)
            } else {
                $paddNum = PaddleNumQuery::create()
                    ->findOneById($input->PaddleNumID);

                $paddNum->setFrId($iCurrentFundraiser);
                $paddNum->setNum($input->Num);
                $paddNum->setPerId($input->PerID);

                $paddNum->save();
            }
        }
        return $response->withJSON(['status' => "success"]);
    }

    return $response->withJSON(['status' => "failed", "test" => [$input->PerID, $input->PaddleNumID, $input->Num, $input->fundraiserID]]);
}


function getAllPersonsNum (Request $request, Response $response, array $args)
{
    //Get People for the drop-down
    $persons = PersonQuery::create()
        ->filterByDateDeactivated(NULL) // GDPR
            ->useFamilyQuery()
                ->addAsColumn('FamAddress1', \EcclesiaCRM\Map\FamilyTableMap::COL_FAM_ADDRESS1)
                ->addAsColumn('FamAddress2', \EcclesiaCRM\Map\FamilyTableMap::COL_FAM_ADDRESS2)
                ->addAsColumn('FamCity', \EcclesiaCRM\Map\FamilyTableMap::COL_FAM_CITY)
                ->addAsColumn('FamState', \EcclesiaCRM\Map\FamilyTableMap::COL_FAM_STATE)
            ->endUse()
        ->orderByLastName()
        ->orderByFirstName()
        ->find();

    $ormGetMaxNum = PaddleNumQuery::create()
        ->findByFrId($args['fundRaiserID']);

    $iNum = $ormGetMaxNum->count() + 1;

    return $response->withJson(["persons" => $persons->toArray(), "Number" => $iNum]);
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

function getPaddleNumList (Request $request, Response $response, array $args)
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

function addDonnors (Request $request, Response $response, array $args)
{
    $input = (object)$request->getParsedBody();

    if (isset ($input->fundraiserID)) {
        $iFundRaiserID = $input->fundraiserID;

        // Get the current fund raiser record
        $ormFRR = FundRaiserQuery::create()
                ->findOneById($iFundRaiserID);

        // Set current fundraiser
        $_SESSION['iCurrentFundraiser'] = $iFundRaiserID;

// Get all the people listed as donors for this fundraiser
        $ormDonors = DonatedItemQuery::create()
            ->addJoin(DonatedItemTableMap::COL_DI_DONOR_ID,PersonTableMap::COL_PER_ID,Criteria::LEFT_JOIN)
            ->addAsColumn('PersonID',PersonTableMap::COL_PER_ID)
            ->orderBy('PersonID')
            ->findByFrId($iFundRaiserID);


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

        return $response->withJSON(['status' => "success"]);
    }

    return $response->withJSON(['status' => "failed"]);
}
