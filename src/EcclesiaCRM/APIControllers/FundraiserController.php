<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

use EcclesiaCRM\TokenQuery;
use EcclesiaCRM\Token;

use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\Utils\InputUtils;

class FundraiserController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    function donatedItemCurrentPicture(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->DonatedItemID)) {
            if ($input->DonatedItemID == -1) {
                $token = TokenQuery::create()->filterByReferenceId(-2)->findOne();
                if (!is_null($token)) {
                    $path = $token->getComment();
                    $token->delete();
                    /*$path[0] = "'";
                    $path = substr($path,1,-1);*/
                    return $response->withJSON(['status' => "success", "picture" => $path]);
                }
            } else {
                $donItem = DonatedItemQuery::create()
                    ->findOneById($input->DonatedItemID);
                return $response->withJSON(['status' => "success", "picture" => $donItem->getPicture()]);
            }
        }

        return $response->withJSON(['status' => "failed"]);
    }

    function donatedItemSubmitPicture(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //DonatedItemID": window.CRM.donatedItemID,"pathFile"

        $input = (object)$request->getParsedBody();

        if (isset($input->DonatedItemID) && isset($input->pathFile)) {
            if ($input->DonatedItemID > 0) {
                $donItem = DonatedItemQuery::create()
                    ->findOneById($input->DonatedItemID);

                $donItem->setPicture($input->pathFile);

                $donItem->save();
            } else {
                TokenQuery::create()->filterByReferenceId(-2)->delete();
                $token = new Token();
                $token->build("filemanager", -2, $input->pathFile);
                $token->save();
                //
            }

            return $response->withJSON(['status' => "success"]);
        }

        return $response->withJSON(['status' => "failed"]);
    }

    function paddleNumInfo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->PerID) && isset($input->Num) && isset ($input->fundraiserID)) {
            $ormPaddleNum = PaddleNumQuery::create()
                ->filterByFrId($input->fundraiserID)
                ->filterByNum($input->Num)
                ->findOneByPerId($input->PerID);

            return $response->withJSON(['status' => "success", 'iPaddleNumID' => $ormPaddleNum->getId()]);
        }

        return $response->withJSON(['status' => "failed"]);
    }

    function addPaddleNum(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->PerID) && isset ($input->PaddleNumID) && isset($input->Num) && isset ($input->fundraiserID)) {
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
                if ($input->PaddleNumID == -1) {
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

    function getAllPersonsNum(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    function findFundRaiser(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    function getAllFundraiserForID(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $sSQL = "SELECT di_ID, di_Item, di_multibuy,
	                a.per_FirstName as donorFirstName, a.per_LastName as donorLastName,
	                b.per_FirstName as buyerFirstName, b.per_LastName as buyerLastName,
	                di_title, di_sellprice, di_estprice, di_materialvalue, di_minimum, di_picture
	         FROM donateditem_di
	         LEFT JOIN person_per a ON di_donor_ID=a.per_ID
	         LEFT JOIN person_per b ON di_buyer_ID=b.per_ID
	         WHERE di_FR_ID = '" . $args['FundRaiserID'] . "' ORDER BY di_multibuy,SUBSTR(di_item,1,1),cast(SUBSTR(di_item,2) as unsigned integer),SUBSTR(di_item,4)";

        $connection = Propel::getConnection();

        $pdoDonatedItems = $connection->prepare($sSQL);
        $pdoDonatedItems->execute();

        return $response->withJSON(['DonatedItems' => $pdoDonatedItems->fetchAll(\PDO::FETCH_ASSOC)]);
    }

    function replicateFundraiser(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    function deleteDonatedItem(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    function donatedItemSubmitFundraiser(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

            if ($sPictureURL[0] == "'") {
                $sPictureURL = str_replace("'", "", $sPictureURL);
            }

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

    function deletePaddleNum(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if (isset($input->fundraiserID) && isset ($input->pnID)) {
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

    function getPaddleNumList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    function addDonnors(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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
                ->addJoin(DonatedItemTableMap::COL_DI_DONOR_ID, PersonTableMap::COL_PER_ID, Criteria::LEFT_JOIN)
                ->addAsColumn('PersonID', PersonTableMap::COL_PER_ID)
                ->orderBy('PersonID')
                ->findByFrId($iFundRaiserID);


            $extraPaddleNum = 1;
            $maxPN = PaddleNumQuery::create()
                ->addAsColumn('Max', 'MAX(' . PaddleNumTableMap::COL_PN_NUM . ")")
                ->findOneByFrId($iFundRaiserID);

            if (!is_null($maxPN)) {
                $extraPaddleNum = $maxPN->getMax() + 1;
            }


// Go through the donors, add buyer records for any who don't have one yet
            foreach ($ormDonors as $donor) {
                $buyer = PaddleNumQuery::create()
                    ->filterByFrId($iFundRaiserID)
                    ->findOneByPerId($donor->getDonorId());

                if (is_null($buyer)) {
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
}
