<?php
/*******************************************************************************
 *
 *  filename    : /EcclesiaCRM/dto/ReportUtilities.php
 *  last change : 2019-05-19
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2003 Chris Gebhardt
 *                Copyright 2019 Philippe Logel
 *
 ******************************************************************************/

namespace EcclesiaCRM\dto;

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;

class ReportUtilities
{
  // Finds and loads the base JPGraph library and any components specified as arguments
  //

  // MakeSalutation: this utility is used to figure out how to address a family
  // for correspondence.
  public static function MakeSalutationUtilityFamily($famID)
  {
      // Make it put the name if there is only one individual in the family
      // Make it put two first names and the last name when there are exactly two people in the family (e.g. "Nathaniel and Jeanette Brooks")
      // Make it put two whole names where there are exactly two people with different names (e.g. "Doug Philbrook and Karen Andrews")
      // When there are more than two people in the family I don't have any way to know which people are children, so I would have to just use the family name (e.g. "Grossman Family").
    
      $family = FamilyQuery::Create()->findOneById ($famID);
    
      if ( is_null ($family) ) {
        return _('Invalid Family').$famID;
      }
    
      $persons = PersonQuery::Create()
                   ->filterByFamId ($famID)
                   ->orderByFmrId()
                   ->find();
                 
      $numChildren = 0;
      $indNotChild = 0;
      $aNotChildren = [];
    
      $numMembers = $persons->count();
    
      foreach ($persons as $person) {
        if ($person->getFmrId() == 3) {
          $numChildren++;
        } else {
          $aNotChildren[$indNotChild++] = $person;
        }
      }
    
      $numNotChildren = $numMembers - $numChildren;
    
      if ($numNotChildren == 1) {
          return $aNotChildren[0]->getFirstName().' '.$aNotChildren[0]->getLastName();
      } elseif ($numNotChildren == 2) {
          $firstFirstName = $aNotChildren[0]->getFirstName();
          $firstLastName = $aNotChildren[0]->getLastName();
          $secondFirstName = $aNotChildren[1]->getFirstName();
          $secondLastName = $aNotChildren[1]->getLastName();
          if ($firstLastName == $secondLastName) {
              return $firstFirstName.' & '.$secondFirstName.' '.$firstLastName;
          } else {
              return $firstFirstName.' '.$firstLastName.' & '.$secondFirstName.' '.$secondLastName;
          }
      } else {
          return $family->getName().' (' . _('Family').')';
      }
  }

  public static function MakeSalutationUtilityPerson($personID)
  {
      // Make it put the name if there is only one individual in the family
      // Make it put two first names and the last name when there are exactly two people in the family (e.g. "Nathaniel and Jeanette Brooks")
      // Make it put two whole names where there are exactly two people with different names (e.g. "Doug Philbrook and Karen Andrews")
      // When there are more than two people in the family I don't have any way to know which people are children, so I would have to just use the family name (e.g. "Grossman Family").
    
      $person = PersonQuery::Create()                                     
                   ->findOneById($personID);
    
      if ( is_null ($person) ) {
        return _('Invalid Person').$personID;
      }
    
      $firstFirstName = $person->getFirstName();
      $firstLastName = $person->getLastName();
      
      return $firstFirstName.' '.$firstLastName;
  }
}  