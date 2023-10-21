<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

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
use EcclesiaCRM\TokenPassword;
use EcclesiaCRM\TokenQuery;
use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\Reports\EmailUsers;

class PeopleFamilyController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function photo (ServerRequest $request, Response $response, array $args): Response {
        $response = $this->container->get('CacheProvider')->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Family", $args['familyId']);
        return $response->write($photo->getPhotoBytes())->withHeader('Content-type', $photo->getPhotoContentType());
    }

    public function thumbnail (ServerRequest $request, Response $response, array $args): Response {
        $response = $this->container->get('CacheProvider')->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Family", $args['familyId']);
        return $response->write($photo->getThumbnailBytes())->withHeader('Content-type', $photo->getThumbnailContentType());
    }

    public function postfamilyproperties (ServerRequest $request, Response $response, array $args): Response {
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

    public function isMailChimpActiveFamily (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        // we get the MailChimp Service
        $mailchimp = $this->container->get('MailChimpService');

        if ( isset ($input->familyId) && isset ($input->email) ){

            $family = FamilyQuery::create()->findPk($input->familyId);
            $isIncludedInMailing = $family->getSendNewsletter();

            if ($mailchimp->isLoaded()) {
                if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
                    return $response->withJson(['success' => true,'isIncludedInMailing' => ($isIncludedInMailing == 'TRUE')?true:false, 'mailChimpActiv' => true, 'statusLists' => $mailchimp->getListNameAndStatus($input->email)]);
                } else {
                    return $response->withJson(['success' => true,'isIncludedInMailing' => ($isIncludedInMailing == 'TRUE')?true:false, 'mailChimpActiv' => false, 'mailingList' => null]);
                }
            } else {
                return $response->withJson(['success' => true,'isIncludedInMailing' => ($isIncludedInMailing == 'TRUE')?true:false, 'mailChimpActiv' => false, 'mailingList' => null]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function getFamily (ServerRequest $request, Response $response, array $args): Response {
        $family = FamilyQuery::create()->findPk($args['familyId']);
        return $response->withJSON($family->toJSON());
    }

    public function familyInfo (ServerRequest $request, Response $response, array $args): Response {
        $values = (object)$request->getParsedBody();

        if ( isset ($values->familyId) )
        {
            $family = FamilyQuery::create()->findPk($values->familyId);
            return $response->write($family->toJSON());
        }

        return $response;
    }

    public function numbersOfAnniversaries (ServerRequest $request, Response $response, array $args): Response {
        return $response->withJson(MenuEventsCount::getNumberAnniversaries());
    }

    public function searchFamily (ServerRequest $request, Response $response, array $args): Response {
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

    public function selfRegisterFamily (ServerRequest $request, Response $response, array $args): Response {
        $families = FamilyQuery::create()
            ->filterByEnteredBy(Person::SELF_REGISTER)
            ->orderByDateEntered(Criteria::DESC)
            ->limit(100)
            ->find();
        return $response->withJSON(['families' => $families->toArray()]);
    }

    public function selfVerifyFamily (ServerRequest $request, Response $response, array $args): Response {
        $verifcationNotes = NoteQuery::create()
            ->filterByEnteredBy(Person::SELF_VERIFY)
            ->orderByDateEntered(Criteria::DESC)
            ->joinWithFamily()
            ->limit(100)
            ->find();
        return $response->withJSON(['families' => $verifcationNotes->toArray()]);
    }

    public function pendingSelfVerify(ServerRequest $request, Response $response, array $args): Response {
        $pendingTokens = TokenQuery::create()
            ->filterByType(Token::typeFamilyVerify)
            ->filterByRemainingUses(array('min' => 1))
            ->filterByValidUntilDate(array('min' => new \DateTime()))
            ->addJoin(TokenTableMap::COL_REFERENCE_ID, FamilyTableMap::COL_FAM_ID)
            ->withColumn(FamilyTableMap::COL_FAM_NAME, "FamilyName")
            ->withColumn(TokenTableMap::COL_REFERENCE_ID, "FamilyId")
            ->limit(100)
            ->find();
        return $response->withJSON(['families' => $pendingTokens->toArray()]);
    }

    public function byCheckNumberScan (ServerRequest $request, Response $response, array $args): Response {
        $scanString = $args['scanString'];

        $fService = $this->container->get('FinancialService');
        return $response->write($fService->getMemberByScanString($scanString));
    }

    public function postFamilyPhoto(ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( !( array_key_exists('familyId', $args) and isset($input->imgBase64) ) ) {
            return $response->withStatus(401);
        }

        $family = FamilyQuery::create()->findPk($args['familyId']);
        $family->setImageFromBase64($input->imgBase64);

        return $response->withJSON(array("status" => "success"));
    }

    public function deleteFamilyPhoto (ServerRequest $request, Response $response, array $args): Response {
        if ( !( array_key_exists('familyId', $args) ) ) {
            return $response->withStatus(401);
        }
        $family = FamilyQuery::create()->findPk($args['familyId']);
        return $response->withJson(["status" => $family->deletePhoto()]);
    }

    public function verifyFamily (ServerRequest $request, Response $response, array $args): Response {
        $familyId = $args["familyId"];
        $family = FamilyQuery::create()->findPk($familyId);
        if ($family != null) {
            TokenQuery::create()->filterByType("verifyFamily")->filterByReferenceId($family->getId())->delete();
            $token = new Token();
            $token->build("verifyFamily", $family->getId());
            $token->save();

            $tokenPassword = new TokenPassword();

            $password = MiscUtils::random_password(8);

            $tokenPassword->setTokenId($token->getPrimaryKey());
            $tokenPassword->setPassword(md5($password));
            $tokenPassword->setMustChangePwd(false);

            $tokenPassword->save();

            // we search the headPeople
            $headPeople = $family->getHeadPeople();

            $emails = [];

            foreach ($headPeople as $headPerson) {
                $emails[] = $headPerson->getEmail();
            }

            if (count($emails) == 0) {
                $emails = $family->getEmails();
            }

            $email = new FamilyVerificationEmail($emails, $family->getName(), $token->getToken(), $emails,  $password);
            if ($email->send()) {
                $family->createTimeLineNote("verify-link");
                $response = $response->withStatus(200);
            } else {
                $logger = $this->container->get('Logger');
                $logger->error($email->getError());
                throw new \Exception($email->getError());
            }
        } else {
            $response = $response->withStatus(404)->getBody()->write("familyId: " . $familyId . " not found");
        }
        return $response;
    }

    public function verifyFamilyPDF (ServerRequest $request, Response $response, array $args): Response {
        $familyId = $args["familyId"];
        $family = FamilyQuery::create()->findPk($familyId);
        if ($family != null) {
            $fams_to_contact = new EmailUsers([$familyId]);

            $familyEmailSent = $fams_to_contact->renderAndSend();

            return $response->withJson(["status" => $familyEmailSent]);
        } else {
            $response = $response->withStatus(404)->getBody()->write("familyId: " . $familyId . " not found");
        }
        return $response;
    }

    public function verifyFamilyNow (ServerRequest $request, Response $response, array $args): Response {
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

    public function verifyFamilyURL (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->famId) ) {
            $family = FamilyQuery::create()->findOneById($input->famId);
            $token = TokenQuery::create()
                ->filterByType("verifyFamily")
                ->findOneByReferenceId($family->getId());
            if (!is_null($token)) {
                $token->delete();
            }
            $token = new Token();
            $token->build("verifyFamily", $family->getId());
            $token->save();

            $tokenPassword = new TokenPassword();

            $password = MiscUtils::random_password(8);

            $tokenPassword->setTokenId($token->getPrimaryKey());
            $tokenPassword->setPassword(md5($password));
            $tokenPassword->setMustChangePwd(false);

            $tokenPassword->save();


            $family->createTimeLineNote("verify-URL");
            return $response->withJSON(["url" => "ident/my-profile/" . $token->getToken(), 'password' => $password]);
        }

        return $response;
    }

    public function familyActivateStatus (ServerRequest $request, Response $response, array $args): Response {
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

    public function familyGeolocation (ServerRequest $request, Response $response, array $args): Response {
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

    public function deleteFamilyField(ServerRequest $request, Response $response, array $args): Response {
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

    public function upactionFamilyField (ServerRequest $request, Response $response, array $args): Response {
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

    public function downactionFamilyField (ServerRequest $request, Response $response, array $args): Response {
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

    public function addressBook (ServerRequest $request, Response $response, array $args): Response {

        if ( !( SessionUser::getUser()->isSeePrivacyDataEnabled() and array_key_exists('famId', $args) ) ) {
            return $response->withStatus(401);
        }

        $fam = FamilyQuery::create()->findOneById($args['famId']);

        $filename = "Fam-".$fam->getName().".vcf";

        $output = $fam->getVCard();
        $size = strlen($output);

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Content-Length',$size)
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Expires', '0');

        $response->getBody()->write($output);
        return $response;
    }
}
