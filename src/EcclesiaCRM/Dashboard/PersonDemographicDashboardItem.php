<?php

namespace EcclesiaCRM\Dashboard;

use Propel\Runtime\Propel;
use EcclesiaCRM\Dashboard\DashboardItemInterface;

class PersonDemographicDashboardItem implements DashboardItemInterface {
  
  public static function getDashboardItemName() {
    return "PersonDemographics";
  }

  public static function getDashboardItemValue() {
     $stats = [];
        $sSQL = 'select count(*) as numb, per_Gender, per_fmr_ID
                from person_per LEFT JOIN family_fam ON family_fam.fam_ID = person_per.per_fam_ID
                where family_fam.fam_DateDeactivated is  null
                group by per_Gender, per_fmr_ID order by per_fmr_ID;';
        $connection = Propel::getConnection();
        $statement = $connection->prepare($sSQL);
        $statement->execute();

        while ($row = $statement->fetch( \PDO::FETCH_ASSOC )) {
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

  public static function shouldInclude($PageName) {
    return $PageName=="/v2/people/dashboard"; // this ID would be found on all pages.
  }

}