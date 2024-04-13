<?php

namespace EcclesiaCRM;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Base\Family as BaseFamily;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\Connection\ConnectionInterface;
use EcclesiaCRM\dto\Photo;
use EcclesiaCRM\Utils\GeoUtils;
use DateTime;
use EcclesiaCRM\Emails\NewPersonOrFamilyEmail;

use Propel\Runtime\Map\TableMap;

/**
 * Skeleton subclass for representing a row from the 'family_fam' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Family extends BaseFamily implements iPhoto
{
    private $photo;


    public function preDelete(ConnectionInterface $con = NULL): bool
    {
      $token = TokenQuery::create()->findByReferenceId($this->getId());
      if ( !is_null($token)) {
            $token->delete();
        }
      $persons = PersonQuery::Create()->findByFamId($this->getId());

      if ($persons->count() > 0) {
        $persons->delete();
      }

      return parent::preDelete($con);
    }

    public function getAddress($all=true)
    {
        $address = [];
        if (!empty($this->getAddress1())) {
            $tmp = $this->getAddress1();
            array_push($address, $tmp);
        }

        if (!empty($this->getCity())) {
            array_push($address, $this->getCity().',');
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

        if (!empty($this->getAddress2()) and !$all) {
            array_push($address, '<br>'.$this->getAddress2());
        }

        return implode(' ', $address);
    }

    public function getTinyAddress()
    {
        $adressStyleNotUS = SystemConfig::getValue('iPersonAddressStyle');

        $address = [];
        if (!empty($this->getAddress1())) {
            $tmp = $this->getAddress1();
            if (!empty($this->getAddress2())) {
                $tmp = $tmp.' '.$this->getAddress2();
            }
            array_push($address, $tmp);
        }

        if (!empty($this->getZip()) && $adressStyleNotUS == 1) {
            array_push($address, $this->getZip());
        }

        if (!empty($this->getCity())) {
            array_push($address, $this->getCity());
        }

        if (!empty($this->getState())) {
            array_push($address, $this->getState());
        }

        if (!empty($this->getZip()) && $adressStyleNotUS == 0) {
            array_push($address, ",   ".$this->getZip());
        }

        return implode(' ', $address);
    }

    public function getViewURI()
    {
        return SystemURLs::getRootPath().'/v2/people/family/view/'.$this->getId();
    }

    public function getWeddingDay()
    {
        if (!is_null($this->getWeddingdate()) && $this->getWeddingdate() != '') {
            $day = $this->getWeddingdate()->format('d');

            return $day;
        }

        return '';
    }

    public function getWeddingMonth()
    {
        if (!is_null($this->getWeddingdate()) && $this->getWeddingdate() != '') {
            $month = $this->getWeddingdate()->format('m');

            return $month;
        }

        return '';
    }

    public function postInsert(ConnectionInterface $con = null): void
    {
        $this->createTimeLineNote('create');
        if (!empty(SystemConfig::getValue("sNewPersonNotificationRecipientIDs")))
        {
          $NotificationEmail = new NewPersonOrFamilyEmail($this);
          if (!$NotificationEmail->send()) {
            LoggerUtils::getAppLogger()->warn($NotificationEmail->getError());
          }
        }
    }

    public function postUpdate(ConnectionInterface $con = null): void
    {
        if (!empty($this->getDateLastEdited())) {
            $this->createTimeLineNote('edit');
        }
    }


  public function getPeopleSorted() {
    $familyMembersParents = array_merge($this->getHeadPeople(), $this->getSpousePeople());
    $familyMembersChildren = $this->getChildPeople();
    $familyMembersOther = $this->getOtherPeople();
    return array_merge($familyMembersParents, $familyMembersChildren, $familyMembersOther);
  }

  public function getHeadPeople() {
    return $this->getPeopleByRole("sDirRoleHead");
  }

  public function getSpousePeople() {
    return $this->getPeopleByRole("sDirRoleSpouse");
  }

  public function getAdults() {
    return array_merge($this->getHeadPeople(),$this->getSpousePeople());
  }

  public function getChildPeople() {
    return $this->getPeopleByRole("sDirRoleChild");
  }

  public function getOtherPeople() {
    $roleIds = array_merge (explode(",", SystemConfig::getValue("sDirRoleHead")), explode(",",
      SystemConfig::getValue("sDirRoleSpouse")),
      explode(",", SystemConfig::getValue("sDirRoleChild")));
    $foundPeople = array();
    foreach ($this->getPeople() as $person) {
      if (!in_array($person->getFmrId(), $roleIds) && is_null($person->getDateDeactivated())) {
        array_push($foundPeople, $person);
      }
    }
    return $foundPeople;
  }

  private function getPeopleByRole($roleConfigName) {
    $roleIds = explode(",", SystemConfig::getValue($roleConfigName));
    $foundPeople = array();
    foreach ($this->getPeople() as $person) {
      if (in_array($person->getFmrId(), $roleIds) && is_null($person->getDateDeactivated())) {
          array_push($foundPeople, $person);
      }
    }
    return $foundPeople;
  }

  public function containsMember($person_id) {
    foreach ($this->getPeople() as $person) {
      if ($person->getId() == $person_id) {
          return true;
      }
    }
    return false;
  }

  public function getEmails() {
    $emails = array();
    foreach ($this->getPeople() as $person) {
      if (!is_null($person->getDateDeactivated())) {
        continue;
      }
      $email = $person->getEmail();
      if ($email != null) {
        array_push($emails, $email);
      }
      $email = $person->getWorkEmail();
      if ($email != null) {
        array_push($emails, $email);
      }
    }
    return $emails;
  }

    public function createTimeLineNote($type)
    {
        $note = new Note();
        $note->setFamId($this->getId());
        $note->setType($type);
        $note->setDateEntered(new DateTime());

        switch ($type) {
            case "create":
              $note->setText(_('Created'));
              $note->setEnteredBy(SessionUser::getId());
              $note->setDateEntered($this->getDateEntered());
              break;
            case "edit":
              $note->setText(_('Updated'));
                $note->setEnteredBy(SessionUser::getId());
                $note->setDateEntered($this->getDateLastEdited());
                break;
            case "verify":
                $note->setText(_('Family Data Verified'));
                $note->setEnteredBy(SessionUser::getId());
                break;
            case "verify-URL":
              $note->setText(_('Family Data Verified by url'));
              $note->setEnteredBy(SessionUser::getId());
              break;
            case "verify-URL-reset":
              $note->setText(_('Family Data Verification reset'));
              $note->setEnteredBy(SessionUser::getId());
              break;              
            case "verify-link":
              $note->setText(_('Verification email sent'));
              $note->setEnteredBy(SessionUser::getId());
              break;
        }

        $note->save();
    }

    public function getActivatedPeople ()
    {
      $people = $this->getPeople();

      $foundPeople = [];

      foreach ($this->getPeople() as $person) {
        if (is_null($person->getDateDeactivated()) /*|| SessionUser::getUser()->isGdrpDpoEnabled()*/) {
          array_push($foundPeople, $person);
        }
      }

      return $foundPeople;
    }

    /**
     * Figure out how to address a family for correspondence.
     *
     * Put the name if there is only one individual in the family.
     * Put two first names and the last name when there are exactly two people in the family
     * (e.g. "Nathaniel and Jeanette Brooks").
     * Put two whole names where there are exactly two people with different names
     * (e.g. "Doug Philbrook and Karen Andrews")
     * When there are more than two people in the family I don't have any way to know
     * which people are children, so I would have to just use the family name (e.g. "Grossman Family").
     *
     * @return string
     */
    public function getSaluation()
    {
        $childRoleId = SystemConfig::getValue("sDirRoleChild");
        $people = $this->getPeople();
        $notChildren = null;
        foreach ($people as $person) {
            if ($person->getFmrId() != $childRoleId && is_null($person->getDateDeactivated())) {
                $notChildren[] = $person;
            }
        }

        $notChildrenCount = count($notChildren);
        if ($notChildrenCount === 1) {
            return $notChildren[0]->getFullName();
        }

        if ($notChildrenCount === 2) {
            if ($notChildren[0]->getLastName() == $notChildren[1]->getLastName()) {
                return $notChildren[0]->getFirstName() .' & '. $notChildren[1]->getFirstName() .' '. $notChildren[0]->getLastName();
            }
            return $notChildren[0]->getFullName() .' & '. $notChildren[1]->getFullName();
        }

        return $this->getName() . ' Family';
    }

    public function getPhoto()
    {
      if (!$this->photo)
      {
        $this->photo = new Photo("Family",  $this->getId());
      }
      return $this->photo;
    }

    public function getPNGPhotoDatas($width = '10px', $heigth = '10px'): string
    {
      if (isset($_SESSION['photos']['families'][$this->getId()])) {
        return $_SESSION['photos']['families'][$this->getId()];
      }

      // usefull for base 64
      $photo = $this->getPhoto();
      $datas = base64_encode($photo->getPhotoBytes());     

      $_SESSION['photos']['families'][$this->getId()] = '<img src="data:image/png;base64, ' . $datas . '" class="initials-image direct-chat-img " width="' . $width . '" height="' . $heigth . '" />';

      return $_SESSION['photos']['families'][$this->getId()];      
    }

    public function deletePhoto()
    {
      if (SessionUser::getUser()->isAddRecordsEnabled() || SessionUser::getUser()->getPerson()->getFamily()->getId() == $this->getId() ) {
        if ( $this->getPhoto()->delete() )
        {
          $note = new Note();
          $note->setText(_("Profile Image Deleted"));
          $note->setType("photo");
          $note->setEntered(SessionUser::getUser()->getPersonId());
          $note->setPerId($this->getId());
          $note->save();
          return true;
        }
      }
      return false;
    }
    public function setImageFromBase64($base64) {
      if (SessionUser::getUser()->isAddRecordsEnabled() || SessionUser::getUser()->getPerson()->getFamily()->getId() == $this->getId() ) {
        $note = new Note();
        $note->setText(_("Profile Image uploaded"));
        $note->setType("photo");
        $note->setEntered(SessionUser::getUser()->getPersonId());
        $this->getPhoto()->setImageFromBase64($base64);
        $note->setFamId($this->getId());
        $note->save();
        return true;
      }
      return false;
    }

    public function verify()
    {
        $this->createTimeLineNote('verify');
    }

    public function getFamilyString($booleanIncludeHOH=true, $withAddress = true)
    {
      $HoH = [];
      if ($booleanIncludeHOH) {
        $HoH = $this->getHeadPeople();
      }
      if (count($HoH) == 1)
      {
         return $this->getName(). ": " . $HoH[0]->getFirstName() . (($withAddress)?" - " . $this->getAddress():"");
      }
      elseif (count($HoH) > 1)
      {
        $HoHs = [];
        foreach ($HoH as $person) {
          array_push($HoHs, $person->getFirstName());
        }

        return $this->getName(). ": " . join(",", $HoHs) . " - " . $this->getAddress();
      }
      else
      {
        return $this->getName(). " " . $this->getAddress();
      }
    }

    public function hasLatitudeAndLongitude() {
        return !empty($this->getLatitude()) && !empty($this->getLongitude());
    }

    /**
     * if the latitude or longitude is empty find the lat/lng from the address and update the lat lng for the family.
     * @return array of Lat/Lng
     */
    public function updateLanLng() {
        if ( !empty($this->getAddress(false)) /*&& (!$this->hasLatitudeAndLongitude())*/ ) {
            $latLng = GeoUtils::getLatLong($this->getAddress());
            if(!empty( $latLng['Latitude']) && !empty($latLng['Longitude'])) {
                $this->setLatitude($latLng['Latitude']);
                $this->setLongitude($latLng['Longitude']);
                $this->save();
            }
        }
    }

    public function toArray(string $keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false): array
    {
      $array = (array)parent::toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, $includeForeignObjects);
      $array['FamilyString']=$this->getFamilyString();
      return $array;
    }

    public function toSearchArray()
    {
      $searchArray=[
          "Id" => $this->getId(),
          "displayName" => $this->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
          "uri" => SystemURLs::getRootPath() . '/v2/people/family/view/' . $this->getId()
      ];
      return $searchArray;
    }

    public function getVCard()
    {
        $persons = $this->getPeople();

        $output = '';

        foreach ($persons as $person) {
            $output .= $person->getVCard();
        }

        return $output;
    }
}
