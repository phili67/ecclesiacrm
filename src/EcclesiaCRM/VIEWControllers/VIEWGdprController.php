<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\GdprInfoQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PropertyQuery;

use Slim\Views\PhpRenderer;

class VIEWGdprController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderGdprDashboard (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/gdpr/');

        if ( !( SessionUser::getUser()->isGdrpDpoEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'dashboard.php', $this->argumentsGdprDashBoardArray());
    }

    public function argumentsGdprDashBoardArray ()
    {
        $paramsArguments = ['sRootPath'        => SystemURLs::getRootPath(),
            'sRootDocument'    => SystemURLs::getDocumentRoot(),
            'sPageTitle'       => _('GDPR Dashboard'),
            'gdprSigner'       => SystemConfig::getValue('sGdprDpoSigner'),
            'gdprSignerEmail'  => SystemConfig::getValue('sGdprDpoSignerEmail')
        ];

        return $paramsArguments;
    }

    public function renderGdprDataStructure (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/gdpr/');

        if ( !( SessionUser::getUser()->isGdrpDpoEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'gdprdatastructure.php', $this->argumentsGdprDataStructureArray());
    }

    public function argumentsGdprDataStructureArray ()
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

}
