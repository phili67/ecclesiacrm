<?php

// Routes
use EcclesiaCRM\Deposit;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\AutoPaymentQuery;
use Propel\Runtime\ActiveQuery\ModelCriteria;


$app->group('/deposits', function () {
    $this->post('', function ($request, $response, $args) {
        $input = (object) $request->getParsedBody();
        $deposit = new Deposit();
        $deposit->setType($input->depositType);
        $deposit->setComment($input->depositComment);
        $deposit->setDate($input->depositDate);
        //$deposit->setFund($input->depositFund);// unusefull actually
        $deposit->save();
        echo $deposit->toJSON();
    });

    $this->get('', function ($request, $response, $args) {
        $deposits = DepositQuery::create()
           ->leftJoinDonationFund()
           ->withColumn('DonationFund.Name','fundName')
           ->find();
           
        echo $deposits->toJSON();
    });

    $this->get('/{id:[0-9]+}', function ($request, $response, $args) {
        $id = $args['id'];
        echo DepositQuery::create()->findOneById($id)->toJSON();
    });

    $this->post('/{id:[0-9]+}', function ($request, $response, $args) {
        $id = $args['id'];
        $input = (object) $request->getParsedBody();
        $thisDeposit = DepositQuery::create()->findOneById($id);
        $thisDeposit->setType($input->depositType);
        $thisDeposit->setComment($input->depositComment);
        $thisDeposit->setDate($input->depositDate);
        $thisDeposit->setClosed($input->depositClosed);
        $thisDeposit->save();
        echo $thisDeposit->toJSON();
    });

    $this->get('/{id:[0-9]+}/ofx', function ($request, $response, $args) {
        $id = $args['id'];
        $OFX = DepositQuery::create()->findOneById($id)->getOFX();
        header($OFX->header);
        echo $OFX->content;
    });

    $this->get('/{id:[0-9]+}/pdf', function ($request, $response, $args) {
        $id = $args['id'];
        DepositQuery::create()->findOneById($id)->getPDF();
    });

    $this->get('/{id:[0-9]+}/csv', function ($request, $response, $args) {
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
    });

    $this->delete('/{id:[0-9]+}', function ($request, $response, $args) {
        $id = $args['id'];
        DepositQuery::create()->findOneById($id)->delete();
        echo json_encode(['success' => true]);
    });

    $this->get('/{id:[0-9]+}/pledges', function ($request, $response, $args) {
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
        
    });
});
