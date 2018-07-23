<?php
// Copyright 2018 Philippe Logel all right reserved
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\FamilyQuery;
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
            ->addAsColumn('Deactivated',PersonTableMap::COL_PER_DATEDEACTIVATED)
            ->find();
      
      $res = [];
      
      foreach ($notes as $note) {
        $person = PersonQuery::Create()->findOneById($note->getPerId());

        $res[] = ['Id' => $note->getId(),
           'personId' => $note->getPerId(),
           'fullNamePerson' => $note->getPerson()->getFullName(),
           'Title' => $note->getTitle(),
           'Text' => $note->getText(),
           'Type' => $note->getType(),
           'DateEntered' => (!empty($note->getDateEntered()))?$note->getDateEntered()->format(SystemConfig::getValue('sDateFormatLong').' H:i'):"",
           'DateLastEdited' => (!empty($note->getDateLastEdited()))?$note->getDateLastEdited()->format(SystemConfig::getValue('sDateFormatLong').' H:i'):"",
           'editedByFullName' => $note->getEditedByLastName()." ".$note->getEditedByFirstName(),
           'Deactivated' => ($person !=null && $person->getDateDeactivated() != null)?'<div style="color:green;text-align:center">'.gettext("Yes").'</div>':'<div style="color:red;text-align:center">'.gettext("No")."</div>"];
      }
      
      return $response->withJson(["Notes" => $res]);
  });

  $this->post('/removeperson', function ($request, $response, $args) {
      if ( !($_SESSION['user']->isGdrpDpoEnabled()) ) {
        return $response->withStatus(401);
      }

      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->personId) )
      {
         $person = PersonQuery::Create()->findOneById($input->personId);
         
         if ($person != null) {
           $person->delete();
         }
         
         return $response->withJson(['status' => "success"]);
      }
      
      return $response->withJson(['status' => "failed"]);
  });
  
  $this->post('/removeallpersons', function ($request, $response, $args) {
      if ( !($_SESSION['user']->isGdrpDpoEnabled()) ) {
        return $response->withStatus(401);
      }
  
      $time = new DateTime('now');
      $newtime = $time->modify('-1 year')->format('Y-m-d');

      $persons = PersonQuery::create()
        ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
            ->find();
         
      if ($persons != null) {
         foreach ($persons as $person) {
           $person->delete();
         }
      }
      
      return $response->withJson(['status' => "success"]);
  });
  
  $this->post('/removefamily', function ($request, $response, $args) {
      if ( !($_SESSION['user']->isGdrpDpoEnabled()) ) {
        return $response->withStatus(401);
      }

      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->familyId) )
      {
         $family = FamilyQuery::Create()->findOneById($input->familyId);
         
         if ($family != null) {
           $family->delete();
         }
         
         return $response->withJson(['status' => "success"]);
      }
      
      return $response->withJson(['status' => "failed"]);
  });
  
  $this->post('/removeallfamilies', function ($request, $response, $args) {
      if ( !($_SESSION['user']->isGdrpDpoEnabled()) ) {
        return $response->withStatus(401);
      }
  
      $time = new DateTime('now');
      $newtime = $time->modify('-1 year')->format('Y-m-d');

      $families = FamilyQuery::create()
        ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
            ->find();
         
      if ($families != null) {
         foreach ($families as $family) {
           $family->delete();
         }
      }
      
      return $response->withJson(['status' => "success"]);
  });
  
  
});

