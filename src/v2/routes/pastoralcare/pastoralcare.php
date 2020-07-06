<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2019-03-23
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *
 ******************************************************************************/

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\Map\PastoralCareTableMap;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Service\PastoralCareService;
use EcclesiaCRM\ListOptionQuery;

use Slim\Views\PhpRenderer;

$app->group('/pastoralcare', function () {
    $this->get('/person/{personId:[0-9]+}', 'renderPastoralCarePerson');
    $this->get('/family/{familyId:[0-9]+}', 'renderPastoralCareFamily');
    $this->get('/dashboard', 'renderPastoralCareDashboard');
    $this->get('/membersList', 'renderPastoralCareMembersList');
});

function renderPastoralCareDashboard (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/pastoralcare/');

    if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }

    return $renderer->render($response, 'pastoralcaredashboard.php', argumentsPastoralDashboardArray());
}


function argumentsPastoralDashboardArray ()
{
    $currentPastorId = SessionUser::getUser()->getPerson()->getID();

    $sPageTitle = _("Pastoral care Dashboard");

    $sRootDocument   = SystemURLs::getDocumentRoot();
    $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
    $sCSPNonce       = SystemURLs::getCSPNonce();

    $pastoralService = new PastoralCareService();

    $pastoralServiceStats = $pastoralService->stats();


    $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
        'sRootDocument'        => $sRootDocument,
        'sPageTitle'           => $sPageTitle,
        'currentPastorId'      => $currentPastorId,
        'sDateFormatLong'      => $sDateFormatLong,
        'sCSPNonce'            => $sCSPNonce,
        'Stats'                => $pastoralServiceStats
    ];
    return $paramsArguments;
}


function renderPastoralCarePerson (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/pastoralcare/');

    $personId = $args['personId'];

    if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }

    return $renderer->render($response, 'pastoralcareperson.php', argumentsPastoralPersonListArray($personId));
}

function argumentsPastoralPersonListArray ($currentPersonID=0)
{
    $currentPastorId = SessionUser::getUser()->getPerson()->getID();


    $ormPastoralCares = PastoralCareQuery::Create()
                          ->orderByDate(Propel\Runtime\ActiveQuery\Criteria::DESC)
                          ->leftJoinWithPastoralCareType()
                          ->findByPersonId($currentPersonID);

    $ormPastors = PastoralCareQuery::Create()
                          ->groupBy(PastoralCareTableMap::COL_PST_CR_PASTOR_ID)
                          ->orderByPastorName(Propel\Runtime\ActiveQuery\Criteria::DESC)
                          ->findByPersonId($currentPersonID);

    $ormPastoralTypeCares = PastoralCareTypeQuery::Create()
                          ->find();

    //Get name
    $person = PersonQuery::Create()->findOneById ($currentPersonID);

    $sPageTitle = _("Individual Pastoral care")."  : \"".$person->getFullName()."\"";

    $sRootDocument   = SystemURLs::getDocumentRoot();
    $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
    $sCSPNonce       = SystemURLs::getCSPNonce();

    $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
                       'sRootDocument'        => $sRootDocument,
                       'sPageTitle'           => $sPageTitle,
                       'ormPastoralCares'     => $ormPastoralCares,
                       'currentPersonID'      => $currentPersonID,
                       'currentPastorId'      => $currentPastorId,
                       'ormPastors'           => $ormPastors,
                       'ormPastoralTypeCares' => $ormPastoralTypeCares,
                       'person'               => $person,
                       'sDateFormatLong'      => $sDateFormatLong,
                       'sCSPNonce'            => $sCSPNonce
                       ];
   return $paramsArguments;
}

function renderPastoralCareFamily (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/pastoralcare/');

    $familyId = $args['familyId'];

    if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }

    return $renderer->render($response, 'pastoralcarefamily.php', argumentsPastoralFamilyListArray($familyId));
}

function argumentsPastoralFamilyListArray ($currentFamilyID=0)
{
    $currentPastorId = SessionUser::getUser()->getPerson()->getID();


    $ormPastoralCares = PastoralCareQuery::Create()
        ->orderByDate(Propel\Runtime\ActiveQuery\Criteria::DESC)
        ->leftJoinWithPastoralCareType()
        ->findByFamilyId($currentFamilyID);

    $ormPastors = PastoralCareQuery::Create()
        ->groupBy(PastoralCareTableMap::COL_PST_CR_PASTOR_ID)
        ->orderByPastorName(Propel\Runtime\ActiveQuery\Criteria::DESC)
        ->findByFamilyId($currentFamilyID);

    $ormPastoralTypeCares = PastoralCareTypeQuery::Create()
        ->find();

    //Get name
    $family = FamilyQuery::Create()->findOneById ($currentFamilyID);

    $sPageTitle = _("Family Pastoral care")."  : \"".$family->getName()."\"";

    $sRootDocument   = SystemURLs::getDocumentRoot();
    $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
    $sCSPNonce       = SystemURLs::getCSPNonce();

    $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
        'sRootDocument'        => $sRootDocument,
        'sPageTitle'           => $sPageTitle,
        'ormPastoralCares'     => $ormPastoralCares,
        'currentFamilyID'      => $currentFamilyID,
        'currentPastorId'      => $currentPastorId,
        'ormPastors'           => $ormPastors,
        'ormPastoralTypeCares' => $ormPastoralTypeCares,
        'family'               => $family,
        'sDateFormatLong'      => $sDateFormatLong,
        'sCSPNonce'            => $sCSPNonce
    ];
    return $paramsArguments;
}




function renderPastoralCareMembersList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/pastoralcare/');


    if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }

    return $renderer->render($response, 'pastoralcareMembersList.php', argumentsPastoralCareMembersListListArray());
}

function argumentsPastoralCareMembersListListArray ()
{
    $currentPastorId = SessionUser::getUser()->getPerson()->getID();

    $ormPastoralTypeCares = PastoralCareTypeQuery::Create()
        ->find();

    //Get name
    $sPageTitle = _("Pastoral care members list by classification");

    $sRootDocument   = SystemURLs::getDocumentRoot();
    $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
    $sCSPNonce       = SystemURLs::getCSPNonce();

    $memberTypes     = ListOptionQuery::create()
        ->orderByOptionName()
        ->findById(1);

    $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
        'sRootDocument'        => $sRootDocument,
        'sPageTitle'           => $sPageTitle,
        'currentPastorId'      => $currentPastorId,
        'ormPastoralTypeCares' => $ormPastoralTypeCares,
        'sDateFormatLong'      => $sDateFormatLong,
        'aMemberTypes'         => $memberTypes->toArray(),
        'sCSPNonce'            => $sCSPNonce
    ];

    return $paramsArguments;
}
