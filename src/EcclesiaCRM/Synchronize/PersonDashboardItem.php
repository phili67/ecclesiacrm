<?php

namespace EcclesiaCRM\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\PersonQuery;
use Propel\Runtime\Propel;

class PersonDashboardItem implements DashboardItemInterface {

  public static function getDashboardItemName() {
    return "PersonCount";
  }

  public static function getDashboardItemValue() {
     $data = ['personCount' => self::getMembersCount(),
         'LatestPersons' => self::getLatestMembers()->toArray(),
         'UpdatedPerson' => self::getUpdatedMembers()->toArray()];

     return $data;
  }

  /**
     * Return last edited members. Only from active families selected
     * @param int $limit
     * @return array|\EcclesiaCRM\Person[]|mixed|\Propel\Runtime\ActiveRecord\ActiveRecordInterface[]|\Propel\Runtime\Collection\ObjectCollection
     */
  public static function getMembersCount()
  {
    return PersonQuery::Create('per')
        ->filterByDateDeactivated(null)
        ->useFamilyQuery('fam','left join')
        ->filterByDateDeactivated(null)
        ->endUse()
        ->count();
  }

   /**
     * Return last edited members. Only from active families selected
     * @param int $limit
     * @return array|\EcclesiaCRM\Person[]|mixed|\Propel\Runtime\ActiveRecord\ActiveRecordInterface[]|\Propel\Runtime\Collection\ObjectCollection
     */
    public static function getUpdatedMembers($limit = 12)
    {
        return PersonQuery::create()
            ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
            ->leftJoinWithFamily()
            ->where('Family.DateDeactivated is null')
            ->orderByDateLastEdited('DESC')
            ->limit($limit)
            ->find();
    }

    /**
     * Newly added members. Only from Active families selected
     * @param int $limit
     * @return array|\EcclesiaCRM\Person[]|mixed|\Propel\Runtime\ActiveRecord\ActiveRecordInterface[]|\Propel\Runtime\Collection\ObjectCollection
     */
    public static function getLatestMembers($limit = 12)
    {
        return PersonQuery::create()
            ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
            ->leftJoinWithFamily()
            ->where('Family.DateDeactivated is null')
            ->filterByDateLastEdited(null)
            ->orderByDateEntered('DESC')
            ->limit($limit)
            ->find();
    }

    public static function getPersonStats()
    {
        $data = [];
        $sSQL = 'select lst_OptionName as Classification, count(*) as count
                from person_per INNER JOIN list_lst ON  per_cls_ID = lst_OptionID
                LEFT JOIN family_fam ON family_fam.fam_ID = person_per.per_fam_ID
                WHERE lst_ID =1 and family_fam.fam_DateDeactivated is null and person_per.per_DateDeactivated is null
                group by per_cls_ID, lst_OptionName order by count desc;';

        $connection = Propel::getConnection();

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $data['ClassificationCount'] = 0;

        while ($row = $statement->fetch( \PDO::FETCH_BOTH )) {
            $data[$row['Classification']] = $row['count'];
            $data['ClassificationCount'] += $row['count'];
        }

        return $data;
    }

    public static function getDemographic()
    {
        $stats = [];
        $sSQL = 'select count(*) as numb, per_Gender, per_fmr_ID
                from person_per LEFT JOIN family_fam ON family_fam.fam_ID = person_per.per_fam_ID
                where family_fam.fam_DateDeactivated is null and person_per.per_DateDeactivated is null
                group by per_Gender, per_fmr_ID order by per_fmr_ID;';
        $connection = Propel::getConnection();

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        while ($row = $statement->fetch( \PDO::FETCH_BOTH )) {
            switch ($row['per_Gender']) {
                case 0:
                    $gender = _('Unknown');
                    break;
                case 1:
                    $gender = _('Male');
                    break;
                case 2:
                    $gender = _('Female');
                    break;
                default:
                    $gender = _('Other');
            }

            switch ($row['per_fmr_ID']) {
                case 0:
                    $role = _('Unknown');
                    break;
                case 1:
                    $role = _('Head of Household');
                    break;
                case 2:
                    $role = _('Spouse');
                    break;
                case 3:
                    $role = _('Child');
                    break;
                default:
                    $role = _('Other');
            }

            array_push($stats, array(
                    "key" => "$role - $gender",
                    "value" => $row['numb'],
                    "gender" => $row['per_Gender'],
                    "role" => $row['per_fmr_ID'])
            );
        }

        return $stats;
    }

    public static function getAgeStats(){
        $persons = PersonQuery::create()->find();

        $stats = [];
        foreach($persons as $person) {
            if ($person->getAge(false) != '') {
                $stats[$person->getAge(false)]++;
            }
        }
        ksort($stats);
        return $stats;
    }


  public static function shouldInclude($PageName) {
    return $PageName=="/v2/dashboard" || $PageName == "/v2/people/dashboard"; // this ID would be found on all pages.
  }

}
