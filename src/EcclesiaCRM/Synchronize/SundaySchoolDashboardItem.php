<?php

namespace EcclesiaCRM\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use Propel\Runtime\Propel;
use EcclesiaCRM\Service\SundaySchoolService;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\PersonQuery;

class DropDownEmailsClass {
    public $allNormal;
    public $allNormalBCC;
}


class EmailRoleClass {
    public $Parents;
    public $Teachers;
    public $Kids;
}

class SundaySchoolDashboardItem implements DashboardItemInterface
{

    public static function getDashboardItemName()
    {
        return "SundaySchoolDisplay";
    }

    protected static function getDetails ($classStats)
    {
        $sundaySchoolService = new SundaySchoolService();

        $TeachersEmails = [];
        $KidsEmails = [];
        $ParentsEmails = [];

        $TeachersIds    = [];
        $KidsIds        = [];
        $ParentsIds     = [];

        foreach ($classStats as $classStat) {
            $iGroupId = $classStat['id'];

            $rsTeachers = $sundaySchoolService->getClassByRole($iGroupId, 'Teacher');

            $thisClassChildren = $sundaySchoolService->getKidsFullDetails($iGroupId);

            foreach ($thisClassChildren as $child) {
                if ($child['dadEmail'] != '' && !in_array($child['dadEmail'], $ParentsEmails)) {
                    array_push($ParentsEmails, $child['dadEmail']);
                    array_push($ParentsIds, $child['dadId']);
                }
                if ($child['momEmail'] != '' && !in_array($child['momEmail'], $ParentsEmails)) {
                    array_push($ParentsEmails, $child['momEmail']);
                    array_push($ParentsIds, $child['momId']);
                }
                if ($child['kidEmail'] != '' && !in_array($child['kidEmail'], $KidsEmails)) {
                    array_push($KidsEmails, $child['kidEmail']);
                    array_push($KidsIds, $child['kidId']);
                }
            }

            $teachersProps = [];
            foreach ($rsTeachers as $teacher) {
                if ($teacher['per_Email'] != '' && !in_array($teacher['per_Email'], $TeachersEmails)) {
                    array_push($TeachersEmails, $teacher['per_Email']);
                }
                if ($teacher['per_ID'] != '' && !in_array($teacher['per_ID'], $TeachersIds)) {
                    array_push($TeachersIds, $teacher['per_ID']);
                }

                $ormPropLists = GroupPropMasterQuery::Create()
                    ->filterByPersonDisplay('true')
                    ->orderByPropId()
                    ->findByGroupId($iGroupId);

                $props = '';
                if ($ormPropLists->count() > 0) {
                    $person = PersonQuery::create()->findOneById($teacher['per_ID']);
                    $sPhoneCountry = MiscUtils::SelectWhichInfo($person->getCountry(), (!is_null($person->getFamily())) ? $person->getFamily()->getCountry() : null, false);

                    $sSQL = 'SELECT * FROM groupprop_' . $iGroupId . ' WHERE per_ID = ' . $teacher['per_ID'];

                    $connection = Propel::getConnection();
                    $statement = $connection->prepare($sSQL);
                    $statement->execute();
                    $aPersonProps = $statement->fetch(\PDO::FETCH_BOTH);


                    if ($ormPropLists->count() > 0) {
                        foreach ($ormPropLists as $ormPropList) {
                            $currentData = trim($aPersonProps[$ormPropList->getField()]);
                            if (strlen($currentData) > 0) {
                                $prop_Special = $ormPropList->getSpecial();

                                if ($ormPropList->getTypeId() == 11) {
                                    $prop_Special = $sPhoneCountry;
                                }

                                $props = $ormPropList->getName() /*. ":" . OutputUtils::displayCustomField($ormPropList->getTypeId(), $currentData, $prop_Special)*/ . ", ";
                            }
                        }
                    }
                }

                array_push($teachersProps, [$teacher['per_ID'] => substr($props, 0, -2)]);
            }
        }

        $allEmails = array_unique(array_merge($ParentsEmails, $KidsEmails, $TeachersEmails));
        $sEmailLink = implode(SessionUser::getUser()->MailtoDelimiter(), $allEmails).',';

        $roleEmails = new EmailRoleClass();

        $roleEmails->Parents = implode(SessionUser::getUser()->MailtoDelimiter(), $ParentsEmails).',';
        $roleEmails->Teachers = implode(SessionUser::getUser()->MailtoDelimiter(), $TeachersEmails).',';
        $roleEmails->Kids = implode(SessionUser::getUser()->MailtoDelimiter(), $KidsEmails).',';

        // Add default email if default email has been set and is not already in string
        if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
            $sEmailLink .= SessionUser::getUser()->MailtoDelimiter().SystemConfig::getValue('sToEmailAddress');
        }
        $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

        $emailLink = mb_substr($sEmailLink, 0, -3);

        $dropDown = new DropDownEmailsClass();

        $dropDown->allNormal    = MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:');
        $dropDown->allNormalBCC = MiscUtils::generateGroupRoleEmailDropdown($roleEmails, 'mailto:?bcc=');

        return ["emailLink" => $emailLink, "dropDown" => $dropDown, "cart" => ["parentIds" => $ParentsIds, "KidIds" => $KidsIds, "TeacherIds" => $TeachersIds]];
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

        $details = self::getDetails($classStats);

        $data = ['sundaySchoolClasses' => intval($groupsAndSundaySchoolStats['SundaySchoolClasses']),
            'sundaySchoolkids' => intval($groupsAndSundaySchoolStats['SundaySchoolKidsCount']),
            'SundaySchoolFamiliesCNT' => intval($groupsAndSundaySchoolStats['SundaySchoolFamiliesCount']),
            'kidsWithoutClasses' => $kidsWithoutClasses,
            'classStats' => $classStats,
            'teachersCNT' => $teachersCNT,
            'kidsCNT' => $kidsCNT,
            'maleKidsCNT' => $maleKidsCNT,
            'femaleKidsCNT' => $femaleKidsCNT,
            'emailLink' => $details['emailLink'],
            'dropDown' => $details['dropDown'],
            'cart' => $details['cart']
        ];

        return $data;
    }

    public static function shouldInclude($PageName)
    {
        return $PageName == "/v2/sundayschool/dashboard" or $PageName == "/menu";
    }
}
