<?php

/* Copyright Philippe Logel All right reserved */

use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\FamilyQuery;

$app->group('/autopayement', function () {
  
    $this->post('/family',function($request,$response,$args) {    
      if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isFinance())) {
            return $response->withStatus(401);
      }
      
      $autoPay = (object)$request->getParsedBody();
      
      
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
    
});