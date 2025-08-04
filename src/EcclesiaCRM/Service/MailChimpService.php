<?php
// copyright Philippe Logel not MIT
namespace EcclesiaCRM\Service;

use EcclesiaCRM\Base\PersonQuery;
use EcclesiaCRM\SendNewsLetterUserUpdateQuery;

use EcclesiaCRM\dto\SystemConfig;
use \DrewM\MailChimp\MailChimp;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\SessionUser;

class ListEmailFilter
{
    private $email;

    function __construct($emailAddress)
    {
        $this->email = $emailAddress;
    }

    public function isEmailInList($list)
    {
        foreach ($list['members'] as $listMember) {
            if (strcmp(strtolower($listMember['email_address']), strtolower($this->email)) == 0) {
                return true;
            }
        }
        return false;
    }
}

class MailChimpService
{
    private $isActive = false;
    private $myMailchimp;
    private $updateRequire = false;
    private $lists;
    private $campaigns;
    private $nlsAdds = null;
    private $nlsDeletes = null;


    public function __construct()
    {
        if ( !empty(SystemConfig::getValue('sMailChimpApiKey')) ) {
            $this->isActive = true;
            $this->myMailchimp = new MailChimp(SystemConfig::getValue('sMailChimpApiKey'));
            $_SESSION['MailChimpConnectionStatus'] = $this->myMailchimp->post("authorized-apps");
        }
    }

    public function isActive()
    {
        return $this->isActive && !is_null(SessionUser::getUser()) && SessionUser::getUser()->isMailChimpEnabled();
    }

    public function isLoaded()
    {
        return isset($_SESSION['MailChimpLists']);
    }

    private function getListsFromCache()
    {
        $this->nlsAdds = SendNewsLetterUserUpdateQuery::create()
            ->filterByState('Add')
            ->find();

        $this->nlsDeletes = SendNewsLetterUserUpdateQuery::create()
            ->filterByState('Delete')
            ->find();

        if ($this->nlsAdds->count() > 0 || $this->nlsDeletes->count() > 0) {
            $this->updateRequire = true;
        }
    
        if (!isset($_SESSION['MailChimpLists']) && !is_null($this->myMailchimp)) {// the second part can be used to force update
            LoggerUtils::getAppLogger()->info("Updating MailChimp List Cache");
            $lists = $this->myMailchimp->get("lists")['lists'];
            if (count($lists) == 1) {// now at this time only one list can be manage, you've to manage other the members manually
                #TODO : terminer le cas de plusieurs listes
                foreach ($this->nlsAdds as $nlsAdd) {
                    $person = PersonQuery::create()
                        ->findOneById($nlsAdd->getPersonId());

                    $res = $this->postMember($lists[0]['id'],32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),$person->getAddressForMailChimp(), $person->getHomePhone(), 'subscribed');                    
                    // we clean up the members
                    $nlsAdd->delete();
                }

                foreach ($this->nlsDeletes as $nlsDel) {
                    $person = PersonQuery::create()
                        ->findOneById($nlsDel->getPersonId());

                        $res = $this->deleteMember($lists[0]['id'],$person->getEmail());
                    
                    // we clean up the members
                    $nlsDel->delete();
                }
            }
            foreach ($lists as &$list) {
                $listmembers = $this->getMembersFromList($list['id'], SystemConfig::getValue('iMailChimpRequestTimeOut'));
                $list['members'] = $listmembers['members'];
                $list['tags'] = $this->getAllSegments($list['id'])[1];
            }
            $_SESSION['MailChimpLists'] = $lists;
        }
        /*else{
          LoggerUtils::getAppLogger()->info("Using cached MailChimp List");
        }*/
        return $_SESSION['MailChimpLists'];
    }

    public function reloadMailChimpDatas()
    {
        LoggerUtils::getAppLogger()->info("Updating MailChimp List Cache");
        $lists = $this->myMailchimp->get("lists")['lists'];
        foreach ($lists as &$list) {
            $listmembers = $this->getMembersFromList($list['id'], SystemConfig::getValue('iMailChimpRequestTimeOut'));
            $list['members'] = $listmembers['members'];
            $list['tags'] = $this->getAllSegments($list['id'])[1];
        }
        $_SESSION['MailChimpLists'] = $lists;

        LoggerUtils::getAppLogger()->info("Updating MailChimp Campaigns Cache");
        $campaigns = $this->myMailchimp->get("campaigns", ["count" => 1000])['campaigns'];
        $_SESSION['MailChimpCampaigns'] = $campaigns;
    }

    public function getCampaignReport ($campaignID) {
        $reports['unsubscribed'] = $this->myMailchimp->get("reports/".$campaignID."/unsubscribed");
        $reports['email-activity'] = $this->myMailchimp->get("reports/".$campaignID."/email-activity");

        return $reports;
    }

    private function getCampaignsFromCache()
    {
        if (!isset($_SESSION['MailChimpCampaigns'])) {// the second part can be used to force update
            LoggerUtils::getAppLogger()->info("Updating MailChimp Campaigns Cache");
            $campaigns = $this->myMailchimp->get("campaigns", ["count" => 1000])['campaigns'];
            $_SESSION['MailChimpCampaigns'] = $campaigns;
        }
        /*else{
          LoggerUtils::getAppLogger()->info("Using cached MailChimp List");
        }*/
        return $_SESSION['MailChimpCampaigns'];
    }

    public function getConnectionStatus()
    {
        if (!isset ($connection_status) && !empty(SystemConfig::getValue('sMailChimpApiKey'))) {
            $_SESSION['MailChimpConnectionStatus'] = $this->myMailchimp->post("authorized-apps");
        }

        return $_SESSION['MailChimpConnectionStatus'];
    }

    public function getListNameFromEmail($email)
    {
        if (!$this->isActive) {
            return 'Mailchimp is not active';
        }

        if ($email == '') {
            return 'No email';
        }

        try {
            $lists = $this->getListsFromCache();
            $lists = array_filter($lists, array(new ListEmailFilter($email), 'isEmailInList'));
            $listNames = array_map(function ($list) {
                return $list['name'];
            }, $lists);
            $listMemberships = implode(',', $listNames);
            return $listMemberships;
        } catch (\Exception $e) {
            return $e;
        }
    }

    /* Lists */

    public function getLists()
    {
        if (!$this->isActive) {
            return 'Mailchimp is not active';
        }
        try {
            $result = $this->getListsFromCache();

            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getListFromListId($list_id)
    {
        $mcLists = $this->getLists();

        $i = 0;
        foreach ($mcLists as $list) {
            if ($list['id'] == $list_id) {
                return $list;
            }
            $i++;
        }

        return NULL;
    }

    private function getMembersForManageListable($listMembers) {
        $res = [];
        $SeePrivacyDataEnabled = SessionUser::getUser()->isSeePrivacyDataEnabled();

        foreach ($listMembers as $member) {
            $data = $member;
            $status = '';

            $data['checkoxColumn'] = '<input type="checkbox" class="checkbox_users checkbox_user_' . $member['id'] . '" name="AddRecords" data-id="'
                . $member['id'] . '" data-email="' . $member['email_address'] . '" ' . $status . '>';

            $data['actionColumn'] = '<a class="edit-subscriber" data-id="' . $member['email_address'] . '"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<a class="delete-subscriber" data-id="'
                . $member['email_address'] . '"><i class="far fa-trash-alt" aria-hidden="true" style="color:red"></i></a>';

            $data['tagsColumn'] = '';

            foreach ($member['tags'] as $tag) {
                $data['tagsColumn'] .= $tag['name'] . ' ';
            }

            if ( $SeePrivacyDataEnabled ) {
                $data['email_address_column'] = $member['email_address'];
            } else {
                $data['email_address_column'] = _("Private Data");
            }

            $d = $member['status'];
            $r = _($d);
            if ($d == 'subscribed') {
                $data['statusColumn'] = '<p class="text-green">' . $r . '</p>';
            } else if ($d == 'unsubscribed') {
                $data['statusColumn'] = '<p class="text-orange">' . $r . '</p>';
            } else {
                $data['statusColumn'] = '<p class="text-red">' . $r . '</p>';
            }

            $res[] = $data;
        }

        return $res;
    }

    public function getListMembersFromListId($list_id)
    {
        $mcLists = $this->getLists();

        $i = 0;
        $listmembers = [];

        foreach ($mcLists as $list) {
            if ($list['id'] == $list_id) {
                if (is_null($list['members'])) {
                    // in the case the list is no more in the cache
                    $listmembers = $this->getMembersFromList($list['id'], SystemConfig::getValue('iMailChimpRequestTimeOut'));

                    if (is_null($listmembers) == null || (!is_null($listmembers) != null && gettype($listmembers) == 'array' && count($listmembers[0]) == 0)) {
                        return [];
                    }

                    return $this->getMembersForManageListable(array_values($listmembers));
                }
                return $this->getMembersForManageListable(array_values($list['members']));
            }
            $i++;
        }


        return $listmembers;
    }

    public function createList($name, $subject, $PermissionReminder, $ArchiveBars, $Status)
    {
        $name = $name; // List Name

        $company = SystemConfig::getValue('sEntityName');
        $address1 = SystemConfig::getValue('sEntityAddress');
        $address2 = "";
        $city = SystemConfig::getValue('sEntityCity');
        $state = SystemConfig::getValue('sEntityState');
        $zip = SystemConfig::getValue('sEntityZip');
        $country = SystemConfig::getValue('sEntityCountry');
        $phone = SystemConfig::getValue('sEntityPhone');
        $permission_reminder = $PermissionReminder;
        $archive_bars = $ArchiveBars; // Whether campaigns for this list use the Archive Bar in archives by default : true false

        $marketing_permissions = SystemConfig::getBooleanValue('bGDPR'); //Whether or not the list has marketing permissions (eg. GDPR) enabled.

        // contact
        $from_name = SessionUser::getUser()->getPerson()->getFullName();

        $from_email = SystemConfig::getValue("sMailChimpEmailSender");

        if (empty($from_email)) {
            $from_email = (!empty (SessionUser::getUser()->getPerson()->getEmail())) ? SessionUser::getUser()->getPerson()->getEmail() : SessionUser::getUser()->getPerson()->getWorkEmail();
            if (empty ($from_email)) {
                $from_email = SystemConfig::getValue('sEntityEmail');
            }
        }
        $subject = $subject;
        $language = substr(SystemConfig::getValue('sLanguage'), 0, 2);

        $notify_subs = SystemConfig::getValue('sEntityEmail'); // The email address to send subscribe notifications to.
        $notify_unsubs = SystemConfig::getValue('sEntityEmail'); // The email address to send subscribe notifications to.
        $type = true; //Whether the list supports multiple formats for emails. When set to true, subscribers can choose whether they want to receive HTML or plain-text emails. When set to false, subscribers will receive HTML emails, with a plain-text alternative backup.
        $visibility = $Status; // Whether this list is public or private : pub or prv

        if ($state == "") {
            $state = $country;
        }

        $data = array( // the information for your new list--not all is required
            "name" => $name,
            "contact" => array(
                "company" => $company,
                "address1" => $address1,
                "address2" => $address2,
                "city" => $city,
                "state" => $state,
                "zip" => $zip,
                "country" => $country,
                "phone" => $phone
            ),
            "marketing_permissions" => $marketing_permissions,
            "permission_reminder" => $permission_reminder,
            "use_archive_bar" => $archive_bars,
            "campaign_defaults" => array(
                "from_name" => $from_name,
                "from_email" => $from_email,
                "subject" => $subject,
                "language" => $language
            ),
            "notify_on_subscribe" => $notify_subs,
            "notify_on_unsubscribe" => $notify_unsubs,
            "email_type_option" => $type,
            "visibility" => $visibility
        );

        // we create the mailing list
        $result = $this->myMailchimp->post('lists', $data);


        if (!(is_array($result) and array_key_exists('title', $result)) ) {
            // we add the list in the cache
            $_SESSION['MailChimpLists'][] = $result;
        }

        return $result;
    }

    private function delete_List($list_id)
    {
        $mcLists = $_SESSION['MailChimpLists'];

        $res = [];
        foreach ($mcLists as $list) {
            if ($list['id'] != $list_id) {
                $res[] = $list;
            }
        }

        $_SESSION['MailChimpLists'] = $res;

        // we delete all campaign binded to the list
        $campaigns = $_SESSION['MailChimpCampaigns'];

        $camps = [];
        foreach ($campaigns as $campaign) {
            if ($campaign['recipients']['list_id'] != $list_id) {
                $camps[] = $campaign;
            } else {
                $this->deleteCampaign($campaign['id']);
            }
        }

        $_SESSION['MailChimpCampaigns'] = $camps;
    }

    public function deleteList($list_id)
    {
        $result = $this->myMailchimp->delete("lists/$list_id");

        if (gettype($result) == 'boolean' && $result == true) {
            // we use always the cache to improve the performance
            $this->delete_List($list_id);
        }

        return $result;
    }

    public function getCampaigns()
    {
        if (!$this->isActive) {
            return 'Mailchimp is not actived';
        }
        try {
            $result = $this->getCampaignsFromCache();

            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function changeLists($new_list)
    {
        $lists = $this->getListsFromCache();

        $res = [];

        foreach ($lists as $list) {
            if ($list['id'] == $new_list['id']) {
                $list['name'] = $new_list['name'];
                $list['campaign_defaults']['subject'] = $new_list['campaign_defaults']['subject'];
                $list['permission_reminder'] = $new_list['permission_reminder'];
            }
            $res[] = $list;
        }

        $_SESSION['MailChimpLists'] = $res;
    }

    public function changeListName($list_id, $name, $subject, $permission_reminder)
    {
        $new_list = $this->myMailchimp->patch("lists/$list_id", ["name" => $name, "campaign_defaults" => array("subject" => $subject), "permission_reminder" => $permission_reminder]);

        $this->changeLists($new_list);

        return $new_list;
    }

    /* segments */
    private function getMembersFor($list_id, $tag_id)
    {
        $tag_arr = [];

        $mcLists = $this->getLists();

        $i = 0;
        foreach ($mcLists as $list) {
            if ($list['id'] == $list_id) {
                foreach ($list['members'] as $member) {// the list is found
                    foreach ($member['tags'] as $tag) {
                        if ($tag['id'] == $tag_id) {
                            $tag_arr[] = $member['email_address'];
                        }
                    }
                }
                break;
            }
            $i++;
        }

        return $tag_arr;
    }

    public function createSegment($list_id, $name, $membersArr)
    {
        $data = array(
            "name" => $name,
            "static_segment" => $membersArr /* Maichimp doc : An array of emails to be used for a static segment. Any emails provided that are not present on the list will be ignored. Passing an empty array will create a static segment without any subscribers. This field cannot be provided with the options field.*/
        );

        $result = $this->myMailchimp->post("lists/$list_id/segments", $data);

        if (array_key_exists('id', $result)) {
            $segment_id = $result['id'];
            // we've to add the modification to the list
            $i = 0; // list index

            $_SESSION['MailChimpLists'][$i]['tags'][] = $result;
            $lists = $_SESSION['MailChimpLists'];

            foreach ($lists as $list) {
                foreach ($membersArr as $member) {
                    $j = 0;// member index
                    foreach ($list['members'] as $listMember) {
                        if ($listMember['email_address'] == $member) {
                            $_SESSION['MailChimpLists'][$i]['members'][$j]['tags'][] = [
                                'id' => $segment_id,
                                'name' => $this->getSegmentName($lists[$i]['tags'], $segment_id)
                            ];

                            break;
                        }
                        $j++;
                    }
                }
                $i++;
            }
        }

        return [$result, "lists/$list_id/segments"];
    }

    // DEAD code
    public function updateSegment($list_id, $name, $tag, $membersArr, $merge = true)
    {

        $old_members = $this->getMembersFor($list_id, $tag);

        if ($merge) {
            $arr = array_merge($membersArr, $old_members);
        } else {
            $arr = $membersArr;
        }

        $data = array(
            "name" => $name,
            "static_segment" => $arr /* Maichimp doc : An array of emails to be used for a static segment. Any emails provided that are not present on the list will be ignored. Passing an empty array will create a static segment without any subscribers. This field cannot be provided with the options field.*/
        );

        $result = $this->myMailchimp->patch("lists/$list_id/segments/$tag", $data);

        $this->reloadMailChimpDatas();

        return [$result, "lists/$list_id/segments/$tag"];
    }

    private function delete_Segment($list_id, $tag_id)
    {
        $mcLists = $_SESSION['MailChimpLists'];

        // we first delete the tags in the list
        $res = [];
        foreach ($mcLists as $list) {
            $break_loop = false;

            if ($list['id'] == $list_id) {
                $resTag = [];
                foreach ($list['tags'] as $tag) {
                    if ($tag['id'] != $tag_id) {
                        $resTag[] = $tag;
                    }
                }

                $list['tags'] = $resTag;

                $resMembers = [];
                foreach ($list['members'] as $member) {
                    $resTag = [];
                    foreach ($member['tags'] as $tag) {
                        if ($tag['id'] != $tag_id) {
                            $resTag[] = $tag;
                        }
                    }
                    $member['tags'] = $resTag;

                    $resMembers[] = $member;
                }
                $list['members'] = $resMembers;

                $break_loop = true;
            }

            $res[] = $list;

            if ($break_loop) {
                break;
            }
        }

        // now we delete the tags for each members
        $_SESSION['MailChimpLists'] = $res;
    }

    public function deleteSegment($list_id, $tag_id)
    {
        $result = $this->myMailchimp->delete("lists/$list_id/segments/$tag_id");

        if (gettype($result) == 'boolean' && $result == true) {
            // We've to remove all the segments for each user
            $this->delete_Segment($list_id, $tag_id);
        }

        return [$result, $result, "lists/$list_id/segments/$tag_id"];
    }

    public function getAllSegments($list_id)
    {
        $result = $this->myMailchimp->get("lists/$list_id/segments");

        if (!(is_array($result) and array_key_exists('title', $result))) {
            // we've to add the modification to the list
            $resultContent = $result['segments'];

            return [$result, $resultContent, "lists/$list_id/segments"];
        }

        return [$result, $result, "lists/$list_id/segments"];
    }

    public function getSegment($list_id, $segment_id)
    {
        $result = $this->myMailchimp->get("lists/$list_id/segments/$segment_id");

        return [$result, $result, "lists/$list_id/segments/$segment_id"];
    }

    private function getSegmentName($tags, $segment_id) {
        foreach ($tags as $tag) {
            if ($tag['id'] == $segment_id) {
                return $tag['name'];
            }
        }
        return '';
    }

    public function addMembersToSegment($list_id, $segment_id, $arr_members)
    {
        $data = array(
            "members_to_add" => $arr_members
        );

        $result = $this->myMailchimp->post("lists/$list_id/segments/$segment_id", $data);

        if (!(is_array($result) and array_key_exists('title', $result)) ) {
            // we've to add the modification to the list
            $i = 0;
            $lists = $_SESSION['MailChimpLists'];

            foreach ($lists as $list) {
                foreach ($arr_members as $member) {
                    $j = 0;
                    foreach ($list['members'] as $listMember) {
                        if ($listMember['email_address'] == $member) {
                            $_SESSION['MailChimpLists'][$i]['members'][$j]['tags'][] = [
                                'id' => $segment_id,
                                'name' => $this->getSegmentName($lists[$i]['tags'], $segment_id)
                            ];

                            break;
                        }
                        $j++;
                    }
                }
                $i++;
            }
        }

        return [$result, $result, "lists/$list_id/segments/$segment_id"];
    }

    public function removeMembersFromSegment($list_id, $segment_id, $arr_members)
    {
        $data = array(
            "members_to_remove" => $arr_members
        );
        $result = $this->myMailchimp->post("lists/$list_id/segments/$segment_id", $data);

        if (!(is_array($result) and array_key_exists('title', $result))) {
            // we've to add the modification to the list
            $i = 0;
            $lists = $_SESSION['MailChimpLists'];

            foreach ($lists as $list) {
                foreach ($arr_members as $member) {
                    $j = 0;
                    foreach ($list['members'] as $listMember) {
                        if ($listMember['email_address'] == $member) {
                            $k = 0;
                            foreach ($listMember['tags'] as $tag) {
                                if ($tag['id'] == $segment_id) {
                                    array_splice($_SESSION['MailChimpLists'][$i]['members'][$j]['tags'], $k, $k);
                                    break;
                                }
                                $k++;
                            }
                        }
                        $j++;
                    }
                }
                $i++;
            }
        }

        return [$result, $result, "lists/$list_id/segments/$segment_id"];
    }

    public function removeMembersFromAllSegments($list_id, $arr_members)
    {
        $data = array(
            "members_to_remove" => $arr_members
        );


        $list = $this->getListFromListId($list_id);

        foreach ($list['tags'] as $tag) {
            $segment_id = $tag['id'];

            $result = $this->myMailchimp->post("lists/$list_id/segments/$segment_id", $data);

            // we've to add the modification to the list
            // we've to reload all the list to be sure to delete in the right one
            $i = 0;
            $lists = $_SESSION['MailChimpLists'];

            foreach ($lists as $list) {
                foreach ($arr_members as $member) {
                    $j = 0;
                    foreach ($list['members'] as $listMember) {
                        if ($listMember['email_address'] == $member) {
                            $k = 0;
                            foreach ($listMember['tags'] as $tag) {
                                if ($tag['id'] == $segment_id) {
                                    array_splice($_SESSION['MailChimpLists'][$i]['members'][$j]['tags'], $k, 1);
                                    break;
                                }
                                $k++;
                            }
                        }
                        $j++;
                    }
                }
                $i++;
            }
        }

        return [$result, $result, "lists/$list_id/segments/$segment_id"];
    }

    public function getMembersFromSegment($list_id, $segment_id)
    {
        $result = $this->myMailchimp->get("lists/$list_id/segments/$segment_id/members");

        if (!(is_array($result) and array_key_exists('title', $result))) {
            // we've to add the modification to the list
            $resultContent = $result['members'];
        }

        return [$result, $resultContent, "lists/$list_id/segments/$segment_id/members"];
    }

    /* Campaigns */

    public function getCampaignFromId($campaignId)
    {
        $campaigns = $this->getCampaigns();

        foreach ($campaigns as $campaign) {
            if ($campaign['id'] == $campaignId) {
                /*$content = $this->getCampaignContent ($campaignId);

                // Be careFull this can change with a new MailChimp api
                $realContent = explode("            <center>\n                <br/>\n                <br/>\n",$content['html'])[0];

                $campaign['content'] = $realContent;*/
                return $campaign;
            }
        }
    }

    public function getCampaignsFromListId($list_id)
    {
        $campaigns = $this->getCampaigns();

        $res = [];

        foreach ($campaigns as $campaign) {
            if ($campaign['recipients']['list_id'] == $list_id) {
                $res[] = $campaign;
            }
        }

        $res_sent = array_filter($res, function ($var) {
            return ($var['status'] == 'sent');
        });
        $res_save = array_filter($res, function ($var) {
            return ($var['status'] == 'save' or $var['status'] == 'paused' or $var['status'] == 'schedule');
        });;

        $your_date_field_name = 'send_time';
        usort($res_sent, function ($a, $b) use (&$your_date_field_name) {
            return  strtotime($b[$your_date_field_name]) - strtotime($a[$your_date_field_name]);
        });

        $your_date_field_name = 'create_time';
        usort($res_save, function ($a, $b) use (&$your_date_field_name) {
            return  strtotime($b[$your_date_field_name]) - strtotime($a[$your_date_field_name]);
        });

        $res_sent = array_slice($res_sent, 0, 5);
        $res_save = array_slice($res_save, 0, 5);

        return [$res_sent, $res_save];
    }

    private function create_Campaign($list_id, $camp)
    {
        // we add the campaign in the cache
        $_SESSION['MailChimpCampaigns'][] = $camp;

        // now we loop on the lists to upgrade de campaign count
        $mcLists = $_SESSION['MailChimpLists'];

        $i = 0;
        foreach ($mcLists as $list) {
            if ($list['id'] == $list_id) {
                $_SESSION['MailChimpLists'][$i]['stats']['campaign_count']++;
            }
            $i++;
        }
    }

    public function createCampaign($list_id, $tag_Id, $subject, $title, $htmlBody)
    {
        $from_name = SystemConfig::getValue('sEntityName');
        $from_email = SystemConfig::getValue("sMailChimpEmailSender");

        if (empty($from_email)) {
            $from_email = (!empty (SessionUser::getUser()->getPerson()->getEmail())) ? SessionUser::getUser()->getPerson()->getEmail() : SessionUser::getUser()->getPerson()->getWorkEmail();
            if (empty ($from_email)) {
                $from_email = SystemConfig::getValue('sEntityEmail');
            }
        }

        if ($tag_Id != -1) {
            $data = array(
                "recipients" =>
                    array(
                        "list_id" => $list_id,
                        "segment_opts" => array(
                            "saved_segment_id" => $tag_Id, //The id for an existing saved segment.
                            //"prebuilt_segment_id" => $tag_Id, //The prebuilt segment id, if a prebuilt segment has been designated for this campaign.
                            "match" => "any" // Segment match type. : any all
                        )
                    ),
                "type" => "regular",
                "settings" => array(
                    "subject_line" => $subject,
                    "title" => $title,
                    "reply_to" => $from_email,
                    "from_name" => $from_name,
                    //"folder_id"    => "8888969b77"
                )
            );
        } else {
            $data = array(
                "recipients" =>
                    array(
                        "list_id" => $list_id
                    ),
                "type" => "regular",
                "settings" => array(
                    "subject_line" => $subject,
                    "title" => $title,
                    "reply_to" => $from_email,
                    "from_name" => $from_name,
                    //"folder_id"    => "8888969b77"
                )
            );
        }

        $result = $this->myMailchimp->post("campaigns", $data);

        if (!(is_array($result) and array_key_exists('title', $result))) {

            $campaignID = $result['id'];// we get the campaign ID

            $resultContent = $this->setCampaignContent($campaignID, $htmlBody);

            $this->create_Campaign($list_id, $result);
        }

        return [$result, $resultContent, "campaigns/$campaignID/content"];
    }

    private function delete_Campaign($campaignID)
    {
        $campaigns = $_SESSION['MailChimpCampaigns'];

        $res = [];

        foreach ($campaigns as $campaign) {
            if ($campaign['id'] != $campaignID) {
                $res[] = $campaign;
            }
        }

        $_SESSION['MailChimpCampaigns'] = $res;
    }

    public function deleteCampaign($campaignID)
    {

        $result = $this->myMailchimp->delete("campaigns/$campaignID");

        if (gettype($result) == 'boolean' && $result == true) {
            $this->delete_Campaign($campaignID);
        }

        return $result;
    }

    public function setCampaignSchedule($campaignID, $schedule_time, $timewarp, $batch_delay)
    {
        $resultContent = $this->myMailchimp->post("campaigns/$campaignID/actions/schedule", ["schedule_time" => $schedule_time, "timewarp" => $timewarp, "batch_delay" => $batch_delay]);

        $this->setCampaignStatus($campaignID, "schedule", $schedule_time);

        return $resultContent;
    }

    public function setCampaignUnschedule($campaignID)
    {
        $resultContent = $this->myMailchimp->post("campaigns/$campaignID/actions/unschedule");

        $this->setCampaignStatus($campaignID, "paused");

        return $resultContent;
    }

    private function setCampaignStatus($campaignID, $status, $send_time = NULL)
    {
        $campaigns = $_SESSION['MailChimpCampaigns'];

        $res = [];

        foreach ($campaigns as $campaign) {
            if ($campaign['id'] == $campaignID) {
                $campaign['status'] = $status;
                if (!is_null($send_time)) {
                    $campaign['send_time'] = $send_time;
                }
            }
            $res[] = $campaign;
        }

        $_SESSION['MailChimpCampaigns'] = $res;
    }

    public function setCampaignPause($campaignID, $schedule_time, $timewarp, $batch_delay)
    {
        $resultContent = $this->myMailchimp->post("campaigns/$campaignID/actions/pause");

        return $resultContent;
    }

    public function setCampaignContent($campaignID, $htmlBody)
    {
        $resultContent = $this->myMailchimp->put("campaigns/" . $campaignID . "/content", ["html" => $htmlBody]);

        return $resultContent;
    }

    private function set_Campaign_MailSubject($campaignID, $subject)
    {
        $campaigns = $_SESSION['MailChimpCampaigns'];

        $res = [];

        foreach ($campaigns as $campaign) {
            if ($campaign['id'] == $campaignID) {
                $campaign['settings']['subject_line'] = $subject;
            }
            $res[] = $campaign;
        }

        $_SESSION['MailChimpCampaigns'] = $res;
    }

    public function setCampaignMailSubject($campaignID, $subject)
    {
        $data = array(
            "settings" => array(
                "subject_line" => $subject)
        );

        $result = $this->myMailchimp->patch("campaigns/$campaignID", $data);

        if ( !(is_array($result) and array_key_exists('title', $result)) ) {
            $this->set_Campaign_MailSubject($campaignID, $subject);
        }

        return $result;
    }

    public function getCampaignContent($campaignID)
    {
        $result = $this->myMailchimp->get("campaigns/$campaignID/content");

        return $result;
    }

    private function send_Campaign($campaignID)
    {
        $campaigns = $_SESSION['MailChimpCampaigns'];

        $res = [];

        foreach ($campaigns as $campaign) {
            if ($campaign['id'] == $campaignID) {
                $campaign['status'] = 'sent';
            }
            $res[] = $campaign;
        }

        $_SESSION['MailChimpCampaigns'] = $res;
    }

    public function sendCampaign($campaignID)
    {

        $result = $this->myMailchimp->post("campaigns/$campaignID/actions/send");

        if ( (is_bool($result) and $result) or (is_array($result) and !array_key_exists('title', $result) ) ) {
            $this->send_Campaign($campaignID);
        }

        return $result;
    }

    private function add_list_member($list_id, $member)
    {
        $mcLists = $_SESSION['MailChimpLists'];

        $i = 0;
        foreach ($mcLists as $list) {
            if ($list['id'] == $list_id) {
                $_SESSION['MailChimpLists'][$i]['stats']['member_count']++;
                $_SESSION['MailChimpLists'][$i]['members'][] = $member;
            }
            $i++;
        }
    }

    public function sendAllMembers($array, $timeout=500)
    {
        $res = $this->myMailchimp->post("batches", $array, $timeout);

        //$this->restoreCache();

        return $res;
    }

    public function postMember($list_id, $id, $first_name, $last_name, $mail, $address = NULL, $phone = NULL, $status)
    {
        if (!empty($mail)) {
            $merge_fields = ['FNAME' => $first_name, 'LNAME' => $last_name];

            if (!is_null($address) && SystemConfig::getBooleanValue('bMailChimpWithAddressPhone')) {
                $merge_fields['ADDRESS'] = $address;
            }

            if (!is_null($phone) && SystemConfig::getBooleanValue('bMailChimpWithAddressPhone')) {
                $merge_fields['PHONE'] = $phone;
            }

            $result = $this->myMailchimp->post("lists/$list_id/members", [
                'id' => "$id",
                'email_address' => $mail,
                'status' => $status,
                'merge_fields' => $merge_fields
            ]);

            if ( (is_bool($result) and $result) or (is_array($result) and !array_key_exists('title', $result)) ) {
                $this->add_list_member($list_id, $result);
            }

            return $result;
        }

        return NULL;
    }

    private function delete_list_member($list_id, $email)
    {
        $mcLists = $_SESSION['MailChimpLists'];

        $newMembers = [];

        $i = 0;
        foreach ($mcLists as $list) {
            if ($list['id'] == $list_id) {
                $res = "liste1 = ";
                foreach ($_SESSION['MailChimpLists'][$i]['members'] as $memb) {
                    if ($memb['email_address'] != $email) {
                        $newMembers[] = $memb;
                    } else {
                        if ($_SESSION['MailChimpLists'][$i]['stats']['member_count'] > 0) {
                            $_SESSION['MailChimpLists'][$i]['stats']['member_count']--;
                        } else {
                            $_SESSION['MailChimpLists'][$i]['stats']['member_count'] = 0;
                        }
                    }
                }
                break;
            }
            $i++;
        }

        $_SESSION['MailChimpLists'][$i]['members'] = array_values($newMembers);
    }

    public function getMembersFromList($list_id, $time_out = 500)
    {
        $count = 1000;// it's the maximal value (Mailchimp doc)
        return $this->myMailchimp->get("lists/$list_id/members", ['count' => $count], $time_out);
    }

    public function deleteMember($list_id, $email)
    {
        if (is_null($this->myMailchimp)) return null;

        $subscriber_hash = $this->myMailchimp->subscriberHash($email);

        $result = $this->myMailchimp->delete("lists/$list_id/members/$subscriber_hash");

        if (gettype($result) == 'boolean' and $result == true) {
            $this->delete_list_member($list_id, $email);
        }

        return $result;
    }

    public function deleteMemberEmail($oldEmail)
    {
        $lists = $this->getListsFromCache();

        $result = NULL;

        foreach ($lists as $list) {
            $result = $this->deleteMember($list['id'], $oldEmail);
        }
        return $result;
    }

    public function deleteAllMembers($list_id)
    {
        $members = $this->getListMembersFromListId($list_id);

        $res = [];

        foreach ($members as $member) {
            $res[] = $this->deleteMember($list_id, $member['email_address']);
        }

        return $res;
    }

    private function update_list_member($list_id, $member, $status)
    {
        $mcLists = $_SESSION['MailChimpLists'];

        $i = 0;
        foreach ($mcLists as $list) {
            if ($list['id'] == $list_id) {
                $j = 0;
                foreach ($_SESSION['MailChimpLists'][$i]['members'] as $memb) {
                    if ($memb['email_address'] == $member) {
                        $old_status = $_SESSION['MailChimpLists'][$i]['members'][$j]['status'];

                        $_SESSION['MailChimpLists'][$i]['members'][$j]['status'] = $status;

                        if ($status == 'subscribed' && $old_status != $status) {
                            $_SESSION['MailChimpLists'][$i]['stats']['member_count']++;
                            $_SESSION['MailChimpLists'][$i]['stats']['unsubscribe_count']--;
                        } else if ($status == 'unsubscribed' && $old_status != $status) {
                            $_SESSION['MailChimpLists'][$i]['stats']['member_count']--;
                            $_SESSION['MailChimpLists'][$i]['stats']['unsubscribe_count']++;
                        }
                        break;
                    }
                    $j++;
                }

                break;
            }
            $i++;
        }
    }

    public function getStatusMember($list_id, $mail)
    {
        if (is_null($this->myMailchimp)) return null;

        $subscriber_hash = $this->myMailchimp->subscriberHash($mail);

        $result = $this->myMailchimp->get("lists/$list_id/members/$subscriber_hash");

        return $result;
    }

    public function getListNameAndStatus($email)
    {
        try {
            $lists = $this->getListsFromCache();
            $lists = array_filter($lists, array(new ListEmailFilter($email), 'isEmailInList'));
            $listNames = array_map(function ($list) {
                return $list['name'];
            }, $lists);
            $listMemberships = implode(',', $listNames);

            $res = [];

            foreach ($lists as $list) {
                $res[] = [$list['name'], $this->getStatusMember($list['id'], $email)['status'], $list['id']];
            }
            return $res;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateMember($list_id, $first_name, $last_name, $mail, $status) // status : Unsubscribed , Subscribed
    {
        if (is_null($this->myMailchimp)) return null;

        $subscriber_hash = $this->myMailchimp->subscriberHash($mail);

        if (!empty($name) && !empty($last_name)) {
            $result = $this->myMailchimp->patch("lists/$list_id/members/$subscriber_hash", [
                'merge_fields' => ['FNAME' => $first_name, 'LNAME' => $last_name],
                'status' => $status,
                //'interests'    => ['2s3a384h' => true],
            ]);
        } else {
            $result = $this->myMailchimp->patch("lists/$list_id/members/$subscriber_hash", [
                'status' => $status,
                //'interests'    => ['2s3a384h' => true],
            ]);
        }

        if ( !(is_array($result) and array_key_exists('title', $result)) ) {
            $res = $this->update_list_member($list_id, $mail, $status);
        }

        return $result;
    }

    private function update_member_email($list_id, $member, $newEmail)
    {
        $mcLists = $_SESSION['MailChimpLists'];

        $i = 0;
        foreach ($mcLists as $list) {

            if ($list['id'] == $list_id) {
                $j = 0;
                foreach ($_SESSION['MailChimpLists'][$i]['members'] as $memb) {
                    if ($memb['email_address'] == $member) {
                        $_SESSION['MailChimpLists'][$i]['members'][$j]['email_address'] = $newEmail;
                        break;
                    }
                    $j++;
                }

                break;
            }
            $i++;
        }
    }

    public function updateMemberEmail($oldEmail, $newEmail) // status : Unsubscribed , Subscribed
    {
        $subscriber_hash = $this->myMailchimp->subscriberHash($oldEmail);

        $lists = $this->getListsFromCache();

        foreach ($lists as $list) {
            $result = $this->myMailchimp->patch("lists/" . $list['id'] . "/members/$subscriber_hash", [
                'email_address' => $newEmail,
            ]);

            $this->update_member_email($list['id'], $oldEmail, $newEmail);
        }
        return $result;
    }
}
