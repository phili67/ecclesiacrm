<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;


use EcclesiaCRM\PersonQuery;
use Propel\Runtime\Propel;
use EcclesiaCRM\FundRaiserQuery;
use EcclesiaCRM\PaddleNumQuery;
use EcclesiaCRM\Map\PersonTableMap;

use Slim\Views\PhpRenderer;

$app->group('/fundraiser', function () {
    $this->get('/donatedItemEditor/{donatedItemID:[0-9]+}/{CurrentFundraiser:[0-9]+}', 'renderDonatedItemEditor');
});

function renderDonatedItemEditor(Request $request, Response $response, array $args)
{
    $renderer = new PhpRenderer('templates/fundraiser/');

    if (!(SystemConfig::getBooleanValue("bEnabledFundraiser"))) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
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
        $rsDeposit = RunQuery($sSQL);
        extract(mysqli_fetch_array($rsDeposit));*/
    }

    if (strlen($iDonatedItemID) > 0) {
        //Editing....
        //Get all the data on this record

        $sSQL = "SELECT di_ID, di_Item, di_multibuy, di_donor_ID, di_buyer_ID,
                       a.per_FirstName as donorFirstName, a.per_LastName as donorLastName,
                       b.per_FirstName as buyerFirstName, b.per_LastName as buyerLastName,
                       di_title, di_description, di_sellprice, di_estprice, di_materialvalue,
                       di_minimum, di_picture
         FROM donateditem_di
         LEFT JOIN person_per a ON di_donor_ID=a.per_ID
         LEFT JOIN person_per b ON di_buyer_ID=b.per_ID
         WHERE di_ID = '" . $iDonatedItemID . "'";

        $connection = Propel::getConnection();

        $pdoDonatedItem = $connection->prepare($sSQL);
        $pdoDonatedItem->execute();

        $res = $pdoDonatedItem->fetch(PDO::FETCH_ASSOC);

        $sItem = $res['di_Item'];
        $bMultibuy = $res['di_multibuy'];
        $iDonor = $res['di_donor_ID'];
        $iBuyer = $res['di_buyer_ID'];
        //$sFirstName = $res['donorFirstName'];
        //$sLastName = $res['donorLastName'];
        //$sBuyerFirstName = $res['buyerFirstName'];
        //$sBuyerLastName = $res['buyerLastName'];
        $sTitle = $res['di_title'];
        $sDescription = $res['di_description'];
        $nSellPrice = $res['di_sellprice'];
        $nEstPrice = $res['di_estprice'];
        $nMaterialValue = $res['di_materialvalue'];
        $nMinimumPrice = $res['di_minimum'];
        $sPictureURL = $res['di_picture'];
    } else {
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
