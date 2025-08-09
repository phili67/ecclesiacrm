<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\Base\UserQuery;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;


use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\SessionUser;

class PeopleController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function searchonlyuser(ServerRequest $request, Response $response, array $args): Response
    {
        $query = $args['query'];
        $resultsArray = [];

        $id = 1;

        $logger = $this->container->get('Logger');

        //Person Search
        try {
            $searchLikeString = '%' . $query . '%';
            $users = UserQuery::create()
                ->filterByIsDeactivated(0)
                ->filterByUserName($searchLikeString, Criteria::LIKE)
                ->usePersonQuery()
                    ->_or()->filterByFirstName($searchLikeString, Criteria::LIKE)
                    ->_or()->filterByLastName($searchLikeString, Criteria::LIKE)
                    ->_or()->filterByMiddleName($searchLikeString, Criteria::LIKE)                    
                ->endUse()                
                ->find();


            $data = [];
            $id++;

            foreach ($users as $user) {
                // user account is activated
                if ($user->isDeactivated()) continue;
                
                $person = $user->getPerson();

                $elt = ['id' => $id++,
                    'text' => $person->getFullName(),
                    'personID' => $person->getId()];

                array_push($data, $elt);
            }

            if (!empty($data)) {
                $dataPerson = ['children' => $data,
                    'id' => 0,
                    'text' => _('Persons')];

                $resultsArray = array($dataPerson);
            }
            
        } catch (\Exception $e) {
            $logger->warn($e->getMessage());
        }


        return $response->withJson(array_filter($resultsArray));
    }

    public function searchonlyuserwithedrive(ServerRequest $request, Response $response, array $args): Response
    {
        $query = $args['query'];
        $resultsArray = [];

        $currentUserId = SessionUser::getId();

        $id = 1;

        $logger = $this->container->get('Logger');

        //Person Search
        try {
            $searchLikeString = '%' . $query . '%';
            $users = UserQuery::create()
                ->filterByIsDeactivated(0)
                ->filterByUserName($searchLikeString, Criteria::LIKE)
                ->usePersonQuery()
                    ->filterByDateDeactivated(null)
                    ->_or()->filterByFirstName($searchLikeString, Criteria::LIKE)
                    ->_or()->filterByLastName($searchLikeString, Criteria::LIKE)
                    ->_or()->filterByMiddleName($searchLikeString, Criteria::LIKE)                    
                ->endUse()                
                ->find();


            $data = [];
            $id++;

            foreach ($users as $user) {
                // user account is activated
                if (!$user->checkEdrive() or $user->getId() == $currentUserId) continue;
                $person = $user->getPerson();

                $elt = ['id' => $id++,
                    'text' => $person->getFullName(),
                    'personID' => $person->getId()];

                array_push($data, $elt);
            }

            if (!empty($data)) {
                $dataPerson = ['children' => $data,
                    'id' => 0,
                    'text' => _('Persons')];

                $resultsArray = array($dataPerson);
            }
            
        } catch (\Exception $e) {
            $logger->warn($e->getMessage());
        }


        return $response->withJson(array_filter($resultsArray));
    }

    public function searchonlyperson(ServerRequest $request, Response $response, array $args): Response
    {
        $query = $args['query'];
        $resultsArray = [];

        $id = 1;

        $logger = $this->container->get('Logger');

        //Person Search
        try {
            $searchLikeString = '%' . $query . '%';
            $people = PersonQuery::create()->
            filterByDateDeactivated(null)->// gdpr when a person is de-activated
                filterByFirstName($searchLikeString, Criteria::LIKE)->
                _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
                limit(SystemConfig::getValue("iSearchIncludePersonsMax"))->find();


            if (!empty($people)) {
                $data = [];
                $id++;

                foreach ($people as $person) {
                    if ($person->getDateDeactivated() != null)
                        continue;

                    $elt = ['id' => $id++,
                        'text' => $person->getFullName(),
                        'personID' => $person->getId()];

                    array_push($data, $elt);
                }

                if (!empty($data)) {
                    $dataPerson = ['children' => $data,
                        'id' => 0,
                        'text' => _('Persons')];

                    $resultsArray = array($dataPerson);
                }
            }
        } catch (\Exception $e) {
            $logger->warn($e->getMessage());
        }


        return $response->withJson(array_filter($resultsArray));
    }

    public function searchpeople(ServerRequest $request, Response $response, array $args): Response
    {
        $query = $args['query'];
        $resultsArray = [];

        $id = 1;

        $input = (object)$request->getParsedBody();

        $logger = $this->container->get('Logger');

        if (!isset($args['type']) or isset($args['type']) and $args['type'] == 'person') {

            //Person Search
            try {
                $searchLikeString = '%' . $query . '%';
                $people = PersonQuery::create()->
                filterByFirstName($searchLikeString, Criteria::LIKE)->
                _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
                limit(SystemConfig::getValue("iSearchIncludePersonsMax"))->find();


                if (!empty($people)) {
                    $data = [];
                    $id++;

                    foreach ($people as $person) {
                        if ($person->getDateDeactivated() != null)
                            continue;

                        $elt = ['id' => $id++,
                            'text' => $person->getFullName(),
                            'personID' => $person->getId()];

                        array_push($data, $elt);
                    }

                    if (!empty($data)) {
                        $dataPerson = ['children' => $data,
                            'id' => 0,
                            'text' => _('Persons')];

                        $resultsArray = array($dataPerson);
                    }
                }
            } catch (\Exception $e) {
                $logger->warn($e->getMessage());
            }
        }

        if (!isset($args['type']) or isset($args['type']) and $args['type'] == 'family') {
            // Family search
            try {
                $families = FamilyQuery::create()
                    ->filterByName("%$query%", Criteria::LIKE)
                    ->limit(SystemConfig::getValue("iSearchIncludeFamiliesMax"))
                    ->find();

                if (!empty($families)) {
                    $data = [];
                    $id++;

                    foreach ($families as $family) {
                        if ($family->getDateDeactivated() != null)
                            continue;

                        $searchArray = [
                            "id" => $id++,
                            "text" => $family->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                            "name" => $family->getName(),
                            'familyID' => $family->getId()
                        ];

                        array_push($data, $searchArray);
                    }

                    if (!empty($data)) {
                        $dataFamilies = ['children' => $data,
                            'id' => 1,
                            'text' => _('Families')];

                        array_push($resultsArray, $dataFamilies);
                    }
                }
            } catch (\Exception $e) {
                $logger->warn($e->getMessage());
            }
        }

        if (!isset($args['type'])) {
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

                if (!empty($groups)) {
                    $data = [];
                    $id++;

                    foreach ($groups as $group) {
                        $elt = ['id' => $id++,
                            'text' => $group['displayName'],
                            'groupID' => $group['id']];

                        array_push($data, $elt);
                    }

                    if (!empty($data)) {
                        $dataGroup = ['children' => $data,
                            'id' => 2,
                            'text' => _('Groups')];

                        array_push($resultsArray, $dataGroup);
                    }
                }
            } catch (\Exception $e) {
                $logger->warn($e->getMessage());
            }
        }

        return $response->withJson(array_filter($resultsArray));
    }

    public function getAllClassifications(ServerRequest $request, Response $response, array $args): Response
    {
        $classifications = ListOptionQuery::create()->findById(1);

        return $response->withJson(['success' => true, "Classifications" => $classifications->toArray()]);
    }

    public function postPersonClassification(ServerRequest $request, Response $response, array $args): Response
    {
        $classifications = ListOptionQuery::create()->findById(1);

        $input = (object)$request->getParsedBody();

        if (isset ($input->personId) && isset ($input->classId)) {

            $person = PersonQuery::create()->findOneById($input->personId);

            if (!is_null($person)) {
                $person->setClsId($input->classId);

                $person->save();
            }
        }

        return $response->withJson(['success' => true, "Classifications" => $classifications->toArray()]);
    }

}
