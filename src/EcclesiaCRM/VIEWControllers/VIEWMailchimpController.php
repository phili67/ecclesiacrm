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

use EcclesiaCRM\Service\MailChimpService;

use Slim\Views\PhpRenderer;

class VIEWMailchimpController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderMailChimpDashboard (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/email/mailchimp/');

        if ( !( SessionUser::getUser()->isMailChimpEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'dashboard.php', $this->mailchimpDashboardArgumentsArray());
    }

    public function mailchimpDashboardArgumentsArray ()
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

    public function renderMailChimpCampaign (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/email/mailchimp/');

        $campaignId = $args['campaignId'];

        $mailchimp       = new MailChimpService();

        if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'campaign.php', $this->mailchimpCampaignArgumentsArray($campaignId, $mailchimp));
    }

    public function mailchimpCampaignArgumentsArray ($campaignId,$mailchimp)
    {
        $mailChimpStatus = $mailchimp->getConnectionStatus();
        $campaign        = $mailchimp->getCampaignFromId($campaignId);
        $reports = $mailchimp->getCampaignReport($campaignId);

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
            'sDateFormatLong'   => SystemConfig::getValue('sDateFormatLong'),
            'reports'           => $reports
        ];

        return $paramsArguments;
    }

    public function renderMailChimpManageList (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/email/mailchimp/');

        $listId = $args['listId'];

        $mailchimp       = new MailChimpService();

        if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'managelist.php', $this->mailchimpManageListArgumentsArray($listId, $mailchimp));
    }

    public function mailchimpManageListArgumentsArray ($listId,$mailchimp)
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


    public function renderMailChimpDuplicateEmails (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/email/mailchimp/');

        $mailchimp       = new MailChimpService();

        if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'duplicateemails.php', $this->mailchimpDuplicateEmailsArgumentsArray());
    }

    public function mailchimpDuplicateEmailsArgumentsArray ()
    {
        $sPageTitle = _('Find Duplicate Emails');

        $paramsArguments = ['sRootPath'       => SystemURLs::getRootPath(),
            'sRootDocument'   => SystemURLs::getDocumentRoot(),
            'sPageTitle'      => $sPageTitle,
            'lang'            => substr(SystemConfig::getValue('sLanguage'),0,2),
        ];

        return $paramsArguments;
    }


    public function renderMailChimpNotInMailchimpEmailsPersons (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/email/mailchimp/');

        $mailchimp       = new MailChimpService();

        if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'notinmailchimpemailspersons.php', $this->mailchimpNotInMailchimpEmailsArgumentsArrayPersons());
    }

    public function mailchimpNotInMailchimpEmailsArgumentsArrayPersons ()
    {
        $sPageTitle = _('Persons Not In MailChimp');

        $paramsArguments = ['sRootPath'       => SystemURLs::getRootPath(),
            'sRootDocument'   => SystemURLs::getDocumentRoot(),
            'sPageTitle'      => $sPageTitle,
            'lang'            => substr(SystemConfig::getValue('sLanguage'),0,2),
        ];

        return $paramsArguments;
    }

    public function renderMailChimpNotInMailchimpEmailsFamilies (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/email/mailchimp/');

        $mailchimp       = new MailChimpService();

        if ( !(SessionUser::getUser()->isMailChimpEnabled() && $mailchimp->isActive()) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'notinmailchimpemailsfamilies.php', $this->mailchimpNotInMailchimpEmailsArgumentsArrayFamilies());
    }

    public function mailchimpNotInMailchimpEmailsArgumentsArrayFamilies ()
    {
        $sPageTitle = _('Families Not In MailChimp');

        $paramsArguments = ['sRootPath'       => SystemURLs::getRootPath(),
            'sRootDocument'   => SystemURLs::getDocumentRoot(),
            'sPageTitle'      => $sPageTitle,
            'lang'            => substr(SystemConfig::getValue('sLanguage'),0,2),
        ];

        return $paramsArguments;
    }

}
