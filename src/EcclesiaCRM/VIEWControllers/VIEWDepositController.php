<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2023/05/22
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\DepositQuery;

use Slim\Views\PhpRenderer;

class VIEWDepositController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderDepositSlipEditor (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        $iDepositSlipID = 0;

        if (isset($args['DepositSlipID'])) {
           $iDepositSlipID = $args['DepositSlipID'];
        }

        // Security: User must have finance permission or be the one who created this deposit
        if ( !(SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') )) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $res = $this->argumentsDepositSlipEditorArray($iDepositSlipID);
        
        if ( $res['error'] ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/' . $res['link']);
        }

        return $renderer->render($response, 'depositslipeditor.php', $res);
    }

    public function argumentsDepositSlipEditorArray ($iDepositSlipID)
    {
        $thisDeposit = 0;
        $dep_Closed = false;

        // Get the current deposit slip data
        if ($iDepositSlipID) {
            $thisDeposit = DepositQuery::create()->findOneById($iDepositSlipID);
            // Set the session variable for default payment type so the new payment form will come up correctly
            if ($thisDeposit->getType() == 'Bank') {
                $_SESSION['idefaultPaymentMethod'] = 'CHECK';
            } elseif ($thisDeposit->getType() == 'CreditCard') {
                $_SESSION['idefaultPaymentMethod'] = 'CREDITCARD';
            } elseif ($thisDeposit->getType() == 'BankDraft') {
                $_SESSION['idefaultPaymentMethod'] = 'BANKDRAFT';
            } elseif ($thisDeposit->getType() == 'eGive') {
                $_SESSION['idefaultPaymentMethod'] = 'EGIVE';
            }

            // Security: User must have finance permission or be the one who created this deposit
            if (!(SessionUser::getUser()->isFinanceEnabled() || SessionUser::getUser()->getPersonId() == $thisDeposit->getEnteredby()) && SystemConfig::getBooleanValue('bEnabledFinance')) {
                return [
                    'error' => true,
                    'link'  => 'v2/dashboard'
                ];
            }
        } else {
            return [
                'error' => true,
                'link'  => 'v2/dashboard'
            ];
        }

        $funds = $thisDeposit->getFundTotals();

        //Set the page title
        $sPageTitle = _($thisDeposit->getType()) . ' : ' . _('Deposit Slip Number: ') . "#" . $iDepositSlipID;

        if ($thisDeposit->getClosed()) {
            $sPageTitle .= ' &nbsp; <font color=red>' . _('Deposit closed') . " (" . $thisDeposit->getDate()->format(SystemConfig::getValue('sDateFormatLong')) . ')</font>';
        }

        //Is this the second pass?

        if (isset($_POST['DepositSlipLoadAuthorized'])) {
            $thisDeposit->loadAuthorized($thisDeposit->getType());
        } elseif (isset($_POST['DepositSlipRunTransactions'])) {
            $thisDeposit->runTransactions();
        }

        $_SESSION['iCurrentDeposit'] = $iDepositSlipID;  // Probably redundant

        /* @var $currentUser \EcclesiaCRM\User */
        $currentUser = SessionUser::getUser();
        $currentUser->setCurrentDeposit($iDepositSlipID);
        $currentUser->save();

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'error'                     => false,
            'iDepositSlipID'            => $iDepositSlipID,
            'thisDeposit'               => $thisDeposit,
            'dep_Closed'                => $dep_Closed,
            'funds'                     => $funds

        ];

        return $paramsArguments;
    }
}
