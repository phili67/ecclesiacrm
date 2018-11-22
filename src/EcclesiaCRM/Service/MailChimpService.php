<?php
// copyright Philippe Logel not MIT
namespace EcclesiaCRM\Service;

use EcclesiaCRM\dto\SystemConfig;
use \DrewM\MailChimp\MailChimp;
use EcclesiaCRM\Utils\LoggerUtils;

class ListEmailFilter {
  private $email;
  
  function __construct($emailAddress)
  {
    $this->email = $emailAddress;
  }
  public function isEmailInList($list) {
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
    private $lists;
    private $campaigns;
    public function __construct()
    {
        if (!empty(SystemConfig::getValue('sMailChimpApiKey'))) {
            $this->isActive = true;
            $this->myMailchimp = new MailChimp(SystemConfig::getValue('sMailChimpApiKey'));
        }
    }
    public function isActive()
    {
        return $this->isActive; 
    }
    private function getListsFromCache(){
      if (!isset($_SESSION['MailChimpLists']) ){// the second part can be used to force update
        LoggerUtils::getAppLogger()->info("Updating MailChimp List Cache");
        $lists = $this->myMailchimp->get("lists")['lists'];
        foreach($lists as &$list) {
          $listmembers = $this->myMailchimp->get('lists/'.$list['id'].'/members',['count' => 100000]);
          $list['members'] = $listmembers['members'];
        }
        $_SESSION['MailChimpLists'] = $lists;
      }
      else{
        LoggerUtils::getAppLogger()->info("Using cached MailChimp List");
      }
      return $_SESSION['MailChimpLists'];
    }
    private function getCampaignsFromCache(){
      if (!isset($_SESSION['MailChimpCampaigns']) ){// the second part can be used to force update
        LoggerUtils::getAppLogger()->info("Updating MailChimp Campaigns Cache");
        $campaigns = $this->myMailchimp->get("campaigns")['campaigns'];
        $_SESSION['MailChimpCampaigns'] = $campaigns;
      }
      else{
        LoggerUtils::getAppLogger()->info("Using cached MailChimp List");
      }
      return $_SESSION['MailChimpCampaigns'];
    }
    public function isEmailInMailChimp($email)
    {
        if (!$this->isActive) {
            return 'Mailchimp is not active';
        }
        
        if ($email == '') {
            return 'No email';
        }
        
        try {
            $lists = $this->getListsFromCache();
            $lists = array_filter($lists, array(new ListEmailFilter($email),'isEmailInList'));
            $listNames = array_map(function ($list) { return $list['name']; }, $lists);
            $listMemberships = implode(',', $listNames);
            LoggerUtils::getAppLogger()->info($email. "is a member of ".$listMemberships);
            return $listMemberships;
        } catch (\Mailchimp_Invalid_ApiKey $e) {
            return 'Invalid ApiKey';
        } catch (\Mailchimp_List_NotSubscribed $e) {
            return '';
        } catch (\Mailchimp_Email_NotExists $e) {
            return '';
        } catch (\Exception $e) {
            return $e;
        }
    }
    public function getLists()
    {
        if (!$this->isActive) {
          return 'Mailchimp is not active';
        }
        try {
            $result = $this->getListsFromCache();
            
            return $result;
        } catch (\Mailchimp_Invalid_ApiKey $e) {
            return 'Invalid ApiKey';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public  function getListFromListId ($list_id) {
      $mcLists = $this->getLists();
      
      $i = 0;
      foreach ($mcLists as $list) {
        if ($list['id'] == $list_id) {
          return $list;
        }
        $i++;
      }
      
      return nil;
    }
    public  function getListMembersFromListId ($list_id) {
      $mcLists = $this->getLists();
      
      $i = 0;
      foreach ($mcLists as $list) {
        if ($list['id'] == $list_id) {
          if (is_null ($list['members'])) {
            // in the case the list is no more in the cache
            $listmembers = $this->myMailchimp->get('lists/'.$list['id'].'/members',['count' => 100000]);
            
            if (count($listmembers[0]) == 0) {
              return [];
            }

            return array_values($listmembers);
          }
          return array_values($list['members']);
        }
        $i++;
      }
      
      
      return $listmembers;
    }
    public function createList ($name, $subject, $PermissionReminder, $ArchiveBars, $Status)
    {
      $name                  = $name; // List Name

      $company               = SystemConfig::getValue('sChurchName');
      $address1              = SystemConfig::getValue('sChurchAddress');
      $address2              = "";
      $city                  = SystemConfig::getValue('sChurchCity');
      $state                 = SystemConfig::getValue('sChurchState');
      $zip                   = SystemConfig::getValue('sChurchZip');
      $country               = SystemConfig::getValue('sChurchCountry');
      $phone                 = SystemConfig::getValue('sChurchPhone');
      $permission_reminder   = $PermissionReminder;
      $archive_bars          = $ArchiveBars; // Whether campaigns for this list use the Archive Bar in archives by default : true false
     
      $marketing_permissions = SystemConfig::getBooleanValue('bGDPR'); //Whether or not the list has marketing permissions (eg. GDPR) enabled.
      
      // contact
      $from_name             = $_SESSION['user']->getPerson()->getFullName();
      $from_email            = ( !empty ( $_SESSION['user']->getPerson()->getEmail() ) )?$_SESSION['user']->getPerson()->getEmail():$_SESSION['user']->getPerson()->getWorkEmail();
      if (empty ($from_email)) {
        $from_email          = SystemConfig::getValue('sChurchEmail');
      }
      $subject               = $subject;
      $language              = substr (SystemConfig::getValue('sLanguage'),0,2);

      $notify_subs           = SystemConfig::getValue('sChurchEmail'); // The email address to send subscribe notifications to.
      $notify_unsubs         = SystemConfig::getValue('sChurchEmail'); // The email address to send subscribe notifications to.
      $type                  = true; //Whether the list supports multiple formats for emails. When set to true, subscribers can choose whether they want to receive HTML or plain-text emails. When set to false, subscribers will receive HTML emails, with a plain-text alternative backup.
      $visibility            = $Status; // Whether this list is public or private : pub or prv


      $data = array( // the information for your new list--not all is required
          "name" => $name,
          "contact" => array (
              "company" => $company,
              "address1" => $address1,
              "address2" => $address2,
              "city" => $city,
              "state" => $state,
              "zip" => $zip,
              "country" => $country,
              "phone" => $phone
          ),
          "marketing_permissions" => $marketing_permissions ,
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
      $result = $this->myMailchimp->post('lists',$data);
      
      
      if ( !array_key_exists ('title',$result) ) {
        // we add the list in the cache
        $_SESSION['MailChimpLists'][] = $result;
      }
      
      return $result;
    }
    private function delete_List ($list_id) {
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
            $this->deleteCampaign ($campaign['id']);
          }
        }
        
        $_SESSION['MailChimpCampaigns'] = $camps;
    }

    public function deleteList ($list_id) {
        $result = $this->myMailchimp->delete("lists/$list_id");
        
        if ( !array_key_exists ('title',$result) ) {
          // we use always the cache to improve the performance
          $this->delete_List($list_id);
        }
        
        return $result;
    }

    public function getCampaigns()
    {
        if (!$this->isActive) {
          return 'Mailchimp is not active';
        }
        try {
            $result = $this->getCampaignsFromCache();
            
            return $result;
        } catch (\Mailchimp_Invalid_ApiKey $e) {
            return 'Invalid ApiKey';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    public function changeLists ($new_list) {
      $lists = $this->getListsFromCache();
      
      $res = [];
      
      foreach ($lists as $list) {
        if ($list['id'] == $new_list['id']) {
          $list['name'] = $new_list['name'];
          $list['campaign_defaults']['subject'] = $new_list['campaign_defaults']['subject'];
        }
        $res[] = $list;
      }
      
      $_SESSION['MailChimpLists'] = $res;
    }
    
    public function changeListName ($list_id,$name,$subject) {
      $new_list = $this->myMailchimp->patch("lists/$list_id",["name" => $name,"campaign_defaults" => array("subject" => $subject)]);
      
      $this->changeLists ($new_list);
      
      return $new_list;
    }
    
    public function getCampaignFromId($campaignId) {
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
    
    public function getCampaignsFromListId($list_id) {
      $campaigns = $this->getCampaigns();
      
      $res = [];
      
      foreach ($campaigns as $campaign) {
        if ($campaign['recipients']['list_id'] == $list_id) {
          $res[] = $campaign;
        }
      }
      
      return $res;
    }
    private function create_Campaign ($list_id,$camp) {      
      // we add the campaign in the cache
      $_SESSION['MailChimpCampaigns'][] = $camp;

      // now we loop on the lists to upgrade de campaign count
      $mcLists      = $_SESSION['MailChimpLists'];

      $i = 0;
      foreach ($mcLists as $list) {
        if ($list['id'] == $list_id) {
          $_SESSION['MailChimpLists'][$i]['stats']['campaign_count']++;
        }
        $i++;
      }
    }
    public function createCampaign ($list_id, $subject, $title, $htmlBody) {
      $from_name             = $_SESSION['user']->getPerson()->getFullName();
      $from_email            = ( !empty ( $_SESSION['user']->getPerson()->getEmail() ) )?$_SESSION['user']->getPerson()->getEmail():$_SESSION['user']->getPerson()->getWorkEmail();
      if (empty ($from_email)) {
        $from_email          = SystemConfig::getValue('sChurchEmail');
      }

      $data = array(
                "recipients" => 
                  array(
                   "list_id" => $list_id
                  ), 
                "type"         => "regular", 
                "settings"     => array(
                  "subject_line" => $subject, 
                  "title"        => $title, 
                  "reply_to"     => $from_email, 
                  "from_name"    => $from_name, 
                //"folder_id"    => "8888969b77"
                )
              );
              
      $result = $this->myMailchimp->post("campaigns", $data);
      
      if ( !array_key_exists ('tilte',$result) ) {

        $campaignID = $result['id'];// we get the campaign ID
      
        $resultContent = $this->setCampaignContent ($campaignID, $htmlBody);
      
        $this->create_Campaign($list_id,$result);
      }
      
      return [$result,$resultContent,"campaigns/$campaignID/content"];
    }
    private function delete_Campaign ($campaignID) {
      $campaigns = $_SESSION['MailChimpCampaigns'];
      
      $res = [];
      
      foreach ($campaigns as $campaign) {
        if ($campaign['id'] != $campaignID) {
          $res[] = $campaign;
        }
      }
        
      $_SESSION['MailChimpCampaigns'] = $res;
    }

    public function deleteCampaign ($campaignID) {
      
      $result = $this->myMailchimp->delete("campaigns/$campaignID");
      
      if ( !array_key_exists ('title',$result) ) {
        $this->delete_Campaign ($campaignID);
      }
      
      return $result;
    }

    public function setCampaignContent ($campaignID,$htmlBody) {
      $resultContent = $this->myMailchimp->put("campaigns/$campaignID/content", ["html" => $htmlBody]);
      
      return $resultContent;
    }
    
    private function set_Campaign_MailSubject ($campaignID,$subject) {
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
    
    public function setCampaignMailSubject ($campaignID,$subject) {
      $data = array(
                "settings"     => array(
                  "subject_line" => $subject)
              );
              
      $result = $this->myMailchimp->patch("campaigns/$campaignID", $data);

      if ( !array_key_exists ('title',$result) ) {
        $this->set_Campaign_MailSubject ($campaignID,$subject);
      }
              
      return $result;
    }

    public function getCampaignContent ($campaignID) {
      $result = $this->myMailchimp->get("campaigns/$campaignID/content");
      
      return $result;
    }
    
    private function send_Campaign ($campaignID) {
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
    
    public function sendCampaign ($campaignID) {
      
      $result = $this->myMailchimp->post("campaigns/$campaignID/actions/send");
      
      if ( !array_key_exists ('title',$result) ) {
        $this->send_Campaign ($campaignID);
      }
      
      return $result;
    }
    private function add_list_member ($list_id,$member) {
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
    public function postMember($list_id,$id,$first_name,$last_name,$mail,$status)
    {
      if ( !empty($mail) ) {
        $result = $this->myMailchimp->post("lists/$list_id/members", [
          'id'            => "$id",
          'email_address' => $mail,
          'status'        => $status,
          'merge_fields' => ['FNAME'=>$first_name, 'LNAME'=>$last_name]
        ]);

        if ( !array_key_exists ('title',$result) ) {
            $this->add_list_member ($list_id,$result);
        }
        
        return $result;
      }
      
      return nil;
    }
    private function delete_list_member ($list_id,$email) {
      $mcLists = $_SESSION['MailChimpLists'];

      $newMembers     = [];
      
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
    
    public function deleteMember($list_id,$email){
        $subscriber_hash = $this->myMailchimp->subscriberHash($email);
        
        $result = $this->myMailchimp->delete("lists/$list_id/members/$subscriber_hash"); 

        if (!array_key_exists ('title',$result) ) {
          $this->delete_list_member ($list_id,$email);
        }
        
        return $result;
    }

    public function deleteAllMembers ($list_id) {
      $members = $this->getListMembersFromListId ($list_id);
      
      $res = [];
      
      foreach ($members as $member) {
        $res[] = $this->deleteMember($list_id,$member['email_address']);
      }
      
      return $res;
    }
    
    private function update_list_member ($list_id,$member,$status) {
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
    public function updateMember($list_id,$first_name,$last_name,$mail,$status) // status : Unsubscribed , Subscribed
    {
        $subscriber_hash = $this->myMailchimp->subscriberHash($mail);

        if (!empty($name) && !empty($last_name)) {
           $result = $this->myMailchimp->patch("lists/$list_id/members/$subscriber_hash", [
                'merge_fields' => ['FNAME'=>$first_name, 'LNAME'=>$last_name],
                'status' => $status,
                  //'interests'    => ['2s3a384h' => true],
           ]);
        } else {
           $result = $this->myMailchimp->patch("lists/$list_id/members/$subscriber_hash", [
                'status' => $status,
                  //'interests'    => ['2s3a384h' => true],
           ]);
        }
         
        if (!array_key_exists ('title',$result) ) {
          $res = $this->update_list_member ($list_id,$mail,$status);
        }
        
        return $result;
    }
}
