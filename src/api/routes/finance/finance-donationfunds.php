<?php
// Copyright 2018 Philippe Logel all right reserved
use Slim\Http\Request;
use Slim\Http\Response;


use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\DonationFundQuery;
use EcclesiaCRM\DonationFund;

$app->group('/donationfunds', function () {

  $this->post('/', 'getAllDonationFunds' );
  $this->post('/edit', 'editDonationFund' );
  $this->post('/set', 'setDonationFund' );
  $this->post('/delete', 'deleteDonationFund' );
  $this->post('/create', 'createDonationFund' );

});

function getAllDonationFunds (Request $request, Response $response, array $args) {    
  return DonationFundQuery::Create()->find()->toJSON();
}

function editDonationFund (Request $request, Response $response, array $args) {    
  $donation = (object)$request->getParsedBody();
  
  return DonationFundQuery::Create()->findOneById($donation->fundId)->toJSON();
}

function setDonationFund (Request $request, Response $response, array $args) {    
  $fund = (object)$request->getParsedBody();
  
  $donation = DonationFundQuery::Create()->findOneById($fund->fundId);
  
  $donation->setName($fund->Name);
  $donation->setDescription($fund->Description);
  $donation->setActive($fund->Activ);
  
  $donation->save();
  
  return json_encode(['status' => "OK"]); 
}

function deleteDonationFund (Request $request, Response $response, array $args) {    
  $fund = (object)$request->getParsedBody();
  
  $donation = DonationFundQuery::Create()->findOneById($fund->fundId);
  $donation->delete();
  
  return json_encode(['status' => "OK"]); 
}

function createDonationFund (Request $request, Response $response, array $args) {    
  $fund = (object)$request->getParsedBody();
  
  $donation = new DonationFund();
  
  $donation->setName($fund->Name);
  $donation->setDescription($fund->Description);
  $donation->setActive($fund->Activ);
  
  $donation->save();
  
  return json_encode(['status' => "OK"]); 
}