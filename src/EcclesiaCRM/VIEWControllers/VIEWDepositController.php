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

use EcclesiaCRM\Utils\InputUtils;

use EcclesiaCRM\DonationFundQuery;

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

    public function renderDepositFind (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        // Security: User must have finance permission or be the one who created this deposit
        if ( !(SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') )) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'finddepositslip.php', $this->argumentsFindDepositArray());
    }

    public function argumentsFindDepositArray ()
    {
        $iDepositSlipID = $_SESSION['iCurrentDeposit'];
        $donationFunds = DonationFundQuery::Create()->find();

        //Set the page title
        $sPageTitle = _("Deposit Listing");

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,            
            'iDepositSlipID'            => $iDepositSlipID,
            'donationFunds'             => $donationFunds
        ];

        return $paramsArguments;
    }

    public function renderManageEnvelopes (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        // Security: User must have finance permission or be the one who created this deposit
        if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'manageEnvelopes.php', $this->argumentsManageEnvelopesArray());
    }

    public function argumentsManageEnvelopesArray ()
    {
        //Set the page title
        $sPageTitle = _("Envelope Manager");

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle
        ];

        return $paramsArguments;
    }

    public function renderFinancialReports (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        // Security: User must have finance permission or be the one who created this deposit
        if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'financialReports.php', $this->argumentsFinancialReportsArray());
    }

    public function renderFinancialReportsNoRows (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        $year = -1;
        if (isset($args['year'])) {
            $year = $args['year'];
        }

        // Security: User must have finance permission or be the one who created this deposit
        if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $sReportType = '';
        
        if (isset($args['ReportType'])) {
            $sReportType = InputUtils::LegacyFilterInput($args['ReportType']);
        }

        return $renderer->render($response, 'financialReports.php', $this->argumentsFinancialReportsArray('NoRows', $sReportType, $year));
    }

    public function argumentsFinancialReportsArray ($ReturnMessage = '', $sReportType = '', $year = -1)
    {
        //Set the page title
        if (array_key_exists('ReportType', $_POST)) {
            $sReportType = InputUtils::LegacyFilterInput($_POST['ReportType']);
        }
        
        if ($sReportType == '' && array_key_exists('ReportType', $_GET)) {
            $sReportType = InputUtils::LegacyFilterInput($_GET['ReportType']);
        }
        
        // Set the page title and include HTML header
        $sPageTitle = _("Financial Reports");
        if ($sReportType) {
            $sPageTitle .= ': '._($sReportType);
        }

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'sReportType'               => $sReportType,
            'ReturnMessage'             => $ReturnMessage,
            'year'                      => $year
        ];

        return $paramsArguments;
    }

    public function renderTaxReport (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        // Security: User must have finance permission or be the one who created this deposit
        if (!SessionUser::getUser()->isAdmin() && SystemConfig::getValue('bCSVAdminOnly')) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $sReportType = '';
        
        if (isset($args['ReportType'])) {
            $sReportType = InputUtils::LegacyFilterInput($args['ReportType']);
        }

        return $renderer->render($response, 'taxReport.php', $this->argumentsTaxReportArray());
    }

    public function argumentsTaxReportArray ()
    {
        
        
        // Set the page title and include HTML header
        $sPageTitle = _("Tax Report");

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle
        ];

        return $paramsArguments;
    }

    public function renderAutoPaymentEditor (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        // Security: User must have finance permission or be the one who created this deposit
        if (!(SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $iAutID = -1;
        
        if (isset($args['AutID'])) {
            $iAutID = InputUtils::LegacyFilterInput($args['AutID'], 'int');
        }

        $iFamily = -1;
        
        if (isset($args['FamilyID'])) {
            $iFamily = InputUtils::LegacyFilterInput($args['FamilyID'], 'int');
        }

        $linkBack = '';
        
        if (isset($args['linkBack'])) {
            $linkBack = InputUtils::LegacyFilterInput($args['linkBack']);            
        }

        return $renderer->render($response, 'autaPaymentEditor.php', $this->argumentsAutoPaymentEditorArray($iAutID, $iFamily, $linkBack));
    }

    public function argumentsAutoPaymentEditorArray ($iAutID, $iFamily, $linkBack)
    {
        // Set the page title and include HTML header
        $sPageTitle = _("Automatic payment configuration");

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iAutID'                    => $iAutID, 
            'iFamily'                   => $iFamily, 
            'linkBack'                  => str_replace("-","/", $linkBack),
            'origLinkBack'              => $linkBack

        ];

        return $paramsArguments;
    }

    public function renderElectronicPaymentList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        // Security: User must have finance permission or be the one who created this deposit
        if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'electronicPaymentList.php', $this->argumentsElectronicPaymentListArray());
    }

    public function argumentsElectronicPaymentListArray ()
    {
        // Set the page title and include HTML header
        $sPageTitle = _("Electronic Payment Listing");

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle
        ];

        return $paramsArguments;
    }
}
