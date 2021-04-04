<?php

// Routes
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\Deposit;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemConfig;

use Propel\Runtime\Parser\CsvParser;


$app->group('/deposits', function (RouteCollectorProxy $group) {

    $group->post('', 'createDeposit');
    $group->get('', 'getAllDeposits');
    $group->get('/{id:[0-9]+}', 'getOneDeposit');
    $group->post('/{id:[0-9]+}', 'modifyOneDeposit');
    $group->get('/{id:[0-9]+}/ofx', 'createDepositOFX');
    $group->get('/{id:[0-9]+}/pdf', 'createDepositPDF');
    $group->get('/{id:[0-9]+}/csv', 'createDepositCSV');
    $group->delete('/{id:[0-9]+}', 'deleteDeposit');
    $group->get('/{id:[0-9]+}/pledges', 'getAllPledgesForDeposit');

});


function createDeposit(Request $request, Response $response, array $args)
{
    $input = (object)$request->getParsedBody();
    $deposit = new Deposit();
    $deposit->setType($input->depositType);
    $deposit->setComment($input->depositComment);
    $deposit->setDate($input->depositDate);
    //$deposit->setFund($input->depositFund);// unusefull actually
    $deposit->save();
    return $response->write($deposit->toJSON());
}

function getAllDeposits(Request $request, Response $response, array $args)
{
    $deposits = DepositQuery::create()
        ->leftJoinDonationFund()
        ->withColumn('DonationFund.Name', 'fundName')
        ->find();

    return $response->write($deposits->toJSON());
}

function getOneDeposit(Request $request, Response $response, array $args)
{
    $id = $args['id'];
    return $response->write(DepositQuery::create()->findOneById($id)->toJSON());
}

function modifyOneDeposit(Request $request, Response $response, array $args)
{
    $id = $args['id'];
    $input = (object)$request->getParsedBody();
    $thisDeposit = DepositQuery::create()->findOneById($id);
    $thisDeposit->setType($input->depositType);
    $thisDeposit->setComment($input->depositComment);
    $thisDeposit->setDate($input->depositDate);
    $thisDeposit->setClosed($input->depositClosed);
    $thisDeposit->save();
    return $response->write($thisDeposit->toJSON());
}

function createDepositOFX(Request $request, Response $response, array $args)
{
    $id = $args['id'];
    $OFX = DepositQuery::create()->findOneById($id)->getOFX();
    header($OFX->header);
    return $response->write($OFX->content);
}

function createDepositPDF(Request $request, Response $response, array $args)
{
    $id = $args['id'];
    $file = DepositQuery::create()->findOneById($id)->getPDF();

    /*
     * Unuseful part yet
     *
     * $response = $response
        ->withHeader('Content-Type', 'application/octet-stream')
        ->withHeader('Content-Disposition', 'attachment;filename="' . basename($file) . '"')
        ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
        ->withHeader('Pragma', 'no-cache')
        ->withBody((new \Slim\Psr7\Stream(fopen($file, 'rb'))));

    return $response;*/
}

function createDepositCSV(Request $request, Response $response, array $args)
{
    $id = $args['id'];
    //cho DepositQuery::create()->findOneById($id)->toCSV();
    $res = EcclesiaCRM\PledgeQuery::create()->filterByDepid($id)
        ->joinDonationFund()->useDonationFundQuery()
        ->withColumn('DonationFund.Name', 'DonationFundName')
        ->endUse()
        ->joinFamily()->useFamilyQuery()
        ->withColumn('Family.Name', 'FamilyName')
        ->endUse()
        ->find()
        ->toCsv();

    /*$parser = new CsvParser();

    $parser->delimiter = ';';

    $res = $parser->toCSV($res);*/

    $response = $response
        ->withHeader('Content-Type', 'application/octet-stream')
        ->withHeader('Content-Disposition', 'attachment; filename=filename=EcclesiaCRM-Deposit-' . $id . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.csv')
        ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
        ->withHeader('Pragma', 'no-cache')
        ->write($res);

    return $response;

}

function deleteDeposit(Request $request, Response $response, array $args)
{
    $id = $args['id'];
    DepositQuery::create()->findOneById($id)->delete();
    return $response->withJson(['success' => true]);
}

function getAllPledgesForDeposit(Request $request, Response $response, array $args)
{
    $id = $args['id'];
    /*$Pledges = \EcclesiaCRM\PledgeQuery::create()
        ->filterByDepid($id)
        ->groupByGroupkey()
        ->withColumn('SUM(Pledge.Amount)', 'sumAmount')
        ->joinDonationFund()
        ->withColumn('DonationFund.Name')
        ->find()
        ->toArray();*/

    $Pledges = \EcclesiaCRM\PledgeQuery::create()
        ->filterByDepid($id)
        ->groupByGroupkey()
        ->withColumn('SUM(Pledge.Amount)', 'sumAmount')
        ->useDonationFundQuery()
        ->withColumn("GROUP_CONCAT(DonationFund.Name SEPARATOR ', ')", 'DonationFundNames')
        ->endUse()
        ->leftJoinDeposit()
        ->withColumn('Deposit.Closed', 'Closed')
        ->find()
        ->toArray();


    return $response->withJSON($Pledges);

}
