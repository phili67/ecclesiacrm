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
use Slim\Http\Response;
use Slim\Http\ServerRequest;


use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\SessionUser;

class FinancePledgeController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function pledgeDetail(ServerRequest $request, Response $response, array $args): Response
    {// only in DepositSlipEditor
        $plg = (object)$request->getParsedBody();

        if (!( SessionUser::getUser()->isFinanceEnabled() and isset($plg->groupKey) )) {
            return $response->withStatus(401);
        }

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

    public function familyPledges(ServerRequest $request, Response $response, array $args): Response
    {
        $plg = (object)$request->getParsedBody();

        if (!( SessionUser::getUser()->isFinanceEnabled() and isset($plg->famId) )) {
            return $response->withStatus(401);
        }

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

    public function deletePledge(ServerRequest $request, Response $response, array $args): Response
    {
        $plg = (object)$request->getParsedBody();

        if (!( SessionUser::getUser()->isFinanceEnabled() and isset($plg->paymentId) )) {
            return $response->withStatus(401);
        }

        $pledge = PledgeQuery::Create()
            ->findOneById($plg->paymentId);

        if ( !is_null($pledge) ) {
            $pledge->delete();
        }

        return $response->withJson(['status' => "OK"]);
    }
}
