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

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\GroupQuery;

use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\Service\PastoralCareService;
use EcclesiaCRM\Service\DashboardItemService;

use Slim\Views\PhpRenderer;

class VIEWDashboardController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderDashboard (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/dashboard/');

        return $renderer->render($response, 'maindashboard.php', $this->argumentsFashboardArray());
    }

    public function argumentsFashboardArray ()
    {
        $depositData = false;  //Determine whether or not we should display the deposit line graph
        $deposits = Null;
        if (SessionUser::getUser()->isFinanceEnabled()) {
            $deposits = DepositQuery::create()->filterByDate(['min' => date('Y-m-d', strtotime('-90 days'))])->find();
            if (count($deposits) > 0) {
                $depositData = $deposits->toJSON();
            }
        }

        $showBanner = SystemConfig::getBooleanValue("bEventsOnDashboardPresence");

        // MenuEventCounts
        $peopleWithBirthDays = MenuEventsCount::getBirthDates();
        $Anniversaries = MenuEventsCount::getAnniversaries();
        $peopleWithBirthDaysCount = MenuEventsCount::getNumberBirthDates();
        $AnniversariesCount = MenuEventsCount::getNumberAnniversaries();

        // Dashboard People and so on event count
        $dshiS = new DashboardItemService();

        $dashboardCounts = $dshiS->getAllItems();

        // end of Dashboard people count

        $families = null;
        $persons = null;

        $numFamilies = 0;
        $numPersons = 0;

        if (SessionUser::getUser()->isGdrpDpoEnabled() && SystemConfig::getBooleanValue('bGDPR')) {
            $time = new \DateTime('now');
            $newtime = $time->modify('-' . SystemConfig::getValue('iGdprExpirationDate') . ' year')->format('Y-m-d');

            // when a family is completely deactivated : we seek the families with more than one member. A one person family = a fmaily with an address
            $subQuery = FamilyQuery::create()
                ->withColumn('Family.Id', 'FamId')
                ->leftJoinPerson()
                ->withColumn('COUNT(Person.Id)', 'cnt')
                ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
                ->groupById();//groupBy('Family.Id');

            $families = FamilyQuery::create()
                ->addSelectQuery($subQuery, 'res')
                ->where('res.cnt>1 AND Family.Id=res.FamId')
                ->find();

            $numFamilies = $families->count();

            // for the persons
            $persons = PersonQuery::create()
                ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP
                ->_or() // or : this part is unusefull, it's only for debugging
                ->useFamilyQuery()
                ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP, when a Family is completely deactivated
                ->endUse()
                ->orderByLastName()
                ->find();

            $numPersons = $persons->count();
        }

        $sCSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'sPageTitle'  => $sPageTitle = _('Welcome to') . ' ' . ChurchMetaData::getChurchName(),
            'dashboardCounts' => $dashboardCounts,
            'peopleWithBirthDays' => $peopleWithBirthDays,
            'numFamilies' => $numFamilies,
            'numPersons' => $numPersons,
            'CSPNonce' => $sCSPNonce,

        ];

        return $paramsArguments;
    }
}
