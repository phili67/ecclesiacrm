<?php
// Copyright 2018 Philippe Logel all right reserved
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;


use EcclesiaCRM\DonationFundQuery;
use EcclesiaCRM\DonationFund;

$app->group('/donationfunds', function (RouteCollectorProxy $group) {

    $group->post('/', 'getAllDonationFunds' );
    $group->post('/edit', 'editDonationFund' );
    $group->post('/set', 'setDonationFund' );
    $group->post('/delete', 'deleteDonationFund' );
    $group->post('/create', 'createDonationFund' );

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
