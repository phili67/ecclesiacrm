<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

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

class VIEWFundraiserController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderPaddleNumList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/fundraiser/');

        if (!(SystemConfig::getBooleanValue("bEnabledFundraiser"))) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'paddleNumList.php', $this->argumentsPaddleNumListArray($args['CurrentFundraiser']));
    }

    public function argumentsPaddleNumListArray($CurrentFundraiser)
    {
        $sPageTitle = _("Donated Item Editor");

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle' => $sPageTitle,
            'iFundRaiserID' => $CurrentFundraiser
        ];

        return $paramsArguments;
    }

    public function renderFindFundRaiser(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/fundraiser/');

        if (!(SystemConfig::getBooleanValue("bEnabledFundraiser"))) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'findFundRaiser.php', $this->argumentsFindFundRaiserArray());
    }

    public function argumentsFindFundRaiserArray()
    {
        $sPageTitle = _("Donated Item Editor");

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle' => $sPageTitle
        ];

        return $paramsArguments;
    }

    public function renderDonatedItemEditor(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/fundraiser/');

        if (!(SystemConfig::getBooleanValue("bEnabledFundraiser"))) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'donatedItemEditor.php', $this->argumentsDonatedItemEditorArray($args['donatedItemID'], $args['CurrentFundraiser']));
    }

    public function argumentsDonatedItemEditorArray($iDonatedItemID, $iCurrentFundraiser)
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

    

    public function renderFundraiserEditor(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/fundraiser/');

        if (!(SystemConfig::getBooleanValue("bEnabledFundraiser"))) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $iFundRaiserID = -1;

        if (isset($args['FundRaiserID'])) {
            $iFundRaiserID = $args['FundRaiserID'];
        }

        $linkBack = '';
        if (isset($args['linkBack'])) {
            $linkBack = $args['linkBack'];
        }

        return $renderer->render($response, 'fundRaiserEditor.php', $this->argumentsFundRaiserEditorArray($iFundRaiserID, $linkBack));
    }

    public function argumentsFundRaiserEditorArray($iFundRaiserID, $linkBack)
    {       

        if ($iFundRaiserID > 0) {
            // Get the current fund raiser record
            $ormFRR = FundRaiserQuery::create()
                ->findOneById($iFundRaiserID);
            // Set current fundraiser
            $_SESSION['iCurrentFundraiser'] = $iFundRaiserID;
        }

        if ($iFundRaiserID > 0) {
            $sPageTitle = _('Fundraiser') . ' #' . $iFundRaiserID . ' ' . $ormFRR->getTitle();
        } else {
            $sPageTitle = _('Create New Fund Raiser');
        }

        $sCSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sCSPNonce'     => $sCSPNonce,
            'sPageTitle'    => $sPageTitle,
            'iFundRaiserID' => $iFundRaiserID,
            'linkBack'      => $linkBack
        ];

        return $paramsArguments;
    }
}
