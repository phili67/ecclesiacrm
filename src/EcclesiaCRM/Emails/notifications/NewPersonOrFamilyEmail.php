<?php

namespace EcclesiaCRM\Emails;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\dto\SystemURLs;

class NewPersonOrFamilyEmail extends BaseEmail
{
    private $relatedObject;
    
    public function __construct($RelatedObject)
    {
      $this->relatedObject = $RelatedObject;

      $toAddresses = [];
      $recipientPeople = explode(",",SystemConfig::getValue("sNewPersonNotificationRecipientIDs") );

      foreach($recipientPeople as $PersonID) {
        $Person = PersonQuery::create()->findOneById($PersonID);
        if(!empty($Person)) {
          $email = $Person->getEmail();
          if (!empty($email)) {
            array_push($toAddresses,$email);   
          }
        }
      }

      parent::__construct($toAddresses);
      $this->mail->Subject = SystemConfig::getValue("sEntityName") . ": " . $this->getSubSubject();
      $this->mail->isHTML(true);
      $this->mail->msgHTML($this->buildMessage());
    }

    protected function getSubSubject()
    {
      if (get_class($this->relatedObject) == "EcclesiaCRM\Person")
      {
        return gettext("New Person Added");
      }
      else if (get_class($this->relatedObject) == "EcclesiaCRM\Family")
      {
        return gettext("New Family Added");
      }
        
    }
   
     public function getTokens()
    {
        $myTokens =  [
            "toName" => gettext("Church Greeter")
        ];
        if (get_class($this->relatedObject) == "EcclesiaCRM\Family")
        {
          $myTokens['body'] = gettext("New Family Added")."\r\n".
                  gettext("Family Name").": ".$this->relatedObject->getName();
          $myTokens["familyLink"] = SystemURLs::getURL()."/v2/people/family/view/".$this->relatedObject->getId();
        }
        elseif (get_class($this->relatedObject) == "EcclesiaCRM\Person")
        {
          $myTokens['body'] = gettext("New Person Added")."\r\n". gettext("Name").": ". $this->relatedObject->getFullName();
          $myTokens['personLink'] = SystemURLs::getURL()."/v2/people/person/view/".$this->relatedObject->getId();
        }
        
        return array_merge($this->getCommonTokens(), $myTokens);
    }
}
