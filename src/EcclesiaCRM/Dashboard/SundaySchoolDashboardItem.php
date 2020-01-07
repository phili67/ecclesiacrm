<?php

namespace EcclesiaCRM\Dashboard;

use EcclesiaCRM\Dashboard\DashboardItemInterface;
use Propel\Runtime\Propel;
use EcclesiaCRM\Service\SundaySchoolService;

class SundaySchoolDashboardItem implements DashboardItemInterface
{

    public static function getDashboardItemName()
    {
        return "SundaySchoolDisplay";
    }

    public static function getDashboardItemValue()
    {
        $sSQL = 'select
        (select count(*) from group_grp) as Groups,
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

        // now we get the sundayschool group stats
        $sundaySchoolService = new SundaySchoolService();

        $kidsWithoutClasses = $sundaySchoolService->getKidsWithoutClasses();
        $classStats = $sundaySchoolService->getClassStats();
        $teachersCNT = 0;
        $kidsCNT = 0;
        $maleKidsCNT = 0;
        $femaleKidsCNT = 0;

        foreach ($classStats as $class) {
            $kidsCNT = $kidsCNT + $class['kids'];
            $teachersCNT = $teachersCNT + $class['teachers'];
            $classKids = $sundaySchoolService->getKidsFullDetails($class['id']);
            foreach ($classKids as $kid) {
                if ($kid['kidGender'] == '1') {
                    $maleKidsCNT++;
                } elseif ($kid['kidGender'] == '2') {
                    $femaleKidsCNT++;
                }
            }
        }

        $data = ['sundaySchoolClasses' => intval($groupsAndSundaySchoolStats['SundaySchoolClasses']),
            'sundaySchoolkids' => intval($groupsAndSundaySchoolStats['SundaySchoolKidsCount']),
            'SundaySchoolFamiliesCNT' => intval($groupsAndSundaySchoolStats['SundaySchoolFamiliesCount']),
            'kidsWithoutClasses' => $kidsWithoutClasses,
            'classStats' => $classStats,
            'teachersCNT' => $teachersCNT,
            'kidsCNT' => $kidsCNT,
            'maleKidsCNT' => $maleKidsCNT,
            'femaleKidsCNT' => $femaleKidsCNT
        ];

        return $data;
    }

    public static function shouldInclude($PageName)
    {
        return $PageName == "/v2/sundayschool/dashboard";
    }
}
