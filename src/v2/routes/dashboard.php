<?php

/*******************************************************************************
 *
 *  filename    : route/backup.php
 *  last change : 2019-11-21
 *  description : manage the backup
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;

use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\Service\PastoralCareService;



use Slim\Views\PhpRenderer;

$app->group('/dashboard', function (RouteCollectorProxy $group) {
    $group->get('', 'renderDashboard');
    $group->get('/', 'renderDashboard');
});

function renderDashboard (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/dashboard/');

    if (!(SessionUser::getUser()->isFinanceEnabled() || SessionUser::getUser()->isMainDashboardEnabled() || SessionUser::getUser()->isPastoralCareEnabled()) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . SessionUser::getUser()->getPersonId());
    }

    return $renderer->render($response, 'maindashboard.php', argumentsFashboardArray());
}

function argumentsFashboardArray ()
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

    $peopleWithBirthDays = MenuEventsCount::getBirthDates();
    $Anniversaries = MenuEventsCount::getAnniversaries();
    $peopleWithBirthDaysCount = MenuEventsCount::getNumberBirthDates();
    $AnniversariesCount = MenuEventsCount::getNumberAnniversaries();

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

    $pastoralServiceStats = null;
    $range = null;
    $caresPersons = null;
    $caresFamilies = null;

    if (SessionUser::getUser()->isPastoralCareEnabled()) {

        // Persons and Families never been searched

        $pastoralService = new PastoralCareService();

        /*
         *  get all the stats of the pastoral care service
         */

        $pastoralServiceStats = $pastoralService->stats();

        /*
         *  get the period for the pastoral care
         */

        $range = $pastoralService->getRange();

        /*
         *  last pastoralcare search persons for the current system user
         */

        $caresPersons = $pastoralService->lastContactedPersons();

        /*
         *  last pastoralcare search families for the current system user
         */

        $caresFamilies = $pastoralService->lastContactedFamilies();
    }

    $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
                        'sRootDocument' => SystemURLs::getDocumentRoot(),
                        'sPageTitle'  => $sPageTitle = _('Welcome to') . ' ' . ChurchMetaData::getChurchName(),
                        'deposits' => $deposits,
                        'depositData' => $depositData,
                        'showBanner' => $showBanner,
                        'peopleWithBirthDays' => $peopleWithBirthDays,
                        'Anniversaries' => $Anniversaries,
                        'peopleWithBirthDaysCount' => $peopleWithBirthDaysCount,
                        'AnniversariesCount' => $AnniversariesCount,
                        'families' => $families,
                        'numFamilies' => $numFamilies,
                        'persons' => $persons,
                        'numPersons' => $numPersons,
                        'pastoralServiceStats' => $pastoralServiceStats,
                        'range' => $range,
                        'caresPersons' => $caresPersons,
                        'caresFamilies' => $caresFamilies
                      ];

   return $paramsArguments;
}
