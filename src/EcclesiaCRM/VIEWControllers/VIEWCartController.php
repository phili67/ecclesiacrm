<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Utils\MiscUtils;

use Slim\Views\PhpRenderer;

use Propel\Runtime\Propel;

class VIEWCartController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderCarView (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/cart/');

        $params = (object)$request->getParsedBody();

        if (isset($params->BulkAddToCart)) {// for the QueryView.php
            $aItemsToProcess = explode(',', $params->BulkAddToCart);

            if (isset($params->AndToCartSubmit)) {
                if (isset($_SESSION['aPeopleCart'])) {
                    $_SESSION['aPeopleCart'] = array_intersect($_SESSION['aPeopleCart'], $aItemsToProcess);
                }
            } elseif (isset($params->NotToCartSubmit)) {
                if (isset($_SESSION['aPeopleCart'])) {
                    $_SESSION['aPeopleCart'] = array_diff($_SESSION['aPeopleCart'], $aItemsToProcess);
                }
            } else {
                $aItemsToProcess = array_filter($aItemsToProcess);
                for ($iCount = 0; $iCount < count($aItemsToProcess); $iCount++) {
                    Cart::AddPerson(str_replace(',', '', $aItemsToProcess[$iCount]));
                }
            }
        }

        if ( !( SessionUser::getUser()->isShowCartEnabled() && Cart::HasPeople() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'cartview.php', $this->argumentsCartViewArray());
    }

    public function argumentsCartViewArray ()
    {
        $connection = Propel::getConnection();

        $iNumPersons = Cart::CountPeople();
        $iNumFamilies = Cart::CountFamilies();

// Email Cart links
// Note: This will email entire group, even if a specific role is currently selected.
        $sSQL = "SELECT per_Email, fam_Email
                        FROM person_per
                        LEFT JOIN person2group2role_p2g2r ON per_ID = p2g2r_per_ID
                        LEFT JOIN group_grp ON grp_ID = p2g2r_grp_ID
                        LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID
                    WHERE per_DateDeactivated IS NULL AND per_ID NOT IN (SELECT per_ID FROM person_per INNER JOIN record2property_r2p ON r2p_record_ID = per_ID INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email') AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

        $statementEmails = $connection->prepare($sSQL);
        $statementEmails->execute();

        $sEmailLink = '';
        while ($row = $statementEmails->fetch(\PDO::FETCH_BOTH)) {
            $sEmail = MiscUtils::SelectWhichInfo($row['per_Email'], $row['fam_Email'], false);
            if ($sEmail) {
                /* if ($sEmailLink) // Don't put delimiter before first email
                    $sEmailLink .= SessionUser::getUser()->MailtoDelimiter(); */
                // Add email only if email address is not already in string
                if (!stristr($sEmailLink, $sEmail)) {
                    $sEmailLink .= $sEmail .= SessionUser::getUser()->MailtoDelimiter();
                }
            }
        }

        $sEmailLink = mb_substr($sEmailLink, 0, -1);

        if ($sEmailLink) {
            // Add default email if default email has been set and is not already in string
            if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
                $sEmailLink .= SessionUser::getUser()->MailtoDelimiter() . SystemConfig::getValue('sToEmailAddress');
            }

            $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368
        }

//Text Cart Link
        $sSQL = "SELECT per_CellPhone, fam_CellPhone
                            FROM person_per LEFT
                            JOIN family_fam ON person_per.per_fam_ID = family_fam.fam_ID
                        WHERE per_DateDeactivated IS NULL AND per_ID NOT IN (
                            SELECT per_ID FROM person_per 
                            INNER JOIN record2property_r2p ON r2p_record_ID = per_ID 
                            INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not SMS') AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $sPhoneLink = '';
        $sPhoneLinkSMS = '';
        $sCommaDelimiter = ', ';

        while ($row = $statement->fetch(\PDO::FETCH_BOTH)) {
            $sPhone = MiscUtils::SelectWhichInfo($row['per_CellPhone'], $row['fam_CellPhone'], false);
            if ($sPhone) {
                /* if ($sPhoneLink) // Don't put delimiter before first phone
                    $sPhoneLink .= $sCommaDelimiter;  */
                // Add phone only if phone is not already in string
                if (!stristr($sPhoneLink, $sPhone)) {
                    $sPhoneLink .= $sPhone . $sCommaDelimiter;
                    $sPhoneLinkSMS .= $sPhone . $sCommaDelimiter;
                }
            }
        }

        $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'CSPNonce' => SystemURLs::getCSPNonce(),
            'sPageTitle'  => _('View Your Cart'),
            'iNumPersons' => $iNumPersons,
            'iNumFamilies' => $iNumFamilies,
            'sEmailLink' => $sEmailLink,
            'sPhoneLink' => $sPhoneLink,
            'sPhoneLinkSMS' => $sPhoneLinkSMS,
            'sCommaDelimiter' => $sCommaDelimiter
        ];

        return $paramsArguments;
    }

    

    public function renderCarToBadge (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/cart/');

        if ( !( SessionUser::getUser()->isAdmin() || SessionUser::getUser()->isCreateDirectoryEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $typeProblem = 0;
        if (isset ($args['flag'])) {
            $typeProblem = ($args['flag']>0)?1:0;
        }

        return $renderer->render($response, 'carttobadge.php', $this->argumentsCartToBadgeArray($typeProblem));
    }

    public function argumentsCartToBadgeArray ($typeProblem)
    {

        $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'CSPNonce' => SystemURLs::getCSPNonce(),
            'sPageTitle'  => _('Cart to Badges'),
            'typeProblem' => $typeProblem
        ];

        return $paramsArguments;
    }

    
    public function renderCarToFamily (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/cart/');

        if (!SessionUser::getUser()->isAddRecordsEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'cartToFamily.php', $this->argumentsCartToFamilyArray());
    }

    public function argumentsCartToFamilyArray ()
    {

        $paramsArguments = [ 'sRootPath'   => SystemURLs::getRootPath(),
            'sRootDocument' => SystemURLs::getDocumentRoot(),
            'CSPNonce' => SystemURLs::getCSPNonce(),
            'sPageTitle'  => _('Add Cart to Family')
        ];

        return $paramsArguments;
    }
}
