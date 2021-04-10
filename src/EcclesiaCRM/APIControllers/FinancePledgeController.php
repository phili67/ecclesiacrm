<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\SessionUser;

class FinancePledgeController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function pledgeDetail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {// only in DepositSlipEditor
        if (!(SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isFinance())) {
            return $response->withStatus(401);
        }

        $plg = (object)$request->getParsedBody();

        $pledges = PledgeQuery::Create()
            ->leftJoinFamily()
            ->withColumn('Family.Name', 'FamilyName')
            ->withColumn('Family.Address1', 'Address1')
            ->leftJoinAutoPayment()
            ->withColumn('AutoPayment.CreditCard', 'CreditCard')
            ->withColumn('AutoPayment.BankName', 'BankName')
            ->withColumn('AutoPayment.EnableCreditCard', 'EnableCreditCard')
            ->withColumn('AutoPayment.EnableBankDraft', 'EnableBankDraft')
            ->leftJoinDeposit()
            ->findByGroupkey($plg->groupKey);


        return $response->write($pledges->toJson());
    }

    public function familyPledges(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $plg = (object)$request->getParsedBody();

        $pledges = PledgeQuery::Create()
            ->leftJoinPerson()
            ->withColumn('Person.FirstName', 'EnteredFirstName')
            ->withColumn('Person.LastName', 'EnteredLastName')
            ->leftJoinDonationFund()
            ->withColumn('DonationFund.Name', 'fundName')
            ->leftJoinDeposit()
            ->withColumn('Deposit.Closed', 'Closed')
            ->findByFamId($plg->famId);


        return $response->write($pledges->toJSON());
    }

    public function deletePledge(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $plg = (object)$request->getParsedBody();

        $pledge = PledgeQuery::Create()
            ->findOneById($plg->paymentId);

        if ( !is_null($pledge) ) {
            $pledge->delete();
        }

        return $response->withJson(['status' => "OK"]);
    }
}
