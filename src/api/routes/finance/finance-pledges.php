<?php

/* Copyright Philippe Logel All right reserved */


use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\AutoPaymentQuery;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use EcclesiaCRM\SessionUser;

$app->group('/pledges', function (RouteCollectorProxy $group) {

    $group->post('/detail', 'pledgeDetail' );
    $group->post('/family', 'familyPledges' );
    $group->post('/delete', 'deletePledge' );

});

function pledgeDetail (Request $request, Response $response, array $args) {// only in DepositSlipEditor
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

function familyPledges (Request $request, Response $response, array $args) {
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

function deletePledge (Request $request, Response $response, array $args) {
  $plg = (object)$request->getParsedBody();

  $pledge = PledgeQuery::Create()
        ->findOneById($plg->paymentId);

  if (!empty($pledge)) {
    $pledge->delete();
  }

  return $response->withJson(['status' => "OK"]);
}
