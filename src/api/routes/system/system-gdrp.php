<?php
// Copyright 2018 Philippe Logel all right reserved
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\NoteTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\GdprInfoQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\SessionUser;


$app->group('/gdrp', function (RouteCollectorProxy $group) {

    $group->post('/', 'getAllGdprNotes' );
    $group->post('/setComment', 'setGdprComment' );
    $group->post('/removeperson', 'removePersonGdpr' );
    $group->post('/removeallpersons', 'removeAllPersonsGdpr' );
    $group->post('/removefamily', 'removeFamilyGdpr' );
    $group->post('/removeallfamilies', 'removeAllFamiliesGdpr' );

});

function getAllGdprNotes(Request $request, Response $response, array $args) {
    if ( !(SessionUser::getUser()->isGdrpDpoEnabled()) ) {
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
}

function setGdprComment (Request $request, Response $response, array $args) {
    if ( !(SessionUser::getUser()->isGdrpDpoEnabled()) ) {
      return $response->withStatus(401);
    }

    $input = (object)$request->getParsedBody();

    if ( isset ($input->custom_id) && isset ($input->comment) && isset ($input->type) )
    {
      if ($input->type == 'person') {
        $person = GdprInfoQuery::Create()->findOneById($input->custom_id);

        if ( !is_null ($person) ) {
          $person->setComment($input->comment);
          $person->save();
        }

        return $response->withJson(['status' => "success"]);
      } else if ($input->type == 'personCustom') {
        $personCM = PersonCustomMasterQuery::Create()->findOneById($input->custom_id);

        if ( !is_null ($personCM) ) {
          $personCM->setCustomComment($input->comment);
          $personCM->save();
        }

        return $response->withJson(['status' => "success"]);
      } else if ($input->type == 'personProperty') {
        $personProp = PropertyQuery::Create()->filterByProClass('p')->findOneByProId($input->custom_id);

        if ( !is_null ($personProp) ) {
          $personProp->setProComment($input->comment);
          $personProp->save();
        }

        return $response->withJson(['status' => "success"]);
      } else if ($input->type == 'family') {
        $family = GdprInfoQuery::Create()->findOneById($input->custom_id);

        if ( !is_null ($family) ) {
          $family->setComment($input->comment);
          $family->save();
        }

        return $response->withJson(['status' => "success"]);
      } else if ($input->type == 'familyCustom') {
        $familyCM = FamilyCustomMasterQuery::Create()->findOneById($input->custom_id);

        if ( !is_null ($familyCM) ) {
          $familyCM->setCustomComment($input->comment);
          $familyCM->save();
        }

        return $response->withJson(['status' => "success"]);
      } else if ($input->type == 'pastoralCare') {
        $pastoralCare = PastoralCareTypeQuery::Create()->findOneById($input->custom_id);

        if ( !is_null ($pastoralCare) ) {
          $pastoralCare->setComment($input->comment);
          $pastoralCare->save();
        }

        return $response->withJson(['status' => "success"]);
      } else if ($input->type == 'familyProperty') {
        $personProp = PropertyQuery::Create()->filterByProClass('f')->findOneByProId($input->custom_id);

        if ( !is_null ($personProp) ) {
          $personProp->setProComment($input->comment);
          $personProp->save();
        }

        return $response->withJson(['status' => "success"]);
      }
    }

    return $response->withJson(['status' => "failed"]);
}

function removePersonGdpr (Request $request, Response $response, array $args) {
    if ( !(SessionUser::getUser()->isGdrpDpoEnabled()) ) {
      return $response->withStatus(401);
    }

    $input = (object)$request->getParsedBody();

    if ( isset ($input->personId) )
    {
       $person = PersonQuery::Create()->findOneById($input->personId);

       if (!is_null($person)) {
         $person->delete();
       }

       return $response->withJson(['status' => "success"]);
    }

    return $response->withJson(['status' => "failed"]);
}

function removeAllPersonsGdpr (Request $request, Response $response, array $args) {
    if ( !(SessionUser::getUser()->isGdrpDpoEnabled()) ) {
      return $response->withStatus(401);
    }

    $time = new DateTime('now');
    $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

    $persons = PersonQuery::create()
      ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
      ->_or() // or : this part is unusefull, it's only for debugging
      ->useFamilyQuery()
        ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)// GDRP, when a Family is completely deactivated
      ->endUse()
      ->find();

    $count = 0;

    if ($persons->count() > 0) {
       foreach ($persons as $person) {
         $pledges  = PledgeQuery::Create()->findByFamId($person->getFamId());

         if (is_null($pledges) || !is_null($pledges) && $pledges->count() == 0) {
           $person->delete();
           $count++;
         }
       }
    }

    if ($persons->count() == $count) {
      return $response->withJson(['status' => "success"]);
    }

    return $response->withJson(['status' => "failed"]);
}

function removeFamilyGdpr (Request $request, Response $response, array $args) {
    if ( !(SessionUser::getUser()->isGdrpDpoEnabled()) ) {
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
}

function removeAllFamiliesGdpr (Request $request, Response $response, array $args) {
    if ( !(SessionUser::getUser()->isGdrpDpoEnabled()) ) {
      return $response->withStatus(401);
    }

    $time = new DateTime('now');
    $newtime = $time->modify('-'.SystemConfig::getValue('iGdprExpirationDate').' year')->format('Y-m-d');

    $families = FamilyQuery::create()
      ->filterByDateDeactivated($newtime, Criteria::LESS_THAN)
      ->find();

    $count = 0;

    if ($families->count() > 0) {
       foreach ($families as $family) {
         $pledges  = PledgeQuery::Create()->findByFamId($family->getId());

         if (is_null($pledges) || !is_null($pledges) && $pledges->count() == 0) {
           $family->delete();
           $count++;
         }
       }
    }

    if ($families->count() == $count) {
      return $response->withJson(['status' => "success"]);
    }

    return $response->withJson(['status' => "failed"]);
}
