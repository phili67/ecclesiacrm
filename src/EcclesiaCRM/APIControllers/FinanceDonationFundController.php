<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\SessionUser;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;


use EcclesiaCRM\DonationFundQuery;
use EcclesiaCRM\DonationFund;

class FinanceDonationFundController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllDonationFunds(ServerRequest $request, Response $response, array $args): Response
    {
        if ( SessionUser::getUser()->isDonationFundEnabled() ) {
            return $response->write(DonationFundQuery::Create()->find()->toJSON());
        }

        return $response->withStatus(401);
    }

    public function editDonationFund(ServerRequest $request, Response $response, array $args): Response
    {
        $donation = (object)$request->getParsedBody();

        if ( SessionUser::getUser()->isDonationFundEnabled() and isset($donation->fundId) ) {
            return $response->write(DonationFundQuery::Create()->findOneById($donation->fundId)->toJSON());
        }

        return $response->withStatus(401);
    }

    public function setDonationFund(ServerRequest $request, Response $response, array $args): Response
    {
        $fund = (object)$request->getParsedBody();

        if ( SessionUser::getUser()->isDonationFundEnabled() and isset($fund->fundId) and isset($fund->Name) and isset($fund->Description)) {

            $donation = DonationFundQuery::Create()->findOneById($fund->fundId);

            $donation->setName($fund->Name);
            $donation->setDescription($fund->Description);
            $donation->setActive(($fund->Activ) ? 1 : 0);

            $donation->save();

            return $response->write(json_encode(['status' => "OK"]));
        }

        return $response->withStatus(401);
    }

    public function deleteDonationFund(ServerRequest $request, Response $response, array $args): Response
    {
        $fund = (object)$request->getParsedBody();

        if ( SessionUser::getUser()->isDonationFundEnabled() and isset($fund->fundId) ) {
            $donation = DonationFundQuery::Create()->findOneById($fund->fundId);
            $donation->delete();

            return $response->write(json_encode(['status' => "OK"]));
        }

        return $response->withStatus(401);
    }

    public function createDonationFund(ServerRequest $request, Response $response, array $args): Response
    {
        $fund = (object)$request->getParsedBody();

        if ( SessionUser::getUser()->isDonationFundEnabled() and isset($fund->Name) and isset($fund->Description) ) {
            $donation = new DonationFund();

            $donation->setName($fund->Name);
            $donation->setDescription($fund->Description);
            $donation->setActive(($fund->Activ) ? 1 : 0);

            $donation->save();

            return $response->write(json_encode(['status' => "OK"]));
        }

        return $response->withStatus(401);
    }
}
