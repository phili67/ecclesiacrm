<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\GdprInfoQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PropertyQuery;


use Slim\Views\PhpRenderer;

$app->group('/gdpr', function (RouteCollectorProxy $group) {
    $group->get('', 'renderGdprDashboard');
    $group->get('/', 'renderGdprDashboard');
    $group->get('/gdprdatastructure', 'renderGdprDataStructure');
});

function renderGdprDashboard (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/gdpr/');

    if ( !( SessionUser::getUser()->isGdrpDpoEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'dashboard.php', argumentsGdprDashBoardArray());
}

function argumentsGdprDashBoardArray ()
{
   $paramsArguments = ['sRootPath'        => SystemURLs::getRootPath(),
                       'sRootDocument'    => SystemURLs::getDocumentRoot(),
                       'sPageTitle'       => _('GDPR Dashboard'),
                       'gdprSigner'       => SystemConfig::getValue('sGdprDpoSigner'),
                       'gdprSignerEmail'  => SystemConfig::getValue('sGdprDpoSignerEmail')
                      ];

   return $paramsArguments;
}

function renderGdprDataStructure (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/gdpr/');

    if ( !( SessionUser::getUser()->isGdrpDpoEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'gdprdatastructure.php', argumentsGdprDataStructureArray());
}

function argumentsGdprDataStructureArray ()
{

    // for persons
    $personCustMasts = PersonCustomMasterQuery::Create()
          ->orderByCustomName()
          ->find();

    $personInfos = GdprInfoQuery::Create()->filterByAbout('Person')->find();

    $personProperties = PropertyQuery::Create()->filterByProClass('p')->find();

    // for families
    $familyCustMasts = FamilyCustomMasterQuery::Create()
          ->orderByCustomName()
          ->find();

    $familyInfos = GdprInfoQuery::Create()->filterByAbout('Family')->find();

    $familyProperties = PropertyQuery::Create()->filterByProClass('f')->find();

    // for pastoral care
    $pastoralCareTypes = PastoralCareTypeQuery::Create()->find();


    $paramsArguments = ['sRootPath'        => SystemURLs::getRootPath(),
                       'sRootDocument'     => SystemURLs::getDocumentRoot(),
                       'sPageTitle'        => _('GDPR Data Structure'),
                       'personCustMasts'   => $personCustMasts,
                       'personInfos'       => $personInfos,
                       'personProperties'  => $personProperties,
                       'familyCustMasts'   => $familyCustMasts,
                       'familyInfos'       => $familyInfos,
                       'familyProperties'  => $familyProperties,
                       'pastoralCareTypes' => $pastoralCareTypes
                      ];

   return $paramsArguments;
}
