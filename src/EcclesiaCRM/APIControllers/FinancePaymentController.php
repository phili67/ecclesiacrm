<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\utils\MiscUtils;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\SessionUser;


class FinancePaymentController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPayment (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $id = $args['id'];
        $FinancialService = $this->container->get('FinancialService');
        return $response->write($FinancialService->getPaymentJSON($FinancialService->getPayments($id)));
    }

    public function getSubmitOrPayement (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $payment = $request->getParsedBody();
        $FinancialService = $this->container->get('FinancialService');
        return $response->write(json_encode(['payment' => $FinancialService->submitPledgeOrPayment($payment)]));
    }

    public function deletePaymentByGroupKey (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $payments = (object) $request->getParsedBody();

        if ( isset($payments->Groupkey) ) {
            $groupKey = $payments->Groupkey;
            $FinancialService = $this->container->get('FinancialService');
            $FinancialService->deletePayment($groupKey);
            return $response->withJson(['status' => 'success']);
        }

        return $response->withJson(['status' => 'failed']);
    }


    public function getAllPayementsForFamily(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $payments = (object)$request->getParsedBody();

        $AutoPayments = AutoPaymentQuery::create()
            ->leftJoinPerson()
            ->withColumn('Person.FirstName', 'EnteredFirstName')
            ->withColumn('Person.LastName', 'EnteredLastName')
            ->withColumn('Person.FirstName', 'EnteredFirstName')
            ->withColumn('Person.LastName', 'EnteredLastName')
            ->leftJoinDonationFund()
            ->withColumn('DonationFund.Name', 'fundName')
            ->orderByNextPayDate()
            ->findByFamilyid($payments->famId);

        return $response->write($AutoPayments->toJSON());
    }

    public function getAutoPaymentInfo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $payments = (object)$request->getParsedBody();

        if (isset ($payments->autID)) {
            $AutoPayments = AutoPaymentQuery::create()
                ->leftJoinFamily()
                ->findOneById($payments->autID);

            return $response->write($AutoPayments->toJSON());
        }

        return $response->withJson(['success' => false]);
    }

    public function getAllPayementsForFamilies(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $autoPay = (object)$request->getParsedBody();

        if (!(SessionUser::getUser()->isFinance())) {
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
                $showStr = gettext('Credit card') . " : " . mb_substr($autoPayement->getCreditCard(), strlen($autoPayement->getCreditCard()) - 4, 4) . "....";
            } else {
                $showStr = gettext("Bank account") . " : " . $autoPayement->getBankName() . ' ' . $autoPayement->getRoute() . ' ' . $autoPayement->getAccount();
            }

            $elt = ['authID' => $autoPayement->getId(),
                'showStr' => $showStr];

            array_push($result, $elt);
        }

        return $response->withJSON($result);
    }

    public function deletePaymentForFamily(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isFinance())) {
            return $response->withStatus(401);
        }

        $payments = (object)$request->getParsedBody();

        $AutoPayment = AutoPaymentQuery::create()
            ->filterByFamilyid($payments->famId)
            ->findOneById($payments->paymentId);

        if (!empty($AutoPayment)) {
            $AutoPayment->delete();
        }

        return $response->withJson(['status' => "OK"]);
    }

    public function deleteAutoPayment(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isFinance())) {
            return $response->withStatus(401);
        }

        $AutoPayment = AutoPaymentQuery::create()
            ->findOneById($args['authID']);

        if (!empty($AutoPayment)) {
            $AutoPayment->delete();
        }

        return $response->withJson(['status' => "OK"]);
    }

    public function invalidatePledge(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isFinance())) {
            return $response->withStatus(401);
        }

        $payments = (object)$request->getParsedBody();

        foreach ($payments->data as $payment) {
            $pledge = PledgeQuery::Create()->findOneById($payment['Id']);
            if (!empty($pledge)) {
                $pledge->setPledgeorpayment('Pledge');
                $pledge->save();
            }
        }

        return $response->withJson(['status' => "OK"]);
    }

    public function validatePledge(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!(SessionUser::getUser()->isFinance())) {
            return $response->withStatus(401);
        }

        $payments = (object)$request->getParsedBody();

        foreach ($payments->data as $payment) {
            $pledge = PledgeQuery::Create()->findOneById($payment['Id']);
            if (!empty($pledge)) {
                $pledge->setPledgeorpayment('Payment');
                $pledge->save();
            }
        }

        return $response->withJson(['status' => "OK"]);
    }

    public function getDepositSlipChartsArrays(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = (object)$request->getParsedBody();

        $thisDeposit = DepositQuery::create()->findOneById($params->depositSlipID);

        $funds = $thisDeposit->getFundTotals();

        $fundLabels = [];
        $fundDatas = [];
        $fundBackgroundColor = [];
        $fundborderColor = [];
        foreach ($funds as $tmpfund) {
            $fundLabels[] = $tmpfund['Name'];
            $fundDatas[] = $tmpfund['Total'];
            $fundBackgroundColor[] = '#' . MiscUtils::random_color();
            $fundborderColor[] = '#' . MiscUtils::random_color();
        }

        $funddatasets = new \StdClass();

        $funddatasets->label = '# of Votes';
        $funddatasets->data = $fundDatas;
        $funddatasets->backgroundColor = $fundBackgroundColor;
        $funddatasets->borderColor = $fundborderColor;
        $funddatasets->borderWidth = 1;


        $fund = new \StdClass();

        $fund->datasets = [];
        $fund->datasets[] = $funddatasets;
        $fund->labels = $fundLabels;


        // the pledgesDatas
        $pledgeTypeData = [];
        $t1 = new \StdClass();
        $t1->value = $thisDeposit->getTotalamount() ? $thisDeposit->getTotalCash() : '0';
        $t1->countCash = $thisDeposit->getCountCash();
        $t1->color = '#197A05';
        $t1->highlight = '#4AFF23';
        $t1->label = gettext("Cash");
        array_push($pledgeTypeData, $t1);

        $t1 = new \StdClass();
        $t1->value = $thisDeposit->getTotalamount() ? $thisDeposit->getTotalChecks() : '0';
        $t1->countChecks = $thisDeposit->getCountChecks();
        $t1->color = '#003399';
        $t1->highlight = '#3366ff';
        $t1->label = gettext("Checks");
        array_push($pledgeTypeData, $t1);

        // the pledges
        $pledgedatasets = new \StdClass();

        $pledgedatasets->label = '# of Votes';
        $pledgedatasets->data = [$thisDeposit->getTotalamount() ? $thisDeposit->getTotalCash() : '0', $thisDeposit->getTotalamount() ? $thisDeposit->getTotalChecks() : '0'];
        $pledgedatasets->backgroundColor = ['#197A05', '#003399'];
        $pledgedatasets->borderColor = ['#4AFF23', '#3366ff'];
        $pledgedatasets->borderWidth = 1;


        $pledge = new \StdClass();

        $pledge->datasets = [];
        $pledge->datasets[] = $pledgedatasets;
        $pledge->labels = [gettext("Cash"), gettext("Checks")];


        // now the json
        return $response->withJson(['status' => "OK", 'pledgeData' => $pledge, 'pledgeTypeData' => $pledgeTypeData, 'fundData' => $fund]);
    }
}
