<?php
// Copyright 2018 Philippe Logel all right reserved

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\DonationFundQuery;
use EcclesiaCRM\DonationFund;

$app->group('/donationfunds', function () {

  $this->post('/', function ($request, $response, $args) {    
      return DonationFundQuery::Create()->find()->toJSON();
  });
  
  $this->post('/edit', function ($request, $response, $args) {    
    $donation = (object)$request->getParsedBody();
    
    return DonationFundQuery::Create()->findOneById($donation->fundId)->toJSON();
  });
  
  $this->post('/set', function ($request, $response, $args) {    
    $fund = (object)$request->getParsedBody();
    
    $donation = DonationFundQuery::Create()->findOneById($fund->fundId);
    
    $donation->setName($fund->Name);
    $donation->setDescription($fund->Description);
    $donation->setActive($fund->Activ);
    
    $donation->save();
    
    return json_encode(['status' => "OK"]); 
  });
  
  $this->post('/delete', function ($request, $response, $args) {    
    $fund = (object)$request->getParsedBody();
    
    $donation = DonationFundQuery::Create()->findOneById($fund->fundId);
    $donation->delete();
    
    return json_encode(['status' => "OK"]); 
  });
  
  $this->post('/create', function ($request, $response, $args) {    
    $fund = (object)$request->getParsedBody();
    
    $donation = new DonationFund();
    
    $donation->setName($fund->Name);
    $donation->setDescription($fund->Description);
    $donation->setActive($fund->Activ);
    
    $donation->save();
    
    return json_encode(['status' => "OK"]); 
  });

});
