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

use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\Pledge;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\DonationFundQuery;

use EcclesiaCRM\Map\PledgeTableMap;

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\RedirectUtils;


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

    public function renderAutoPaymentClearAccount (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        // Security: User must have finance permission or be the one who created this deposit
        if ( !( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $iVancoAutID = -1;

        if (isset ($args['customerid'])) {
            $iVancoAutID = InputUtils::LegacyFilterInput($args['customerid'], 'int');
        }

        return $renderer->render($response, 'autoPaymentClearAccount.php', $this->argumentsAutoPaymentClearAccountArray($iVancoAutID));
    }

    public function argumentsAutoPaymentClearAccountArray ($iVancoAutID)
    {
        // Set the page title and include HTML header
        $sPageTitle = "";

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iVancoAutID'                => $iVancoAutID
        ];

        return $paramsArguments;
    }

    

    public function renderPledgeEditor (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/deposit/');

        // Security: User must have finance permission or be the one who created this deposit
        if (!(SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance'))) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $iCurrentDeposit = 0;

        if (isset ($args['CurrentDeposit'])) {
            $iCurrentDeposit = InputUtils::LegacyFilterInput($args['CurrentDeposit'], 'int');
        }
        
        $PledgeOrPayment = "Pledge";

        if (isset ($args['PledgeOrPayment'])) {
            $PledgeOrPayment = InputUtils::LegacyFilterInput($args['PledgeOrPayment'], 'int');
        }


        $linkBack = '';
        
        if (isset($args['linkBack'])) {
            $linkBack = InputUtils::LegacyFilterInput($args['linkBack']);            
        }


        $sGroupKey = '';
        
        if (isset($args['GroupKey'])) {
            $sGroupKey = InputUtils::LegacyFilterInput($args['GroupKey']);            
        }

        $iFamily = 0;
        if (isset($args['FamilyID'])) {
            $iFamily = InputUtils::LegacyFilterInput($args['FamilyID'], 'int');
        }
        
        if (isset($_SESSION['iCurrentDeposit'])) {
            $iCurrentDeposit = $_SESSION['iCurrentDeposit'];
        }
        

        return $renderer->render($response, 'pledgeEditor.php', $this->argumentsPledgeEditorArray($iCurrentDeposit, $PledgeOrPayment, $linkBack, $sGroupKey, $iFamily));
    }
    
    public function argumentsPledgeEditorArray ($iCurrentDeposit, $PledgeOrPayment, $linkBack, $sGroupKey, $iFamily)
    {
        if (SystemConfig::getValue('bUseScannedChecks')) { // Instantiate the MICR class dead code ?
            $micrObj = new MICRReader();
        }
        
        $iEnvelope = 0;
        $sCheckNoError = '';
        $iCheckNo = '';
        $sDateError = '';
        $sAmountError = '';
        $nNonDeductible = [];
        $sComment = '';
        $tScanString = '';
        $dep_Closed = false;
        $iAutID = 0;
        $iCurrentDeposit = 0;
        $origLinkback = str_replace("-","/", $linkBack);
        
        $nAmount = []; // this will be the array for collecting values for each fund
        $sAmountError = [];
        $sComment = [];
        
        $checkHash = [];
        
        // Get the list of funds
        $funds = DonationFundQuery::Create()->findByActive('true');
        
        foreach ($funds as $fund) {
            $fundId2Name[$fund->getId()] = $fund->getName();
            $nAmount[$fund->getId()] = 0.0;
            $nNonDeductible[$fund->getId()] = 0.0;
            $sAmountError[$fund->getId()] = '';
            $sComment[$fund->getId()] = '';
            if (!isset($defaultFundID)) {
                $defaultFundID = $fund->getId();
            }
            $fundIdActive[$fund->getId()] = $fund->getActive();
        }
        
        
        $fund2PlgIds = []; // this will be the array cross-referencing funds to existing plg_plgid's
        
        if ($sGroupKey) {
            $pledges = PledgeQuery::Create()->findByGroupkey($sGroupKey);
        
            foreach ($pledges as $pledge) {
                $onePlgID = $pledge->getId();
                $oneFundID = $pledge->getFundid();
                $oneDepID = $pledge->getDepid();
                $iOriginalSelectedFund = $oneFundID; // remember the original fund in case we switch to splitting
                $fund2PlgIds[$oneFundID] = $onePlgID;
        
                // Security: User must have Finance permission or be the one who entered this record originally
                if (!(SessionUser::getUser()->isFinanceEnabled() || SessionUser::getUser()->getPersonId() == $pledge->getEditedby())) {
                    RedirectUtils::Redirect('v2/dashboard');
                    exit;
                }
            }
        }
        
        
        if ($iCurrentDeposit == 0) {
            $iCurrentDeposit = $oneDepID;
        }
        
        
        // Handle _POST input if the form was up and a button press came in
        if (isset($_POST['PledgeSubmit']) or
            isset($_POST['PledgeSubmitAndAdd']) or
            isset($_POST['MatchFamily']) or
            isset($_POST['MatchEnvelope']) or
            isset($_POST['SetDefaultCheck']) or
            isset($_POST['SetFundTypeSelection']) or
            isset($_POST['PledgeOrPayment'])) {
        
            if (array_key_exists('PledgeOrPayment', $_POST)) {
                $PledgeOrPayment = InputUtils::LegacyFilterInput($_POST['PledgeOrPayment'], 'string');
            } else {
                $PledgeOrPayment = "Pledge";
            }
        
            $iFamily = InputUtils::LegacyFilterInput($_POST['FamilyID'], 'int');
        
            $dDate = InputUtils::FilterDate($_POST['Date']);
            if (!$dDate) {
                if (array_key_exists('idefaultDate', $_SESSION)) {
                    $dDate = $_SESSION['idefaultDate'];
                } else {
                    $dDate = date('Y-m-d');
                }
            }
            $_SESSION['idefaultDate'] = $dDate;
        
            // set from drop-down if set, saved session default, or by calcuation
            $iFYID = InputUtils::LegacyFilterInput($_POST['FYID'], 'int');
            if (!$iFYID) {
                $iFYID = $_SESSION['idefaultFY'];
            }
            if (!$iFYID) {
                $iFYID = MiscUtils::CurrentFY();
            }
            $_SESSION['idefaultFY'] = $iFYID;
        
            if (array_key_exists('CheckNo', $_POST)) {
                $iCheckNo = InputUtils::LegacyFilterInput($_POST['CheckNo'], 'int');
            } else {
                $iCheckNo = 0;
            }
        
            if (array_key_exists('Schedule', $_POST)) {
                $iSchedule = InputUtils::LegacyFilterInput($_POST['Schedule']);
            } else {
                $iSchedule = 'Once';
            }
            $_SESSION['iDefaultSchedule'] = $iSchedule;
        
            $iMethod = InputUtils::LegacyFilterInput($_POST['Method']);
            if (!$iMethod) {
                if ($sGroupKey) {
                    $ormResult = PledgeQuery::Create()
                        ->setDistinct(PledgeTableMap::COL_PLG_METHOD)
                        ->findOneByGroupkey($sGroupKey);
        
                    $iMethod = $ormResult->getMethod();
                } elseif ($iCurrentDeposit) {
                    $ormMethod = PledgeQuery::Create()
                        ->orderById()
                        ->limit(1)
                        ->findOneByDepid($iCurrentDeposit);
        
                    if (!is_null($ormMethod)) {
                        $iMethod = $ormMethod->getMethod();
                    } else {
                        $iMethod = 'CHECK';
                    }
                } else {
                    $iMethod = 'CHECK';
                }
            }
            $_SESSION['idefaultPaymentMethod'] = $iMethod;
        
            $iEnvelope = 0;
            if (array_key_exists('Envelope', $_POST)) {
                $iEnvelope = InputUtils::LegacyFilterInput($_POST['Envelope'], 'int');
            }
        } else { // Form was not up previously, take data from existing records or make default values
            if ($sGroupKey) {
                $pledgeSearch = PledgeQuery::Create()
                    ->orderByGroupkey()
                    ->withColumn('COUNT(plg_GroupKey)', 'NumGroupKeys')
                    ->findOneByGroupkey($sGroupKey);
        
                $numGroupKeys = $pledgeSearch->getNumGroupKeys();
                $iAutID = $pledgeSearch->getAutId();
                $PledgeOrPayment = $pledgeSearch->getPledgeorpayment();
                $fundId = $pledgeSearch->getFundid();
                $dDate = $pledgeSearch->getDate()->format('Y-m-d');
                $iFYID = $pledgeSearch->getFyid();
                $iCheckNo = $pledgeSearch->getCheckno();
                $iSchedule = $pledgeSearch->getSchedule();
                $iMethod = $pledgeSearch->getMethod();
                $iCurrentDeposit = $pledgeSearch->getDepid();
        
                $ormFam = PledgeQuery::Create()
                    ->setDistinct(PledgeTableMap::COL_PLG_METHOD)
                    ->findOneByGroupkey($sGroupKey);
        
                $iFamily = $ormFam->getFamId();
                $iCheckNo = $ormFam->getCheckno();
                $iFYID = $ormFam->getFyid();
        
                $pledgesAmount = PledgeQuery::Create()
                    ->findByGroupkey($sGroupKey);
        
                foreach ($pledgesAmount as $pledgeAmount) {
                    $nAmount[$pledgeAmount->getFundid()] = $pledgeAmount->getAmount();
                    $nNonDeductible[$pledgeAmount->getFundid()] = $pledgeAmount->getNondeductible();
                    $sComment[$pledgeAmount->getFundid()] = $pledgeAmount->getComment();
                }
            } else {
                if (array_key_exists('idefaultDate', $_SESSION)) {
                    $dDate = $_SESSION['idefaultDate'];
                } else {
                    $dDate = date('Y-m-d');
                }
        
                if (array_key_exists('idefaultFY', $_SESSION)) {
                    $iFYID = $_SESSION['idefaultFY'];
                } else {
                    $iFYID = MiscUtils::CurrentFY();
                }
                if (array_key_exists('iDefaultSchedule', $_SESSION)) {
                    $iSchedule = $_SESSION['iDefaultSchedule'];
                } else {
                    $iSchedule = 'Once';
                }
                if (array_key_exists('idefaultPaymentMethod', $_SESSION)) {
                    $iMethod = $_SESSION['idefaultPaymentMethod'];
                } else {
                    $iMethod = 'Check';
                }
            }
            if (!$iEnvelope && $iFamily) {
                $fam = FamilyQuery::Create()->findOneById($iFamily);
        
                if ($fam->getEnvelope()) {
                    $iEnvelope = $fam->getEnvelope();
                }
            }
        }
        
        if ($PledgeOrPayment == 'Pledge') { // Don't assign the deposit slip if this is a pledge
            //$iCurrentDeposit = 0;
        } else { // its a deposit
            if ($iCurrentDeposit > 0) {
                $_SESSION['iCurrentDeposit'] = $iCurrentDeposit;
            } else {
                $iCurrentDeposit = $_SESSION['iCurrentDeposit'];
            }
        }
        
        // Get the current deposit slip data
        if ($iCurrentDeposit) {
            $deposit = DepositQuery::Create()->findOneById($iCurrentDeposit);
        
            $dep_Closed = $deposit->getClosed();
            $dep_Date = $deposit->getDate()->format('Y-m-d');
            $dep_Type = $deposit->getType();
        }
        
        
        if ($iMethod == 'CASH' || $iMethod == 'CHECK') {
            $dep_Type = 'Bank';
        } elseif ($iMethod == 'CREDITCARD') {
            $dep_Type = 'CreditCard';
        } elseif ($iMethod == 'BANKDRAFT') {
            $dep_Type = 'BankDraft';
        }
        
        if ($PledgeOrPayment == 'Payment') {
            $bEnableNonDeductible = SystemConfig::getValue('bEnableNonDeductible'); // this could/should be a config parm?  regardless, having a non-deductible amount for a pledge doesn't seem possible
        }
        
        if (isset($_POST['PledgeSubmit']) || isset($_POST['PledgeSubmitAndAdd'])) {
            //Initialize the error flag
            $bErrorFlag = false;
            // make sure at least one fund has a non-zero numer
            $nonZeroFundAmountEntered = 0;
            foreach ($fundId2Name as $fun_id => $fun_name) {
                //$fun_active = $fundActive[$fun_id];
                $nAmount[$fun_id] = InputUtils::LegacyFilterInput($_POST[$fun_id . '_Amount']);
                $sComment[$fun_id] = InputUtils::LegacyFilterInput($_POST[$fun_id . '_Comment']);
                if ($nAmount[$fun_id] > 0) {
                    ++$nonZeroFundAmountEntered;
                }
        
                if ($bEnableNonDeductible) {
                    $nNonDeductible[$fun_id] = InputUtils::LegacyFilterInput($_POST[$fun_id . '_NonDeductible']);
                    //Validate the NonDeductible Amount
                    if ($nNonDeductible[$fun_id] > $nAmount[$fun_id]) { //Validate the NonDeductible Amount
                        $sNonDeductibleError[$fun_id] = _("NonDeductible amount can't be greater than total amount.");
                        $bErrorFlag = true;
                    }
                }
            } // end foreach
        
            if (!$nonZeroFundAmountEntered) {
                $sAmountError[$fun_id] = _('At least one fund must have a non-zero amount.');
                $bErrorFlag = true;
            }
        
        
            if (array_key_exists('ScanInput', $_POST)) {
                $tScanString = InputUtils::LegacyFilterInput($_POST['ScanInput']);
            } else {
                $tScanString = '';
            }
            $iAutID = 0;
            if (array_key_exists('AutoPay', $_POST)) {
                $iAutID = InputUtils::LegacyFilterInput($_POST['AutoPay']);
            }
            //$iEnvelope = InputUtils::LegacyFilterInput($_POST["Envelope"], 'int');
        
            if ($PledgeOrPayment == 'Payment' && !$iCheckNo && $iMethod == 'CHECK') {
                $sCheckNoError = '<span style="color: red; ">' . _('Must specify non-zero check number') . '</span>';
                $bErrorFlag = true;
            }
        
            // detect check inconsistencies
            if ($PledgeOrPayment == 'Payment' && $iCheckNo) {
                if ($iMethod == 'CASH') {
                    $sCheckNoError = '<span style="color: red; ">' . _("Check number not valid for 'CASH' payment") . '</span>';
                    $bErrorFlag = true;
                } elseif ($iMethod == 'CHECK' && !$sGroupKey) {
                    $chkKey = $iFamily . '|' . $iCheckNo;
                    if (array_key_exists($chkKey, $checkHash)) {
                        $text = "Check number '" . $iCheckNo . "' for selected family already exists.";
                        $sCheckNoError = '<span style="color: red; ">' . _($text) . '</span>';
                        $bErrorFlag = true;
                    }
                }
            }
        
            // Validate Date
            if (strlen($dDate) > 0) {
                list($iYear, $iMonth, $iDay) = sscanf($dDate, '%04d-%02d-%02d');
                if (!checkdate($iMonth, $iDay, $iYear)) {
                    $sDateError = '<span style="color: red; ">' . _('Not a valid date') . '</span>';
                    $bErrorFlag = true;
                }
            }
        
            //If no errors, then let's update...
            if (!$bErrorFlag && !$dep_Closed) {
                // Only set PledgeOrPayment when the record is first created
                // loop through all funds and create non-zero amount pledge records
                foreach ($fundId2Name as $fun_id => $fun_name) {
                    if (!$iCheckNo) {
                        $iCheckNo = 0;
                    }
                    if ($fund2PlgIds && array_key_exists($fun_id, $fund2PlgIds)) {
                        if ($nAmount[$fun_id] > 0) {
                            $pledge = PledgeQuery::Create()->findOneById($fund2PlgIds[$fun_id]);
        
                            $pledge->setPledgeorpayment($PledgeOrPayment);
                            $pledge->setFamId($iFamily);
                            $pledge->setFyid($iFYID);
                            $pledge->setDate($dDate);
                            $pledge->setAmount($nAmount[$fun_id]);
                            $pledge->setSchedule($iSchedule);
                            $pledge->setMethod($iMethod);
                            $pledge->setComment($sComment[$fun_id]);
                            $pledge->setDatelastedited(date('YmdHis'));
                            $pledge->setEditedby(SessionUser::getUser()->getPersonId());
                            $pledge->setCheckno($iCheckNo);
                            $pledge->setScanstring($tScanString);
                            $pledge->setAutId($iAutID);
                            $pledge->setNondeductible($nNonDeductible[$fun_id]);
        
                            $pledge->save();
                        } else { // delete that record
                            $pledge = PledgeQuery::Create()->findOneById($fund2PlgIds[$fun_id]);
                            $pledge->delete();
                        }
                    } elseif ($nAmount[$fun_id] > 0) {
                        if ($iMethod != 'CHECK') {
                            $iCheckNo = 'NULL';
                        }
                        if (!$sGroupKey) {
                            if ($iMethod == 'CHECK') {
                                $sGroupKey = MiscUtils::genGroupKey($iCheckNo, $iFamily, $fun_id, $dDate);
                            } elseif ($iMethod == 'BANKDRAFT') {
                                if (!$iAutID) {
                                    $iAutID = 'draft';
                                }
                                $sGroupKey = MiscUtils::genGroupKey($iAutID, $iFamily, $fun_id, $dDate);
                            } elseif ($iMethod == 'CREDITCARD') {
                                if (!$iAutID) {
                                    $iAutID = 'credit';
                                }
                                $sGroupKey = MiscUtils::genGroupKey($iAutID, $iFamily, $fun_id, $dDate);
                            } else {
                                $sGroupKey = MiscUtils::genGroupKey('cash', $iFamily, $fun_id, $dDate);
                            }
                        }
        
                        if ($iCurrentDeposit == 0) {
                            $iCurrentDeposit = $_SESSION['iCurrentDeposit'];
                        }
        
        
                        $pledge = new Pledge();
        
                        $pledge->setFamId($iFamily);
                        $pledge->setFyid($iFYID);
                        $pledge->setDate($dDate);
                        $pledge->setAmount($nAmount[$fun_id]);
                        $pledge->setSchedule($iSchedule);
                        $pledge->setMethod($iMethod);
                        $pledge->setComment($sComment[$fun_id]);
                        $pledge->setDatelastedited(date('YmdHis'));
                        $pledge->setEditedby(SessionUser::getUser()->getPersonId());
                        $pledge->setPledgeorpayment($PledgeOrPayment);
                        $pledge->setFundid($fun_id);
                        $pledge->setDepid($iCurrentDeposit);
                        $pledge->setCheckno($iCheckNo);
                        $pledge->setScanstring($tScanString);
                        $pledge->setAutId($iAutID);
                        $pledge->setNondeductible($nNonDeductible[$fun_id]);
                        $pledge->setGroupkey($sGroupKey);
        
                        $pledge->save();
        
                    }
                } // end foreach of $fundId2Name
                if (isset($_POST['PledgeSubmit'])) {
                    // Check for redirection to another page after saving information: (ie. PledgeEditor.php?previousPage=prev.php?a=1;b=2;c=3)
                    if ($linkBack != '') {
                        RedirectUtils::Redirect($origLinkback);
                    } else {
                        //Send to the view of this pledge
                        RedirectUtils::Redirect('v2/deposit/pledge/editor/GroupKey/' . $sGroupKey . '/'. $PledgeOrPayment . '/'. $linkBack);
                    }
                } elseif (isset($_POST['PledgeSubmitAndAdd'])) {
                    //Reload to editor to add another record
                    RedirectUtils::Redirect("v2/deposit/pledge/editor/CurrentDeposit/". $iCurrentDeposit . "/" . $PledgeOrPayment . '/'. $linkBack);
                }
            } // end if !$bErrorFlag
        } elseif (isset($_POST['MatchFamily']) || isset($_POST['MatchEnvelope']) || isset($_POST['SetDefaultCheck'])) {
        
            //$iCheckNo = 0;
            // Take care of match-family first- select the family based on the scanned check
            if (SystemConfig::getValue('bUseScannedChecks') && isset($_POST['MatchFamily'])) {
                $tScanString = InputUtils::LegacyFilterInput($_POST['ScanInput']);
        
                $routeAndAccount = $micrObj->FindRouteAndAccount($tScanString); // use routing and account number for matching
        
                if ($routeAndAccount) {
                    $fam = FamilyQuery::Create()->findOneByScanCheck($routeAndAccount);
                    $iFamily = $fam->getId();
                    $iCheckNo = $micrObj->FindCheckNo($tScanString);
                } else {
                    $iFamily = InputUtils::LegacyFilterInput($_POST['FamilyID'], 'int');
                    $iCheckNo = InputUtils::LegacyFilterInput($_POST['CheckNo'], 'int');
                }
            } elseif (isset($_POST['MatchEnvelope'])) {
                // Match envelope is similar to match check- use the envelope number to choose a family
        
                $iEnvelope = InputUtils::LegacyFilterInput($_POST['Envelope'], 'int');
                if ($iEnvelope && strlen($iEnvelope) > 0) {
                    $fam = FamilyQuery::Create()->findOneByEnvelope($iEnvelope);
                    if (!is_null($fam)) {
                        $iFamily = $fam->getId();
                    }
                }
            } else {
                $iFamily = InputUtils::LegacyFilterInput($_POST['FamilyID']);
                $iCheckNo = InputUtils::LegacyFilterInput($_POST['CheckNo'], 'int');
            }
        
            // Handle special buttons at the bottom of the form.
            if (isset($_POST['SetDefaultCheck'])) {
                $tScanString = InputUtils::LegacyFilterInput($_POST['ScanInput']);
                $routeAndAccount = $micrObj->FindRouteAndAccount($tScanString); // use routing and account number for matching
                $iFamily = InputUtils::LegacyFilterInput($_POST['FamilyID'], 'int');
                $fam = FamilyQuery::Create()->findOneById($iFamily);
                $fam->setScanCheck($routeAndAccount);
                $fam->save();
            }
        }
        
        // Set Current Deposit setting for user
        if ($iCurrentDeposit) {
            /* @var $currentUser \EcclesiaCRM\User */
            $currentUser = SessionUser::getUser();
            $currentUser->setCurrentDeposit($iCurrentDeposit);
            $currentUser->save();
        }
        
        //Set the page title
        if ($PledgeOrPayment == 'Pledge') {
            $sPageTitle = _('Pledge Editor') . ': ' . _($dep_Type) . _(' Deposit Slip #') . $iCurrentDeposit . " (" . OutputUtils::change_date_for_place_holder($dep_Date) . ")";
        } elseif ($iCurrentDeposit) {
            $sPageTitle = _('Payment Editor') . ': ' . _($dep_Type) . _(' Deposit Slip #') . $iCurrentDeposit . " (" . OutputUtils::change_date_for_place_holder($dep_Date) . ")";
        
            $checksFit = SystemConfig::getValue('iChecksPerDepositForm');
        
            $pledges = PledgeQuery::Create()->findByDepid($iCurrentDeposit);
        
            $depositCount = 0;
            foreach ($pledges as $pledge) {
                $chkKey = $pledge->getFamId() . '|' . $pledge->getCheckno();
        
                if ($pledge->getMethod() == 'CHECK' && (!array_key_exists($chkKey, $checkHash))) {
                    $checkHash[$chkKey] = $pledge->getId();
                    ++$depositCount;
                }
            }
        
            $roomForDeposits = $checksFit - $depositCount;
            if ($roomForDeposits <= 0) {
                $sPageTitle .= '<font color=red>';
            }
            $sPageTitle .= ' (' . $roomForDeposits . _(' more entries will fit.') . ')';
            if ($roomForDeposits <= 0) {
                $sPageTitle .= '</font>';
            }
        } else { // not a plege and a current deposit hasn't been created yet
            if ($sGroupKey) {
                $sPageTitle = _('Payment Editor - Modify Existing Payment');
            } else {
                $sPageTitle = _('Payment Editor - New Deposit Slip Will Be Created');
            }
        } // end if $PledgeOrPayment
        
        if ($dep_Closed) {
            $sPageTitle .= ' &nbsp; <font color=red>' . _('Deposit closed') . '</font>';
        }
        
        //$familySelectHtml = MiscUtils::buildFamilySelect($iFamily, $sDirRoleHead, $sDirRoleSpouse);
        $sFamilyName = '';
        if ($iFamily) {
            $fam = FamilyQuery::Create()->findOneById($iFamily);
            $sFamilyName = $fam->getName() . ' ' . MiscUtils::FormatAddressLine($fam->getAddress1(), $fam->getCity(), $fam->getState());
        }

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iCurrentDeposit'           => $iCurrentDeposit,
            'PledgeOrPayment'           => $PledgeOrPayment,
            'linkBack'                  => str_replace("-","/", $linkBack),
            'origLinkBack'              => $linkBack,
            'sGroupKey'                 => $sGroupKey,
            'iFYID'                     => $iFYID,
            'iFamily'                   => $iFamily,
            'sFamilyName'               => $sFamilyName,
            'dDate'                     => $dDate,
            'dep_Date'                  => $dep_Date,
            'dep_Type'                  => $dep_Type,
            'dep_Closed'                => $dep_Closed,
            'sDateError'                => $sDateError,
            'iEnvelope'                 => $iEnvelope,
            'iSchedule'                 => $iSchedule,
            'iMethod'                   => $iMethod,
            'iCheckNo'                  => $iCheckNo,
            'sCheckNoError'             => $sCheckNoError,
            'iAutID'                    => $iAutID,
            'tScanString'               => $tScanString,
            'bEnableNonDeductible'      => $bEnableNonDeductible,
            'fundId2Name'               => $fundId2Name,
            'nNonDeductible'            => $nNonDeductible,
            'sComment'                  => $sComment,
            'nAmount'                   => $nAmount,
            'sAmountError'              => $sAmountError,
            'nNonDeductible'            => $nNonDeductible,
            'checkHash'                 => $checkHash
        ];

        return $paramsArguments;
    }
}