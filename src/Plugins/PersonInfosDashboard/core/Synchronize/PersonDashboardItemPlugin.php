<?php

namespace Plugins\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\PersonQuery;
use Propel\Runtime\Propel;

class PersonDashboardItemPlugin implements DashboardItemInterface {

  public static function getDashboardItemName() {
    return "PersonInfosDashboard";
  }

  public static function getDashboardItemValue() {
     $data = ['LatestPersons' => self::getLatestMembers(),
         'UpdatedPerson' => self::getUpdatedMembers()];

     return $data;
  }

   /**
     * Return last edited members. Only from active families selected
     * @param int $limit
     * @return array|\EcclesiaCRM\Person[]|mixed|\Propel\Runtime\ActiveRecord\ActiveRecordInterface[]|\Propel\Runtime\Collection\ObjectCollection
     */
    public static function getUpdatedMembers($limit = 6) : array
    {
        $persons =  PersonQuery::create()
            ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
            ->leftJoinWithFamily()
            ->where('Family.DateDeactivated is null')
            ->orderByDateLastEdited('DESC')
            ->limit($limit)
            ->find();

        $res = [];
        foreach ($persons as $person) {
            $res[] = [
                'Id' => $person->getId(),
                'LastName' => $person->getLastName(),
                'FirstName' => $person->getFirstName(),
                'Address1' => $person->getAddress(),
                'DateEntered' => (!is_null($person->getDateEntered())?$person->getDateEntered()->format('Y-m-d h:i'):''),
                'DateLastEdited' => (!is_null($person->getDateLastEdited())?$person->getDateLastEdited()->format('Y-m-d h:i'):''),
                'img' => $person->getJPGPhotoDatas()
            ];
        }

        return $res;
    }

    /**
     * Newly added members. Only from Active families selected
     * @param int $limit
     * @return array|\EcclesiaCRM\Person[]|mixed|\Propel\Runtime\ActiveRecord\ActiveRecordInterface[]|\Propel\Runtime\Collection\ObjectCollection
     */
    public static function getLatestMembers($limit = 6) : array
    {
        $persons = PersonQuery::create()
            ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
            ->leftJoinWithFamily()
            ->where('Family.DateDeactivated is null')
            ->filterByDateLastEdited(null)
            ->orderByDateEntered('DESC')
            ->limit($limit)
            ->find();

        $res = [];
        foreach ($persons as $person) {
            $res[] = [
                'Id' => $person->getId(),                
                'LastName' => $person->getLastName(),
                'FirstName' => $person->getFirstName(),
                'Address1' => $person->getAddress(),
                'DateEntered' => (!is_null($person->getDateEntered())?$person->getDateEntered()->format('Y-m-d h:i'):''),                
                'DateLastEdited' => (!is_null($person->getDateLastEdited())?$person->getDateLastEdited()->format('Y-m-d h:i'):''),
                'img' => $person->getJPGPhotoDatas()
            ];
        }

        return $res;
    }

  public static function shouldInclude($PageName) {
    return $PageName=="/v2/dashboard" or $PageName == "/v2/people/dashboard"; // this ID would be found on all pages.
  }

}