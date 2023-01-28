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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use EcclesiaCRM\DonationFundQuery;
use EcclesiaCRM\DonationFund;

class FinanceDonationFundController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllDonationFunds(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( SessionUser::getUser()->isFinanceEnabled() ) {
            return $response->write(DonationFundQuery::Create()->find()->toJSON());
        }

        return $response->withStatus(401);
    }

    public function editDonationFund(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $donation = (object)$request->getParsedBody();

        if ( SessionUser::getUser()->isFinanceEnabled() and isset($donation->fundId) ) {
            return $response->write(DonationFundQuery::Create()->findOneById($donation->fundId)->toJSON());
        }

        return $response->withStatus(401);
    }

    public function setDonationFund(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $fund = (object)$request->getParsedBody();

        if ( SessionUser::getUser()->isFinanceEnabled() and isset($fund->fundId) and isset($fund->Name) and isset($fund->Description)) {

            $donation = DonationFundQuery::Create()->findOneById($fund->fundId);

            $donation->setName($fund->Name);
            $donation->setDescription($fund->Description);
            $donation->setActive(($fund->Activ) ? 1 : 0);

            $donation->save();

            return $response->write(json_encode(['status' => "OK"]));
        }

        return $response->withStatus(401);
    }

    public function deleteDonationFund(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $fund = (object)$request->getParsedBody();

        if ( SessionUser::getUser()->isFinanceEnabled() and isset($fund->fundId) ) {
            $donation = DonationFundQuery::Create()->findOneById($fund->fundId);
            $donation->delete();

            return $response->write(json_encode(['status' => "OK"]));
        }

        return $response->withStatus(401);
    }

    public function createDonationFund(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $fund = (object)$request->getParsedBody();

        if ( SessionUser::getUser()->isFinanceEnabled() and isset($fund->Name) and isset($fund->Description) ) {
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
