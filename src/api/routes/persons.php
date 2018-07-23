<?php
/* contributor Philippe Logel */

// Person APIs
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

            //Create a note to record the status change
            $note = new Note();
            $note->setPerId($personId);
            if ($newStatus == 'false') {
                $note->setText(gettext('Deactivated the Person'));
            } else {
                $note->setText(gettext('Activated the Person'));
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
        AddToPeopleCart($args['personId']);
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

});
