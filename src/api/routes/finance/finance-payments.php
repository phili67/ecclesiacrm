<?php

// Routes
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\utils\MiscUtils;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\SessionUser;

$app->group('/payments', function (RouteCollectorProxy $group) {

    $group->get('/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $FinancialService = $this->get('FinancialService');
        return $response->write($FinancialService->getPaymentJSON($FinancialService->getPayments($id)));
    });

    $group->post('/', function (Request $request, Response $response, array $args) {
        $payment = $request->getParsedBody();
        return $response->write(json_encode(['payment' => $this->FinancialService->submitPledgeOrPayment($payment)]));
    });

    $group->delete('/byGroupKey', function (Request $request, Response $response, array $args) {
        $payments = (object) $request->getParsedBody();

        if ( isset($payments->Groupkey) ) {
            $groupKey = $payments->Groupkey;
            $FinancialService = $this->get('FinancialService');
            $FinancialService->deletePayment($groupKey);
            return $response->withJson(['status' => 'success']);
        }

        return $response->withJson(['status' => 'failed']);
    });

    $group->post('/family', 'getAllPayementsForFamily' );
    $group->post('/info', 'getAutoPaymentInfo' );

    // this can be used only as an admin or in finance in pledgeEditor
    $group->post('/families', 'getAllPayementsForFamilies' );
    $group->post('/delete', 'deletePaymentForFamily' );
    $group->get('/delete/{authID:[0-9]+}', 'deleteAutoPayment' );
    $group->post('/invalidate', 'invalidatePledge' );
    $group->post('/validate', 'validatePledge' );
    $group->post('/getchartsarrays', 'getDepositSlipChartsArrays' );

});


function getAllPayementsForFamily (Request $request, Response $response, array $args) {
    $payments = (object) $request->getParsedBody();

    $AutoPayments = AutoPaymentQuery::create()
       ->leftJoinPerson()
         ->withColumn('Person.FirstName','EnteredFirstName')
         ->withColumn('Person.LastName','EnteredLastName')
         ->withColumn('Person.FirstName','EnteredFirstName')
         ->withColumn('Person.LastName','EnteredLastName')
       ->leftJoinDonationFund()
         ->withColumn('DonationFund.Name','fundName')
       ->orderByNextPayDate()
       ->findByFamilyid($payments->famId);

  return $response->write($AutoPayments->toJSON());
}
function getAutoPaymentInfo (Request $request, Response $response, array $args) {
    $payments = (object) $request->getParsedBody();

    if ( isset ($payments->autID) )
    {
      $AutoPayments = AutoPaymentQuery::create()
         ->leftJoinFamily()
         ->findOneById($payments->autID);

      return $response->write($AutoPayments->toJSON());
    }

    return $response->withJson(['success' => false]);
}

function getAllPayementsForFamilies (Request $request, Response $response, array $args) {
  $autoPay = (object)$request->getParsedBody();

  if (!(SessionUser::getUser()->isFinance())) {
        return $response->withStatus(401);
  }

  if ($autoPay->type == 'CreditCard') {
    $autoPayements = AutoPaymentQuery::Create()->filterByEnableCreditCard(true)->findByFamilyid($autoPay->famId);
  } else if ($autoPay->type == 'BankDraft') {
    $autoPayements = AutoPaymentQuery::Create()->filterByEnableBankDraft(true)->findByFamilyid($autoPay->famId);
  } else {
    $autoPayements = AutoPaymentQuery::Create()->findByFamilyid($autoPay->famId);
  }

  $result = [];

  foreach ($autoPayements as $autoPayement) {
    if ($autoPayement->getCreditCard() != '') {
        $showStr = gettext('Credit card')." : ".mb_substr($autoPayement->getCreditCard(), strlen($autoPayement->getCreditCard()) - 4, 4)."....";
    } else {
        $showStr = gettext("Bank account")." : ".$autoPayement->getBankName().' '.$autoPayement->getRoute().' '.$autoPayement->getAccount();
    }

    $elt = ['authID'=>$autoPayement->getId(),
            'showStr'=>$showStr];

    array_push($result, $elt);
  }

  return $response->withJSON($result);
}

function deletePaymentForFamily (Request $request, Response $response, array $args) {
  if (!(SessionUser::getUser()->isFinance())) {
        return $response->withStatus(401);
  }

  $payments = (object) $request->getParsedBody();

  $AutoPayment = AutoPaymentQuery::create()
       ->filterByFamilyid($payments->famId)
       ->findOneById($payments->paymentId);

  if (!empty($AutoPayment)) {
    $AutoPayment->delete();
  }

  return $response->withJson(['status' => "OK"]);
}

function deleteAutoPayment (Request $request, Response $response, array $args) {
  if (!(SessionUser::getUser()->isFinance())) {
        return $response->withStatus(401);
  }

  $AutoPayment = AutoPaymentQuery::create()
       ->findOneById($args['authID']);

  if (!empty($AutoPayment)) {
    $AutoPayment->delete();
  }

  return $response->withJson(['status' => "OK"]);
}

function invalidatePledge (Request $request, Response $response, array $args) {
  if (!(SessionUser::getUser()->isFinance())) {
        return $response->withStatus(401);
  }

  $payments = (object) $request->getParsedBody();

  foreach($payments->data as $payment) {
    $pledge = PledgeQuery::Create()->findOneById ($payment['Id']);
    if (!empty($pledge)) {
      $pledge->setPledgeorpayment('Pledge');
      $pledge->save();
    }
  }

  return $response->withJson(['status' => "OK"]);
}

function validatePledge (Request $request, Response $response, array $args) {
  if (!(SessionUser::getUser()->isFinance())) {
        return $response->withStatus(401);
  }

  $payments = (object) $request->getParsedBody();

  foreach($payments->data as $payment) {
    $pledge = PledgeQuery::Create()->findOneById ($payment['Id']);
    if (!empty($pledge)) {
      $pledge->setPledgeorpayment('Payment');
      $pledge->save();
    }
  }

  return $response->withJson(['status' => "OK"]);
}

function getDepositSlipChartsArrays (Request $request, Response $response, array $args) {
    $params = (object) $request->getParsedBody();

    $thisDeposit = DepositQuery::create()->findOneById($params->depositSlipID);

    $funds = $thisDeposit->getFundTotals();

    $fundLabels          = [];
    $fundDatas           = [];
    $fundBackgroundColor = [];
    $fundborderColor     = [];
    foreach ($funds as $tmpfund) {
      $fundLabels[]           = $tmpfund['Name'];
      $fundDatas[]            = $tmpfund['Total'];
      $fundBackgroundColor[]  = '#'.MiscUtils::random_color();
      $fundborderColor[]      = '#'.MiscUtils::random_color();
    }

    $funddatasets = new StdClass();

    $funddatasets->label           = '# of Votes';
    $funddatasets->data            = $fundDatas;
    $funddatasets->backgroundColor = $fundBackgroundColor;
    $funddatasets->borderColor     = $fundborderColor;
    $funddatasets->borderWidth     = 1;


    $fund = new StdClass();

    $fund->datasets   = [];
    $fund->datasets[] = $funddatasets;
    $fund->labels     = $fundLabels;


    // the pledgesDatas
    $pledgeTypeData = [];
    $t1 = new stdClass();
    $t1->value = $thisDeposit->getTotalamount() ? $thisDeposit->getTotalCash() : '0';
    $t1->countCash = $thisDeposit->getCountCash();
    $t1->color = '#197A05';
    $t1->highlight = '#4AFF23';
    $t1->label = gettext("Cash");
    array_push($pledgeTypeData, $t1);

    $t1 = new stdClass();
    $t1->value = $thisDeposit->getTotalamount() ? $thisDeposit->getTotalChecks() : '0';
    $t1->countChecks = $thisDeposit->getCountChecks();
    $t1->color = '#003399';
    $t1->highlight = '#3366ff';
    $t1->label = gettext("Checks");
    array_push($pledgeTypeData, $t1);

    // the pledges
    $pledgedatasets = new StdClass();

    $pledgedatasets->label           = '# of Votes';
    $pledgedatasets->data            = [$thisDeposit->getTotalamount() ? $thisDeposit->getTotalCash() : '0', $thisDeposit->getTotalamount() ? $thisDeposit->getTotalChecks() : '0'];
    $pledgedatasets->backgroundColor = ['#197A05','#003399'];
    $pledgedatasets->borderColor     = ['#4AFF23', '#3366ff'];
    $pledgedatasets->borderWidth     = 1;


    $pledge = new StdClass();

    $pledge->datasets   = [];
    $pledge->datasets[] = $pledgedatasets;
    $pledge->labels     = [gettext("Cash"), gettext("Checks")];


    // now the json
    return $response->withJson(['status' => "OK",'pledgeData' => $pledge, 'pledgeTypeData' => $pledgeTypeData, 'fundData' => $fund]);
}
