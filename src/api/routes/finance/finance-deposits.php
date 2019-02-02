<?php

// Routes
use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\Deposit;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\AutoPaymentQuery;
use Propel\Runtime\ActiveQuery\ModelCriteria;


$app->group('/deposits', function () {

    $this->post('', 'createDeposit' );
    $this->get('', 'getAllDeposits' );
    $this->get('/{id:[0-9]+}', 'getOneDeposit' );
    $this->post('/{id:[0-9]+}', 'modifyOneDeposit' );
    $this->get('/{id:[0-9]+}/ofx', 'createDepositOFX' );
    $this->get('/{id:[0-9]+}/pdf', 'createDepositPDF' );
    $this->get('/{id:[0-9]+}/csv', 'createDepositCSV' );
    $this->delete('/{id:[0-9]+}', 'deleteDeposit' );
    $this->get('/{id:[0-9]+}/pledges', 'getAllPledgesForDeposit' );

});


function createDeposit (Request $request, Response $response, array $args) {
        $input = (object) $request->getParsedBody();
        $deposit = new Deposit();
        $deposit->setType($input->depositType);
        $deposit->setComment($input->depositComment);
        $deposit->setDate($input->depositDate);
        //$deposit->setFund($input->depositFund);// unusefull actually
        $deposit->save();
        echo $deposit->toJSON();
    }
    
function getAllDeposits (Request $request, Response $response, array $args) {
        $deposits = DepositQuery::create()
           ->leftJoinDonationFund()
           ->withColumn('DonationFund.Name','fundName')
           ->find();
           
        echo $deposits->toJSON();
    }

function getOneDeposit (Request $request, Response $response, array $args) {
        $id = $args['id'];
        echo DepositQuery::create()->findOneById($id)->toJSON();
    }
    
function modifyOneDeposit (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $input = (object) $request->getParsedBody();
        $thisDeposit = DepositQuery::create()->findOneById($id);
        $thisDeposit->setType($input->depositType);
        $thisDeposit->setComment($input->depositComment);
        $thisDeposit->setDate($input->depositDate);
        $thisDeposit->setClosed($input->depositClosed);
        $thisDeposit->save();
        echo $thisDeposit->toJSON();
    }
    
function createDepositOFX (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $OFX = DepositQuery::create()->findOneById($id)->getOFX();
        header($OFX->header);
        echo $OFX->content;
    }

function createDepositPDF (Request $request, Response $response, array $args) {
        $id = $args['id'];
        DepositQuery::create()->findOneById($id)->getPDF();
    }
    
function createDepositCSV (Request $request, Response $response, array $args) {
        $id = $args['id'];
    //echo DepositQuery::create()->findOneById($id)->toCSV();
    header('Content-Disposition: attachment; filename=EcclesiaCRM-Deposit-'.$id.'-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
        echo EcclesiaCRM\PledgeQuery::create()->filterByDepid($id)
            ->joinDonationFund()->useDonationFundQuery()
            ->withColumn('DonationFund.Name', 'DonationFundName')
            ->endUse()
            ->joinFamily()->useFamilyQuery()
            ->withColumn('Family.Name', 'FamilyName')
            ->endUse()
            ->find()
            ->toCSV();
    }
    
function deleteDeposit (Request $request, Response $response, array $args) {
        $id = $args['id'];
        DepositQuery::create()->findOneById($id)->delete();
        echo json_encode(['success' => true]);
    }
    
function getAllPledgesForDeposit (Request $request, Response $response, array $args) {
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