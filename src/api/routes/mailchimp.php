<?php

/* Copyright Philippe Logel not MIT */
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


// Routes
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Service\MailChimpService;
use Propel\Runtime\ActiveQuery\Criteria;

use PHPMailer\PHPMailer\PHPMailer;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;


$app->group('/mailchimp', function (RouteCollectorProxy $group) {

    $group->get('/search/{query}', \MailchimpController::class . ':searchList' );
    $group->get('/list/{listID}', \MailchimpController::class . ':oneList' );
    $group->get('/lists', \MailchimpController::class . ':lists' );
    $group->get('/listmembers/{listID}', \MailchimpController::class . ':listmembers' );
    $group->post('/createlist', \MailchimpController::class . ':createList' );
    $group->post('/modifylist', \MailchimpController::class . ':modifyList' );
    $group->post('/deleteallsubscribers', \MailchimpController::class . ':deleteallsubscribers' );
    $group->post('/deletelist', \MailchimpController::class . ':deleteList' );

    $group->post('/list/removeTag', \MailchimpController::class . ':removeTag' );
    $group->post('/list/removeAllTagsForMembers', \MailchimpController::class . ':removeAllTagsForMembers' );
    $group->post('/list/addTag', \MailchimpController::class . ':addTag' );
    $group->post('/list/getAllTags', \MailchimpController::class . ':getAllTags' );
    $group->post('/list/removeTagForMembers', \MailchimpController::class . ':removeTagForMembers' );

    $group->post('/campaign/actions/create', \MailchimpController::class . ':campaignCreate' );
    $group->post('/campaign/actions/delete', \MailchimpController::class . ':campaignDelete' );
    $group->post('/campaign/actions/send', \MailchimpController::class . ':campaignSend' );
    $group->post('/campaign/actions/save', \MailchimpController::class . ':campaignSave' );
    $group->get('/campaign/{campaignID}/content', \MailchimpController::class . ':campaignContent' );

    $group->post('/status', \MailchimpController::class . ':statusList' );
    $group->post('/suppress', \MailchimpController::class . ':suppress' );
    $group->post('/suppressMembers', \MailchimpController::class . ':suppressMembers' );
    $group->post('/addallnewsletterpersons', \MailchimpController::class . ':addallnewsletterpersons' );
    $group->post('/addallpersons', \MailchimpController::class . ':addallpersons' );
    $group->post('/addperson', \MailchimpController::class . ':addPerson' );
    $group->post('/addfamily', \MailchimpController::class . ':addFamily' );
    $group->post('/addAllFamilies', \MailchimpController::class . ':addAllFamilies' );
    $group->post('/addgroup', \MailchimpController::class . ':addGroup' );

    $group->post('/testConnection', \MailchimpController::class . ':testEmailConnectionMVC' );

});


class MailchimpController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    // classic lists managements functions
    public function searchList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $query = $args['query'];
        $resultsArray = [];

        $id = 1;
// all person in the CRM
        if ($query == '*' || mb_strtolower($query) == _("persons") ||  mb_strtolower($query) == _("people")) {
            $elt = ['id'=>$id++,
                'text'=>"*",
                'typeId' => 1
            ];

            $data = ['children' => [$elt],
                'id' => 0,
                'text' => _('All People')];

            array_push($resultsArray, $data);
        }


// add all person from the newsletter
        if (mb_strpos("newsletter",mb_strtolower($query)) !== false) {
            $elt = ['id'=>$id++,
                'text'=>"newsletter",
                'typeId' => 2
            ];

            $data = ['children' => [$elt],
                'id' => 1,
                'text' => _('newsletter')];

            array_push($resultsArray, $data);
        }

        // all person in the CRM
        if (mb_strtolower($query) == _('families')) {
            $elt = ['id'=>$id++,
                'text'=>"families",
                'typeId' => 3
            ];

            $data = ['children' => [$elt],
                'id' => 0,
                'text' => _('All Families')];

            array_push($resultsArray, $data);
        }



// Person Search
        try {
            $searchLikeString = '%'.$query.'%';
            $people = PersonQuery::create()->
            filterByFirstName($searchLikeString, Criteria::LIKE)->
            _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
            limit(SystemConfig::getValue("iSearchIncludePersonsMax"))->find();

            if (!empty($people))
            {
                $data = [];
                $id++;

                foreach ($people as $person) {
                    if ($person->getDateDeactivated() != null)
                        continue;

                    $elt = ['id'=>$id++,
                        'text'=>$person->getFullName(),
                        'personID'=>$person->getId()];

                    array_push($data, $elt);
                }

                if (!empty($data))
                {
                    $dataPerson = ['children' => $data,
                        'id' => 2,
                        'text' => _('Persons')];

                    $resultsArray = array ($dataPerson);
                }
            }
        } catch (Exception $e) {
            $logger = LoggerUtils::getAppLogger();
            $logger->warn($e->getMessage());
        }

// Family search
        try {
            $families = FamilyQuery::create()
                ->filterByName("%$query%", Criteria::LIKE)
                ->limit(SystemConfig::getValue("iSearchIncludeFamiliesMax"))
                ->find();

            if ( $families->count() > 0 )
            {
                $data = [];
                $id++;

                foreach ($families as $family)
                {
                    if ($family->getDateDeactivated() != null)
                        continue;

                    $searchArray=[
                        "id" => $id++,
                        "text" => $family->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                        'familyID'=>$family->getId()
                    ];

                    array_push($data,$searchArray);
                }

                if (!empty($data))
                {
                    $dataFamilies = ['children' => $data,
                        'id' => 3,
                        'text' => _('Families')];

                    array_push($resultsArray, $dataFamilies);
                }
            }
        } catch (Exception $e) {
            $logger = LoggerUtils::getAppLogger();
            $logger->warn($e->getMessage());
        }

// Group Search
        try {
            $groups = GroupQuery::create()
                ->filterByName("%$query%", Criteria::LIKE)
                ->limit(SystemConfig::getValue("iSearchIncludeGroupsMax"))
                ->withColumn('grp_Name', 'displayName')
                ->withColumn('grp_ID', 'id')
                ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri')
                ->select(['displayName', 'uri', 'id'])
                ->find();

            if (!empty($groups))
            {
                $data = [];
                $id++;

                foreach ($groups as $group) {
                    $elt = ['id'=>$id++,
                        'text'=>$group['displayName'],
                        'groupID'=>$group['id']];

                    array_push($data, $elt);
                }

                if (!empty($data))
                {
                    $dataGroup = ['children' => $data,
                        'id' => 4,
                        'text' => _('Groups')];

                    array_push($resultsArray, $dataGroup);
                }
            }
        } catch (Exception $e) {
            $logger = LoggerUtils::getAppLogger();
            $logger->warn($e->getMessage());
        }

        return $response->withJson(array_filter($resultsArray));
    }

    public function oneList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $mailchimp = $this->container->get('MailChimpService');

        $list      = $mailchimp->getListFromListId ($args['listID']);
        $campaign  = $mailchimp->getCampaignsFromListId($args['listID']);

        return $response->withJSON(['MailChimpList' => $list,'MailChimpCampaign' => $campaign,'membersCount' => count($mailchimp->getListMembersFromListId($args['listID']))]);
    }

    public function lists (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withJSON(['isActive' => false]);
        }

        $mailchimp = $this->container->get('MailChimpService');

        $isActive = $mailchimp->isActive();

        if ($isActive == false) {
            return $response->withJSON(['isActive' => $isActive]);
        }

        $isLoaded = $mailchimp->isLoaded();

        $lists = $mailchimp->getLists();

        $campaigns = [];

        foreach ($lists as $list){
            $campaigns[] = $mailchimp->getCampaignsFromListId($list['id']);
        }

        return $response->withJSON(['MailChimpLists' => $mailchimp->getLists(),'MailChimpCampaigns' => $campaigns, 'firstLoaded' => !$isLoaded, 'isActive' => $isActive]);
    }

    public function listmembers (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $mailchimp = $this->container->get('MailChimpService');

        return $response->withJSON(['MailChimpMembers' => $mailchimp->getListMembersFromListId($args['listID']), 'id' => $args['listID']]);
    }

    public function createList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {

        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->ListTitle) && isset ($input->Subject) && isset ($input->PermissionReminder) && isset ($input->ArchiveBars) && isset ($input->Status) ){
            $mailchimp = $this->container->get('MailChimpService');

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
                $res = $mailchimp->createList($input->ListTitle, $input->Subject, $input->PermissionReminder, $input->ArchiveBars, $input->Status);

                if ( !array_key_exists ('title',$res) ) {
                    return $response->withJson(['success' => true, "result" => $res]);
                } else {
                    return $response->withJson(['success' => false, "error" => $res]);
                }
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function modifyList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->name) && isset ($input->subject) && isset ($input->permission_reminder) ){
            $mailchimp = $this->container->get('MailChimpService');

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
                $res = $mailchimp->changeListName($input->list_id, $input->name, $input->subject, $input->permission_reminder);

                if ( !array_key_exists ('title',$res) ) {
                    return $response->withJson(['success' => true, "result" => $res]);
                } else {
                    return $response->withJson(['success' => false, "error" => $res]);
                }
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function deleteallsubscribers (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){
            $mailchimp = $this->container->get('MailChimpService');

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
                $res = $mailchimp->deleteAllMembers($input->list_id);

                if ( !array_key_exists ('title',$res) ) {
                    return $response->withJson(['success' => true, "result" => $res]);
                } else {
                    return $response->withJson(['success' => false, "error" => $res]);
                }
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function deleteList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){
            $mailchimp = $this->container->get('MailChimpService');

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
                $res = $mailchimp->deleteList($input->list_id);

                if ( gettype($res) == 'boolean' && $res == true ) {
                    return $response->withJson(['success' => true, "result" => $res]);
                } else {
                    return $response->withJson(['success' => false, "error" => $res]);
                }
            }
        }

        return $response->withJson(['success' => false]);
    }

    // tags managements
    public function addTag (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->tag) && isset ($input->name) && isset ($input->emails) ){
            $mailchimp = $this->container->get('MailChimpService');

            if ($input->tag != -1) {
                $res = $mailchimp->addMembersToSegment($input->list_id, $input->tag, $input->emails);

                if ( !array_key_exists ('title',$res) ) {
                    return $response->withJson(['success' => true, "result" => $res]);
                } else {
                    return $response->withJson(['success' => false, "error" => $res]);
                }
            } else {
                $res = $mailchimp->createSegment($input->list_id, $input->name,$input->emails);
                if ( !array_key_exists ('title',$res) ) {
                    return $response->withJson(['success' => true, "result" => $res]);
                } else {
                    return $response->withJson(['success' => false, "error" => $res]);
                }
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function getAllTags (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){
            $mailchimp = $this->container->get('MailChimpService');

            $list = $mailchimp->getListFromListId ($input->list_id);
            if ( !array_key_exists ('title',$list) ) {
                return $response->withJson(['success' => true, "result" => $list['tags']]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function removeTag (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->tag_ID) ){
            $mailchimp = $this->container->get('MailChimpService');

            $res = $mailchimp->deleteSegment($input->list_id, $input->tag_ID);

            if ( gettype($res) == 'boolean' && $res == true ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }
    }

    public function removeTagForMembers (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->tag) && isset ($input->emails) ){
            $mailchimp = $this->container->get('MailChimpService');

            $res = $mailchimp->removeMembersFromSegment($input->list_id, $input->tag, $input->emails);

            if ( !array_key_exists ('title',$res) ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function removeAllTagsForMembers (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->emails) ){
            $mailchimp = $this->container->get('MailChimpService');

            $res = $mailchimp->removeMembersFromAllSegments($input->list_id, $input->emails);

            if ( !array_key_exists ('title',$res) ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }


    // Campaigns
    public function campaignCreate (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->subject) && isset ($input->title) && isset ($input->htmlBody) && isset ($input->tagId) ){
            $mailchimp = $this->container->get('MailChimpService');

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ){
                $res = $mailchimp->createCampaign($input->list_id, $input->tagId, $input->subject, $input->title, $input->htmlBody);

                if ( !array_key_exists ('title',$res) ) {
                    return $response->withJson(['success' => true, "result" => $res]);
                } else {
                    return $response->withJson(['success' => false, "error" => $res]);
                }
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function campaignDelete (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->campaign_id) ){

            $mailchimp = $this->container->get('MailChimpService');

            $res = $mailchimp->deleteCampaign ($input->campaign_id);

            if ( gettype($res) == 'boolean' && $res == true ) {
                return $response->withJson(['success' => true,'content' => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function campaignSend (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->campaign_id) ){

            $mailchimp = $this->container->get('MailChimpService');

            $res = $mailchimp->sendCampaign ($input->campaign_id);

            if ( !array_key_exists ('title',$res) ) {
                return $response->withJson(['success' => true,'content' => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function campaignSave (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->campaign_id)
            && isset ($input->subject)
            && isset ($input->content)
            && isset ($input->oldStatus) ) {

            $mailchimp = $this->container->get('MailChimpService');

            $res1 = $mailchimp->setCampaignContent ($input->campaign_id,$input->content);
            $res2 = $mailchimp->setCampaignMailSubject ($input->campaign_id,$input->subject);

            $status = "save";

            $res3 = null;

            if ( ( $input->oldStatus == "save" || $input->oldStatus == "paused" ) && $input->isSchedule) {
                $res3 = $mailchimp->setCampaignSchedule ($input->campaign_id,$input->realScheduleDate, "false", "false");
                $status = "schedule";
            } else if ( $input->oldStatus == "schedule" ) {
                $res3 = $mailchimp->setCampaignUnschedule ($input->campaign_id);
                $status = "paused";
            }

            if ( !array_key_exists ('title',$res1) && !array_key_exists ('title',$res2) && !is_null($res3) && gettype($res3) == 'boolean' ) {
                return $response->withJson(['success' => true,'content' => $res1,'subject' => $res2, 'schedule' => $res3, 'status' => _($status)]);
            } else {
                return $response->withJson(['success' => false, "error1" => $res1, "error2" => $res2, "error3" => $res3]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function campaignContent(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $mailchimp = $this->container->get('MailChimpService');

        $campaignContent = $mailchimp->getCampaignContent ($args['campaignID']);

        // Be careFull this can change with a new MailChimp api
        $realContent = explode("            <center>\n                <br/>\n                <br/>\n",$campaignContent['html'])[0];

        if ( !empty($campaignContent['html']) ) {
            return $response->withJson(['success' => true,'content' => $realContent]);
        }

        return $response->withJson(['success' => false]);

        return $response;
    }

    public function statusList(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->status) && isset ($input->list_id) && isset ($input->email) ){

            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');

            $res = $mailchimp->updateMember($input->list_id,"","",$input->email,$input->status);

            if ( !array_key_exists ('title',$res) ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    // members management
    public function suppress (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->email) ){

            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');

            $res = $mailchimp->deleteMember($input->list_id,$input->email);

            if ( gettype($res) == 'boolean' and $res == true  ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function suppressMembers (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->emails) ){

            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');

            foreach ($input->emails as $email) {
                $res = $mailchimp->deleteMember($input->list_id,$email);
            }

            if ( !array_key_exists ('title',$res) ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addallnewsletterpersons (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');

            if ( !is_null ($mailchimp) && $mailchimp->isActive() /*&& !is_null($person) && $mailchimp->getListNameFromEmail($person->getEmail()) == ''*/ ) {
                $list           = $mailchimp->getListFromListId($input->list_id);
                $listID         = $input->list_id;

                $onlyPersons = PersonQuery::Create()// Get the persons and not the family with the news letter
                ->leftJoinFamily()
                    ->addAsColumn('FamName',FamilyTableMap::COL_FAM_NAME)
                    ->addAsColumn('FamEmail',FamilyTableMap::COL_FAM_EMAIL)
                    ->filterBySendNewsletter('TRUE')
                    ->_and()
                    ->useFamilyQuery()
                    ->filterBySendNewsletter('FALSE')
                    ->endUse()
                    ->find();

                $onlyFamilyPersons = PersonQuery::Create() // Get only the head and spouse of each families when they don't want to have the newsletter as a family
                ->leftJoinFamily()
                    ->addAsColumn('FamName',FamilyTableMap::COL_FAM_NAME)
                    ->addAsColumn('FamEmail',FamilyTableMap::COL_FAM_EMAIL)
                    ->filterBySendNewsletter('FALSE')
                    ->filterByFmrId (SystemConfig::getValue("sDirRoleHead"))// get the Head
                    ->_or()->filterByFmrId (SystemConfig::getValue("sDirRoleSpouse")) // get spouse
                    ->_and()
                    ->useFamilyQuery()
                    ->filterBySendNewsletter('TRUE')
                    ->endUse()
                    ->find();

                $persons = array_merge ($onlyPersons->toArray(),$onlyFamilyPersons->toArray());


                $resError = [];

                $numberOfPerson = 0;
                $count = 0;

                foreach ($persons as $per) {
                    $person = PersonQuery::Create()->findOneById ($per['Id']);
                    if (strlen($person->getEmail()) > 0) {
                        $numberOfPerson++;

                        if (SystemConfig::getValue("iMailChimpApiMaxMembersCount") < $numberOfPerson) {
                            $new_List = $mailchimp->createList($list['name'].'_'.time(), $list['campaign_defaults']['subject'], $list['permission_reminder'], isset($list['use_archive_bar']), $list['visibility']);
                            $listID   = $new_List['id'];

                            $numberOfPerson = 0;
                        }

                        $merge_fields = ['FNAME'=>$person->getFirstName(), 'LNAME'=>$person->getLastName()];

                        $address = $person->getAddressForMailChimp();

                        if ( !is_null ($address) && SystemConfig::getBooleanValue('bMailChimpWithAddressPhone') ) {
                            $merge_fields['ADDRESS'] = $person->getAddressForMailChimp();
                        }

                        $phone = $person->getHomePhone();

                        if ( !is_null ($phone) && SystemConfig::getBooleanValue('bMailChimpWithAddressPhone') ) {
                            $merge_fields['PHONE']   = $phone;
                        }

                        $data = array(
                            "apikey"        => SystemConfig::getValue("sMailChimpApiKey"),
                            "email_address" => $person->getEmail(),
                            "status"        => "subscribed",
                            "merge_fields"  => $merge_fields
                        );

                        $json_data = json_encode($data);

                        $allUsers[] = array(
                            "method" => "POST",
                            "path" => "/lists/" . $listID . "/members/",
                            "body" => $json_data
                        );
                    }

                    $count++;
                }

                $array = array(
                    "operations" => $allUsers
                );

                $res = $mailchimp->sendAllMembers($array);

                if ( array_key_exists ('title',$res) ) {
                    $resError[] = $res;
                }

                sleep ( (int)($count*3)/10 );

                $mailchimp->reloadMailChimpDatas();

                if ( count($resError) > 0) {
                    return $response->withJson(['success' => false, "error" => $resError]);
                } else {
                    return $response->withJson(['success' => true, "result" => $res]);
                }

            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addallpersons (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');

            if ( !is_null ($mailchimp) && $mailchimp->isActive() /*&& !is_null($person) && $mailchimp->getListNameFromEmail($person->getEmail()) == ''*/ ) {
                $persons = PersonQuery::create()
                    ->filterByEmail(null, Criteria::NOT_EQUAL)
                    ->_or()->filterByWorkEmail(null, Criteria::NOT_EQUAL)
                    ->find();

                $resError = [];

                $numberOfPerson = 0;
                $list           = $mailchimp->getListFromListId($input->list_id);
                $listID         = $input->list_id;

                $allUsers = [];

                $count = 0;

                foreach ($persons as $person) {
                    if (strlen($person->getEmail()) > 0) {
                        $numberOfPerson++;

                        if (SystemConfig::getValue("iMailChimpApiMaxMembersCount") < $numberOfPerson) {
                            $new_List = $mailchimp->createList($list['name'].'_'.time(), $list['campaign_defaults']['subject'], $list['permission_reminder'], isset($list['use_archive_bar']), $list['visibility']);
                            $listID   = $new_List['id'];
                            $numberOfPerson = 0;
                        }

                        $merge_fields = ['FNAME'=>$person->getFirstName(), 'LNAME'=>$person->getLastName()];

                        $address = $person->getAddressForMailChimp();

                        if ( !is_null ($address) && SystemConfig::getBooleanValue('bMailChimpWithAddressPhone') ) {
                            $merge_fields['ADDRESS'] = $address;
                        }

                        $phone = $person->getHomePhone();

                        if ( !is_null ($phone) && SystemConfig::getBooleanValue('bMailChimpWithAddressPhone') ) {
                            $merge_fields['PHONE']   = $address;
                        }

                        $data = array(
                            "apikey"        => SystemConfig::getValue("sMailChimpApiKey"),
                            "email_address" => $person->getEmail(),
                            "status"        => "subscribed",
                            "merge_fields"  => $merge_fields
                        );

                        $json_data = json_encode($data);

                        $allUsers[] = array(
                            "method" => "POST",
                            "path" => "/lists/" . $listID . "/members/",
                            "body" => $json_data
                        );

                        $count++;
                    }
                }

                $array = array(
                    "operations" => $allUsers
                );

                $res = $mailchimp->sendAllMembers($array);

                if ( array_key_exists ('title',$res) ) {
                    $resError[] = $res;
                }

                sleep ( (int)($count*3)/10 );

                $mailchimp->reloadMailChimpDatas();

                if ( count($resError) > 0) {
                    return $response->withJson(['success' => false, "error" => $resError]);
                } else {
                    return $response->withJson(['success' => true, "result" => $res]);
                }

            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addPerson (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->personID) && isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');
            $person = PersonQuery::create()->findPk($input->personID);

            if ( !is_null ($mailchimp) && $mailchimp->isActive() /*&& !is_null($person) && $mailchimp->getListNameFromEmail($person->getEmail()) == ''*/ ) {
                $res = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),$person->getAddressForMailChimp(), $person->getHomePhone(), 'subscribed');

                if ( !array_key_exists ('title',$res) ) {
                    return $response->withJson(['success' => true, "result" => $res]);
                } else {
                    return $response->withJson(['success' => false, "error" => $res]);
                }
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addFamily (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->familyID) && isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
                $family = FamilyQuery::create()->findPk($input->familyID);
                $persons = $family->getPeople();

                // all person from the family should be deactivated too
                $res = [];
                foreach ($persons as $person) {
                    $res[] = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),$person->getAddressForMailChimp(), $person->getHomePhone(),'subscribed');
                }

                return $response->withJson(['success' => true, "result" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addAllFamilies (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ) {
            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');

            $list           = $mailchimp->getListFromListId($input->list_id);
            $listID         = $input->list_id;

            $families = FamilyQuery::create()
                ->filterByDateDeactivated(NULL)
                ->find();


            if (!empty($families))
            {
                $count = 0;
                $allUsers = [];

                $numberOfPerson = 0;

                foreach ($families as $family)
                {
                    $persons = $family->getHeadPeople();

                    $person = $persons[0];

                    if (!is_null ($person) && strlen($person->getEmail()) > 0) {
                        $numberOfPerson++;

                        if (SystemConfig::getValue("iMailChimpApiMaxMembersCount") < $numberOfPerson) {
                            $new_List = $mailchimp->createList($list['name'].'_'.time(), $list['campaign_defaults']['subject'], $list['permission_reminder'], isset($list['use_archive_bar']), $list['visibility']);
                            $listID   = $new_List['id'];
                            $numberOfPerson = 0;
                        }

                        $merge_fields = ['FNAME'=>$person->getFirstName(), 'LNAME'=>$person->getLastName()];

                        $address = $person->getAddressForMailChimp();

                        if ( !is_null ($address) && SystemConfig::getBooleanValue('bMailChimpWithAddressPhone') ) {
                            $merge_fields['ADDRESS'] = $address;
                        }

                        $phone = $person->getHomePhone();

                        if ( !is_null ($phone) && SystemConfig::getBooleanValue('bMailChimpWithAddressPhone') ) {
                            $merge_fields['PHONE']   = $address;
                        }

                        $data = array(
                            "apikey"        => SystemConfig::getValue("sMailChimpApiKey"),
                            "email_address" => $person->getEmail(),
                            "status"        => "subscribed",
                            "merge_fields"  => $merge_fields
                        );

                        $json_data = json_encode($data);

                        $allUsers[] = array(
                            "method" => "POST",
                            "path" => "/lists/" . $listID . "/members/",
                            "body" => $json_data
                        );

                        $count++;
                    }
                }

                $array = array(
                    "operations" => $allUsers
                );

                $res = $mailchimp->sendAllMembers($array);

                if ( array_key_exists ('title',$res) ) {
                    $resError[] = $res;
                }

                sleep ( (int)($count*3)/10 );

                $mailchimp->reloadMailChimpDatas();

                if ( count($resError) > 0) {
                    return $response->withJson(['success' => false, "error" => $resError]);
                } else {
                    return $response->withJson(['success' => true, "result" => $res]);
                }
            }
            return $response->withJson(['success' => false, "error" => "try problem"]);
        }
    }

    public function addGroup (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        if (!SessionUser::getUser()->isMailChimpEnabled()) {
            return $response->withStatus(404);
        }

        $input = (object)$request->getParsedBody();

        if ( isset ($input->groupID) && isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = $this->container->get('MailChimpService');

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
                $members = EcclesiaCRM\Person2group2roleP2g2rQuery::create()
                    ->joinWithPerson()
                    ->usePersonQuery()
                    ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
                    ->endUse()
                    ->findByGroupId($input->groupID);


                // all person from the family should be deactivated too
                $res = [];
                foreach ($members as $member) {
                    $res[] = $mailchimp->postMember($input->list_id,32,$member->getPerson()->getFirstName(),$member->getPerson()->getLastName(),$member->getPerson()->getEmail(),$member->getPerson()->getAddressForMailChimp(), $member->getPerson()->getHomePhone(),'subscribed');
                }

                return $response->withJson(['success' => true, "result" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    // test connection
    public function testEmailConnectionMVC (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $mailer = new PHPMailer();
        $message = "";
        if (!empty(SystemConfig::getValue("sSMTPHost")) && !empty(ChurchMetaData::getChurchEmail())) {
            $mailer->IsSMTP();
            $mailer->CharSet = 'UTF-8';
            $mailer->Timeout = intval(SystemConfig::getValue("iSMTPTimeout"));
            $mailer->Host = SystemConfig::getValue("sSMTPHost");
            if (SystemConfig::getBooleanValue("bSMTPAuth")) {
                $mailer->SMTPAuth = true;
                $result = "<b>SMTP Auth Used</b></br></br>";
                $mailer->Username = SystemConfig::getValue("sSMTPUser");
                $mailer->Password = SystemConfig::getValue("sSMTPPass");
            }
            $mailer->SMTPDebug = 3;
            $mailer->Subject = "Test SMTP Email";
            $mailer->setFrom(ChurchMetaData::getChurchEmail());
            $mailer->addAddress(ChurchMetaData::getChurchEmail());
            $mailer->Body = "test email";
            $mailer->Debugoutput = "html";
        } else {
            $message = _("SMTP Host is not setup, please visit the settings page");
        }

        if (empty($message)) {
            ob_start();
            $mailer->send();
            $result .= ob_get_clean();
            return $response->withJson(['success' => true,"result" => $result]);
        } else {
            return $response->withJson(['success' => false,"error" => $message]);
        }
    }
}
