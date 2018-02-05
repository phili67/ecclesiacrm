<?php

// Routes

use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\AutoPaymentQuery;

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
    
    // this can be used only as an admin or in finance in pledgeEditor
    $this->post('/families',function($request,$response,$args) {          
      $autoPay = (object)$request->getParsedBody();
      
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isFinance())) {
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
      $payments = (object) $request->getParsedBody();
      
      $AutoPayment = AutoPaymentQuery::create()
           ->filterByFamilyid($payments->famId)
           ->findOneById($payments->paymentId);
           
      if (!empty($AutoPayment)) {
        $AutoPayment->delete();
      }
           
      return json_encode(['status' => "OK"]);
    });

    
    $this->post('/invalidate', function ($request, $response, $args) {
        $payments = (object) $request->getParsedBody();
        
        foreach($payments->data as $payment) {  
          $pledge = PledgeQuery::Create()->findOneById ($payment['Id']);
          if (!empty($pledge)) {
            $pledge->setStatut('invalidate');
            $pledge->save();
          }
        }
           
        return json_encode(['status' => "OK"]);
    });
    
    $this->post('/validate', function ($request, $response, $args) {
        $payments = (object) $request->getParsedBody();
        
        foreach($payments->data as $payment) {  
          $pledge = PledgeQuery::Create()->findOneById ($payment['Id']);
          if (!empty($pledge)) {
            $pledge->setStatut('validate');
            $pledge->save();
          }
        }
           
        return json_encode(['status' => "OK"]);
    });

    $this->delete('/{groupKey}', function ($request, $response, $args) {
        $groupKey = $args['groupKey'];
        $this->FinancialService->deletePayment($groupKey);
        echo json_encode(['status' => 'ok']);
    });
});
