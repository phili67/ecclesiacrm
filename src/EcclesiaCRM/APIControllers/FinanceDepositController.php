<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\SessionUser;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use EcclesiaCRM\Deposit;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\PledgeQuery;


class FinanceDepositController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function createDeposit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if ( isset($input->depositType) and isset($input->depositComment)
            and isset($input->depositDate) and SessionUser::getUser()->isFinanceEnabled() ) {
            $deposit = new Deposit();
            $deposit->setType($input->depositType);
            $deposit->setComment($input->depositComment);
            $deposit->setDate($input->depositDate);
            //$deposit->setFund($input->depositFund);// unusefull actually
            $deposit->save();

            return $response->write($deposit->toJSON());
        }

        return $response->withStatus(401);
    }

    public function getAllDeposits(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( SessionUser::getUser()->isFinanceEnabled() ) {
            $deposits = DepositQuery::create()
                ->leftJoinDonationFund()
                ->withColumn('DonationFund.Name', 'fundName')
                ->find();

            return $response->write($deposits->toJSON());
        }

        return $response->withStatus(401);
    }

    public function getOneDeposit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( array_key_exists('id', $args) and SessionUser::getUser()->isFinanceEnabled() ) {
            $id = $args['id'];
            return $response->write(DepositQuery::create()->findOneById($id)->toJSON());
        }
        return $response->withStatus(401);
    }

    public function modifyOneDeposit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $input = (object)$request->getParsedBody();

        if ( array_key_exists('id', $args)  and isset($input->depositType) and isset($input->depositComment)
            and isset($input->depositDate) and isset($input->depositClosed) and SessionUser::getUser()->isFinanceEnabled() ) {

            $id = $args['id'];

            $thisDeposit = DepositQuery::create()->findOneById($id);
            $thisDeposit->setType($input->depositType);
            $thisDeposit->setComment($input->depositComment);
            $thisDeposit->setDate($input->depositDate);
            $thisDeposit->setClosed($input->depositClosed);
            $thisDeposit->save();
            return $response->write($thisDeposit->toJSON());
        }

        return $response->withStatus(401);
    }

    public function createDepositOFX(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( SessionUser::getUser()->isFinanceEnabled() and array_key_exists('id', $args) ) {
            $id = $args['id'];
            $OFX = DepositQuery::create()->findOneById($id)->getOFX();
            return $response
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Disposition', 'attachment; filename=filename=EcclesiaCRM-Deposit-' . $id . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.ofx')
                ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
                ->withHeader('Pragma', 'no-cache')
                ->write($OFX->content);

            /*$response
            ->withHeader('Content-Disposition', 'attachment;filename="EcclesiaCRM-Deposit-' . $id . '-' . date(SystemConfig::getValue("sDateFilenameFormat")) . '.ofx')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->write(json_encode($OFX->content));

        header($OFX->header);
        return $response->write($OFX->content);*/
        }

        return $response->withStatus(401);
    }

    public function createDepositPDF(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( SessionUser::getUser()->isFinanceEnabled() and array_key_exists('id', $args) ) {
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

        return $response->withStatus(401);
    }

    public function createDepositCSV(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( SessionUser::getUser()->isFinanceEnabled() and array_key_exists('id', $args) ) {
            $id = $args['id'];
            //cho DepositQuery::create()->findOneById($id)->toCSV();
            $res = PledgeQuery::create()->filterByDepid($id)
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

        return $response->withStatus(401);

    }

    public function deleteDeposit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( SessionUser::getUser()->isFinanceEnabled() and array_key_exists('id', $args) ) {
            $id = $args['id'];
            $dep = DepositQuery::create()->findOneById($id);
            if ( !is_null($dep) and !$dep->isClosed() ) {
                $dep->delete();
                return $response->withJson(['success' => true]);
            }
        }

        return $response->withStatus(401);
    }

    public function getAllPledgesForDeposit(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if ( SessionUser::getUser()->isFinanceEnabled() and array_key_exists('id', $args) ) {
            $id = $args['id'];
            /*$Pledges = \EcclesiaCRM\PledgeQuery::create()
                ->filterByDepid($id)
                ->groupByGroupkey()
                ->withColumn('SUM(Pledge.Amount)', 'sumAmount')
                ->joinDonationFund()
                ->withColumn('DonationFund.Name')
                ->find()
                ->toArray();*/

            $Pledges = PledgeQuery::create()
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

        return $response->withStatus(401);
    }
}
