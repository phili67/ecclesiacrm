<?php
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
    public  function getListMembersFromListId ($list_id) {
      $mcLists = $this->getLists();
      
      $i = 0;
      foreach ($mcLists as $list) {
        if ($list['id'] == $list_id) {
          if (is_null ($list['members'])) {
            return [];
          }
          return array_values($list['members']);
        }
        $i++;
      }
      
      return [];
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
    public function createList ()
    {
      $name                  = "Freddie's Favorite Hats";

      $company               = "Epis Strasbourg";
      $address1              = "7 rue des Frères Eberts";
      $address2              = "";
      $city                  = "Strasbourg";
      $state                 = "";
      $zip                   = "67100";
      $country               = "France";
      $phone                 = "03 88 62 64 75";
      $permission_reminder   = "You're receiving this email because you signed up for updates about Freddie's newest hats.";
      $archive_bars          = false; // Whether campaigns for this list use the Archive Bar in archives by default.
     
      $marketing_permissions = true; //Whether or not the list has marketing permissions (eg. GDPR) enabled.

      $from_name             = "Michel Schneider";
      $from_email            = "michel.schneider@epis-strasbourg.eu";
      $subject               = "Ma nouvelle campagne de tartampion";
      $language              = "fr";

      $notify_subs           = "erp@epis-strasbourg.eu"; // The email address to send subscribe notifications to.
      $notify_unsubs         = "erp@epis-strasbourg.eu"; // The email address to send subscribe notifications to.
      $type                  = true; //Whether the list supports multiple formats for emails. When set to true, subscribers can choose whether they want to receive HTML or plain-text emails. When set to false, subscribers will receive HTML emails, with a plain-text alternative backup.
      $visibility            = "prv"; // Whether this list is public or private : pub or prv


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
      
      // we add the list in the cache
      $_SESSION['MailChimpLists'][] = $result;
      
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
    }
    public function deleteList ($list_id) {
        $result = $this->myMailchimp->delete("lists/$list_id");
        
        // we use always the cache to improve the performance
        $this->delete_List($list_id);
        
        return $result;
    }
    private function create_Campaign ($list_id,$camp) {
      $allCampaigns = $this->getCampaigns();
      $mcLists      = $_SESSION['MailChimpLists'];
      
      $allCampaigns[] = $camp;
      
  
      $i = 0;
      foreach ($mcLists as $list) {
        if ($list['id'] == $list_id) {
          $_SESSION['MailChimpLists'][$i]['stats']['campaign_count']++;
        }
        $i++;
      }
    }
    public function createCampaign ($list_id) {
      $data = array("recipients" => 
                array(
                   "list_id" => $list_id
                ), 
                "type" => "regular", 
                "settings" => array(
                "subject_line" => "Subject", 
                "title" => "Essai 456", 
                "reply_to" => "test@gmail.com", 
                "from_name" => "Test", 
                //"folder_id" => "8888969b77"
                )
              );
              
      $result = $this->myMailchimp->post("campaigns", $data);
      
      $campaignID = $result['id'];// we get the campaign ID
      
      $resultContent = $this->myMailchimp->put("campaigns/$campaignID/content", ["html" => "<p>The HTML to use for the saved campaign</p>"]);
      
      $this->create_Campaign($list_id,$result);
      
      return [$result,$resultContent,"campaigns/$campaignID/content"];
    }
    private function delete_Campaign ($campaignID) {
      // todo : in all list delete the campaigns
    }
    public function deleteCampaign ($campaignID) {
      
      $result = $this->myMailchimp->delete("campaigns/$campaignID"); 
      
      return $result;
    }
    public function sendCampaign ($campaignID) {
      
      $result = $this->myMailchimp->post("campaigns/$campaignID/actions/send"); 
      
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

        if (!isset($result['title'])) {
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
              $_SESSION['MailChimpLists'][$i]['stats']['member_count']--;
            }
          }
          break;
        }
        $i++;
      }
      
      $_SESSION['MailChimpLists'][$i]['members'] = array_values($newMembers);
    }
    public function deleteUser($list_id,$email){
        $subscriber_hash = $this->myMailchimp->subscriberHash($email);
        
        $result = $this->myMailchimp->delete("lists/$list_id/members/$subscriber_hash"); 

        if (!isset($result['title'])) {
          $this->delete_list_member ($list_id,$email);
        }
        
        return $result;
    }
    private function update_list_member ($list_id,$member,$status) {
      $mcLists = $_SESSION['MailChimpLists'];
      
      $i = 0;
      foreach ($mcLists as $list) {
        if ($list['id'] == $list_id) {
          $j = 0;
          foreach ($_SESSION['MailChimpLists'][$i]['members'] as $memb) {
            if ($memb['email_address'] == $member) {
              $_SESSION['MailChimpLists'][$i]['members'][$j]['status'] = $status;
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
         
        if (!isset($result['title'])) {
          $res = $this->update_list_member ($list_id,$mail,$status);
        }
        
        return $result;
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
}