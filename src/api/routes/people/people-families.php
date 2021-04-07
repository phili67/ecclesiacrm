<?php

/* Contributors Philippe Logel */

use Slim\Routing\RouteCollectorProxy;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


// Routes
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
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
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Service\FinancialService;


$app->group('/families', function (RouteCollectorProxy $group) {

/*
 * @! Return family properties for familyID
 * #! param: id->int   :: familyId as id
 */
    $group->post('/familyproperties/{familyID:[0-9]+}', PeopleFamilyController::class . ":postfamilyproperties" );
/*
 * @! Return if mailchimp is activated for family
 * #! param: id->int   :: familyId as id
 * #! param: ref->string :: email as ref
 */
    $group->post('/isMailChimpActive', PeopleFamilyController::class . ":isMailChimpActiveFamily" );
/*
 * @! Return the family as json
 * #! param: id->int   :: familyId as id
 */
    $group->get('/{familyId:[0-9]+}', PeopleFamilyController::class . ":getFamily" );
/*
 * @! Return the family info as json
 * #! param: id->int   :: familyId as id
 */
    $group->post('/info', PeopleFamilyController::class . ":familyInfo" );
/*
 * @! Return the numbers of Anniversaries for MenuEvent
 */
    $group->get('/numbers', PeopleFamilyController::class . ":numbersOfAnniversaries" );
/*
 * @! Returns a list of the families who's name matches the :query parameter
 * #! param: ref->string :: query as ref
 */
    $group->get('/search/{query}', PeopleFamilyController::class . ":searchFamily" );
/*
 * @! Returns a list of the self-registered families
 */
    $group->get('/self-register', PeopleFamilyController::class . ":selfRegisterFamily" );
/*
 * @! Returns a list of the self-verified families
 */
    $group->get('/self-verify', PeopleFamilyController::class . ":selfVerifyFamily" );
/*
 * @! Returns a list of the pending self-verified families
 */
    $group->get('/pending-self-verify', PeopleFamilyController::class . ":pendingSelfVerify" );
/*
 * @! Returns a family string based on the scan string of an MICR reader containing a routing and account number
 * #! param: ref->string :: scanString as ref
 */
    $group->get('/byCheckNumber/{scanString}', PeopleFamilyController::class . ":byCheckNumberScan" );

 /*
 * @! Returns the photo for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->get('/{familyId:[0-9]+}/photo', PeopleFamilyController::class . ":photo" );

 /*
 * @! Returns the thumbnail for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->get('/{familyId:[0-9]+}/thumbnail', PeopleFamilyController::class . ":thumbnail" );

 /*
 * @! Post the photo for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->post('/{familyId:[0-9]+}/photo', PeopleFamilyController::class . ":postFamilyPhoto" );

 /*
 * @! Delete the photo for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->delete('/{familyId:[0-9]+}/photo', PeopleFamilyController::class . ":deleteFamilyPhoto" );

 /*
 * @! Verify the family for the familyId
 * #! param: id->int :: familyId as id
 */
    $group->post('/{familyId:[0-9]+}/verify', PeopleFamilyController::class . ":verifyFamily" );

 /*
 * @! Verify the family for the familyId now
 * #! param: id->int :: familyId as id
 */
    $group->post('/verify/{familyId:[0-9]+}/now', PeopleFamilyController::class . ":verifyFamilyNow" );

/*
 * @! Verify the family for the familyId now
 * #! param: id->int :: family
 */
    $group->post('/verify/url', PeopleFamilyController::class . ':verifyFamilyURL' );

/*
 * @! Update the family status to activated or deactivated with :familyId and :status true/false. Pass true to activate and false to deactivate.
 * #! param: id->int   :: familyId as id
 * #! param: ref->bool :: status as ref
 */
    $group->post('/{familyId:[0-9]+}/activate/{status}', PeopleFamilyController::class . ":familyActivateStatus" );
 /*
 * @! Return the location for the family
 * #! param: id->int :: familyId as id
 */
    $group->get('/{familyId:[0-9]+}/geolocation', PeopleFamilyController::class . ":familyGeolocation" );

 /*
 * @! delete familyField custom field
 * #! param: id->int :: orderID as id
 * #! param: id->int :: field as id
 */
    $group->post('/deletefield', PeopleFamilyController::class . ":deleteFamilyField" );
 /*
 * @! Move up the family custom field
 * #! param: id->int :: orderID as id
 * #! param: id->int :: field as id
 */
    $group->post('/upactionfield', PeopleFamilyController::class . ":upactionFamilyField" );
 /*
 * @! Move down the family custom field
 * #! param: id->int :: orderID as id
 * #! param: id->int :: field as id
 */
    $group->post('/downactionfield', PeopleFamilyController::class . ":downactionFamilyField" );

});

class PeopleFamilyController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function photo (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $res = $this->container->get('CacheProvider')->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Family", $args['familyId']);
        return $res->write($photo->getPhotoBytes())->withHeader('Content-type', $photo->getPhotoContentType());
    }

    public function thumbnail (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $res = $this->container->get('CacheProvider')->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Family", $args['familyId']);
        return $res->write($photo->getThumbnailBytes())->withHeader('Content-type', $photo->getThumbnailContentType());
    }

    public function postfamilyproperties (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

        return $response->write($ormAssignedProperties->toJSON());
    }

    public function isMailChimpActiveFamily (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->familyId) && isset ($input->email)){

            // we get the MailChimp Service
            $mailchimp = new MailChimpService();
            $family = FamilyQuery::create()->findPk($input->familyId);
            $isIncludedInMailing = $family->getSendNewsletter();

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
                return $response->withJson(['success' => true,'isIncludedInMailing' => ($family->getSendNewsletter() == 'TRUE')?true:false, 'mailChimpActiv' => true, 'statusLists' => $mailchimp->getListNameAndStatus($input->email)]);
            } else {
                return $response->withJson(['success' => true,'isIncludedInMailing' => ($family->getSendNewsletter() == 'TRUE')?true:false, 'mailChimpActiv' => false, 'mailingList' => null]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function getFamily (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $family = FamilyQuery::create()->findPk($args['familyId']);
        return $response->withJSON($family->toJSON());
    }

    public function familyInfo (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $values = (object)$request->getParsedBody();

        if ( isset ($values->familyId) )
        {
            $family = FamilyQuery::create()->findPk($values->familyId);
            return $response->write($family->toJSON());
        }
    }

    public function numbersOfAnniversaries (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        return $response->withJson(MenuEventsCount::getNumberAnniversaries());
    }

    public function searchFamily (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $query = $args['query'];
        $results = [];
        $q = FamilyQuery::create()
            //->filterByDateDeactivated(null)// PledgeEditor : a financer can add last payment for a family
            ->filterByName("%$query%", Criteria::LIKE)
            ->limit(15)
            ->find();
        foreach ($q as $family) {
            array_push($results, $family->toSearchArray());
        }

        return $response->withJSON(json_encode(["Families" => $results]));
    }

    public function selfRegisterFamily (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $families = FamilyQuery::create()
            ->filterByEnteredBy(Person::SELF_REGISTER)
            ->orderByDateEntered(Criteria::DESC)
            ->limit(100)
            ->find();
        return $response->withJSON(['families' => $families->toArray()]);
    }

    public function selfVerifyFamily (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $verifcationNotes = NoteQuery::create()
            ->filterByEnteredBy(Person::SELF_VERIFY)
            ->orderByDateEntered(Criteria::DESC)
            ->joinWithFamily()
            ->limit(100)
            ->find();
        return $response->withJSON(['families' => $verifcationNotes->toArray()]);
    }

    public function pendingSelfVerify(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
    }

    public function byCheckNumberScan (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $scanString = $args['scanString'];

        $fService = new FinancialService();
        return $response->write($fService->getMemberByScanString($scanString));
    }

    public function postFamilyPhoto(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object)$request->getParsedBody();
        $family = FamilyQuery::create()->findPk($args['familyId']);
        $family->setImageFromBase64($input->imgBase64);

        return $response->withJSON(array("status" => "success"));
    }

    public function deleteFamilyPhoto (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $family = FamilyQuery::create()->findPk($args['familyId']);
        return $response->withJson(["status" => $family->deletePhoto()]);
    }

    public function verifyFamily (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
                $logger = LoggerUtils::getAppLogger();
                $logger->error($email->getError());
                throw new \Exception($email->getError());
            }
        } else {
            $response = $response->withStatus(404)->getBody()->write("familyId: " . $familyId . " not found");
        }
        return $response;
    }

    public function verifyFamilyNow (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $familyId = $args["familyId"];
        $family = FamilyQuery::create()->findPk($familyId);
        if ($family != null) {
            $family->verify();
            $response = $response->withStatus(200);
        } else {
            $response = $response->withStatus(404)->getBody()->write("familyId: " . $familyId . " not found");
        }
        return $response;
    }

    public function verifyFamilyURL (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->famId) ) {
            $family = FamilyQuery::create()->findOneById($input->famId);
            TokenQuery::create()->filterByType("verifyFamily")->filterByReferenceId($family->getId())->delete();
            $token = new Token();
            $token->build("verifyFamily", $family->getId());
            $token->save();
            $family->createTimeLineNote("verify-URL");
            return $response->withJSON(["url" => "external/verify/" . $token->getToken()]);
        }
    }

    public function familyActivateStatus (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
                $note->setText(_('Family Deactivated'));
            } else {
                $note->setText(_('Family Activated'));
            }
            $note->setType('edit');
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->save();
        }
        return $response->withJson(['success' => true]);

    }

    public function familyGeolocation (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
    }

    public function deleteFamilyField(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
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
    }

    public function upactionFamilyField (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
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
    }

    public function downactionFamilyField (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
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
    }
}


