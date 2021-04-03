<?php

/******************************************************************************
*
*  filename    : api/routes/people.php
*  last change : Copyright all right reserved 2018/04/14 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address,
*                 groups, families, etc...
*
******************************************************************************/

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\ListOptionQuery;

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;


// Routes people


$app->group('/people', function (RouteCollectorProxy $group) {

/*
 * @! Returns a list of the person who's first name or last name matches the :query parameter
 * #! param: ref->string :: query string ref
 */
  $group->get('/searchonlyperson/{query}', function(Request $request, Response $response, array $args) {
      $query = $args['query'];
      $resultsArray = [];

      $id = 1;

    //Person Search
      try {
        $searchLikeString = '%'.$query.'%';
        $people = PersonQuery::create()->
            filterByDateDeactivated(null)->// gdpr when a person is de-activated
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
            'id' => 0,
            'text' => _('Persons')];

            $resultsArray = array ($dataPerson);
          }
        }
      } catch (Exception $e) {
          $this->Logger->warn($e->getMessage());
      }


      return $response->withJson(array_filter($resultsArray));
  });

/*
 * @! Returns a list of the members/families/groups who's first name or last name matches the :query parameter
 * #! param: ref->string :: query string ref
 */
  $group->get('/search/{query}', function(Request $request, Response $response, array $args) {
      $query = $args['query'];
      $resultsArray = [];

      $id = 1;

    //Person Search
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
            'id' => 0,
            'text' => _('Persons')];

            $resultsArray = array ($dataPerson);
          }
        }
      } catch (Exception $e) {
          $this->Logger->warn($e->getMessage());
      }

   // Family search
     try {
          $families = FamilyQuery::create()
              ->filterByName("%$query%", Criteria::LIKE)
              ->limit(SystemConfig::getValue("iSearchIncludeFamiliesMax"))
              ->find();

          if (!empty($families))
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
                'id' => 1,
                'text' => _('Families')];

              array_push($resultsArray, $dataFamilies);
            }
          }
        } catch (Exception $e) {
            $this->Logger->warn($e->getMessage());
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
                'id' => 2,
                'text' => _('Groups')];

              array_push($resultsArray, $dataGroup);
            }
          }
      } catch (Exception $e) {
          $this->Logger->warn($e->getMessage());
      }

      return $response->withJson(array_filter($resultsArray));
  });

/*
 * @! Returns all classifications
 * #! param: nothing
 */
  $group->get('/classifications/all', 'getAllClassifications' );

/*
 * @! Returns all classifications
 * #! param: nothing
 */
  $group->post('/person/classification/assign', 'postPersonClassification' );

});

function getAllClassifications(Request $request, Response $response, array $args) {
    $classifications = ListOptionQuery::create()->findById(1);

    return $response->withJson(['success' => true, "Classifications" => $classifications->toArray()]);
}

function postPersonClassification(Request $request, Response $response, array $args) {
    $classifications = ListOptionQuery::create()->findById(1);

    $input = (object)$request->getParsedBody();

    if ( isset ($input->personId) && isset ($input->classId) ) {

        $person = PersonQuery::create()->findOneById($input->personId);

        if (!is_null($person)) {
            $person->setClsId($input->classId);

            $person->save();
        }
    }

    return $response->withJson(['success' => true, "Classifications" => $classifications->toArray()]);
}


