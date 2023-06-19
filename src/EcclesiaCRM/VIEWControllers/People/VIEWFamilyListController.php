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
use EcclesiaCRM\FamilyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\Map\FamilyTableMap;

use Slim\Views\PhpRenderer;

class VIEWFamilyListController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderFamilyList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/people/');

        $sMode = $args['mode'];

        if ( !( SessionUser::getUser()->isEditRecordsEnabled()
            || (strtolower($sMode)  == 'gdrp' && SessionUser::getUser()->isGdrpDpoEnabled())
            || (strtolower($sMode) == 'inactive' && SessionUser::getUser()->isEditRecordsEnabled())
            || (strtolower($sMode) == 'empty' && SessionUser::getUser()->isEditRecordsEnabled())
        )
        ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'familylist.php', $this->argumentsFamilyListArray($sMode));
    }

    public function argumentsFamilyListArray ($sMode='Active')
    {
        if (strtolower($sMode) == 'gdrp') {
            $time = new \DateTime('now');
            $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

            $subQuery = FamilyQuery::create()
                ->withColumn('Family.Id','FamId')
                ->leftJoinPerson()
                ->withColumn('COUNT(Person.Id)','cnt')
                ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
                ->groupById(FamilyTableMap::COL_FAM_ID);

            $families = FamilyQuery::create()
                ->addSelectQuery($subQuery, 'res')
                ->where('res.cnt>1 AND Family.Id=res.FamId')// only real family with more than one member will be showed here
                ->find();

        } else if (strtolower($sMode) == 'inactive') {
            if (SystemConfig::getBooleanValue('bGDPR')) {
                $time = new \DateTime('now');
                $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

                $families = FamilyQuery::create()
                    ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)// GDRP, when a person is completely deactivated, we only can see the person who are over a certain date
                    ->orderByName()
                    ->find();

                $subQuery = FamilyQuery::create()
                    ->withColumn('Family.Id','FamId')
                    ->leftJoinPerson()
                    ->withColumn('COUNT(Person.Id)','cnt')
                    ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)
                    ->groupById(FamilyTableMap::COL_FAM_ID);

                $families = FamilyQuery::create()
                    ->addSelectQuery($subQuery, 'res')
                    ->where('res.cnt>1 AND Family.Id=res.FamId')// only real family with more than one member will be showed here
                    ->find();
            } else {// we're always inactiv
                $time = new \DateTime('now');

                $families = FamilyQuery::create()
                    ->filterByDateDeactivated($time, Criteria::LESS_EQUAL)// GDRP, when a person is completely deactivated, we only can see the person who are over a certain date
                    ->orderByName()
                    ->find();

                $subQuery = FamilyQuery::create()
                    ->withColumn('Family.Id','FamId')
                    ->leftJoinPerson()
                    ->withColumn('COUNT(Person.Id)','cnt')
                    ->filterByDateDeactivated($time, Criteria::LESS_EQUAL)
                    ->groupById(FamilyTableMap::COL_FAM_ID);

                $families = FamilyQuery::create()
                    ->addSelectQuery($subQuery, 'res')
                    ->where('res.cnt>1 AND Family.Id=res.FamId')// only real family with more than one member will be showed here
                    ->find();

            }
        } else if (strtolower($sMode) == 'empty') {
            $subQuery = FamilyQuery::create()
                ->withColumn('Family.Id', 'FamId')
                ->leftJoinPerson()
                ->withColumn('COUNT(Person.Id)', 'cnt')
                ->filterByDateDeactivated(NULL)
                ->groupById();//groupBy('Family.Id');

            $families = FamilyQuery::create()
                ->addSelectQuery($subQuery, 'res')
                ->where('res.cnt=0 AND Family.Id=res.FamId') // The emptied addresses
                ->find();
        } else if (strtolower($sMode) == 'single') {
            $sMode = 'Single';
            $subQuery = FamilyQuery::create()
                ->withColumn('Family.Id','FamId')
                ->leftJoinPerson()
                ->usePersonQuery()
                ->filterByDateDeactivated( null)
                ->withColumn('COUNT(Person.Id)','cnt')
                ->endUse()
                ->filterByDateDeactivated(NULL)
                ->groupById(FamilyTableMap::COL_FAM_ID);

            $families = FamilyQuery::create()
                ->addSelectQuery($subQuery, 'res')
                ->where('res.cnt=1 AND Family.Id=res.FamId') // only real family with more than one member will be showed here
                ->find();
        } else {
            $sMode = 'Active';
            $subQuery = FamilyQuery::create()
                ->withColumn('Family.Id','FamId')
                ->leftJoinPerson()
                ->usePersonQuery()
                ->filterByDateDeactivated( null)
                ->withColumn('COUNT(Person.Id)','cnt')
                ->endUse()
                ->filterByDateDeactivated(NULL)
                ->groupById(FamilyTableMap::COL_FAM_ID);

            $families = FamilyQuery::create()
                ->addSelectQuery($subQuery, 'res')
                ->where('res.cnt>1 AND Family.Id=res.FamId') // only real family with more than one member will be showed here
                ->find();
        }

        if ($sMode == 'Single') {
            $sPageTitle = _("Single Persons");
        } else {
            $sPageTitle = _(ucfirst(_($sMode))) . ' : ' . ((strtolower($sMode) == 'empty') ? _('Addresses') : _('Family List'));
        }


        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'sMode'                => $sMode,
            'sCSPNonce'            => $sCSPNonce,
            'families'             => $families,
            'bNotGDRPNotEmpty'     => SessionUser::getUser()->isAddRecordsEnabled() && strtolower($sMode) != 'gdrp' && strtolower($sMode) != 'empty'
        ];

        return $paramsArguments;
    }

}
