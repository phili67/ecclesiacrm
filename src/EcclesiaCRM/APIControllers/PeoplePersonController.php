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

// Person APIs
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\dto\Photo;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\MenuEventsCount;
use EcclesiaCRM\Emails\PersonVerificationEmail;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Token;
use EcclesiaCRM\TokenQuery;
use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\PersonVolunteerOpportunityQuery;
use EcclesiaCRM\PersonVolunteerOpportunity;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\UserQuery;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Emails\UpdateAccountEmail;
use EcclesiaCRM\Reports\EmailUsers;
use EcclesiaCRM\TokenPassword;

use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Map\PersonVolunteerOpportunityTableMap;
use EcclesiaCRM\Map\VolunteerOpportunityTableMap;

class PeoplePersonController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function generateRandomString($length = 15)
    {
        return substr(sha1(rand()), 0, $length);
    }

    public function photo (ServerRequest $request, Response $response, array $args): Response {
        if ( !array_key_exists('personId', $args) ) {
            return $response->withStatus(401);
        }

        $response=$this->container->get('CacheProvider')->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Person",$args['personId']);
        return $response->write($photo->getPhotoBytes())->withHeader('Content-type', $photo->getPhotoContentType());
    }

    public function thumbnail (ServerRequest $request, Response $response, array $args): Response {
        if ( !array_key_exists('personId', $args) ) {
            return $response->withStatus(401);
        }
        $response=$this->container->get('CacheProvider')->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Person",$args['personId']);
        return $response->write($photo->getThumbnailBytes())->withHeader('Content-type', $photo->getThumbnailContentType());
    }

    public function searchPerson (ServerRequest $request, Response $response, array $args): Response {
        $query = $args['query'];

        $searchLikeString = '%'.$query.'%';
        $people = PersonQuery::create();

        if (SystemConfig::getBooleanValue('bGDPR')) {
            $people->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
        }

        $people->filterByFirstName($searchLikeString, Criteria::LIKE)->
        _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
        _or()->filterByEmail($searchLikeString, Criteria::LIKE)->
        limit(SystemConfig::getValue("iSearchIncludePersonsMax"))->find();

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
    }

    public function searchSundaySchoolPerson (ServerRequest $request, Response $response, array $args): Response {
        $query = $args['query'];

        $searchLikeString = '%'.$query.'%';
        $people = PersonQuery::create();

        if (SystemConfig::getBooleanValue('bGDPR')) {
            $people->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
        }

        $people->filterByFirstName($searchLikeString, Criteria::LIKE)->
        _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
        _or()->filterByEmail($searchLikeString, Criteria::LIKE)->
        limit(SystemConfig::getValue("iSearchIncludePersonsMax"))->find();

        $id = 1;

        $return = [];
        foreach ($people as $person) {
            $values['id'] = $id++;
            $values['objid'] = $person->getId();
            $values['text'] = $person->getFullName()." (".$person->getAge().")";
            $values['uri'] = $person->getViewURI();

            array_push($return, $values);
        }

        return $response->withJson($return);
    }

    public function personCartView (ServerRequest $request, Response $response, array $args): Response
    {
        // Create array with Classification Information (lst_ID = 1)
        $ormClassifications = ListOptionQuery::Create()
            ->orderByOptionSequence()
            ->findById(1);

        unset($aClassificationName);
        $aClassificationName[0] = _('Unassigned');
        foreach ($ormClassifications as $ormClassification) {
            $aClassificationName[intval($ormClassification->getOptionId())] = $ormClassification->getOptionName();
        }

        // Create array with Family Role Information (lst_ID = 2)
        $ormClassifications = ListOptionQuery::Create()
            ->orderByOptionSequence()
            ->findById(2);

        unset($aFamilyRoleName);
        $aFamilyRoleName[0] = _('Unassigned');
        foreach ($ormClassifications as $ormClassification) {
            $aFamilyRoleName[intval($ormClassification->getOptionId())] = $ormClassification->getOptionName();
        }

        $ormCartItems = PersonQuery::Create()->leftJoinFamily()->orderByLastName()->Where('Person.Id IN ?',$_SESSION['aPeopleCart'])->find();

        $sEmailLink = '';
        $iEmailNum = 0;
        $email_array = [];

        $res = [];
        foreach ($ormCartItems as $person) {
            $sEmail = MiscUtils::SelectWhichInfo($person->getEmail(), !is_null($person->getFamily())?$person->getFamily()->getEmail():null, false);
            if (strlen($sEmail) == 0 && strlen($person->getWorkEmail()) > 0) {
                $sEmail = $person->getWorkEmail();
            }

            if (strlen($sEmail)) {
                $sValidEmail = _('Yes');
                if (!stristr($sEmailLink, $sEmail)) {
                    $email_array[] = $sEmail;

                    if ($iEmailNum == 0) {
                        // Comma is not needed before first email address
                        $sEmailLink .= $sEmail;
                        $iEmailNum++;
                    } else {
                        $sEmailLink .= SessionUser::getUser()->MailtoDelimiter() . $sEmail;
                    }
                }
            } else {
                $sValidEmail = _('No');
            }

            $sAddress1 = MiscUtils::SelectWhichInfo($person->getAddress1(), !is_null($person->getFamily())?$person->getFamily()->getAddress1():null, false);
            $sAddress2 = MiscUtils::SelectWhichInfo($person->getAddress2(), !is_null($person->getFamily())?$person->getFamily()->getAddress2():null, false);

            if (strlen($sAddress1) > 0 || strlen($sAddress2) > 0) {
                $sValidAddy = _('Yes');
            } else {
                $sValidAddy = _('No');
            }

            $personName = $person->getFirstName() . ' ' . $person->getLastName();
            $thumbnail = SystemURLs::getRootPath() . '/api/persons/' . $person->getId() . '/thumbnail';

            $res[] = ['personID'           => $person->getId(),
                'Address1'           => $sAddress1,
                'sAddress2'          => $sAddress2,
                'sEmail'             => $sEmail,
                'personName'         => $personName,
                'fullName'           => OutputUtils::FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 1),
                'thumbnail'          => $thumbnail,
                'sValidAddy'         => $sValidAddy,
                'sValidEmail'        => $sValidEmail,
                'ClassificationName' => $aClassificationName[$person->getClsId()],
                'FamilyRoleName'     => $aFamilyRoleName[$person->getFmrId()]
            ];
        }

        return $response->withJson(['CartPersons' => $res]);
    }

    public function volunteersPerPersonId(ServerRequest $request, Response $response, array $args): Response {
        return $response->write(VolunteerOpportunityQuery::Create()
            ->addJoin(VolunteerOpportunityTableMap::COL_VOL_ID,PersonVolunteerOpportunityTableMap::COL_P2VO_VOL_ID,Criteria::LEFT_JOIN)
            ->Where(PersonVolunteerOpportunityTableMap::COL_P2VO_PER_ID.' = '.$args['personID'])
            ->find()->toJson());
    }

    public function volunteersDelete(ServerRequest $request, Response $response, array $args): Response {
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
    }

    public function volunteersAdd(ServerRequest $request, Response $response, array $args): Response {
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
    }

    public function isMailChimpActivePerson (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        // we get the MailChimp Service
        $mailchimp = $this->container->get('MailChimpService');

        if ( isset ($input->personId) && isset ($input->email) ){
            $person = PersonQuery::create()->findPk($input->personId);

            if ($mailchimp->isLoaded()) {
                if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
                    return $response->withJson(['success' => true,'isIncludedInMailing' => ($person->getSendNewsletter() == 'TRUE')?true:false, 'mailChimpActiv' => true, 'statusLists' => $mailchimp->getListNameAndStatus($input->email)]);
                } else {
                    return $response->withJson(['success' => true,'isIncludedInMailing' => ($person->getSendNewsletter() == 'TRUE')?true:false, 'mailChimpActiv' => false, 'mailingList' => null]);
                }
            } else {
                return $response->withJson(['success' => true,'isIncludedInMailing' => ($person->getSendNewsletter() == 'TRUE')?true:false, 'mailChimpActiv' => false, 'mailingList' => null]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function personpropertiesPerPersonId (ServerRequest $request, Response $response, array $args): Response {
        if ( !array_key_exists('personID', $args) ) {
            return $response->withStatus(401);
        }

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

        return $response->write($ormAssignedProperties->toJSON());
    }

    public function numbersOfBirthDates (ServerRequest $request, Response $response, array $args): Response {
        return $response->withJson(MenuEventsCount::getNumberBirthDates());
    }

    public function postPersonPhoto (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( !( array_key_exists('personId', $args) and isset($input->imgBase64) ) ) {
            return $response->withStatus(401);
        }

        $person = PersonQuery::create()->findPk($args['personId']);
        $person->setImageFromBase64($input->imgBase64);

        return $response->withJSON(array("status" => "success"));
    }

    public function deletePersonPhoto (ServerRequest $request, Response $response, array $args): Response {
        if ( !( array_key_exists('personId', $args) ) ) {
            return $response->withStatus(401);
        }
        $person = PersonQuery::create()->findPk($args['personId']);
        return $response->withJSON(json_encode(array("status" => $person->deletePhoto())));
    }

    public function addPersonToCart (ServerRequest $request, Response $response, array $args): Response {
        if ( !( array_key_exists('personId', $args) and SessionUser::getUser()->isShowCartEnabled() ) ) {
            return $response->withStatus(401);
        }
        Cart::AddPerson($args['personId']);

        return $response->withJSON(array("status" => "success"));
    }

    public function deletePerson(ServerRequest $request, Response $response, array $args): Response {
        $sessionUser = SessionUser::getUser();
        if ( !( $sessionUser->isDeleteRecordsEnabled() and array_key_exists('personId', $args) ) ) {
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

        // if in mailchimp
        $mailchimp = $this->container->get('MailChimpService');

        if ( $mailchimp->isActive() ) {
            $memberStatus = $mailchimp->getListNameAndStatus( $person->getEmail() );

            if ( !empty ($memberStatus) ) {
                $res = $mailchimp->updateMember($memberStatus[0][2], "", "", $person->getEmail(), 'unsubscribed');
            }
        }

        $person->delete();

        return $response->withJSON(array("status" => "success"));
    }

    public function deletePersonField (ServerRequest $request, Response $response, array $args): Response {
        $values = (object)$request->getParsedBody();

        if (!(SessionUser::getUser()->isMenuOptionsEnabled() and isset($values->orderID) and isset($values->field) ) ) {
            return $response->withStatus(401);
        }

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
    }

    public function upactionPersonfield (ServerRequest $request, Response $response, array $args): Response {
        $values = (object)$request->getParsedBody();

        if (!( SessionUser::getUser()->isMenuOptionsEnabled() and isset ($values->orderID) and isset ($values->field) ) ) {
            return $response->withStatus(404);
        }

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
    }

    public function downactionPersonfield (ServerRequest $request, Response $response, array $args): Response {
        $values = (object)$request->getParsedBody();

        if (!(SessionUser::getUser()->isMenuOptionsEnabled() and isset ($values->orderID) and isset ($values->field) ) ) {
            return $response->withStatus(401);
        }

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
    }

    public function duplicateEmails(ServerRequest $request, Response $response, array $args): Response {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(401);
        }

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
    }

    public function notInMailChimpEmails (ServerRequest $request, Response $response, array $args): Response {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(401);
        }

        $mailchimp = $this->container->get('MailChimpService');

        if (!$mailchimp->isActive())
        {
            return $response->withRedirect(SystemURLs::getRootPath() . "/email/Dashboard.php");
        }

        if ($args['type'] == "families") {
            $families = FamilyQuery::create()
                ->filterByDateDeactivated(null)
                ->find();


            $missingEmailInMailChimp = array();
            foreach ($families as $family) {
                $persons = $family->getHeadPeople();
                foreach ($persons as $Person) {
                    $mailchimpList = $mailchimp->getListNameFromEmail($Person->getEmail());
                    if ($mailchimpList == '') {
                        array_push($missingEmailInMailChimp, ["id" => $Person->getId(), "url" => '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $family->getId() . '">' . $family->getSaluation() . '</a>', "email" => $Person->getEmail()]);
                    }
                }
            }
        } else if ($args['type'] == "persons") {
            $People = PersonQuery::create()
                ->filterByDateDeactivated(null)
                ->filterByEmail(null, Criteria::NOT_EQUAL)
                ->orderByDateLastEdited(Criteria::DESC)
                ->find();

            $missingEmailInMailChimp = array();
            foreach ($People as $Person) {
                $mailchimpList = $mailchimp->getListNameFromEmail($Person->getEmail());
                if ($mailchimpList == '') {
                    array_push($missingEmailInMailChimp, ["id" => $Person->getId(), "url" => '<a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $Person->getId() . '">' . $Person->getFullName() . '</a>', "email" => $Person->getEmail()]);
                }
            }
        }

        return $response->withJson(["emails" => $missingEmailInMailChimp]);
    }

    public function activateDeacticate (ServerRequest $request, Response $response, array $args): Response {
        if (!SessionUser::getUser()->isDeleteRecordsEnabled()) {
            return $response->withStatus(401);
        }

        $personId = $args["personId"];
        $newStatus = $args["status"];

        $person = PersonQuery::create()->findPk($personId);
        $currentStatus = (empty($person->getDateDeactivated()) ? 'true' : 'false');

        //update only if the status is different

        // note : When a user is deactivated the associated person is deactivated too
        //        but when a person is deactivated the user is deactivated too.
        //        Important : a person re-activated don't reactivate the user

        if ($currentStatus != $newStatus) {
            if ($newStatus == 'false') {
                $user = UserQuery::create()->findPk($personId);

                if (!is_null($user)) {
                    $user->setIsDeactivated(true);
                    $user->save();

                    // a mail is notified
                    $email = new UpdateAccountEmail($user, _("Account Deactivated"));
                    $email->send();

                    //Create a note to record the status change
                    $note = new Note();
                    $note->setPerId($user->getPersonId());

                    $note->setText(_('Account Deactivated'));
                    $note->setType('edit');
                    $note->setEntered(SessionUser::getUser()->getPersonId());
                    $note->save();
                }

                $family = FamilyQuery::create()->findOneById($person->getFamId());
                if (!is_null($family)) {
                    $members = $family->getActivatedPeople();
                    if (count($members) == 1) {
                        $family->setDateDeactivated(date('YmdHis'));
                        $family->save();
                    }
                }

                $person->setDateDeactivated(date('YmdHis'));

            } elseif ($newStatus == 'true') {                
                $family = FamilyQuery::create()->findOneById($person->getFamId());
                if (!is_null($family)) {
                    $members = $family->getActivatedPeople();
                    $family->setDateDeactivated(NULL);
                    $family->save();                    
                }

                $person->setDateDeactivated(Null);
            }

            $mailchimp = $this->container->get('MailChimpService');

            if ( $mailchimp->isActive() ) {
                $memberStatus = $mailchimp->getListNameAndStatus( $person->getEmail() );

                if ( !empty ($memberStatus) ) {

                    if ( $newStatus == 'false' ) {
                        $res = $mailchimp->updateMember($memberStatus[0][2], "", "", $person->getEmail(), 'unsubscribed');
                    } else {
                        $res = $mailchimp->updateMember($memberStatus[0][2], "", "", $person->getEmail(), 'subscribed');
                    }
                }
            }

            $person->save();

            // a one person family is deactivated too
            if ($person->getFamily()->getPeople()->count() == 1) {
                $person->getFamily()->setDateDeactivated(($newStatus == "false")?date('YmdHis'):Null);
                $person->getFamily()->save();
            }

            //Create a note to record the status change
            $note = new Note();
            $note->setPerId($personId);
            $note->setText(($newStatus == 'false')?_('Person Deactivated'):_('Person Activated'));
            $note->setType('edit');
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->save();
        }
        return $response->withJson(['success' => true]);
    }

    public function saveNoteAsWordFile (ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->personId) && isset ($input->noteId) ) {
            $user = UserQuery::create()->findPk($input->personId);

            $actualNote = NoteQuery::Create()->findOneById ($input->noteId);


            if ( !is_null($user) && !is_null($actualNote) ) {
                $realNoteDir = $userDir = $user->getUserRootDir();
                $userName    = $user->getUserName();
                $currentpath = $user->getCurrentpath();

                // [SAVE HTML TO Word file FILE ON THE SERVER]
                $result = MiscUtils::saveHtmlAsWordFilePhpWord( $userName, $realNoteDir, $currentpath, $actualNote->getText() );
                //$result = MiscUtils::saveHtmlAsWordFile( $userName, $realNoteDir, $currentpath, $actualNote->getText() );
                $title    = $result['title'];
                $tmpFile  = $result['tmpFile'];

                // now we create the note
                $note = new Note();
                $note->setPerId($input->personId);
                $note->setFamId(0);
                $note->setTitle($tmpFile);
                $note->setPrivate(1);
                $note->setText($userName . $currentpath . $title.".docx");
                $note->setType('file');
                $note->setEntered(SessionUser::getUser()->getPersonId());
                $note->setInfo(_('Create file'));

                $note->save();

                return $response->withJson(['success' => true, 'title' => $title ]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addressBook (ServerRequest $request, Response $response, array $args): Response {
        if ( !( array_key_exists('personId', $args) ) ) {
            return $response->withStatus(401);
        }

        $person = PersonQuery::create()->findOneById($args['personId']);

        $filename = $person->getLastName()."_".$person->getFirstName().".vcf";

        $output = $person->getVCard();
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

    public function verifyPerson (ServerRequest $request, Response $response, array $args): Response {
        $personId = $args["personId"];
        $person = PersonQuery::create()->findPk($personId);
        if ($person != null) {
            TokenQuery::create()->filterByType("verifyPerson")->filterByReferenceId($person->getId())->delete();
            $token = new Token();
            $token->build("verifyPerson", $person->getId());
            $token->save();

            $tokenPassword = new TokenPassword();

            $password = MiscUtils::random_password(8);

            $tokenPassword->setTokenId($token->getPrimaryKey());
            $tokenPassword->setPassword(md5($password));
            $tokenPassword->setMustChangePwd(false);

            $tokenPassword->save();

            $emails = [];

            if ($person->getEmail() == "") {
                $emails = $person->getFamily()->getEmails();
            } else {
                $emails = [$person->getEmail()];
            }
            
            $email = new PersonVerificationEmail($emails, $person->getFirstName(), $person->getLastName(), $token->getToken(), $emails, $password);
            if ($email->send()) {
                $person->createTimeLineNote("verify-link");
                $response = $response->withStatus(200);
            } else {
                $logger = $this->container->get('Logger');
                $logger->error($email->getError());
                throw new \Exception($email->getError());
            }
        } else {
            $response = $response->withStatus(404)->getBody()->write("personId: " . $personId . " not found");
        }
        return $response;
    }

    public function verifyPersonPDF (ServerRequest $request, Response $response, array $args): Response {
        $personId = $args["personId"];
        $person = PersonQuery::create()->findPk($personId);
        if ($person != null) {
            $person_to_contact = new EmailUsers(null, [(int)$personId]);

            $personEmailSent = $person_to_contact->renderAndSend('person');

            return $response->withJson(["status" => $personEmailSent]);
        } else {
            $response = $response->withStatus(404)->getBody()->write("personId: " . $personId . " not found");
        }
        return $response;
    }

    public function verifyPersonNow (ServerRequest $request, Response $response, array $args): Response {
        $personId = $args["personId"];
        $person = PersonQuery::create()->findPk($personId);
        if ($person != null) {
            $person->verify();
            $response = $response->withStatus(200);
        } else {
            $response = $response->withStatus(404)->getBody()->write("personId: " . $personId . " not found");
        }
        return $response;
    }

    public function verifyPersonURL (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->perId) ) {
            $person = PersonQuery::create()->findOneById($input->perId);
            $token = TokenQuery::create()
                ->filterByType("verifyPerson")
                ->findOneByReferenceId($person->getId());
            if (!is_null($token)) {
                $token->delete();
            }
            $token = new Token();
            $token->build("verifyPerson", $person->getId());
            $token->save();

            $tokenPassword = new TokenPassword();

            $password = MiscUtils::random_password(8);

            $tokenPassword->setTokenId($token->getPrimaryKey());
            $tokenPassword->setPassword(md5($password));
            $tokenPassword->setMustChangePwd(false);

            $tokenPassword->save();


            $person->createTimeLineNote("verify-URL");
            return $response->withJSON(["url" => "ident/my-profile/" . $token->getToken(), 'password' => $password]);
        }

        return $response;
    }

    
    public function resetConfirmDatas (ServerRequest $request, Response $response, array $args): Response {
        $persons = null;

        if ( isset ($args['state']) ) {
            switch ($args['state']) {
                case "pending":
                    $persons = PersonQuery::create()->findByConfirmReport('Pending');                    
                    break;
                case "done":
                    $persons = PersonQuery::create()->findByConfirmReport('Done');
                    break;
            }

            if (!is_null($persons)) {
                foreach ($persons as $person) {
                    $token = TokenQuery::create()
                        ->filterByType("verifyPerson")
                        ->findOneByReferenceId($person->getId());
                    if (!is_null($token)) {
                        $token->delete();
                    }
                
                    $person->createTimeLineNote("verify-URL-reset");

                    $person->setConfirmReport('No');
                    $person->save();
                }

                return $response->withJson(['success' => true]);
            }
        }

        return $response->withJson(['success' => false]);
    }
}
