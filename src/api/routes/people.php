<?php

/******************************************************************************
*
*  filename    : api/routes/people.php
*  last change : Copyright all right reserved 2018/04/14 Philippe Logel
*  description : Search terms like : Firstname, Lastname, phone, address, 
*                 groups, families, etc...
*
******************************************************************************/
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\NoteShareQuery;
use EcclesiaCRM\NoteShare;
use EcclesiaCRM\Person2group2roleP2g2r;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Emails\DocumentEmail;
use EcclesiaCRM\UserQuery;



// Routes people


$app->group('/people', function () {

    $this->get('/searchonlyperson/{query}',function($request,$response,$args) {
      $query = $args['query'];
      $resultsArray = [];
    
      $id = 1;
    
    //Person Search
      try {
        $searchLikeString = '%'.$query.'%';
        $people = PersonQuery::create()->
          filterByFirstName($searchLikeString, Criteria::LIKE)->
            _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
          limit(SystemConfig::getValue("bSearchIncludePersonsMax"))->find();
  

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
            'text' => gettext('Persons')];
      
            $resultsArray = array ($dataPerson);
          }
        }
      } catch (Exception $e) {
          $this->Logger->warn($e->getMessage());
      }
   
    
      return $response->withJson(array_filter($resultsArray));
  });
  
    $this->get('/search/{query}',function($request,$response,$args) {
      $query = $args['query'];
      $resultsArray = [];
    
      $id = 1;
    
    //Person Search
      try {
        $searchLikeString = '%'.$query.'%';
        $people = PersonQuery::create()->
          filterByFirstName($searchLikeString, Criteria::LIKE)->
            _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
          limit(SystemConfig::getValue("bSearchIncludePersonsMax"))->find();
  

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
            'text' => gettext('Persons')];
      
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
              ->limit(SystemConfig::getValue("bSearchIncludeFamiliesMax"))
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
                'text' => gettext('Families')];
      
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
              ->limit(SystemConfig::getValue("bSearchIncludeGroupsMax"))
              ->withColumn('grp_Name', 'displayName')
              ->withColumn('grp_ID', 'id')
              ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/GroupView.php?GroupID=",Group.Id)', 'uri')
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
                'text' => gettext('Groups')];

              array_push($resultsArray, $dataGroup);
            }
          }
      } catch (Exception $e) {
          $this->Logger->warn($e->getMessage());
      }
    
      return $response->withJson(array_filter($resultsArray));
  });
  
});
