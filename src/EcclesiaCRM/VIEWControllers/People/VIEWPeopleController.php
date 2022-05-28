<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/06
//

namespace EcclesiaCRM\VIEWControllers;

use EcclesiaCRM\Service\DashboardItemService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

use Propel\Runtime\Propel;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\Synchronize\PersonDashboardItem;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\ListOptionQuery;
use Propel\Runtime\ActiveQuery\Criteria;

use Slim\Views\PhpRenderer;

class VIEWPeopleController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function peopleDashboard (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/people/');

        return $renderer->render($response, 'peopledashboard.php', $this->argumentsPeopleDashboardArray());
    }

    public function argumentsPeopleDashboardArray ()
    {
        $personCount      = PersonDashboardItem::getMembersCount();
        $personStats      = PersonDashboardItem::getPersonStats();
        $demographicStats = PersonDashboardItem::getDemographic();
        $ageStats         = PersonDashboardItem::getAgeStats();

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

        $classifications = new \stdClass();
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

        $emailList = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $sEmailLink = '';
        $roleEmails = [];
        foreach ($emailList as $emailAccount) {
            $sEmail = MiscUtils::SelectWhichInfo($emailAccount['per_Email'], $emailAccount['fam_Email'], false);
            if ($sEmail) {
                /* if ($sEmailLink) // Don't put delimiter before first email
                    $sEmailLink .= SessionUser::getUser()->MailtoDelimiter(); */
                // Add email only if email address is not already in string
                if (!stristr($sEmailLink, $sEmail)) {
                    $sEmailLink .= $sEmail . SessionUser::getUser()->MailtoDelimiter();
                    $virt_RoleName = $emailAccount['virt_RoleName'];

                    $roleEmails[$virt_RoleName] .= $sEmail . SessionUser::getUser()->MailtoDelimiter();
                }
            }
        }

        $dsiS = new DashboardItemService();

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
            'demographicStats'     => $demographicStats,
            'ageStats'             => $ageStats,
            'kidsGender'           => $kidsGender,
            'adultsGender'         => $adultsGender,
            'classifications'      => $classifications,
            'sEmailLink'           => $sEmailLink,
            'roleEmails'           => $roleEmails,
            'PeopleAndSundaySchoolCountStats' => $dsiS->getAllItems(),
        ];

        return $paramsArguments;
    }

    public function peopleList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/people/');

        $sMode = $args['mode'];
        if (isset($args['gender'])) {
            $iGender = $args['gender'];
        } else {
            $iGender = -1;
        }

        if (isset($args['familyRole'])) {
            $iFamilyRole = $args['familyRole'];
        } else {
            $iFamilyRole = -1;
        }

        if (isset($args['classification'])) {
            $iClassification = $args['classification'];
        } else {
            $iClassification = -1;
        }

        /*if (array_key_exists('mode', $_GET)) {
            $sMode = InputUtils::LegacyFilterInput($_GET['mode']);
        } elseif (array_key_exists('SelectListMode', $_SESSION)) {
            $sMode = $_SESSION['SelectListMode'];
        }*/

        switch ($sMode) {
            case 'groupassign':
                $_SESSION['SelectListMode'] = $sMode;
                break;
            case 'family':
                $_SESSION['SelectListMode'] = $sMode;
                break;
            default:
                $_SESSION['SelectListMode'] = 'person';
                break;
        }

        return $renderer->render($response, 'peoplelist.php', $this->argumentsPeopleListArray($sMode,$iGender,$iFamilyRole,$iClassification));
    }

    public function argumentsPeopleListArray ($sMode='person',$iGender=-1, $iFamilyRole=-1, $iClassification=-1)
    {
        // Set the page title
        $sPageTitle = _('Advanced Search');
        if ($sMode == 'person') {
            $sPageTitle = _('Person Listing');
        } elseif ($sMode == 'groupassign') {
            $sPageTitle = _('Group Assignment Helper');
        }


        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'sCSPNonce'            => $sCSPNonce,
            'sMode'                => $sMode,
            'iGender'              => $iGender,
            'iFamilyRole'          => $iFamilyRole,
            'iClassification'     => $iClassification
        ];

        return $paramsArguments;
    }
}
