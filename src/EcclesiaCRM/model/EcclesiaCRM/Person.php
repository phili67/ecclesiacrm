<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Person as BasePerson;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Base\FamilyQuery as BaseFamilyQuery;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\Photo;
use Propel\Runtime\Connection\ConnectionInterface;
use EcclesiaCRM\Service\GroupService;
use EcclesiaCRM\Emails\NewPersonOrFamilyEmail;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\ListOptionIconQuery;
use EcclesiaCRM\Utils\GeoUtils;

use DateTime;

/**
 * Skeleton subclass for representing a row from the 'person_per' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Person extends BasePerson implements iPhoto
{

    const SELF_REGISTER = -1;
    const SELF_VERIFY = -2;
    private $photo;
    
    public function preDelete(ConnectionInterface $con = null)
    {
      $this->deletePhoto();

      $obj = Person2group2roleP2g2rQuery::create()->filterByPerson($this)->find($con);
      if (!empty($obj)) {
          $groupService = new GroupService();
          foreach ($obj as $group2roleP2g2r) {
              $groupService->removeUserFromGroup($group2roleP2g2r->getGroupId(), $group2roleP2g2r->getPersonId());
          }
      }

      $perCustom = PersonCustomQuery::create()->findPk($this->getId(), $con);
      if (!is_null($perCustom)) {
          $perCustom->delete($con);
      }

      $user = UserQuery::create()->findPk($this->getId(), $con);
      if (!empty($user)) {
          $user->delete($con);
      }

      PersonVolunteerOpportunityQuery::create()->filterByPersonId($this->getId())->find($con)->delete();

      Record2propertyR2pQuery::create()->findByR2pRecordId($this->getId())->delete();

      return parent::preDelete($con);
    }
    
    public function postDelete(ConnectionInterface $con = null)
    {
      $family = null;
      $ret = null;
      
      if ($this->getFamId() > 0) {// a one person family is deleted with the family too : assume family is only the address
        $family = \EcclesiaCRM\FamilyQuery::Create()->filterById($this->getFamId())->findOne();
      }

      if (is_callable('parent::postDelete')) {
          $ret  = parent::postDelete($con);
                       
          $pledges = \EcclesiaCRM\PledgeQuery::Create()->filterByFamId($this->getFamId())->find($con);

          if ( !empty($family) && $family->getPeople()->count() == 0 && $pledges->count() == 0 ) {
              // a one person family is deleted with the family too : assume family is only the address
              // Attention : a family can contain payments, so the payments are deleted with the data constraints
              $family->delete($con);
          }
      }

      return $ret;
    }

    
    public function isDeactivated()
    {
      if ($this->getDateDeactivated() != '' || $this->getFamily()->getDateDeactivated() != '') {
        return true;
      }
      
      return false;
    }

    public function getFullName()
    {
        return $this->getFormattedName(SystemConfig::getValue('iPersonNameStyle'));
    }

    public function getUrlIcon()
    {
      $icon = ListOptionIconQuery::Create()->filterByListId(1)->findOneByListOptionId($this->GetClsId());
      
      if (!empty($icon)) {
        $lst = ListOptionIconQuery::Create()->filterByListId(1)->findOneByListOptionId($this->GetClsId());
        if (!empty($lst)) {
          return $icon->getUrl();
        } else {
          return '../interrogation_point.png';
        }
      } 
      
      return 'gm-red-pushpin.png';
    }
    
    public function getOnlyVisiblePersonView()
    {
      $icon = ListOptionIconQuery::Create()->filterByListId(1)->findOneByListOptionId($this->GetClsId());
      
      if (!empty($icon)) {
        return $icon->getOnlyVisiblePersonView();
      }
      
      return false;
    }

    
    public function isMale()
    {
        return $this->getGender() == 1;
    }

    public function isFemale()
    {
        return $this->getGender() == 2;
    }

    public function hideAge()
    {
        return $this->getFlags() == 1 || $this->getBirthYear() == '' || $this->getBirthYear() == '0';
    }

    public function getBirthDate()
    {
        if (!is_null($this->getBirthDay()) && $this->getBirthDay() != '' &&
            !is_null($this->getBirthMonth()) && $this->getBirthMonth() != ''
        ) {
            $birthYear = $this->getBirthYear();
            if ($this->hideAge()) {
                $birthYear = 1900;
            }

            return date_create($birthYear . '-' . $this->getBirthMonth() . '-' . $this->getBirthDay());
        }

        return date_create();
    }

    public function getViewURI()
    {
        return SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . $this->getId();
    }

    public function getFamilyRole()
    {
        $familyRole = null;
        $roleId = $this->getFmrId();
        if (isset($roleId) && $roleId !== 0) {
            $familyRole = ListOptionQuery::create()->filterById(2)->filterByOptionId($roleId)->findOne();
        }

        return $familyRole;
    }

    public function getFamilyRoleName()
    {
        $roleName = '';
        $role = $this->getFamilyRole();
        if (!is_null($role)) {
            $roleName = $this->getFamilyRole()->getOptionName();
        }

        return $roleName;
    }
    
    public function getClassification()
    {
      $classification = null;
      $clsId = $this->getClsId();
      if (!empty($clsId)) {
        $classification = ListOptionQuery::create()->filterById(1)->filterByOptionId($clsId)->findOne();
      }
      return $classification;
    }
    
    public function getClassificationName()
    {
      $classificationName = '';
      $classification = $this->getClassification();
      if (!is_null($classification)) {
        $classificationName = $classification->getOptionName();
      }
      return $classificationName;
    }

    public function postInsert(ConnectionInterface $con = null)
    {
      $this->createTimeLineNote('create');
      if (!empty(SystemConfig::getValue("sNewPersonNotificationRecipientIDs")))
      {
        $NotificationEmail = new NewPersonOrFamilyEmail($this);
        if (!$NotificationEmail->send()) {
          $logger->warn($NotificationEmail->getError());
        }
      }
    }

    public function postUpdate(ConnectionInterface $con = null)
    {
      if (!empty($this->getDateLastEdited())) {
        $this->createTimeLineNote('edit');
      }
    }

    private function createTimeLineNote($type)
    {
        $note = new Note();
        $note->setPerId($this->getId());
        $note->setType($type);
        $note->setDateEntered(new DateTime());

         switch ($type) {
            case "create":
              $note->setText(gettext('Created'));
              $note->setEnteredBy($this->getEnteredBy());
              $note->setDateEntered($this->getDateEntered());
              break;
            case "edit":
              $note->setText(gettext('Updated'));
              $note->setEnteredBy($this->getEditedBy());
              $note->setDateEntered($this->getDateLastEdited());
              break;
        }

        $note->save();
    }

    public function isUser()
    {
        $user = UserQuery::create()->findPk($this->getId());

        return !is_null($user);
    }

    public function getOtherFamilyMembers()
    {
        $otherFamilyMembers = [];

        if ($this->getFamily() != null) {
          $familyMembers = $this->getFamily()->getActivatedPeople();
          foreach ($familyMembers as $member) {
              if ($member->getId() != $this->getId()) {
                  array_push($otherFamilyMembers, $member);
              }
          }
        }

        return $otherFamilyMembers;
    }

    /**
     * Get address of  a person. If empty, return family address.
     * @return string
     */
    public function getAddress()
    {
        if (!empty($this->getAddress1()) && SystemConfig::getBooleanValue("bHidePersonAddress") == false) {
            $address = [];
            $tmp = $this->getAddress1();
            if (!empty($this->getAddress2())) {
                $tmp = $tmp . ' ' . $this->getAddress2();
            }
            array_push($address, $tmp);
            if (!empty($this->getCity())) {
                array_push($address, $this->getCity() . ',');
            }
            if (!empty($this->getState())) {
                array_push($address, $this->getState());
            }
            if (!empty($this->getZip())) {
                array_push($address, $this->getZip());
            }
            if (!empty($this->getCountry())) {
                array_push($address, $this->getCountry());
            }
            return implode(' ', $address);
        } else {
            if ($this->getFamily()) {
                return $this->getFamily()
                    ->getAddress();
            }
        }
        //if it reaches here, no address found. return empty $address
        return "";
    }

    /**
     * * If person address found, return latitude and Longitude of person address
     * else return family latitude and Longitude
     * @return array
     */
    public function getLatLng()
    {
        $address = $this->getAddress(); //if person address empty, this will get Family address
        $lat = 0;
        $lng = 0;
        if (!empty($this->getAddress1())) {
            $latLng = GeoUtils::getLatLong($this->getAddress());
            if (!empty($latLng['Latitude']) && !empty($latLng['Longitude'])) {
                $lat = $latLng['Latitude'];
                $lng = $latLng['Longitude'];
            }
        } else {
         // Philippe Logel : this is usefull when a person don't have a family : ie not an address
         if (!empty($this->getFamily()))
         {
    if (!$this->getFamily()->hasLatitudeAndLongitude()) {
     $this->getFamily()->updateLanLng();
    }
    $lat = $this->getFamily()->getLatitude();
    $lng = $this->getFamily()->getLongitude();
   }
        }
        return array(
            'Latitude' => $lat,
            'Longitude' => $lng
        );
    }

    public function deletePhoto()
    {
        if ($_SESSION['user']->isAddRecordsEnabled() || $bOkToEdit) {
            if ($this->getPhoto()->delete()) {
                $note = new Note();
                $note->setText(gettext("Profile Image Deleted"));
                $note->setType("photo");
                $note->setEntered($_SESSION['user']->getPersonId());
                $note->setPerId($this->getId());
                $note->save();
                return true;
            }
        }
        return false;
    }

    public function getPhoto()
    {
      if (!$this->photo) 
      {
        $this->photo = new Photo("Person",  $this->getId());
      }
      return $this->photo;
    }

    public function setImageFromBase64($base64)
    {
        if ($_SESSION['user']->isAddRecordsEnabled() || $bOkToEdit) {
            $note = new Note();
            $note->setText(gettext("Profile Image uploaded"));
            $note->setType("photo");
            $note->setEntered($_SESSION['user']->getPersonId());
            $this->getPhoto()->setImageFromBase64($base64);
            $note->setPerId($this->getId());
            $note->save();
            return true;
        }
        return false;

    }

    /**
     * Returns a string of a person's full name, formatted as specified by $Style
     * $Style = 0  :  "Title FirstName MiddleName LastName, Suffix"
     * $Style = 1  :  "Title FirstName MiddleInitial. LastName, Suffix"
     * $Style = 2  :  "LastName, Title FirstName MiddleName, Suffix"
     * $Style = 3  :  "LastName, Title FirstName MiddleInitial., Suffix"
     * $Style = 4  :  "FirstName MiddleName LastName"
     * $Style = 5  :  "Title FirstName LastName"
     * $Style = 6  :  "LastName, Title FirstName"
     *
     * @param $Style
     * @return string
     */
    public function getFormattedName($Style)
    {
        $nameString = '';
        switch ($Style) {
            case 0:
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . $this->getMiddleName();
                }
                if ($this->getLastName()) {
                    $nameString .= ' ' . $this->getLastName();
                }
                if ($this->getSuffix()) {
                    $nameString .= ', ' . $this->getSuffix();
                }
                break;

            case 1:
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . strtoupper(mb_substr($this->getMiddleName(), 0, 1, 'UTF-8')) . '.';
                }
                if ($this->getLastName()) {
                    $nameString .= ' ' . $this->getLastName();
                }
                if ($this->getSuffix()) {
                    $nameString .= ', ' . $this->getSuffix();
                }
                break;

            case 2:
                if ($this->getLastName()) {
                    $nameString .= $this->getLastName() . ', ';
                }
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . $this->getMiddleName();
                }
                if ($this->getSuffix()) {
                    $nameString .= ', ' . $this->getSuffix();
                }
                break;

            case 3:
                if ($this->getLastName()) {
                    $nameString .= $this->getLastName() . ', ';
                }
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . strtoupper(mb_substr($this->getMiddleName(), 0, 1, 'UTF-8')) . '.';
                }
                if ($this->getSuffix()) {
                    $nameString .= ', ' . $this->getSuffix();
                }
                break;

            case 4:
                $nameString .= $this->getFirstName();
                if ($this->getMiddleName()) {
                    $nameString .= ' ' . $this->getMiddleName();
                }
                if ($this->getLastName()) {
                    $nameString .= ' ' . $this->getLastName();
                }
                break;

            case 5:

                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                if ($this->getLastName()) {
                    $nameString .= ' ' . $this->getLastName();
                }
                break;
            case 6:
                if ($this->getLastName()) {
                    $nameString .= $this->getLastName() . ', ';
                }
                if ($this->getTitle()) {
                    $nameString .= $this->getTitle() . ' ';
                }
                $nameString .= $this->getFirstName();
                break;
            default:
                $nameString = $this->getFullName();

        }
        return $nameString;
    }
    
    public function getNumericCellPhone()
    {
      return "1".preg_replace('/[^\.0-9]/',"",$this->getCellPhone());
    }
    
    public function postSave(ConnectionInterface $con = null) {
      $this->getPhoto()->refresh();
      return parent::postSave($con);
    }
    
    
    /* Philippe Logel 2017 */
    public function getAge($with_suffix=true)
    {
       $birthD = $this->getBirthDate();
   
       if ($this->hideAge() == 1) 
       {
            return '';
       }

       $ageSuffix = gettext('Unknown');

       $now = date_create('today');
       $age = date_diff($now,$birthD);

       if ($age->y < 1) {
         if ($age->m > 1) {
           $ageSuffix = gettext('mos old');
         } else {
           $ageSuffix = gettext('mo old');
         }
       } else {
         if ($age->y > 1) {
           $ageSuffix = gettext('yrs old');
         } else {
           $ageSuffix = gettext('yr old');
         }
       }
       
       if ($with_suffix == true) {
         return $age->y." ".$ageSuffix;
       } else {
         return $age->y;
       }
    }
    
    /* Philippe Logel 2017 */
    public function getFullNameWithAge()
    {
       return $this->getFullName()." ".$this->getAge();
    }

}
