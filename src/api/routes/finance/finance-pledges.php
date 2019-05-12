<?php

/* Copyright Philippe Logel All right reserved */


use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\AutoPaymentQuery;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use EcclesiaCRM\SessionUser;

$app->group('/pledges', function () {
  
    $this->post('/detail', 'pledgeDetail' );
    $this->post('/family', 'familyPledges' );
    $this->post('/delete', 'deletePledge' );
    
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
        
      
  return $pledges->toJson();
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
        
      
  return $pledges->toJSON();
}

function deletePledge (Request $request, Response $response, array $args) {
  $plg = (object)$request->getParsedBody();
        
  $pledge = PledgeQuery::Create()
        ->findOneById($plg->paymentId);
        
  if (!empty($pledge)) {
    $pledge->delete();
  }
      
  return json_encode(['status' => "OK"]);
}