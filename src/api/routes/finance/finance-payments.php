<?php

// Routes

use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\utils\MiscUtils;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\SessionUser;

$app->group('/payments', function () {
    $this->get('/', function ($request, $response, $args) {
        $this->FinancialService->getPaymentJSON($this->FinancialService->getPayments());
    });

    $this->post('/', function ($request, $response, $args) {
        $payment = $request->getParsedBody();
        echo json_encode(['payment' => $this->FinancialService->submitPledgeOrPayment($payment)]);
    });
    
    $this->post('/family', function ($request, $response, $args) {  
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
        
      return $AutoPayments->toJSON();
    });
    
    $this->post('/info', function ($request, $response, $args) {  
        $payments = (object) $request->getParsedBody();
        
        if ( isset ($payments->autID) )
        {
          $AutoPayments = AutoPaymentQuery::create()
             ->leftJoinFamily()
             ->findOneById($payments->autID);
          
          return $AutoPayments->toJSON();
        }
        
        return json_encode(['success' => false]);
    });
    
    // this can be used only as an admin or in finance in pledgeEditor
    $this->post('/families',function($request,$response,$args) {          
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
    }); 
    
    
    $this->post('/delete',function($request,$response,$args) {
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
           
      return json_encode(['status' => "OK"]);
    });
    
    $this->get('/delete/{authID:[0-9]+}',function($request,$response,$args) {
      if (!(SessionUser::getUser()->isFinance())) {
            return $response->withStatus(401);
      }
      
      $AutoPayment = AutoPaymentQuery::create()
           ->findOneById($args['authID']);
           
      if (!empty($AutoPayment)) {
        $AutoPayment->delete();
      }
           
      return json_encode(['status' => "OK"]);
    });
    
    $this->post('/invalidate', function ($request, $response, $args) {
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
         
      return json_encode(['status' => "OK"]);
    });
    
    $this->post('/validate', function ($request, $response, $args) {
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
         
      return json_encode(['status' => "OK"]);
    });
    
    $this->post('/getchartsarrays', function ($request, $response, $args) {
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
        return json_encode(['status' => "OK",'pledgeData' => $pledge, 'pledgeTypeData' => $pledgeTypeData, 'fundData' => $fund]);
    });
    

    $this->delete('/{groupKey}', function ($request, $response, $args) {
        $groupKey = $args['groupKey'];
        $this->FinancialService->deletePayment($groupKey);
        echo json_encode(['status' => 'ok']);
    });
});
