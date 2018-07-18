<?php
// Copyright 2018 Philippe Logel all right reserved
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\NoteTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemConfig;

$app->group('/gdrp', function () {

  $this->post('/', function ($request, $response, $args) {    
      if ( !($_SESSION['user']->isGdrpDpoEnabled()) ) {
        return $response->withStatus(401);
      }
      
     $notes = NoteQuery::Create()
            ->filterByPerId(array('min' => 2))
            ->filterByEnteredBy(array('min' => 2))
            ->addJoin(NoteTableMap::COL_NTE_ENTEREDBY,PersonTableMap::COL_PER_ID,Criteria::LEFT_JOIN)
            ->addAsColumn('editedByTitle',PersonTableMap::COL_PER_TITLE)
            ->addAsColumn('editedByLastName',PersonTableMap::COL_PER_LASTNAME)
            ->addAsColumn('editedByMiddleName',PersonTableMap::COL_PER_MIDDLENAME)
            ->addAsColumn('editedByFirstName',PersonTableMap::COL_PER_FIRSTNAME)
            ->find();
            
      $res = [];
      
      foreach ($notes as $note) {
        $person = PersonQuery::Create()->findOneById($note->getEnteredBy());

        $res[] = ['Id' => $note->getId(),
           'personId' => $note->getPerId(),
           'fullNamePerson' => $note->getPerson()->getFullName(),
           'Title' => $note->getTitle(),
           'Text' => $note->getText(),
           'Type' => $note->getType(),
           'DateEntered' => (!empty($note->getDateEntered()))?$note->getDateEntered()->format(SystemConfig::getValue('sDateFormatLong').' H:i'):"",
           'DateLastEdited' => (!empty($note->getDateLastEdited()))?$note->getDateLastEdited()->format(SystemConfig::getValue('sDateFormatLong').' H:i'):"",
           'editedByFullName' => $note->getEditedByLastName()." ".$note->getEditedByFirstName()];
      }
      
      return $response->withJson(["Notes" => $res]);
  });

  $this->post('/checkoutstudent', function ($request, $response, $args) {
    /*if (!($_SESSION['user']->isAdmin() || $_SESSION['user']->isDeleteRecordsEnabled() || $_SESSION['user']->isAddRecordsEnabled())) {
        return $response->withStatus(401);
    }*/

      $cartPayload = (object)$request->getParsedBody();
      
      if ( isset ($cartPayload->personID) && isset ($cartPayload->eventID) && isset($cartPayload->checked) )
      {
        $eventAttent = EventAttendQuery::Create()
            ->filterByEventId($cartPayload->eventID)
            ->filterByPersonId($cartPayload->personID)
            ->findOne();
                    
        $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
        
        if ($eventAttent) {
              $eventAttent->setCheckoutId ($_SESSION['user']->getPersonId());
              if ($cartPayload->checked) {
                $eventAttent->setCheckoutDate($date->format('Y-m-d H:i:s'));
              } else {
                $eventAttent->setCheckoutDate(NULL);
              }
              $eventAttent->save();
        } else {
          try {
              $eventAttent = new EventAttend();        
              $eventAttent->setEventId($event->getID());
              $eventAttent->setCheckinId($_SESSION['user']->getPersonId());
              $eventAttent->setCheckinDate($date->format('Y-m-d H:i:s'));
              $eventAttent->setPersonId($child['kidId']);
              $eventAttent->save();
          } catch (\Exception $ex) {
              $errorMessage = $ex->getMessage();
          }
        }
      }
      else
      {
        throw new \Exception(gettext("POST to cart requires a personID and an eventID"),500);
      }
      $person = PersonQuery::Create()->findOneById($_SESSION['user']->getPersonId());
      
      return $response->withJson(['status' => "success","name" => $person->getFullName(),"date" => OutputUtils::FormatDate($date->format('Y-m-d H:i:s'),1)]);
  });
});
