<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace Plugins\APIControllers;

use Exception;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use EcclesiaCRM\Person2group2roleP2g2rQuery;


// Routes
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\SessionUser;

spl_autoload_register(function ($className) {
    include_once str_replace(array('PluginStore', '\\'), array(__DIR__.'/../model', '/'), $className) . '.php';
    include_once str_replace(array('Plugins\\Service', '\\'), array(__DIR__.'/../../core/Services', '/'), $className) . '.php';
});

use Plugins\Service\MailChimpService;

use PluginStore\MailChimpParamsQuery;


class MailchimpController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    // classic lists managements functions
    public function searchList (ServerRequest $request, Response $response, array $args): Response
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
            $logger = $this->container->get('Logger');
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
            $logger = $this->container->get('Logger');
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
            $logger = $this->container->get('Logger');
            $logger->warn($e->getMessage());
        }

        return $response->withJson(array_filter($resultsArray));
    }

    public function oneList (ServerRequest $request, Response $response, array $args): Response
    {        
        $mailchimp = new MailChimpService();

        $list      = $mailchimp->getListFromListId ($args['listID']);
        $campaign  = $mailchimp->getCampaignsFromListId($args['listID']);

        return $response->withJSON(['MailChimpList' => $list,'MailChimpCampaign' => $campaign,'membersCount' => count($mailchimp->getListMembersFromListId($args['listID']))]);
    }

    public function lists (ServerRequest $request, Response $response, array $args): Response
    {
        $mailchimp = new MailChimpService();

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

        return $response->withJSON(['MailChimpLists' => $lists,'MailChimpCampaigns' => $campaigns, 'firstLoaded' => !$isLoaded, 'isActive' => $isActive]);
    }

    public function listmembers (ServerRequest $request, Response $response, array $args): Response
    {
        $mailchimp = new MailChimpService();

        return $response->withJSON(['MailChimpMembers' => $mailchimp->getListMembersFromListId($args['listID']), 'id' => $args['listID']]);
    }

    public function createList (ServerRequest $request, Response $response, array $args): Response {

        $input = (object)$request->getParsedBody();

        if ( isset ($input->ListTitle) && isset ($input->Subject) && isset ($input->PermissionReminder) && isset ($input->ArchiveBars) && isset ($input->Status) ){
            $mailchimp = new MailChimpService();

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

    public function changeSettings(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (array) $request->getParsedBody();

        try {
            $params = MailChimpParamsQuery::saveSettings($input);

            return $response->withJson([
                'success' => true,
                'settings' => [
                    'apiKey' => (string) $params->getApiKey(),
                    'requestTimeOut' => (int) $params->getRequestTimeout(),
                    'externalCssFont' => (string) $params->getContentsExternalCssFont(),
                    'bWithAddressPhone' => (bool) $params->getWithAddressPhone(),
                    'sMailChimpEmailSender' => (string) $params->getEmailSender(),
                    'bMailServiceExtraFont' => (string) $params->getExtraFont(),
                ]
            ]);
        } catch (\Throwable $ex) {
            return $response->withJson([
                'success' => false,
                'error' => ['detail' => $ex->getMessage()]
            ], 500);
        }
    }

    public function modifyList (ServerRequest $request, Response $response, array $args): Response {        
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->name) && isset ($input->subject) && isset ($input->permission_reminder) ){
            $mailchimp = new MailChimpService();

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

    public function deleteallsubscribers (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){
            $mailchimp = new MailChimpService();

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

    public function deleteList (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){
            $mailchimp = new MailChimpService();

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
    public function addTag (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->tag) && isset ($input->name) && isset ($input->emails) ){
            $mailchimp = new MailChimpService();

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

    public function getAllTags (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){
            $mailchimp = new MailChimpService();

            $list = $mailchimp->getListFromListId ($input->list_id);
            if ( !array_key_exists ('title',$list) ) {
                return $response->withJson(['success' => true, "result" => $list['tags']]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function removeTag (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        $res = "";

        if ( isset ($input->list_id) && isset ($input->tag_ID) ){
            $mailchimp = new MailChimpService();

            $res = $mailchimp->deleteSegment($input->list_id, $input->tag_ID);

            if ( gettype($res) == 'boolean' && $res == true ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false, "error" => $res]);
    }

    public function removeTagForMembers (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->tag) && isset ($input->emails) ){
            $mailchimp = new MailChimpService();

            $res = $mailchimp->removeMembersFromSegment($input->list_id, $input->tag, $input->emails);

            if ( !array_key_exists ('title',$res) ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function removeAllTagsForMembers (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->emails) ){
            $mailchimp = new MailChimpService();

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
    public function campaignCreate (ServerRequest $request, Response $response, array $args): Response
    {        
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->subject) && isset ($input->title) && isset ($input->htmlBody) && isset ($input->tagId) ){
            $mailchimp = new MailChimpService();

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ){

                if ( !empty(SystemConfig::getValue('bMailServiceContentsExternalCssFont')) ) {
                    $input->htmlBody = '<link rel="stylesheet" type="text/css" href="' . SystemConfig::getValue('bMailServiceContentsExternalCssFont') . '"/>' . $input->htmlBody;
                }

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

    public function campaignDelete (ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->campaign_id) ){

            $mailchimp = new MailChimpService();

            $res = $mailchimp->deleteCampaign ($input->campaign_id);

            if ( gettype($res) == 'boolean' && $res == true ) {
                return $response->withJson(['success' => true,'content' => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function campaignSend (ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->campaign_id) ){

            $mailchimp = new MailChimpService();

            $res = $mailchimp->sendCampaign ($input->campaign_id);

            if ( $res ) {
                return $response->withJson(['success' => true,'content' => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function campaignSave (ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->campaign_id)
            && isset ($input->subject)
            && isset ($input->content)
            && isset ($input->oldStatus) ) {

            $mailchimp = new MailChimpService();

            if ( !empty(SystemConfig::getValue('bMailServiceContentsExternalCssFont')) && !mb_strpos($input->content, "text/css") ) {
                $input->content = '<link rel="stylesheet" type="text/css" href="' . SystemConfig::getValue('bMailServiceContentsExternalCssFont') . '"/>' . $input->content;
            }

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

        return $response->withJson(['success' => false, "error1" => "Problem oldStatus is empty"]);
    }

    public function campaignContent(ServerRequest $request, Response $response, array $args): Response
    {
        $mailchimp = new MailChimpService();

        $campaignContent = $mailchimp->getCampaignContent ($args['campaignID']);

        // Be careFull this can change with a new MailChimp api
        $realContent = explode("            <center>\n                <br/>\n                <br/>\n",$campaignContent['html'])[0];

        if ( !empty($campaignContent['html']) ) {
            return $response->withJson(['success' => true,'content' => $realContent]);
        }

        return $response->withJson(['success' => false]);

        return $response;
    }

    public function statusList(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->status) && isset ($input->list_id) && isset ($input->email) ){

            // we get the MailChimp Service
            $mailchimp = new MailChimpService();

            $res = $mailchimp->updateMember($input->list_id,"","",$input->email,$input->status);

            $person = PersonQuery::create()
                            ->filterByEmail($input->email)
                            ->_or()
                            ->filterByWorkEmail($input->email)
                            ->findOne();

            if (!is_null($person)) {
                $person->setSendNewsletter(($input->status == 'unsubscribed')?"FALSE":"TRUE");
                $person->save();
            }

            if ( !array_key_exists ('title',$res) ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    // members management
    public function suppress (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->email) ){

            // we get the MailChimp Service
            $mailchimp = new MailChimpService();

            $res = $mailchimp->deleteMember($input->list_id,$input->email);

            // suppress a person from mailchimp turn send newsletter to false
            $person = PersonQuery::create()->findOneByEmail($input->email);
            $person->setSendNewsletter("FALSE");
            $person->save();

            if ( gettype($res) == 'boolean' and $res == true  ) {
                return $response->withJson(['success' => true, "result" => $res]);
            } else {
                return $response->withJson(['success' => false, "error" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function suppressMembers (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) && isset ($input->emails) ){

            // we get the MailChimp Service
            $mailchimp = new MailChimpService();

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

    public function addallnewsletterpersons (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = new MailChimpService();

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

                        $res = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),$person->getAddressForMailService(), $person->getHomePhone(), 'subscribed');
                    }

                    $count++;
                }

                sleep ( (int)($count*3)/10 );

                $mailchimp->reloadMailChimpDatas();

                return $response->withJson(['success' => true, "result" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addallpersons (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = new MailChimpService();

            if ( !is_null ($mailchimp) && $mailchimp->isActive() /*&& !is_null($person) && $mailchimp->getListNameFromEmail($person->getEmail()) == ''*/ ) {
                $persons = PersonQuery::create()
                    ->filterByEmail(null, Criteria::NOT_EQUAL)
                    ->_or()->filterByWorkEmail(null, Criteria::NOT_EQUAL)
                    ->find();

                $numberOfPerson = 0;
                $count = 0;

                foreach ($persons as $person) {
                    if (strlen($person->getEmail()) > 0) {
                        $numberOfPerson++;

                        $res = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),$person->getAddressForMailService(), $person->getHomePhone(), 'subscribed');

                        $count++;
                    }
                }

                sleep ( (int)($count*3)/10 );

                $mailchimp->reloadMailChimpDatas();

                return $response->withJson(['success' => true, "result" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addPerson (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->personID) && isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = new MailChimpService();
            $person = PersonQuery::create()->findPk($input->personID);

            if ( !is_null ($mailchimp) && $mailchimp->isActive() /*&& !is_null($person) && $mailchimp->getListNameFromEmail($person->getEmail()) == ''*/ ) {
                $res = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),$person->getAddressForMailService(), $person->getHomePhone(), 'subscribed');

                if ( !array_key_exists ('title',$res) ) {
                    return $response->withJson(['success' => true, "result" => $res]);
                } else {
                    return $response->withJson(['success' => false, "error" => $res]);
                }
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addFamily (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->familyID) && isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = new MailChimpService();

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
                $family = FamilyQuery::create()->findPk($input->familyID);
                $persons = $family->getPeople();

                // all person from the family should be deactivated too
                $res = [];
                foreach ($persons as $person) {
                    $res[] = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),$person->getAddressForMailService(), $person->getHomePhone(),'subscribed');
                }

                return $response->withJson(['success' => true, "result" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }

    public function addAllFamilies (ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->list_id) ) {
            // we get the MailChimp Service
            $mailchimp = new MailChimpService();

            $list           = $mailchimp->getListFromListId($input->list_id);
            $listID         = $input->list_id;

            $families = FamilyQuery::create()
                ->filterByDateDeactivated(NULL)
                ->find();


            if (!empty($families))
            {
                $count = 0;
                $count = 0;

                $numberOfPerson = 0;

                foreach ($families as $family)
                {
                    $persons = $family->getHeadPeople();

                    $person = $persons[0];

                    if (!is_null ($person) && strlen($person->getEmail()) > 0) {
                        $numberOfPerson++;

                        $res = $mailchimp->postMember($input->list_id,32,$person->getFirstName(),$person->getLastName(),$person->getEmail(),$person->getAddressForMailService(), $person->getHomePhone(), 'subscribed');

                        $count++;
                    }
                }

                sleep ( (int)($count*3)/10 );

                $mailchimp->reloadMailChimpDatas();

                return $response->withJson(['success' => true, "result" => $res]);
            }
            return $response->withJson(['success' => false, "error" => "try problem"]);
        }

        return $response->withJson(['success' => false, "error" => "try problem"]);
    }

    public function addGroup (ServerRequest $request, Response $response, array $args): Response {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->groupID) && isset ($input->list_id) ){

            // we get the MailChimp Service
            $mailchimp = new MailChimpService();

            if ( !is_null ($mailchimp) && $mailchimp->isActive() ) {
                $members = Person2group2roleP2g2rQuery::create()
                    ->joinWithPerson()
                    ->usePersonQuery()
                    ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
                    ->endUse()
                    ->findByGroupId($input->groupID);


                // all person from the family should be deactivated too
                $res = [];
                foreach ($members as $member) {
                    $res[] = $mailchimp->postMember($input->list_id,32,$member->getPerson()->getFirstName(),$member->getPerson()->getLastName(),$member->getPerson()->getEmail(),$member->getPerson()->getAddressForMailService(), $member->getPerson()->getHomePhone(),'subscribed');
                }

                return $response->withJson(['success' => true, "result" => $res]);
            }
        }

        return $response->withJson(['success' => false]);
    }    

    public function duplicateEmails(ServerRequest $request, Response $response, array $args): Response {
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
        $mailchimp = new MailChimpService();

        /*if ( !is_null ($mailchimp) && $mailchimp->isActive() ) 
        {
            return $response->withRedirect(SystemURLs::getRootPath() . "/email/Dashboard.php");
        }*/

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
                        array_push($missingEmailInMailChimp, 
                            ["id" => $Person->getId(), 
                            "img" => $Person->getJPGPhotoDatas(),
                            "url" => '<a href="' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $family->getId() . '">' . $family->getSaluation() . '</a>', "email" => $Person->getEmail()]);
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
                    array_push($missingEmailInMailChimp, 
                        ["id" => $Person->getId(), 
                        "img" => $Person->getJPGPhotoDatas(),
                        "url" => '<a href="' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $Person->getId() . '">' . $Person->getFullName() . '</a>', "email" => $Person->getEmail()]);
                }
            }
        }

        return $response->withJson(["emails" => $missingEmailInMailChimp]);
    }
}
