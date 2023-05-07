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


use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\Map\Record2propertyR2pTableMap;
use EcclesiaCRM\Map\PropertyTableMap;
use EcclesiaCRM\Map\PropertyTypeTableMap;
use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\Service\TimelineService;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\AutoPaymentQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\FamilyQuery;


use EcclesiaCRM\Map\Person2group2roleP2g2rTableMap;
use EcclesiaCRM\Map\PersonVolunteerOpportunityTableMap;
use EcclesiaCRM\Map\VolunteerOpportunityTableMap;
use EcclesiaCRM\Map\GroupTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\ListOptionIconTableMap;

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

                    if (array_key_exists($virt_RoleName, $roleEmails)) {
                        $roleEmails[$virt_RoleName] .= $sEmail . SessionUser::getUser()->MailtoDelimiter();
                    } else {
                        $roleEmails[$virt_RoleName] = '';
                    }
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

    public function personview (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/people/');

        $personId = $args['personId'];

        $res = $this->argumentsPeoplePersonViewArray($personId);

        if ( $res['error'] ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/' . $res['link']);
        }

        return $renderer->render($response, 'personview.php', $res);
    }

    public function argumentsPeoplePersonViewArray ($iPersonID)
    {
        // for ckeditor fonts
        $contentsExternalCssFont = SystemConfig::getValue("sMailChimpContentsExternalCssFont");
        $extraFont = SystemConfig::getValue("sMailChimpExtraFont");

        $user = UserQuery::Create()->findPk($iPersonID);

        // we get the TimelineService
        $maxMainTimeLineItems = 20; // max number

        $timelineService = new TimelineService();
        $timelineServiceItems = $timelineService->getForPerson($iPersonID);

        $timelineNotesServiceItems = $timelineService->getNotesForPerson($iPersonID);

        // we get the MailChimp Service
        $mailchimp = new MailChimpService();

        // person informations
        $userName = '';
        $userDir = '';
        $Currentpath = '';
        $currentNoteDir = '';
        $directories = [];

        if (!is_null($user)) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName = $user->getUserName();
            $currentpath = $user->getCurrentpath();

            $currentNoteDir = SystemURLs::getRootPath() . "/" . $realNoteDir . "/" . $userName;

            $directories = MiscUtils::getDirectoriesInPath($currentNoteDir . $currentpath);
        }

        $bDocuments = false;

        if (array_key_exists('documents', $_GET)) {
            $bDocuments = true;
        }

        $bEDrive = false;

        if (array_key_exists('edrive', $_GET)) {
            $bEDrive = true;
        }

        $bGroup = false;

        if (array_key_exists('group', $_GET)) {
            $bGroup = true;
        }

        // Get this person's data
        $person = PersonQuery::create('a')
            ->leftJoinFamily()
            ->addAlias('cls', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array(PersonTableMap::alias('a', PersonTableMap::COL_PER_CLS_ID),
                        ListOptionTableMap::alias('cls', ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_ID), 1)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('ClassName', 'COALESCE('. ListOptionTableMap::alias( 'cls', ListOptionTableMap::COL_LST_OPTIONNAME." , 'Unassigned')"))
            ->addAsColumn('ClassID', 'COALESCE('. ListOptionTableMap::alias( 'cls', ListOptionTableMap::COL_LST_OPTIONID." , 'Unassigned')"))
            ->addAlias('clsicon', ListOptionIconTableMap::TABLE_NAME)
            ->addJoin(ListOptionTableMap::alias('cls', ListOptionTableMap::COL_LST_OPTIONID),
                ListOptionTableMap::alias('clsicon', ListOptionIconTableMap::COL_LST_IC_LST_OPTION_ID),
                Criteria::LEFT_JOIN)
            ->addAsColumn('ClassIcon', ListOptionIconTableMap::COL_LST_IC_LST_URL)
            ->addAlias('fmr', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array(PersonTableMap::alias('a', PersonTableMap::COL_PER_FMR_ID),
                        ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("fmr",ListOptionTableMap::COL_LST_ID), 2)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('FamRole', ListOptionTableMap::Alias("fmr",ListOptionTableMap::COL_LST_OPTIONNAME))
            ->addAlias('b', PersonTableMap::TABLE_NAME)
            ->addJoin(PersonTableMap::alias('a', PersonTableMap::COL_PER_ENTEREDBY),
                PersonTableMap::alias('b', PersonTableMap::COL_PER_ID), Criteria::LEFT_JOIN)
            ->addAsColumn('EnteredFirstName', PersonTableMap::alias('b',PersonTableMap::COL_PER_FIRSTNAME))
            ->addAsColumn('EnteredLastName', PersonTableMap::alias('b',PersonTableMap::COL_PER_LASTNAME))
            ->addAsColumn('EnteredId', PersonTableMap::alias('b',PersonTableMap::COL_PER_ID))
            ->addAlias('c', PersonTableMap::TABLE_NAME)
            ->addJoin(PersonTableMap::alias('a', PersonTableMap::COL_PER_EDITEDBY),
                PersonTableMap::alias('c', PersonTableMap::COL_PER_ID), Criteria::LEFT_JOIN)
            ->addAsColumn('EditedFirstName', PersonTableMap::alias('c',PersonTableMap::COL_PER_FIRSTNAME))
            ->addAsColumn('EditedLastName', PersonTableMap::alias('c',PersonTableMap::COL_PER_LASTNAME))
            ->addAsColumn('EditedId', PersonTableMap::alias('c',PersonTableMap::COL_PER_ID))
            ->filterById($iPersonID)
            ->findOne();

        $connection = Propel::getConnection();

        if (is_null($person)) {
            return [
                'error' => true,
                'link'  => 'members/404.php?type=Person'
            ];
        }

        if ($person->getDateDeactivated() != null) {
            $time = new \DateTime('now');
            $new_time = $time->modify('-' . SystemConfig::getValue('iGdprExpirationDate') . ' year')->format('Y-m-d');

            if ($new_time > $person->getDateDeactivated()) {
                if (!SessionUser::getUser()->isGdrpDpoEnabled()) {
                    return [
                        'error' => true,
                        'link'  => 'members/404.php?type=Person'
                    ];
                }
            } else if (!SessionUser::getUser()->isEditRecordsEnabled()) {
                return [
                    'error' => true,
                    'link'  => 'members/404.php?type=Person'
                ];
            }
        }

        $ormAssignedProperties = Record2propertyR2pQuery::Create()
            ->addJoin(Record2propertyR2pTableMap::COL_R2P_PRO_ID, PropertyTableMap::COL_PRO_ID, Criteria::LEFT_JOIN)
            ->addJoin(PropertyTableMap::COL_PRO_PRT_ID, PropertyTypeTableMap::COL_PRT_ID, Criteria::LEFT_JOIN)
            ->addAsColumn('ProName', PropertyTableMap::COL_PRO_NAME)
            ->addAsColumn('ProId', PropertyTableMap::COL_PRO_ID)
            ->addAsColumn('ProPrtId', PropertyTableMap::COL_PRO_PRT_ID)
            ->addAsColumn('ProPrompt', PropertyTableMap::COL_PRO_PROMPT)
            ->addAsColumn('ProName', PropertyTableMap::COL_PRO_NAME)
            ->addAsColumn('ProTypeName', PropertyTypeTableMap::COL_PRT_NAME)
            ->where(PropertyTableMap::COL_PRO_CLASS . "='p'")
            ->addAscendingOrderByColumn('ProName')
            ->addAscendingOrderByColumn('ProTypeName')
            ->findByR2pRecordId($iPersonID);

        $iFamilyID = $person->getFamId();

        //Get the automatic payments for this family
        $ormAutoPayments = AutoPaymentQuery::create()
            ->leftJoinPerson()
            ->withColumn('Person.FirstName', 'EnteredFirstName')
            ->withColumn('Person.LastName', 'EnteredLastName')
            ->withColumn('Person.FirstName', 'EnteredFirstName')
            ->withColumn('Person.LastName', 'EnteredLastName')
            ->leftJoinDonationFund()
            ->withColumn('DonationFund.Name', 'fundName')
            ->orderByNextPayDate()
            ->findByFamilyid($iFamilyID);


        // Get the lists of custom person fields
        $ormPersonCustomFields = PersonCustomMasterQuery::Create()
            ->orderByCustomOrder()
            ->find();

        // Get the custom field data for this person.
        $rawQry = PersonCustomQuery::create();
        foreach ($ormPersonCustomFields as $customfield) {
            $rawQry->withColumn($customfield->getCustomField());
        }

        if (!is_null($rawQry->findOneByPerId($iPersonID))) {
            $aCustomData = $rawQry->findOneByPerId($iPersonID)->toArray();
        }

        // Get the Groups this Person is assigned to
        $ormAssignedGroups = Person2group2roleP2g2rQuery::Create()
            ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
            ->addMultipleJoin(
                array(
                    array(Person2group2roleP2g2rTableMap::COL_P2G2R_RLE_ID, ListOptionTableMap::COL_LST_OPTIONID),
                    array(GroupTableMap::COL_GRP_ROLELISTID, ListOptionTableMap::COL_LST_ID)),
                Criteria::LEFT_JOIN)
            ->add(ListOptionTableMap::COL_LST_OPTIONNAME, null, Criteria::ISNOTNULL)
            ->Where(Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID . ' = ' . $iPersonID . ' ORDER BY grp_Name')
            ->addAsColumn('roleName', ListOptionTableMap::COL_LST_OPTIONNAME)
            ->addAsColumn('groupName', GroupTableMap::COL_GRP_NAME)
            ->addAsColumn('hasSpecialProps', GroupTableMap::COL_GRP_HASSPECIALPROPS)
            ->find();

        // Get the volunteer opportunities this Person is assigned to
        $ormAssignedVolunteerOpps = VolunteerOpportunityQuery::Create()
            ->addJoin(VolunteerOpportunityTableMap::COL_VOL_ID, PersonVolunteerOpportunityTableMap::COL_P2VO_VOL_ID, Criteria::LEFT_JOIN)
            ->Where(PersonVolunteerOpportunityTableMap::COL_P2VO_PER_ID . ' = ' . $iPersonID)
            ->find();

        // Get all the volunteer opportunities
        $ormVolunteerOpps = VolunteerOpportunityQuery::Create()->orderByName()->find();

        //Get all the properties
        $ormProperties = PropertyQuery::Create()
            ->filterByProClass('p')
            ->orderByProName()
            ->find();

        $dBirthDate = OutputUtils::FormatBirthDate($person->getBirthYear(), $person->getBirthMonth(), $person->getBirthDay(), '-', $person->getFlags());

        // Assign the values locally, after selecting whether to display the family or person information

        if (!is_null($person->getFamily())) {
            $famAddress1 = $person->getFamily()->getAddress1();
            $famAddress2 = $person->getFamily()->getAddress2();
            $famCity = $person->getFamily()->getCity();
            $famSate = $person->getFamily()->getState();
            $famZip = $person->getFamily()->getZip();
            $famCountry = $person->getFamily()->getCountry();
            $famHompePhone = $person->getFamily()->getHomePhone();
            $famWorkPhone = $person->getFamily()->getWorkPhone();
            $famCellPhone = $person->getFamily()->getCellPhone();
            $famEmail = $person->getFamily()->getEmail();
        }

        //Get an unformatted mailing address to pass as a parameter to a google maps search
        MiscUtils::SelectWhichAddress($Address1, $Address2, $person->getAddress1(), $person->getAddress2(), $famAddress1, $famAddress2, false);
        $sCity = MiscUtils::SelectWhichInfo($person->getCity(), $famCity, false);
        $sState = MiscUtils::SelectWhichInfo($person->getState(), $famSate, false);
        $sZip = MiscUtils::SelectWhichInfo($person->getZip(), $famZip, false);
        $sCountry = MiscUtils::SelectWhichInfo($person->getCountry(), $famCountry, false);
        $plaintextMailingAddress = $person->getAddress();

        //Get a formatted mailing address to use as display to the user.
        MiscUtils::SelectWhichAddress($Address1, $Address2, $person->getAddress1(), $person->getAddress2(), $famAddress1, $famAddress2, true);
        $sCity = MiscUtils::SelectWhichInfo($person->getCity(), $famCity, true);
        $sState = MiscUtils::SelectWhichInfo($person->getState(), $famSate, true);
        $sZip = MiscUtils::SelectWhichInfo($person->getZip(), $famZip, true);
        $sCountry = MiscUtils::SelectWhichInfo($person->getCountry(), $famCountry, true);
        $formattedMailingAddress = $person->getAddress();

        $sPhoneCountry = MiscUtils::SelectWhichInfo($person->getCountry(), $famCountry, false);
        $sHomePhone = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($person->getHomePhone(), $sPhoneCountry, $dummy),
            MiscUtils::ExpandPhoneNumber($famHompePhone, $famCountry, $dummy), true);
        $sHomePhoneUnformatted = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($person->getHomePhone(), $sPhoneCountry, $dummy),
            MiscUtils::ExpandPhoneNumber($famHompePhone, $famCountry, $dummy), false);
        $sWorkPhone = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($person->getWorkPhone(), $sPhoneCountry, $dummy),
            MiscUtils::ExpandPhoneNumber($famWorkPhone, $famCountry, $dummy), true);
        $sWorkPhoneUnformatted = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($person->getWorkPhone(), $sPhoneCountry, $dummy),
            MiscUtils::ExpandPhoneNumber($famWorkPhone, $famCountry, $dummy), false);
        $sCellPhone = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($person->getCellPhone(), $sPhoneCountry, $dummy),
            MiscUtils::ExpandPhoneNumber($famCellPhone, $famCountry, $dummy), true);
        $sCellPhoneUnformatted = MiscUtils::SelectWhichInfo(MiscUtils::ExpandPhoneNumber($person->getCellPhone(), $sPhoneCountry, $dummy),
            MiscUtils::ExpandPhoneNumber($famCellPhone, $famCountry, $dummy), false);
        $sEmail = MiscUtils::SelectWhichInfo($person->getEmail(), $famEmail, true);
        $sUnformattedEmail = MiscUtils::SelectWhichInfo($person->getEmail(), $famEmail, false);

        if ($person->getEnvelope() > 0) {
            $sEnvelope = $person->getEnvelope();
        } else {
            $sEnvelope = _('Not assigned');
        }

        $iTableSpacerWidth = 10;

        $isMailChimpActive = $mailchimp->isActive();

        $bOkToEdit = (SessionUser::getUser()->isEditRecordsEnabled() ||
            (SessionUser::getUser()->isEditSelfEnabled() && $person->getId() == SessionUser::getUser()->getPersonId()) ||
            (SessionUser::getUser()->isEditSelfEnabled() && $person->getFamId() == SessionUser::getUser()->getPerson()->getFamId())
        );

        $ormNextPersons = PersonQuery::Create()
            ->orderByLastName()
            ->find();

        $last_id = 0;
        $next_id = 0;
        $capture_next = 0;

        foreach ($ormNextPersons as $ormNextPerson) {
            $pid = $ormNextPerson->getId();
            if ($capture_next == 1) {
                $next_id = $pid;
                break;
            }
            if ($pid == $iPersonID) {
                $previous_id = $last_id;
                $capture_next = 1;
            } else {
                $last_id = $pid;
            }
        }

        $sAssignedGroups = "";

        // Set the page title and include HTML header
        $sPageTitle = _('Person Profile');
        $sPageTitleSpan = $sPageTitle . '<span style="float:right"><div class="btn-group">';
        if ($previous_id > 0) {
            $sPageTitleSpan .= '<button title="' . _('Previous Person') . '" class="btn btn-round btn-info mat-raised-button" type="button" onclick="location.href=\'' . SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . $previous_id . '\'">
        <span class="mat-button-wrapper"><i class="far fa-hand-point-left"></i></span>
        <div class="mat-button-ripple mat-ripple" ></div>
        <div class="mat-button-focus-overlay"></div>
        </button>';
        }

        $sPageTitleSpan .= '<button title="' . _('Person List') . '" class="btn btn-round btn-info mat-raised-button"  type="button" onclick="location.href=\'' . SystemURLs::getRootPath() . '/v2/personlist\'">
        <span class="mat-button-wrapper"><i class="fas fa-list-ul"></i></span>
        <div class="mat-button-ripple mat-ripple" ></div>
        <div class="mat-button-focus-overlay"></div>
        </button>';

        if ($next_id > 0) {
            $sPageTitleSpan .= '<button title="' . _('Next Person') . '" class="btn btn-round btn-info mat-raised-button" type="button" onclick="location.href=\'' . SystemURLs::getRootPath() . '/PersonView.php?PersonID=' . $next_id . '\'">
        <span class="mat-button-wrapper"><i class="far fa-hand-point-right"></i></span>
        <div class="mat-button-ripple mat-ripple"></div>
        <div class="mat-button-focus-overlay"></div>
        </button>
        </div>';
        }

        /* location and MAP */
        $location_available = false;

        if ( ! is_null($person->getFamily()) ) {
            $lat = str_replace(",",".",$person->getFamily()->getLatitude());
            $lng = str_replace(",",".",$person->getFamily()->getLongitude());

            $iLittleMapZoom = SystemConfig::getValue("iLittleMapZoom");
            $sMapProvider = SystemConfig::getValue('sMapProvider');
            $sGoogleMapKey = SystemConfig::getValue('sGoogleMapKey');

            if ( !empty($lat) && !empty($lng) ) {
                $location_available = true;
            }
        }

        $sPageTitleSpan .= '</span>';

        if (!empty($person->getDateDeactivated())) {
            ?>
            <div class="alert alert-warning">
                <strong><?= _("This Person is Deactivated") ?> </strong>
            </div>
            <?php
        }


        $persons = PersonQuery::Create()->filterByDateDeactivated(null)->findByFamId($iFamilyID);

        $singlePerson = false;
        if (!is_null($persons) && $persons->count() == 1) {
            $singlePerson = true;
        }

        $sFamilyEmails = [];
        $family = FamilyQuery::create()->findOneById($iFamilyID);

        if (!is_null($family)) {
            foreach ($family->getActivatedPeople() as $per) {
                $tmpEmail = $per->getEmail();
                if ($tmpEmail != "") {
                    $sFamilyEmails[] = $tmpEmail;
                }
            }
        }


        // Set the page title
        $sPageTitle = _('Person Profile');

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = [
            'error'                 => false,
            'sRootPath'             => SystemURLs::getRootPath(),
            'sRootDocument'         => $sRootDocument,
            'sPageTitle'            => $sPageTitle,
            'connection'            => $connection,// propel connection
            'sPageTitleSpan'        => $sPageTitleSpan,
            'sCSPNonce'             => $sCSPNonce,
            'contentsExternalCssFont' => $contentsExternalCssFont,
            'extraFont'               => $extraFont,
            'maxMainTimeLineItems'    => $maxMainTimeLineItems,
            'timelineServiceItems'    => $timelineServiceItems,
            'timelineNotesServiceItems' => $timelineNotesServiceItems,
            'userName'              => $userName,
            'Currentpath'           => $Currentpath,
            'directories'           => $directories,
            'bDocuments'            => $bDocuments,
            'bEDrive'               => $bEDrive,
            'bGroup'                => $bGroup,
            'ormAssignedProperties' => $ormAssignedProperties,
            'ormAutoPayments'       => $ormAutoPayments,
            'ormPersonCustomFields' => $ormPersonCustomFields,
            'aCustomData'           => $aCustomData,
            'sAssignedGroups'       => $sAssignedGroups,
            'ormAssignedGroups'     => $ormAssignedGroups,
            'ormAssignedVolunteerOpps'  => $ormAssignedVolunteerOpps,
            'ormVolunteerOpps'      => $ormVolunteerOpps,
            'ormProperties'         => $ormProperties,
            'dBirthDate'            => $dBirthDate,
            //'familyInfos'           => [
                'iFamilyID'             => $iFamilyID,
                'family'                => $family,
                'sFamilyEmails'         => $sFamilyEmails,
                'famAddress1'           => $famAddress1,
                'famAddress2'           => $famAddress2,
                'famCity'               => $famCity,
                'famSate'               => $famSate,
                'famZip'                => $famZip,
                'famCountry'            => $famCountry,
                'famHompePhone'         => $famHompePhone,
                'famWorkPhone'          => $famWorkPhone,
                'famCellPhone'          => $famCellPhone,
                'famEmail'              => $famEmail,
                'persons'               => $persons,
            //],
            'PersonInfos'           => [
                'iPersonID'             => $iPersonID,
                'person'                => $person,            
                'sPhoneCountry'         => $sPhoneCountry,
                'sHomePhone'            => $sHomePhone,
                'sHomePhoneUnformatted' => $sHomePhoneUnformatted,
                'sWorkPhone'            => $sWorkPhone,
                'sWorkPhoneUnformatted' => $sWorkPhoneUnformatted,
                'sCellPhone'            => $sCellPhone,
                'sCellPhoneUnformatted' => $sCellPhoneUnformatted,
                'sEmail'                => $sEmail,
                'plaintextMailingAddress' => $plaintextMailingAddress,
                'formattedMailingAddress' => $formattedMailingAddress,
                'sUnformattedEmail'     => $sUnformattedEmail,
                'user'                  => $user,
                'location_available'    => $location_available,
                'lat'                   => $lat,
                'lng'                   => $lng,                
            ],
            'iTableSpacerWidth'         => $iTableSpacerWidth,
            'isMailChimpActive'         => $isMailChimpActive,
            'bOkToEdit'                 => $bOkToEdit,
            'iLittleMapZoom'            => $iLittleMapZoom,
            'sMapProvider'              => $sMapProvider,
            'sGoogleMapKey'             => $sGoogleMapKey,
        ];

        return $paramsArguments;
    }
}
