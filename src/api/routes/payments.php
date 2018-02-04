<?php

// Routes

use EcclesiaCRM\PledgeQuery;

$app->group('/payments', function () {
    $this->get('/', function ($request, $response, $args) {
        $this->FinancialService->getPaymentJSON($this->FinancialService->getPayments());
    });

    $this->post('/', function ($request, $response, $args) {
        $payment = $request->getParsedBody();
        echo json_encode(['payment' => $this->FinancialService->submitPledgeOrPayment($payment)]);
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
