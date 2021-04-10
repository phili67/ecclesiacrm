<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;


use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\DonatedItemQuery;

use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\PaddleNumQuery;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\DonatedItemTableMap;


use Slim\Views\PhpRenderer;
use Propel\Runtime\ActiveQuery\Criteria;

$app->group('/fundraiser', function (RouteCollectorProxy $group) {
    $group->get('/donatedItemEditor/{donatedItemID:[0-9]+}/{CurrentFundraiser:[0-9]+}', 'renderDonatedItemEditor');
    $group->get('/find', 'renderFindFundRaiser');
    $group->get('/paddlenum/list/{CurrentFundraiser:[0-9]+}', 'renderPaddleNumList');
});



function renderPaddleNumList(Request $request, Response $response, array $args)
{
    $renderer = new PhpRenderer('templates/fundraiser/');

    if (!(SystemConfig::getBooleanValue("bEnabledFundraiser"))) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'paddleNumList.php', argumentsPaddleNumListArray($args['CurrentFundraiser']));
}

function argumentsPaddleNumListArray($CurrentFundraiser)
{
    $sPageTitle = _("Donated Item Editor");

    $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
        'sRootDocument' => SystemURLs::getDocumentRoot(),
        'sPageTitle' => $sPageTitle,
        'iFundRaiserID' => $CurrentFundraiser
    ];

    return $paramsArguments;
}

function renderFindFundRaiser(Request $request, Response $response, array $args)
{
    $renderer = new PhpRenderer('templates/fundraiser/');

    if (!(SystemConfig::getBooleanValue("bEnabledFundraiser"))) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'findFundRaiser.php', argumentsFindFundRaiserArray());
}

function argumentsFindFundRaiserArray()
{
    $sPageTitle = _("Donated Item Editor");

    $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
        'sRootDocument' => SystemURLs::getDocumentRoot(),
        'sPageTitle' => $sPageTitle
    ];

    return $paramsArguments;
}

function renderDonatedItemEditor(Request $request, Response $response, array $args)
{
    $renderer = new PhpRenderer('templates/fundraiser/');

    if (!(SystemConfig::getBooleanValue("bEnabledFundraiser"))) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'donatedItemEditor.php', argumentsDonatedItemEditorArray($args['donatedItemID'], $args['CurrentFundraiser']));
}

function argumentsDonatedItemEditorArray($iDonatedItemID, $iCurrentFundraiser)
{
    // Get the current fundraiser data
    if ($iCurrentFundraiser) {
        $ormDeposit = FundRaiserQuery::create()
            ->findOneById($iCurrentFundraiser);

        /*$sSQL = 'SELECT * from fundraiser_fr WHERE fr_ID = ' . $iCurrentFundraiser;
        $rsDeposit = OunQuery($sSQL);
        extract(mysqli_fetch_array($rsDeposit));*/
    }

    //Adding....
    //Set defaults
    $sItem = '';
    $bMultibuy = 0;
    $iDonor = 0;
    $iBuyer = 0;
    $sTitle = '';
    $sDescription = '';
    $nSellPrice = 0.0;
    $nEstPrice = 0.0;
    $nMaterialValue = 0.0;
    $nMinimumPrice = 0.0;
    $sPictureURL = '';

    if (strlen($iDonatedItemID) > 0) {
        //Editing....
        //Get all the data on this record

        $ormDonatedItem = DonatedItemQuery::create()
            ->addAlias('a', PersonTableMap::TABLE_NAME)
            ->addJoin(DonatedItemTableMap::COL_DI_DONOR_ID, PersonTableMap::alias('a', PersonTableMap::COL_PER_ID), Criteria::LEFT_JOIN)
            ->addAlias('b', PersonTableMap::TABLE_NAME)
            ->addJoin(DonatedItemTableMap::COL_DI_BUYER_ID, PersonTableMap::alias('b', PersonTableMap::COL_PER_ID), Criteria::LEFT_JOIN)
            ->addAsColumn('DonorFirstName', PersonTableMap::alias('a', PersonTableMap::COL_PER_FIRSTNAME))
            ->addAsColumn('DonorLastName', PersonTableMap::alias('a', PersonTableMap::COL_PER_LASTNAME))
            ->addAsColumn('BuyerFirstName', PersonTableMap::alias('b', PersonTableMap::COL_PER_FIRSTNAME))
            ->addAsColumn('BuyerLastName', PersonTableMap::alias('b', PersonTableMap::COL_PER_LASTNAME))
            ->findOneById($iDonatedItemID);

        if ( !is_null ($ormDonatedItem) ) {

            $sItem = $ormDonatedItem->getItem();
            $bMultibuy = $ormDonatedItem->getMultibuy();
            $iDonor = $ormDonatedItem->getDonorId();
            $iBuyer = $ormDonatedItem->getBuyerId();
            //$sFirstName = $ormDonatedItem->getDonorFirstName();
            //$sLastName = $ormDonatedItem->getDonorLastName();
            //$sBuyerFirstName = $ormDonatedItem->getBuyerFirstName();
            //$sBuyerLastName = $ormDonatedItem->getBuyerLastName();
            $sTitle = $ormDonatedItem->getTitle();
            $sDescription = $ormDonatedItem->getDescription();
            $nSellPrice = $ormDonatedItem->getSellprice();
            $nEstPrice = $ormDonatedItem->getEstprice();
            $nMaterialValue = $ormDonatedItem->getMaterialValue();
            $nMinimumPrice = $ormDonatedItem->getMinimum();
            $sPictureURL = $ormDonatedItem->getPicture();
        }
    }


    //Get People for the drop-down
    $ormPeople = PersonQuery::create()
        ->filterByDateDeactivated(NULL)
        ->orderByLastName()
        ->orderByFirstName()
        ->find();

    //Get Paddles for the drop-down
    $ormPaddleNum = PaddleNumQuery::create()
        ->usePersonQuery()
        ->addAsColumn('BuyerFirstName', PersonTableMap::COL_PER_FIRSTNAME)
        ->addAsColumn('BuyerLastName', PersonTableMap::COL_PER_LASTNAME)
        ->endUse()
        ->findByFrId($iCurrentFundraiser);

    $sPageTitle = _("Donated Item Editor");

    if ($sPictureURL[0] == "'") {
        $sPictureURL = substr($sPictureURL,1,-1);
    }

    $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
        'sRootDocument' => SystemURLs::getDocumentRoot(),
        'sPageTitle' => $sPageTitle,
        'iDonatedItemID' => ($iDonatedItemID == 0)?-1:$iDonatedItemID,
        'iCurrentFundraiser' => $iCurrentFundraiser,
        'sItem' => $sItem,
        'bMultibuy' => $bMultibuy,
        'iDonor' => $iDonor,
        'iBuyer' => $iBuyer,
        'sTitle' => $sTitle,
        'sDescription' => $sDescription,
        'nSellPrice' => $nSellPrice,
        'nEstPrice' => $nEstPrice,
        'nMaterialValue' => $nMaterialValue,
        'nMinimumPrice' => $nMinimumPrice,
        'sPictureURL' => $sPictureURL,
        'ormPeople' => $ormPeople,
        'ormPaddleNum' => $ormPaddleNum
    ];

    return $paramsArguments;
}
