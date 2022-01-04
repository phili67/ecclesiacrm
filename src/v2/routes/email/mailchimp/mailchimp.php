<?php

/* Copyright Philippe Logel not MIT */
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\Service\MailChimpService;

use Slim\Views\PhpRenderer;

$app->group('/mailchimp', function (RouteCollectorProxy $group) {
    $group->get('', 'renderMailChimpDashboard');
    $group->get('/dashboard', 'renderMailChimpDashboard');
    $group->get('/debug', 'renderMailChimpDebug');
    $group->get('/campaign/{campaignId}', 'renderMailChimpCampaign');
    $group->get('/managelist/{listId}', 'renderMailChimpManageList');
    $group->get('/duplicateemails', 'renderMailChimpDuplicateEmails');
    $group->get('/notinmailchimpemailspersons', 'renderMailChimpNotInMailchimpEmailsPersons');
    $group->get('/notinmailchimpemailsfamilies', 'renderMailChimpNotInMailchimpEmailsFamilies');
});

function renderMailChimpDashboard (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/email/mailchimp/');

    if ( !( SessionUser::getUser()->isMailChimpEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'dashboard.php', mailchimpDashboardArgumentsArray());
}

function mailchimpDashboardArgumentsArray ()
{
   $sPageTitle = _("MailChimp Dashboard");

   $mailchimp       = new MailChimpService();
   $mailChimpStatus = $mailchimp->getConnectionStatus();

   $paramsArguments = ['sRootPath'         => SystemURLs::getRootPath(),
                       'sRootDocument'     => SystemURLs::getDocumentRoot(),
                       'sPageTitle'        => $sPageTitle,
                       'mailchimp'         => $mailchimp,
                       'lang'              => substr(SystemConfig::getValue('sLanguage'),0,2),
                       'mailChimpStatus'   => $mailChimpStatus,
                       'isMenuOption'      => SessionUser::getUser()->isMenuOptionsEnabled(),
                       'getSupportURL'     => SystemURLs::getSupportURL(),
                       'isMailChimpActiv'  => (($mailchimp->isActive())?1:0),
                       'isMailChimpLoaded' => (($mailchimp->isLoaded())?1:0)
                       ];

   return $paramsArguments;
}


function renderMailChimpDebug (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/email/mailchimp/');

    if ( !( SessionUser::getUser()->isMailChimpEnabled() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'debug.php', mailchimpDebugArgumentsArray());
}

function mailchimpDebugArgumentsArray ()
{
   $sPageTitle = _("Debug Email Connection");

   $mailchimp       = new MailChimpService();
   $mailChimpStatus = $mailchimp->getConnectionStatus();

   $paramsArguments = ['sRootPath'       => SystemURLs::getRootPath(),
                       'sRootDocument'   => SystemURLs::getDocumentRoot(),
                       'sPageTitle'      => $sPageTitle,
                       'isMenuOption'    => SessionUser::getUser()->isMenuOptionsEnabled()
                       ];

   return $paramsArguments;
}


function renderMailChimpCampaign (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/email/mailchimp/');

    $campaignId = $args['campaignId'];

    $mailchimp       = new MailChimpService();

    if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'campaign.php', mailchimpCampaignArgumentsArray($campaignId, $mailchimp));
}

function mailchimpCampaignArgumentsArray ($campaignId,$mailchimp)
{
   $mailChimpStatus = $mailchimp->getConnectionStatus();
   $campaign        = $mailchimp->getCampaignFromId($campaignId);

   $sPageTitle = _('Email Campaign').' : '.$campaign['settings']['title'];
   $sPageTitleSpan = _('Email Campaign').' : '.$campaign['settings']['title'].' <b><span style="color:'.(($campaign['status'] == "sent")?'green':'gray').';float:right" >('._($campaign['status']).')</span></b>';

   $paramsArguments = ['sRootPath'         => SystemURLs::getRootPath(),
                       'sRootDocument'     => SystemURLs::getDocumentRoot(),
                       'sPageTitle'        => $sPageTitle,
                       'sPageTitleSpan'    => $sPageTitleSpan,
                       'campaignId'        => $campaignId,
                       'campaign'          => $campaign,
                       'isMailchimpActiv'  => $mailchimp->isActive(),
                       'contentsExternalCssFont' => SystemConfig::getValue("sMailChimpContentsExternalCssFont"),
                       'extraFont' => SystemConfig::getValue("sMailChimpExtraFont"),
                       'lang'              => substr(SystemConfig::getValue('sLanguage'),0,2),
                       'isMenuOption'      => !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()),
                       'bWithAddressPhone' => SystemConfig::getBooleanValue('bMailChimpWithAddressPhone'),
                       'sDateFormatLong'   => SystemConfig::getValue('sDateFormatLong')
                       ];

   return $paramsArguments;
}

function renderMailChimpManageList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/email/mailchimp/');

    $listId = $args['listId'];

    $mailchimp       = new MailChimpService();

    if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'managelist.php', mailchimpManageListArgumentsArray($listId, $mailchimp));
}

function mailchimpManageListArgumentsArray ($listId,$mailchimp)
{
   $mailChimpStatus = $mailchimp->getConnectionStatus();

   $list = $mailchimp->getListFromListId($listId);

   $sPageTitle     = _('Email List')." : ". $list['name'].(($list['marketing_permissions'])?'  ('._("GDPR").')':'');
   $sPageTitleSpan = _('Email List')." : <span  id=\"ListTitle\">". $list['name'].(($list['marketing_permissions'])?'</span>  <span style="float:right">'._("GDPR").'</span>':'');

   $paramsArguments = ['sRootPath'         => SystemURLs::getRootPath(),
                       'sRootDocument'     => SystemURLs::getDocumentRoot(),
                       'sPageTitle'        => $sPageTitle,
                       'sPageTitleSpan'    => $sPageTitleSpan,
                       'listId'            => $listId,
                       'mailchimp'         => $mailchimp,
                       'list'              => $list,
                       'isMailchimpActiv'  => $mailchimp->isActive(),
                       'contentsExternalCssFont' => SystemConfig::getValue("sMailChimpContentsExternalCssFont"),
                       'extraFont'         => SystemConfig::getValue("sMailChimpExtraFont"),
                       'lang'              => substr(SystemConfig::getValue('sLanguage'),0,2),
                       'getSupportURL'     => SystemURLs::getSupportURL(),
                       'isMenuOption'      => !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()),
                       'bWithAddressPhone' => SystemConfig::getBooleanValue('bMailChimpWithAddressPhone'),
                        'sDateFormatLong'  => SystemConfig::getValue('sDateFormatLong')
                       ];

   return $paramsArguments;
}


function renderMailChimpDuplicateEmails (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/email/mailchimp/');

    $mailchimp       = new MailChimpService();

    if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'duplicateemails.php', mailchimpDuplicateEmailsArgumentsArray());
}

function mailchimpDuplicateEmailsArgumentsArray ()
{
   $sPageTitle = _('Find Duplicate Emails');

   $paramsArguments = ['sRootPath'       => SystemURLs::getRootPath(),
                       'sRootDocument'   => SystemURLs::getDocumentRoot(),
                       'sPageTitle'      => $sPageTitle,
                       'lang'            => substr(SystemConfig::getValue('sLanguage'),0,2),
                       ];

   return $paramsArguments;
}


function renderMailChimpNotInMailchimpEmailsPersons (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/email/mailchimp/');

    $mailchimp       = new MailChimpService();

    if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'notinmailchimpemailspersons.php', mailchimpNotInMailchimpEmailsArgumentsArrayPersons());
}

function mailchimpNotInMailchimpEmailsArgumentsArrayPersons ()
{
   $sPageTitle = _('Persons Not In MailChimp');

   $paramsArguments = ['sRootPath'       => SystemURLs::getRootPath(),
                       'sRootDocument'   => SystemURLs::getDocumentRoot(),
                       'sPageTitle'      => $sPageTitle,
                       'lang'            => substr(SystemConfig::getValue('sLanguage'),0,2),
                       ];

   return $paramsArguments;
}

function renderMailChimpNotInMailchimpEmailsFamilies (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/email/mailchimp/');

    $mailchimp       = new MailChimpService();

    if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'notinmailchimpemailsfamilies.php', mailchimpNotInMailchimpEmailsArgumentsArrayFamilies());
}

function mailchimpNotInMailchimpEmailsArgumentsArrayFamilies ()
{
    $sPageTitle = _('Families Not In MailChimp');

    $paramsArguments = ['sRootPath'       => SystemURLs::getRootPath(),
        'sRootDocument'   => SystemURLs::getDocumentRoot(),
        'sPageTitle'      => $sPageTitle,
        'lang'            => substr(SystemConfig::getValue('sLanguage'),0,2),
    ];

    return $paramsArguments;
}
