<?php
/* contributor Philippe Logel */

// Person APIs
use Propel\Runtime\Propel;
use EcclesiaCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\Photo;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Property;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Family;
use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\Map\PersonVolunteerOpportunityTableMap;
use EcclesiaCRM\Map\VolunteerOpportunityTableMap;
use EcclesiaCRM\PersonVolunteerOpportunityQuery;
use EcclesiaCRM\PersonVolunteerOpportunity;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;


$app->group('/persons', function () {
    // search person by Name
    $this->get('/search/{query}', function ($request, $response, $args) {
        $query = $args['query'];

      $searchLikeString = '%'.$query.'%';
      $people = PersonQuery::create()->
      filterByFirstName($searchLikeString, Criteria::LIKE)->
      _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
      _or()->filterByEmail($searchLikeString, Criteria::LIKE)->
          limit(15)->find();
        
        $id = 1;
        
        $return = [];        
        foreach ($people as $person) {
            $values['id'] = $id++;
            $values['objid'] = $person->getId();
            $values['text'] = $person->getFullName();
            $values['uri'] = $person->getViewURI();
            
            array_push($return, $values);
        }
        
        return $response->withJson($return);    
    });
    

    /**
     *
     * VolunteerOpportunity 
     *
     **/
    $this->post('/volunteers/{personID:[0-9]+}', function ($request, $response, $args) {
      return VolunteerOpportunityQuery::Create()
        ->addJoin(VolunteerOpportunityTableMap::COL_VOL_ID,PersonVolunteerOpportunityTableMap::COL_P2VO_VOL_ID,Criteria::LEFT_JOIN)
        ->Where(PersonVolunteerOpportunityTableMap::COL_P2VO_PER_ID.' = '.$args['personID'])
        ->find()->toJson();
    });

    $this->post('/volunteers/delete', function ($request, $response, $args) {
      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->personId) && isset ($input->volunteerOpportunityId) ){
      
        $vol = PersonVolunteerOpportunityQuery::Create()
          ->filterByPersonId($input->personId)
          ->findOneByVolunteerOpportunityId($input->volunteerOpportunityId);
          
        if (!is_null($vol)){
          $vol->delete();
        }
        
        $vols = PersonVolunteerOpportunityQuery::Create()
          ->filterByPersonId($input->personId)
          ->find();
        
        return $response->withJson(['success' => true,'count' => $vols->count()]);
      }
      
      return $response->withJson(['success' => false]);
    });
    
    $this->post('/isMailChimpActive', function ($request, $response, $args) {
      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->personId) && isset ($input->email)){
      
        // we get the MailChimp Service
        $mailchimp = new MailChimpService();
        $person = PersonQuery::create()->findPk($input->personId);
        
        if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
          return $response->withJson(['success' => true,'isIncludedInMailing' => ($person->getFamily()->getSendNewsletter() == 'TRUE')?true:false,'mailingList' => $mailchimp->isEmailInMailChimp($input->email)]);
        }
      }
      
      return $response->withJson(['success' => false]);
    });

    $this->post('/volunteers/add', function ($request, $response, $args) {
      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->personId) && isset ($input->volID) ){
      
        $vol = PersonVolunteerOpportunityQuery::Create()
          ->filterByPersonId($input->personId)
          ->findOneByVolunteerOpportunityId($input->volID);
          
        if (is_null($vol)){
          $vol = new PersonVolunteerOpportunity();
          
          $vol->setPersonId($input->personId);
          $vol->setVolunteerOpportunityId($input->volID);

          $vol->save();
        }
        
        $vols = PersonVolunteerOpportunityQuery::Create()
          ->filterByPersonId($input->personId)
          ->find();
        
        return $response->withJson(['success' => true,'count' => $vols->count()]);
      }
      
      return $response->withJson(['success' => false]);
    });
    
    /**
     * Update the person status to activated or deactivated with :familyId and :status true/false.
     * Pass true to activate and false to deactivate.     *
     */
    $this->post('/{personId:[0-9]+}/activate/{status}', function ($request, $response, $args) {
        $personId = $args["personId"];
        $newStatus = $args["status"];

        $person = PersonQuery::create()->findPk($personId);
        $currentStatus = (empty($person->getDateDeactivated()) ? 'true' : 'false');

        //update only if the value is different
        if ($currentStatus != $newStatus) {
            if ($newStatus == "false") {
                $person->setDateDeactivated(date('YmdHis'));
            } elseif ($newStatus == "true") {
                $person->setDateDeactivated(Null);
            }
            $person->save();
            
            // a one person family is deactivated too
            if ($person->getFamily()->getPeople()->count() == 1) {
              if ($newStatus == "false") {
                  $person->getFamily()->setDateDeactivated(date('YmdHis'));
              } elseif ($newStatus == "true") {
                  $person->getFamily()->setDateDeactivated(Null);
              }
              $person->getFamily()->save();
            }

            //Create a note to record the status change
            $note = new Note();
            $note->setPerId($personId);
            if ($newStatus == 'false') {
                $note->setText(gettext('Person Deactivated'));
            } else {
                $note->setText(gettext('Person Activated'));
            }
            $note->setType('edit');
            $note->setEntered($_SESSION['user']->getPersonId());
            $note->save();
        }
        return $response->withJson(['success' => true]);

    });    
    
    // api for person properties
    $this->post('/personproperties/{personID:[0-9]+}', function ($request, $response, $args) {
      $ormAssignedProperties = Record2propertyR2pQuery::Create()
                            ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                            ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                            ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                            ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                            ->where(PropertyTableMap::COL_PRO_CLASS."='p'")
                            ->addAscendingOrderByColumn('ProName')
                            ->addAscendingOrderByColumn('ProTypeName')
                            ->findByR2pRecordId($args['personID']);

      return $ormAssignedProperties->toJSON();
    });
    
    $this->get('/numbers', function ($request, $response, $args) {
      return $response->withJson(MenuEventsCount::getNumberBirthDates());       
    });

    $this->get('/{personId:[0-9]+}/photo', function ($request, $response, $args) {
      $res=$this->cache->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
      $photo = new Photo("Person",$args['personId']);
      return $res->write($photo->getPhotoBytes())->withHeader('Content-type', $photo->getPhotoContentType());
      
    });

    $this->get('/{personId:[0-9]+}/thumbnail', function ($request, $response, $args) {
      $res=$this->cache->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
      $photo = new Photo("Person",$args['personId']);
      return $res->write($photo->getThumbnailBytes())->withHeader('Content-type', $photo->getThumbnailContentType());
    });

    $this->post('/{personId:[0-9]+}/photo', function ($request, $response, $args) {
        $input = (object)$request->getParsedBody();
        $person = PersonQuery::create()->findPk($args['personId']);
        $person->setImageFromBase64($input->imgBase64);
        $response->withJSON(array("status" => "success"));
    });

    $this->delete('/{personId:[0-9]+}/photo', function ($request, $response, $args) {
        $person = PersonQuery::create()->findPk($args['personId']);
        return json_encode(array("status" => $person->deletePhoto()));
    });

    $this->post('/{personId:[0-9]+}/addToCart', function ($request, $response, $args) {
        Cart::AddPerson($args['personId']);
    });

    /**
     * @var $response \Psr\Http\Message\ResponseInterface
     */
    $this->delete('/{personId:[0-9]+}', function ($request, $response, $args) {
        /**
         * @var \EcclesiaCRM\User $sessionUser
         */
        $sessionUser = $_SESSION['user'];
        if (!$sessionUser->isDeleteRecordsEnabled()) {
            return $response->withStatus(401);
        }
        $personId = $args['personId'];
        if ($sessionUser->getId() == $personId) {
            return $response->withStatus(403);
        }
        $person = PersonQuery::create()->findPk($personId);
        if (is_null($person)) {
            return $response->withStatus(404);
        }

        $person->delete();

        return $response->withJSON(array("status" => "success"));

    });
    
    $this->post('/deletefield', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMenuOptionsEnabled()) {
          return $response->withStatus(404);
      }
      
      $values = (object)$request->getParsedBody();
      
      if ( isset ($values->orderID) && isset ($values->field) )
      {
        // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
        $perCus = PersonCustomMasterQuery::Create()->findOneByCustomField($values->field);
        
        if ( !is_null ($perCus) && $perCus->getTypeId() == 12 ) {
           $list = ListOptionQuery::Create()->findById($perCus->getCustomSpecial());
           if( !is_null($list) ) {
             $list->delete();
           }
        }
        
        // this can't be propeled
        $connection = Propel::getConnection();
        $sSQL = 'ALTER TABLE `person_custom` DROP `'.$values->field.'` ;';
        $connection->exec($sSQL); 

        // now we can delete the FamilyCustomMaster
        $perCus->delete();

        $allperCus = PersonCustomMasterQuery::Create()->find();
        $numRows = $allperCus->count();

        // Shift the remaining rows up by one, unless we've just deleted the only row
        if ($numRows > 0) {
            for ($reorderRow = $values->orderID + 1; $reorderRow <= $numRows + 1; $reorderRow++) {
                $firstperCus = PersonCustomMasterQuery::Create()->findOneByCustomOrder($reorderRow);
                if (!is_null($firstperCus)) {
                  $firstperCus->setCustomOrder($reorderRow - 1)->save();
                }
            }
        }
        
        return $response->withJson(['success' => true]);
      }
      
      return $response->withJson(['success' => false]);
    });

    $this->post('/upactionfield', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMenuOptionsEnabled()) {
          return $response->withStatus(404);
      }

      $values = (object)$request->getParsedBody();
      
      if ( isset ($values->orderID) && isset ($values->field) )
      {
        // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
        $firstFamCus = PersonCustomMasterQuery::Create()->findOneByCustomOrder($values->orderID - 1);
        $firstFamCus->setCustomOrder($values->orderID)->save();
        
        $secondFamCus = PersonCustomMasterQuery::Create()->findOneByCustomField($values->field);
        $secondFamCus->setCustomOrder($values->orderID - 1)->save();
        
        return $response->withJson(['success' => true]);
      }
      
      return $response->withJson(['success' => false]);
    });
    
    $this->post('/downactionfield', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMenuOptionsEnabled()) {
          return $response->withStatus(404);
      }

      $values = (object)$request->getParsedBody();
      
      if ( isset ($values->orderID) && isset ($values->field) )
      {
        // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
        $firstFamCus = PersonCustomMasterQuery::Create()->findOneByCustomOrder($values->orderID + 1);
        $firstFamCus->setCustomOrder($values->orderID)->save();
        
        $secondFamCus = PersonCustomMasterQuery::Create()->findOneByCustomField($values->field);
        $secondFamCus->setCustomOrder($values->orderID + 1)->save();
        
        return $response->withJson(['success' => true]);
      }
      
      return $response->withJson(['success' => false]);
    });
    
/**
 * A method that review dup emails in the db and returns families and people where that email is used.
 */
 
 $this->get('/duplicate/emails', function ($request, $response, $args) {
    $connection = Propel::getConnection();
    $dupEmailsSQL = "SELECT email, total FROM email_count where total > 1";
    $statement = $connection->prepare($dupEmailsSQL);
    $statement->execute();
    $dupEmails = $statement->fetchAll();
    $emails = [];
    foreach ($dupEmails as $dbEmail) {
        $email = $dbEmail['email'];
        $dbPeople = PersonQuery::create()->filterByEmail($email)->_or()->filterByWorkEmail($email)->find();
        $people = [];
        foreach ($dbPeople as $person) {
            array_push($people, ["id" => $person->getId(), "name" => $person->getFullName()]);
        }
        $families = [];
        $dbFamilies = FamilyQuery::create()->findByEmail($email);
        foreach ($dbFamilies as $family) {
            array_push($families, ["id" => $family->getId(), "name" => $family->getName()]);
        }
        array_push($emails, [
            "email" => $email,
            "people" => $people,
            "families" => $families
        ]);
    }
    return $response->withJson(["emails" => $emails]);
  });
  
  
/**
 * A method that review dup emails in the db and returns families and people where that email is used.
 */
 
 $this->get('/NotInMailChimp/emails', function ($request, $response, $args) {    
    $mailchimp = new MailChimpService();
    if (!$mailchimp->isActive())
    {
      return $response->withRedirect(SystemURLs::getRootPath() . "/email/Dashboard.php");
    }
    $People = PersonQuery::create()
            ->filterByEmail(null, Criteria::NOT_EQUAL)
            ->orderByDateLastEdited(Criteria::DESC)
            ->find();
    
    $missingEmailInMailChimp = array();
    foreach($People as $Person)
    {
        $mailchimpList = $mailchimp->isEmailInMailChimp($Person->getEmail());
        if ($mailchimpList == '') {
           array_push($missingEmailInMailChimp, ["id" => $Person->getId(), "url" => '<a href="'.SystemURLs::getRootPath().'/PersonView.php?PersonID='.$Person->getId().'">'. $Person->getFullName() . '</a>', "email" => $Person->getEmail()]);
        }
    }
     
    return $response->withJson(["emails" => $missingEmailInMailChimp]);
  });
});
