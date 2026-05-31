<?php

namespace Plugins\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\SessionUser;

spl_autoload_register(function ($className) {
    include_once str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Services', '/'), $className) . '.php';
});

use Plugins\Service\MailChimpService;

class MailchimpDashboardItemPlugin implements DashboardItemInterface {

    public static function getDashboardItemName() {
        return "MailchimpDisplay";
    }

    public static function getDashboardItemValue() {
        $mailchimp = new MailChimpService();

        $isActive = $mailchimp->isActive();

        if ($isActive == false) {
            return ['isActive' => $isActive];
        }

        $isLoaded = $mailchimp->isLoaded();

        $lists = $mailchimp->getLists();

        $campaigns = [];

        foreach ($lists as $list){
            $campaigns[] = $mailchimp->getCampaignsFromListId($list['id']);
        }

        return ['MailChimpLists' => $mailchimp->getLists(),'MailChimpCampaigns' => $campaigns, 'firstLoaded' => !$isLoaded, 'isActive' => $isActive];
    }

    public static function shouldInclude($PageName) {
        $mailchimp = new MailChimpService();

        return (SessionUser::getUser()->isMailChimpEnabled())?true:false;
    }

}
