<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/06
//

namespace EcclesiaCRM\VIEWControllers;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;


use Slim\Views\PhpRenderer;

class VIEWPersonListController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderPersonList (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        $sMode = $args['mode'];

        if ( !( SessionUser::getUser()->isEditRecordsEnabled()
            || (strtolower($sMode)  == 'gdrp' && SessionUser::getUser()->isGdrpDpoEnabled())
            || (strtolower($sMode) == 'inactive' && SessionUser::getUser()->isEditRecordsEnabled())
        )
        ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'personlist.php', $this->argumentsPersonListArray($sMode));
    }

    public function argumentsPersonListArray ($sMode='Active')
    {
        if (strtolower($sMode) == 'gdrp') {
            $time = new \DateTime('now');
            $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

            $persons = PersonQuery::create()
                ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP, when a person is completely deactivated
                ->_or() // or : this part is unusefull, it's only for debugging
                ->useFamilyQuery()
                ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP, when a Family is completely deactivated
                ->endUse()
                ->orderByLastName()
                ->find();

        } else if (strtolower($sMode) == 'inactive') {
            if (SystemConfig::getBooleanValue('bGDPR')) {
                $time = new \DateTime('now');
                $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

                $persons = PersonQuery::create()
                    ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)// GDRP, when a person isn't under GDRP but deactivated, we only can see the person who are over a certain date
                    ->_or()// this part is unusefull, it's only for debugging
                    ->useFamilyQuery()
                    ->filterByDateDeactivated($newtime, Criteria::GREATER_THAN)// GDRP, when a Family is completely deactivated
                    ->endUse()
                    ->orderByLastName()
                    ->find();
            } else {
                $time = new \DateTime('now');

                $persons = PersonQuery::create()
                    ->filterByDateDeactivated($time, Criteria::LESS_EQUAL)
                    ->orderByLastName()
                    ->find();
            }
        } else {
            $sMode = 'Active';
            $persons = PersonQuery::create()
                ->filterByDateDeactivated(null)
                ->orderByLastName()
                ->find();
        }

        $sPageTitle = _(ucfirst(_($sMode))) . ' : ' . _('Person List');

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'sMode'                => $sMode,
            'sCSPNonce'            => $sCSPNonce,
            'persons'              => $persons,
            'bNotGDRP'             => SessionUser::getUser()->isAddRecordsEnabled() && strtolower($sMode) != 'gdrp'
        ];

        return $paramsArguments;
    }

}
