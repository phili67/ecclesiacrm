<?php

/*******************************************************************************
 *
 *  filename    : route/cartview.php
 *  last change : 2019-12-26
 *  description : manage the cartview
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incorporated in another software authorization
 *
 ******************************************************************************/

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Utils\MiscUtils;

use Slim\Views\PhpRenderer;

use Propel\Runtime\Propel;

$app->group('/cart', function () {
    $this->get('/view', 'renderCarView');
});

function renderCarView (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/cart/');

    if ( !( SessionUser::getUser()->isShowCartEnabled() && Cart::HasPeople() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'cartview.php', argumentsCartViewArray());
}

function argumentsCartViewArray ()
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
                    WHERE per_ID NOT IN (SELECT per_ID FROM person_per INNER JOIN record2property_r2p ON r2p_record_ID = per_ID INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email') AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

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
                        WHERE per_ID NOT IN (SELECT per_ID FROM person_per INNER JOIN record2property_r2p ON r2p_record_ID = per_ID INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not SMS') AND per_ID IN (" . Cart::ConvertCartToString($_SESSION['aPeopleCart']) . ')';

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
