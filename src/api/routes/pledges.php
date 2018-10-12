<?php

/* Copyright Philippe Logel All right reserved */

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\AutoPaymentQuery;
use Propel\Runtime\ActiveQuery\ModelCriteria;

$app->group('/pledges', function () {
  
    $this->post('/detail',function($request,$response,$args) {// only in DepositSlipEditor    
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isFinance())) {
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
    });
    
    
    $this->post('/family',function($request,$response,$args) {
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
    });
    
    $this->post('/delete',function($request,$response,$args) {
      $plg = (object)$request->getParsedBody();
            
      $pledge = PledgeQuery::Create()
            ->findOneById($plg->paymentId);
            
      if (!empty($pledge)) {
        $pledge->delete();
      }
          
      return json_encode(['status' => "OK"]);
    });
});