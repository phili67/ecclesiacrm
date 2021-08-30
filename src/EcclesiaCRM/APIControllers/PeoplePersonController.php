<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\Utils\LoggerUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// Person APIs
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\dto\Photo;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\MenuEventsCount;

use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\PersonVolunteerOpportunityQuery;
use EcclesiaCRM\PersonVolunteerOpportunity;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Emails\UpdateAccountEmail;

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

    public function photo (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $res=$this->container->get('CacheProvider')->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Person",$args['personId']);
        return $res->write($photo->getPhotoBytes())->withHeader('Content-type', $photo->getPhotoContentType());
    }

    public function thumbnail (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $res=$this->container->get('CacheProvider')->withExpires($response, MiscUtils::getPhotoCacheExpirationTimestamp());
        $photo = new Photo("Person",$args['personId']);
        return $res->write($photo->getThumbnailBytes())->withHeader('Content-type', $photo->getThumbnailContentType());
    }

    public function searchPerson (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function personCartView (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
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

    public function volunteersPerPersonId(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        return $response->write(VolunteerOpportunityQuery::Create()
            ->addJoin(VolunteerOpportunityTableMap::COL_VOL_ID,PersonVolunteerOpportunityTableMap::COL_P2VO_VOL_ID,Criteria::LEFT_JOIN)
            ->Where(PersonVolunteerOpportunityTableMap::COL_P2VO_PER_ID.' = '.$args['personID'])
            ->find()->toJson());
    }

    public function volunteersDelete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function volunteersAdd(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function isMailChimpActivePerson (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->personId) && isset ($input->email)){

            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');

            $person = PersonQuery::create()->findPk($input->personId);

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
                return $response->withJson(['success' => true,'isIncludedInMailing' => ($person->getSendNewsletter() == 'TRUE')?true:false, 'mailChimpActiv' => true, 'statusLists' => $mailchimp->getListNameAndStatus($input->email)]);
            } else {
                return $response->withJson(['success' => true,'isIncludedInMailing' => ($person->getSendNewsletter() == 'TRUE')?true:false, 'mailChimpActiv' => false, 'mailingList' => null]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function personpropertiesPerPersonId (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function numbersOfBirthDates (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        return $response->withJson(MenuEventsCount::getNumberBirthDates());
    }

    public function postPersonPhoto (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object)$request->getParsedBody();
        $person = PersonQuery::create()->findPk($args['personId']);
        $person->setImageFromBase64($input->imgBase64);
        return $response->withJSON(array("status" => "success"));
    }

    public function deletePersonPhoto (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $person = PersonQuery::create()->findPk($args['personId']);
        return json_encode(array("status" => $person->deletePhoto()));
    }

    public function addPersonToCart (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        Cart::AddPerson($args['personId']);

        return $response->withJSON(array("status" => "success"));
    }

    public function deletePerson(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        /**
         * @var \EcclesiaCRM\User $sessionUser
         */
        $sessionUser = SessionUser::getUser();
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

    public function deletePersonField (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
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
    }

    public function upactionPersonfield (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
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
    }

    public function downactionPersonfield (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
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
    }

    public function duplicateEmails(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

    public function notInMailChimpEmails (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

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
                        array_push($missingEmailInMailChimp, ["id" => $Person->getId(), "url" => '<a href="' . SystemURLs::getRootPath() . '/FamilyView.php?FamilyID=' . $family->getId() . '">' . $family->getSaluation() . '</a>', "email" => $Person->getEmail()]);
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
                    array_push($missingEmailInMailChimp, ["id" => $Person->getId(), "url" => '<a href="' . SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . $Person->getId() . '">' . $Person->getFullName() . '</a>', "email" => $Person->getEmail()]);
                }
            }
        }

        return $response->withJson(["emails" => $missingEmailInMailChimp]);
    }

    public function activateDeacticate (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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

                $person->setDateDeactivated(date('YmdHis'));

            } elseif ($newStatus == 'true') {
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

    public function saveNoteAsWordFile (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->personId) && isset ($input->noteId) ) {
            $user = UserQuery::create()->findPk($input->personId);

            $actualNote = NoteQuery::Create()->findOneById ($input->noteId);


            if ( !is_null($user) && !is_null($actualNote) ) {
                $realNoteDir = $userDir = $user->getUserRootDir();
                $userName    = $user->getUserName();
                $currentpath = $user->getCurrentpath();

                $pw = new \PhpOffice\PhpWord\PhpWord();

                // [THE HTML]
                $section = $pw->addSection();
                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $actualNote->getText(), false, false);

                $title = "note_".$this->generateRandomString(5);

                // we set a random title

                // [SAVE FILE ON THE SERVER]
                $tmpFile = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$title.".docx";
                $pw->save($tmpFile, "Word2007");

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

    public function addressBook (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
}
