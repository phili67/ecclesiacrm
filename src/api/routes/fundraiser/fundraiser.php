<?php

// Routes
use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\DonatedItemQuery;
use EcclesiaCRM\DonatedItem;
use EcclesiaCRM\SessionUser;

$app->group('/fundraiser', function () {

    $this->post('/replicate', 'replicateFundraiser');
    $this->delete('/donateditem', 'deleteDonatedItem');

});

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
