<?php

/* Contributors Philippe Logel */
// Routes
use Propel\Runtime\Propel;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\dto\Photo;
use EcclesiaCRM\Emails\FamilyVerificationEmail;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\TokenTableMap;
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\Token;
use EcclesiaCRM\TokenQuery;
use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Property;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Service\MailChimpService;



$app->group('/families', function () {
    $this->post('/familyproperties/{familyID:[0-9]+}', function ($request, $response, $args) {
      $ormAssignedProperties = Record2propertyR2pQuery::Create()
                            ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID,PropertyTableMap::COL_PRO_ID,Criteria::LEFT_JOIN)
                            ->addJoin(PropertyTableMap::COL_PRO_PRT_ID,PropertyTypeTableMap::COL_PRT_ID,Criteria::LEFT_JOIN)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProId',PropertyTableMap::COL_PRO_ID)
                            ->addAsColumn('ProPrtId',PropertyTableMap::COL_PRO_PRT_ID)
                            ->addAsColumn('ProPrompt',PropertyTableMap::COL_PRO_PROMPT)
                            ->addAsColumn('ProName',PropertyTableMap::COL_PRO_NAME)
                            ->addAsColumn('ProTypeName',PropertyTypeTableMap::COL_PRT_NAME)
                            ->where(PropertyTableMap::COL_PRO_CLASS."='f'")
                            ->addAscendingOrderByColumn('ProName')
                            ->addAscendingOrderByColumn('ProTypeName')
                            ->findByR2pRecordId($args['familyID']);

      return $ormAssignedProperties->toJSON();
    });
        
    $this->post('/isMailChimpActive', function ($request, $response, $args) {
      $input = (object)$request->getParsedBody();
    
      if ( isset ($input->familyId) && isset ($input->email)){
      
        // we get the MailChimp Service
        $mailchimp = new MailChimpService();
        $family = FamilyQuery::create()->findPk($input->familyId);
        
        if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
          return $response->withJson(['success' => true,'isIncludedInMailing' => ($family->getSendNewsletter() == 'TRUE')?true:false,'mailingList' => $mailchimp->isEmailInMailChimp($input->email)]);
        }
      }
      
      return $response->withJson(['success' => false]);
    });

    $this->get('/{familyId:[0-9]+}', function ($request, $response, $args) {
        $family = FamilyQuery::create()->findPk($args['familyId']);
        return $response->withJSON($family->toJSON());
    });
    
    $this->post('/info', function ($request, $response, $args) {
        $values = (object)$request->getParsedBody();
      
        if ( isset ($values->familyId) )
        {
          $family = FamilyQuery::create()->findPk($values->familyId);
          return $family->toJSON();
        }
    });


    $this->get('/numbers', function ($request, $response, $args) {
        return $response->withJson(MenuEventsCount::getNumberAnniversaries());
    });


    $this->get('/search/{query}', function ($request, $response, $args) {
        $query = $args['query'];
        $results = [];
        $q = FamilyQuery::create()
            ->filterByName("%$query%", Propel\Runtime\ActiveQuery\Criteria::LIKE)
            ->limit(15)
            ->find();
        foreach ($q as $family) {
            array_push($results, $family->toSearchArray());
        }

        return $response->withJSON(json_encode(["Families" => $results]));
    });

    $this->get('/self-register', function ($request, $response, $args) {
        $families = FamilyQuery::create()
            ->filterByEnteredBy(Person::SELF_REGISTER)
            ->orderByDateEntered(Criteria::DESC)
            ->limit(100)
            ->find();
        return $response->withJSON(['families' => $families->toArray()]);
    });

    $this->get('/self-verify', function ($request, $response, $args) {
        $verifcationNotes = NoteQuery::create()
            ->filterByEnteredBy(Person::SELF_VERIFY)
            ->orderByDateEntered(Criteria::DESC)
            ->joinWithFamily()
            ->limit(100)
            ->find();
        return $response->withJSON(['families' => $verifcationNotes->toArray()]);
    });

    $this->get('/pending-self-verify', function ($request, $response, $args) {
        $pendingTokens = TokenQuery::create()
            ->filterByType(Token::typeFamilyVerify)
            ->filterByRemainingUses(array('min' => 1))
            ->filterByValidUntilDate(array('min' => new DateTime()))
            ->addJoin(TokenTableMap::COL_REFERENCE_ID, FamilyTableMap::COL_FAM_ID)
            ->withColumn(FamilyTableMap::COL_FAM_NAME, "FamilyName")
            ->withColumn(TokenTableMap::COL_REFERENCE_ID, "FamilyId")
            ->limit(100)
            ->find();
        return $response->withJSON(['families' => $pendingTokens->toArray()]);
    });


    $this->get('/byCheckNumber/{scanString}', function ($request, $response, $args) {
        $scanString = $args['scanString'];
        echo $this->FinancialService->getMemberByScanString($scanString);
    });

    $this->get('/{familyId:[0-9]+}/photo', function ($request, $response, $args) {
        $res = $this->cache->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Family", $args['familyId']);
        return $res->write($photo->getPhotoBytes())->withHeader('Content-type', $photo->getPhotoContentType());
    });

    $this->get('/{familyId:[0-9]+}/thumbnail', function ($request, $response, $args) {

        $res = $this->cache->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Family", $args['familyId']);
        return $res->write($photo->getThumbnailBytes())->withHeader('Content-type', $photo->getThumbnailContentType());
    });

    $this->post('/{familyId:[0-9]+}/photo', function ($request, $response, $args) {
        $input = (object)$request->getParsedBody();
        $family = FamilyQuery::create()->findPk($args['familyId']);
        $family->setImageFromBase64($input->imgBase64);

        $response->withJSON(array("status" => "success", "upload" => $upload));
    });

    $this->delete('/{familyId:[0-9]+}/photo', function ($request, $response, $args) {
        $family = FamilyQuery::create()->findPk($args['familyId']);
        return json_encode(array("status" => $family->deletePhoto()));
    });

    $this->post('/{familyId}/verify', function ($request, $response, $args) {
        $familyId = $args["familyId"];
        $family = FamilyQuery::create()->findPk($familyId);
        if ($family != null) {
            TokenQuery::create()->filterByType("verifyFamily")->filterByReferenceId($family->getId())->delete();
            $token = new Token();
            $token->build("verifyFamily", $family->getId());
            $token->save();
            $email = new FamilyVerificationEmail($family->getEmails(), $family->getName(), $token->getToken());
            if ($email->send()) {
                $family->createTimeLineNote("verify-link");
                $response = $response->withStatus(200);
            } else {
                $this->Logger->error($email->getError());
                throw new \Exception($email->getError());
            }
        } else {
            $response = $response->withStatus(404)->getBody()->write("familyId: " . $familyId . " not found");
        }
        return $response;
    });

    $this->post('/verify/{familyId}/now', function ($request, $response, $args) {
        $familyId = $args["familyId"];
        $family = FamilyQuery::create()->findPk($familyId);
        if ($family != null) {
            $family->verify();
            $response = $response->withStatus(200);
        } else {
            $response = $response->withStatus(404)->getBody()->write("familyId: " . $familyId . " not found");
        }
        return $response;
    });

    /**
     * Update the family status to activated or deactivated with :familyId and :status true/false.
     * Pass true to activate and false to deactivate.     *
     */
    $this->post('/{familyId:[0-9]+}/activate/{status}', function ($request, $response, $args) {
        $familyId = $args["familyId"];
        $newStatus = $args["status"];

        $family = FamilyQuery::create()->findPk($familyId);
        $currentStatus = (empty($family->getDateDeactivated()) ? 'true' : 'false');

        //update only if the value is different
        if ($currentStatus != $newStatus) {
            if ($newStatus == "false") {
                $family->setDateDeactivated(date('YmdHis'));
            } elseif ($newStatus == "true") {
                $family->setDateDeactivated(Null);
            }
            $family->save();
            
            $persons = $family->getPeople();
            
            // all person from the family should be deactivated too
            foreach ($persons as $person) {
              if ($newStatus == "false") {
                $person->setDateDeactivated(date('YmdHis'));
              } elseif ($newStatus == "true") {
                $person->setDateDeactivated(Null);
              }
              $person->save();
            }

            //Create a note to record the status change
            $note = new Note();
            $note->setFamId($familyId);
            if ($newStatus == 'false') {
                $note->setText(gettext('Family Deactivated'));
            } else {
                $note->setText(gettext('Family Activated'));
            }
            $note->setType('edit');
            $note->setEntered($_SESSION['user']->getPersonId());
            $note->save();
        }
        return $response->withJson(['success' => true]);

    });


    $this->get('/{familyId:[0-9]+}/geolocation', function ($request, $response, $args) {
        $familyId = $args["familyId"];
        $family = FamilyQuery::create()->findPk($familyId);
        if (!empty($family)) {
            $familyAddress = $family->getAddress();
            $familyLatLong = GeoUtils::getLatLong($familyAddress);

            $familyDrivingInfo = GeoUtils::DrivingDistanceMatrix($familyAddress, ChurchMetaData::getChurchAddress());
            $geoLocationInfo = array_merge($familyDrivingInfo, $familyLatLong);

            return $response->withJson($geoLocationInfo);
        }
        return $response->withStatus(404)->getBody()->write("familyId: " . $familyId . " not found");
    });
    
    $this->post('/deletefield', function ($request, $response, $args) {
      if (!$_SESSION['user']->isMenuOptionsEnabled()) {
          return $response->withStatus(404);
      }
      
      $values = (object)$request->getParsedBody();
      
      if ( isset ($values->orderID) && isset ($values->field) )
      {
        // Check if this field is a custom list type.  If so, the list needs to be deleted from list_lst.
        $famCus = FamilyCustomMasterQuery::Create()->findOneByCustomField($values->field);
        
        if ( !is_null ($famCus) && $famCus->getTypeId() == 12 ) {
           $list = ListOptionQuery::Create()->findById($famCus->getCustomSpecial());
           if( !is_null($list) ) {
             $list->delete();
           }
        }
        
        // this can't be propeled
        $connection = Propel::getConnection();
        $sSQL = 'ALTER TABLE `family_custom` DROP `'.$values->field.'` ;';
        $connection->exec($sSQL); 

        // now we can delete the FamilyCustomMaster
        $famCus->delete();

        $allFamCus = FamilyCustomMasterQuery::Create()->find();
        $numRows = $allFamCus->count();

        // Shift the remaining rows up by one, unless we've just deleted the only row
        if ($numRows > 0) {
            for ($reorderRow = $values->orderID + 1; $reorderRow <= $numRows + 1; $reorderRow++) {
                $firstFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomOrder($reorderRow);
                if (!is_null($firstFamCus)) {
                  $firstFamCus->setCustomOrder($reorderRow - 1)->save();
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
        $firstFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomOrder($values->orderID - 1);
        $firstFamCus->setCustomOrder($values->orderID)->save();
        
        $secondFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomField($values->field);
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
        $firstFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomOrder($values->orderID + 1);
        $firstFamCus->setCustomOrder($values->orderID)->save();
        
        $secondFamCus = FamilyCustomMasterQuery::Create()->findOneByCustomField($values->field);
        $secondFamCus->setCustomOrder($values->orderID + 1)->save();
        
        return $response->withJson(['success' => true]);
      }
      
      return $response->withJson(['success' => false]);
    });

});
