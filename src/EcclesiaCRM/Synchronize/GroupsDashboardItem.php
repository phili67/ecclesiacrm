<?php

namespace EcclesiaCRM\Synchronize;

use Propel\Runtime\Propel;

use EcclesiaCRM\Synchronize\DashboardItemInterface;

class GroupsDashboardItem implements DashboardItemInterface
{

    public static function getDashboardItemName()
    {
        return "GroupsDisplay";
    }

    public static function getDashboardItemValue()
    {
        $sSQL = 'select
        (select count(*) from group_grp) as AllGroups,
        (select count(*) from group_grp where grp_Type = 4 ) as SundaySchoolClasses,
        (Select count(*) from person_per
          INNER JOIN person2group2role_p2g2r ON p2g2r_per_ID = per_ID
          INNER JOIN group_grp ON grp_ID = p2g2r_grp_ID
          LEFT JOIN family_fam ON fam_ID = per_fam_ID
          where fam_DateDeactivated is null and person_per.per_DateDeactivated is null and
              p2g2r_rle_ID = 2 and grp_Type = 4) as SundaySchoolKidsCount,
        (select count(*) as cnt from
              (
              Select per_fam_ID from person_per
                        INNER JOIN person2group2role_p2g2r ON p2g2r_per_ID = per_ID
                        INNER JOIN group_grp ON grp_ID = p2g2r_grp_ID
                        LEFT JOIN family_fam ON fam_ID = per_fam_ID
                        where fam_DateDeactivated is null and person_per.per_DateDeactivated is null and
                            p2g2r_rle_ID = 2 and grp_Type = 4 and per_fam_ID!= 0 GROUP BY per_fam_ID
              ) as tpm1
            ) as SundaySchoolFamiliesCount
        from dual ;
        ';


        $connection = Propel::getConnection();
        $statement = $connection->prepare($sSQL);
        $statement->execute();
        $groupsAndSundaySchoolStats = $statement->fetch(\PDO::FETCH_ASSOC);

        $data = ['groups' => $groupsAndSundaySchoolStats['AllGroups'] - $groupsAndSundaySchoolStats['SundaySchoolClasses'],
            'sundaySchoolClasses' => intval($groupsAndSundaySchoolStats['SundaySchoolClasses']),
            'sundaySchoolkids' => intval($groupsAndSundaySchoolStats['SundaySchoolKidsCount'])
        ];

        return $data;
    }

    public static function shouldInclude($PageName)
    {
        return $PageName == "/Menu.php" || $PageName == "/menu" || $PageName == "/v2/people/dashboard";
    }
}
