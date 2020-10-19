<?php

namespace EcclesiaCRM\Synchronize;


use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\SessionUser;

class MailchimpDashboardItem implements DashboardItemInterface {

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

        return ((SessionUser::getUser()->isMailChimpEnabled())?true:false) && ($PageName=="/v2/dashboard" || $PageName == "/menu") && !$mailchimp->isLoaded();
    }

}
