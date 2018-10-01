<?php
namespace EcclesiaCRM\dto;

use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\EventAttend;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\SundaySchoolService;


class Cart
{
  public static function PersonInCart($PersonID)
  {
    if (in_array($PersonID,$_SESSION['aPeopleCart'])) {
         return true;
    }
    
    return false;
  }
  
  public static function GroupInCart($GroupID)
  {
    $GroupMembers = Person2group2roleP2g2rQuery::create()
            ->filterByGroupId($GroupID)
            ->find();
            
    foreach ($GroupMembers as $GroupMember) 
    {
      if (in_array($GroupMember->getPersonId(),$_SESSION['aPeopleCart'])) {
        return true;
      }
    }
    
    return false;
  }

  public static function FamilyInCart($FamilyID)
  {
    $FamilyMembers = PersonQuery::create()
            ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
            ->filterByFamId($FamilyID)
            ->find();
            
    foreach ($FamilyMembers as $FamilyMember)
    {
      if (in_array($FamilyMember->getId(),$_SESSION['aPeopleCart'])) {
        return true;
      }
    }  
    
    return false;
  }
 
  public static function TeacherInCart($GroupID)
  {
     $sundaySchoolService = new SundaySchoolService();

     $thisClassTeachers = $sundaySchoolService->getTeacherFullDetails($GroupID);
     
     foreach ($thisClassTeachers as $teacher) {
       if (in_array($teacher['teacherId'],$_SESSION['aPeopleCart'])) {
         return true;
       }
    }
    
    return false;
  }

  public static function StudentInCart($GroupID)
  {
    $sundaySchoolService = new SundaySchoolService();

    $thisClassChildren = $sundaySchoolService->getKidsFullDetails($GroupID);
     
    foreach ($thisClassChildren as $child) {
       if (in_array($child['kidId'],$_SESSION['aPeopleCart'])) {
         return true;
       }
    }
    
    return false;
  }
  
  private static function CheckCart()
  {
    if (!isset($_SESSION['aPeopleCart']))
    {
      $_SESSION['aPeopleCart'] = [];
    }
  }
  public static function AddPerson($PersonID)
  {
    self::CheckCart();
    if (!is_numeric($PersonID))
    {
      throw new \Exception (gettext("PersonID for Cart must be numeric"),400);
    }
    if ($PersonID !== null && !in_array($PersonID, $_SESSION['aPeopleCart'], false)) {
      array_push($_SESSION['aPeopleCart'], $PersonID);
    }
  }

  public static function AddPersonArray($PersonArray)
  {
    foreach($PersonArray as $PersonID)
    {
      Cart::AddPerson($PersonID);
    }
    
  }
  
  public static function AddGroup($GroupID)
  {
    if (!is_numeric($GroupID))
    {
      throw new \Exception (gettext("GroupID for Cart must be numeric"),400);
    }
    $GroupMembers = Person2group2roleP2g2rQuery::create()
            ->usePersonQuery()
              ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
            ->endUse()
            ->filterByGroupId($GroupID)
            ->find();
    foreach ($GroupMembers as $GroupMember) 
    {
      Cart::AddPerson($GroupMember->getPersonId());
    }
  }
  
  public static function AddFamily($FamilyID)
  {
    if (!is_numeric($FamilyID))
    {
      throw new \Exception (gettext("FamilyID for Cart must be numeric"),400);
    }
    $FamilyMembers = PersonQuery::create()
            ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
            ->filterByFamId($FamilyID)
            ->find();
    foreach ($FamilyMembers as $FamilyMember)
    {
      Cart::AddPerson($FamilyMember->getId());
    }       
  }

  public static function IntersectArrayWithPeopleCart($aIDs)
  {
      if (isset($_SESSION['aPeopleCart']) && is_array($aIDs)) {
          $_SESSION['aPeopleCart'] = array_intersect($_SESSION['aPeopleCart'], $aIDs);
      }
  }

  public static function RemovePerson($PersonID)
  {
    // make sure the cart array exists
    // we can't remove anybody if there is no cart
    if (!is_numeric($PersonID))
    {
      throw new \Exception (gettext("PersonID for Cart must be numeric"),400);
    }
    if (isset($_SESSION['aPeopleCart'])) {
      $aTempArray[] = $PersonID; // the only element in this array is the ID to be removed
      $_SESSION['aPeopleCart'] = array_values(array_diff($_SESSION['aPeopleCart'], $aTempArray));
    }
  }
  
  public static function AddStudents($GroupID) 
  {
     $sundaySchoolService = new SundaySchoolService();
     
     $thisClassChildren = $sundaySchoolService->getKidsFullDetails($GroupID);
     
     foreach ($thisClassChildren as $child) {
       Cart::AddPerson($child['kidId']);
     }
  }
  
  public static function RemoveStudents($GroupID) 
  {
     $sundaySchoolService = new SundaySchoolService();
     
     $thisClassChildren = $sundaySchoolService->getKidsFullDetails($GroupID);
     
     foreach ($thisClassChildren as $child) {
       Cart::RemovePerson($child['kidId']);
     }
  }

  public static function AddTeachers($GroupID) 
  {
     $sundaySchoolService = new SundaySchoolService();
     
     $thisClassTeachers = $sundaySchoolService->getTeacherFullDetails($GroupID);
     
     foreach ($thisClassTeachers as $teacher) {
       Cart::AddPerson($teacher['teacherId']);
     }
  }
  
  public static function RemoveTeachers($GroupID) 
  {
     $sundaySchoolService = new SundaySchoolService();
     
     $thisClassTeachers = $sundaySchoolService->getTeacherFullDetails($GroupID);
     
     foreach ($thisClassTeachers as $teacher) {
       Cart::RemovePerson($teacher['teacherId']);
     }
  }

  
  public static function RemoveFamily($FamilyID)
  {
    if (!is_numeric($FamilyID))
    {
      throw new \Exception (gettext("FamilyID for Cart must be numeric"),400);
    }
    $FamilyMembers = PersonQuery::create()
            ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
            ->filterByFamId($FamilyID)
            ->find();
    foreach ($FamilyMembers as $FamilyMember)
    {
      Cart::RemovePerson($FamilyMember->getId());
    }       
  }
  
  public static function DeletePersonArray(&$personsID)
  {
    foreach ($personsID as $key => $personID) {
      if ($_SESSION['user']->getId() != $personID && $personID != 1) {
        $user = UserQuery::create()
              ->findOneByPersonId($personID);

        $person = PersonQuery::create()
                ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
                ->findOneById($personID);              
              
        if (empty($user)) {// it's only a person, we can delete.
          $person->delete();
          
          unset($personsID[$key]);          
        } else if (!empty($user) && !$user->isAdmin()) {// it's a user but not an admin, we can delete.
            $person->delete();      
                
            unset($personsID[$key]);
        }
      }
    }
  }

  
  public static function RemovePersonArray($aIDs)
  {
    // make sure the cart array exists
    // we can't remove anybody if there is no cart
    if (isset($_SESSION['aPeopleCart']) && is_array($aIDs)) {
        $_SESSION['aPeopleCart'] = array_values(array_diff($_SESSION['aPeopleCart'], $aIDs));
    }
  }
  
  public static function RemoveGroup($GroupID)
  {
    if (!is_numeric($GroupID))
    {
      throw new \Exception (gettext("GroupID for Cart must be numeric"),400);
    }
    $GroupMembers = Person2group2roleP2g2rQuery::create()
            ->filterByGroupId($GroupID)
            ->find();
    foreach ($GroupMembers as $GroupMember) 
    {
      Cart::RemovePerson($GroupMember->getPersonId());
    }
  }
  
  public static function HasPeople()
  {
    return array_key_exists('aPeopleCart', $_SESSION) && count($_SESSION['aPeopleCart']) != 0;
  }
  
  public static function CountPeople()
  {
    return count($_SESSION['aPeopleCart']);
  }
  
  public static function ConvertCartToString($aCartArray)
  {
      // Implode the array
    $sCartString = implode(',', $aCartArray);

    // Make sure the comma is chopped off the end
    if (mb_substr($sCartString, strlen($sCartString) - 1, 1) == ',') {
        $sCartString = mb_substr($sCartString, 0, strlen($sCartString) - 1);
    }

    // Make sure there are no duplicate commas
    $sCartString = str_replace(',,', '', $sCartString);

      return $sCartString;
  }

  public static function CountFamilies()
  {
    $persons = PersonQuery::create()
            ->filterByDateDeactivated(null)// RGPD, when a person is completely deactivated
            ->distinct()
            ->select(['Person.FamId'])
            ->filterById($_SESSION['aPeopleCart'])
            ->orderByFamId()
            ->find();
    return $persons->count();
  }


  public static function EmptyToNewGroup($GroupID)
  {
    self::EmptyToGroup($GroupID);
  }
  
  public static function EmptyToEvent($eventID)
  {
    // Loop through the session array
    $iCount = 0;
    while ($element = each($_SESSION['aPeopleCart'])) {
        // Enter ID into event
        try {
            $eventAttent = new EventAttend();
        
            $eventAttent->setEventId($eventID);
            $eventAttent->setCheckinId($_SESSION['user']->getPersonId());
            $eventAttent->setCheckinDate(date("Y-m-d H:i:s"));
            $eventAttent->setPersonId($_SESSION['aPeopleCart'][$element['key']]);
            $eventAttent->save();
        } catch (\Exception $ex) {
           $errorMessage = $ex->getMessage();
        }
        
        $iCount++;
    }
    
    $_SESSION['aPeopleCart'] = [];
  }
    
  public static function EmptyToGroup($GroupID,$RoleID=0)
  {
    $iCount = 0;

    $group = GroupQuery::create()->findOneById($GroupID);
    
    if($RoleID == 0)
    {
      $RoleID = $group->getDefaultRole();
    }
    
    while ($element = each($_SESSION['aPeopleCart'])) {      
      $personGroupRole = Person2group2roleP2g2rQuery::create()
        ->filterByGroupId($GroupID)
        ->filterByPersonId($_SESSION['aPeopleCart'][$element['key']])
        ->findOneOrCreate()
        ->setRoleId($RoleID)
        ->save();  
        
      /*
      This part of code should be done 
      */  
      // Check if this group has special properties
      /*      $sSQL = 'SELECT grp_hasSpecialProps FROM group_grp WHERE grp_ID = '.$iGroupID;
            $rsTemp = RunQuery($sSQL);
            $rowTemp = mysqli_fetch_row($rsTemp);
            $bHasProp = $rowTemp[0];

            if ($bHasProp == 'true') {
                $sSQL = 'INSERT INTO groupprop_'.$iGroupID." (per_ID) VALUES ('".$iPersonID."')";
                RunQuery($sSQL);
            }  */
      
      $iCount += 1;
    }
    
    $_SESSION['aPeopleCart'] = [];
  }
  
}
