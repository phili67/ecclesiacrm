<?php

/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2019-06-16
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/
 
use Slim\Http\Request;
use Slim\Http\Response;

use Propel\Runtime\Propel;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Service\DashboardService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\ListOptionQuery;
use Propel\Runtime\ActiveQuery\Criteria;

use Slim\Views\PhpRenderer;

$app->group('/people', function () {
    $this->get('/dashboard', 'peopleDashboard' );
});


function peopleDashboard (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/people/');
    
    return $renderer->render($response, 'peopledashboard.php', argumentsPeopleDashboardArray());
}

function argumentsPeopleDashboardArray ()
{
    $dashboardService = new DashboardService();
    $personCount      = $dashboardService->getPersonCount();
    $personStats      = $dashboardService->getPersonStats();
    $familyCount      = $dashboardService->getFamilyCount();
    $groupStats       = $dashboardService->getGroupStats();
    $demographicStats = $dashboardService->getDemographic();
    $ageStats         = $dashboardService->getAgeStats();

    $adultsGender = PersonQuery::Create()
        ->filterByGender(array('1', '2'), Criteria::IN) // criteria Criteria::IN not usefull
        ->_and()->filterByFmrId( explode(",", SystemConfig::getValue('sDirRoleChild')) , Criteria::NOT_IN)
        ->_and()->useFamilyQuery()
        ->filterByDateDeactivated(null,Criteria::EQUAL)
        ->endUse()
        ->groupByGender()
        ->withColumn('COUNT(*)', 'Numb')
        ->find();

    $kidsGender = PersonQuery::Create()
        ->filterByGender(array('1', '2'), Criteria::IN) // criteria Criteria::IN not usefull
        ->_and()->filterByFmrId( explode(",", SystemConfig::getValue('sDirRoleChild')) , Criteria::IN)
        ->_and()->useFamilyQuery()
        ->filterByDateDeactivated(null,Criteria::EQUAL)
        ->endUse()
        ->groupByGender()
        ->withColumn('COUNT(*)', 'Numb')
        ->find();

    $ormClassifications = ListOptionQuery::Create()
        ->orderByOptionSequence()
        ->findById(1);

    $classifications = new stdClass();
    foreach ($ormClassifications as $classification) {
        $lst_OptionName = $classification->getOptionName();
        $classifications->$lst_OptionName = $classification->getOptionId();
    }

    $connection = Propel::getConnection();

    $sSQL = "SELECT per_Email, fam_Email, lst_OptionName as virt_RoleName FROM person_per
          LEFT JOIN family_fam ON per_fam_ID = family_fam.fam_ID
          INNER JOIN list_lst on lst_ID=1 AND per_cls_ID = lst_OptionID
          WHERE fam_DateDeactivated is  null
       AND per_ID NOT IN
          (SELECT per_ID
              FROM person_per
              INNER JOIN record2property_r2p ON r2p_record_ID = per_ID
              INNER JOIN property_pro ON r2p_pro_ID = pro_ID AND pro_Name = 'Do Not Email')";

    $statement = $connection->prepare($sSQL);
    $statement->execute();

    $emailList = $statement->fetchAll(PDO::FETCH_ASSOC);

    $sEmailLink = '';
    foreach ($emailList as $emailAccount) {
        $sEmail = MiscUtils::SelectWhichInfo($emailAccount['per_Email'], $emailAccount['fam_Email'], false);
        if ($sEmail) {
            /* if ($sEmailLink) // Don't put delimiter before first email
                $sEmailLink .= SessionUser::getUser()->MailtoDelimiter(); */
            // Add email only if email address is not already in string
            if (!stristr($sEmailLink, $sEmail)) {
                $sEmailLink .= $sEmail .= SessionUser::getUser()->MailtoDelimiter();
                $virt_RoleName = $emailAccount['virt_RoleName'];
                $roleEmails->$virt_RoleName .= $sEmail .= SessionUser::getUser()->MailtoDelimiter();
            }
        }
    }

    // Set the page title
    $sPageTitle = _('People Dashboard');

    $sRootDocument   = SystemURLs::getDocumentRoot();
    $sCSPNonce       = SystemURLs::getCSPNonce();
          
    $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
                       'sRootDocument'        => $sRootDocument,
                       'sPageTitle'           => $sPageTitle,
                       'sCSPNonce'            => $sCSPNonce,
                       'personCount'          => $personCount,
                       'personStats'          => $personStats,
                       'familyCount'          => $familyCount,
                       'demographicStats'     => $demographicStats,
                       'groupStats'           => $groupStats,
                       'ageStats'             => $ageStats,
                       'kidsGender'           => $kidsGender,
                       'adultsGender'         => $adultsGender,
                       'classifications'      => $classifications,
                       'sEmailLink'           => $sEmailLink,
                       'roleEmails'           => $roleEmails
                       ];
                       
   return $paramsArguments;
}