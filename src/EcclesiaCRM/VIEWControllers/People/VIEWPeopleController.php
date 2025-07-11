<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2024 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2024/01/06
//

namespace EcclesiaCRM\VIEWControllers;

use EcclesiaCRM\Service\DashboardItemService;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
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

use EcclesiaCRM\FamilyCustomQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;

use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\Utils\InputUtils;

use Slim\Views\PhpRenderer;

class VIEWPeopleController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function peopleDashboard (ServerRequest $request, Response $response, array $args): Response {
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

    public function peopleList (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        $sMode = 'none';

        if (isset($args['mode'])) {
            $sMode = $args['mode'];
        } else {
            $sMode = $_SESSION['SelectListMode'];
        }

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

        switch ($sMode) {
            case 'groupassign':
                $_SESSION['SelectListMode'] = $sMode;
                break;
            case 'family':
                $_SESSION['SelectListMode'] = $sMode;
                break;
            case 'person':
                $_SESSION['SelectListMode'] = $sMode;
                break;
            default:
                $_SESSION['SelectListMode'] = 'none';
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

    

    public function personEditor (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        $iPersonID = -1;

        if (isset ($args['personId'])) {
            $iPersonID = InputUtils::LegacyFilterInput($args['personId'], 'int');
        }

        $iFamilyID = -1;

        if (isset ($args['FamilyID'])) {
            $iFamilyID = InputUtils::LegacyFilterInput($args['FamilyID'], 'int');
        }        

        if ( !(SessionUser::getUser()->isEditRecordsEnabled() ||
            SessionUser::getUser()->isEditSelfEnabled() )  ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'personEditor.php', $this->argumentsPeoplePersonEditorArray($iPersonID, $iFamilyID));
    }

    public function argumentsPeoplePersonEditorArray ($iPersonID, $iFamilyID) {
        $sPageTitle = _("Person Editor");

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => SystemURLs::getDocumentRoot(),
            'CSPNonce'                  => SystemURLs::getCSPNonce(),
            'sPageTitle'                => $sPageTitle,
            'iPersonID'                 => $iPersonID,
            'iFamilyID'                 => $iFamilyID
        ];
    }

    public function personview (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        $personId = InputUtils::LegacyFilterInput($args['personId'], 'int');

        $mode = 'none';
        if (isset($args['mode'])) {
            $mode = InputUtils::LegacyFilterInput($args['mode']);
        }

        $res = $this->argumentsPeoplePersonViewArray($personId, $mode);

        if ( $res['error'] ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/' . $res['link']);
        }

        return $renderer->render($response, 'personview.php', $res);
    }

    public function argumentsPeoplePersonViewArray ($iPersonID, $mode = 'none')
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

        $bDocuments = false;

        if ( $mode == 'Documents' ) {
            $bDocuments = true;
        }

        $bEDrive = false;

        if ( $mode == 'eDrive' ) {
            $bEDrive = true;
        }

        $bGroup = false;

        if ( $mode == 'Group' ) {
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
        $plaintextMailingAddress = $person->getAddress();

        //Get a formatted mailing address to use as display to the user.
        MiscUtils::SelectWhichAddress($Address1, $Address2, $person->getAddress1(), $person->getAddress2(), $famAddress1, $famAddress2, true);
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

        $bOkToEdit = (SessionUser::getUser()->isEditRecordsEnabled() ||
            (SessionUser::getUser()->isEditSelfEnabled() && $person->getId() == SessionUser::getUser()->getPersonId()) ||
            (SessionUser::getUser()->isEditSelfEnabled() && $person->getFamId() == SessionUser::getUser()->getPerson()->getFamId())
        );

        // by default in the next previous person : dectivated are not showned
        // find the next personID
        $previous_id = 0;
        $next_id = 0;        

        $connection = Propel::getConnection();

        $sSQL = "SELECT t.next_id 
        FROM (
            SELECT per_ID, 
                LEAD(per_ID, 1) OVER (ORDER BY per_LastName ASC, per_FirstName ASC) AS next_id 
            FROM person_per
            WHERE per_DateDeactivated IS NULL 
        ) t 
        WHERE t.per_ID = ".$iPersonID.";";

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $next = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (count($next) ) {
            $next_id = (int)$next[0]['next_id'];
        }

        // get previous id
        $sSQL = "SELECT t.previous_id 
        FROM (
            SELECT per_ID, 
                LEAD(per_ID, 1) OVER (ORDER BY per_LastName DESC, per_FirstName DESC) AS previous_id 
            FROM person_per
            WHERE per_DateDeactivated IS NULL 
        ) t 
        WHERE t.per_ID = ".$iPersonID.";";

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $previous = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (count($previous) ) {
            $previous_id = (int)$previous[0]['previous_id'];
        }


        $sAssignedGroups = "";

        // Set the page title and include HTML header
        $sPageTitle = _('Person Profile');
        $sPageTitleSpan = $sPageTitle . ' <span style="float:right"><div class="btn-group">';
        if ($previous_id > 0) {
            $sPageTitleSpan .= '<button title="' . _('Previous Person') . '" class="btn btn-round btn-secondary mat-raised-button" type="button" onclick="location.href=\'' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $previous_id . '\'">
        <span class="mat-button-wrapper"><i class="far fa-hand-point-left"></i></span>
        <div class="mat-button-ripple mat-ripple" ></div>
        <div class="mat-button-focus-overlay"></div>
        </button>';
        }

        $sPageTitleSpan .= '<button title="' . _('Person List') . '" class="btn btn-round btn-secondary mat-raised-button"  type="button" onclick="location.href=\'' . SystemURLs::getRootPath() . '/v2/personlist\'">
        <span class="mat-button-wrapper"><i class="fas fa-list-ul"></i></span>
        <div class="mat-button-ripple mat-ripple" ></div>
        <div class="mat-button-focus-overlay"></div>
        </button>';

        if ($next_id > 0) {
            $sPageTitleSpan .= '<button title="' . _('Next Person') . '" class="btn btn-round btn-secondary mat-raised-button" type="button" onclick="location.href=\'' . SystemURLs::getRootPath() . '/v2/people/person/view/' . $next_id . '\'">
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
            'bDocuments'            => $bDocuments,
            'bEDrive'               => $bEDrive,
            'bGroup'                => $bGroup,
            'bOkToEdit'                 => $bOkToEdit,        
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
            'familyInfos'           => [
                'iFamilyID'             => $iFamilyID,
                'sFamilyEmails'         => $sFamilyEmails
            ],
            'PersonInfos'   => [
                'iPersonID'             => $iPersonID,
                'singlePerson'          => $singlePerson,
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
            'isMailChimpActive'         => $mailchimp->isActive(),
            'iLittleMapZoom'            => $iLittleMapZoom,
            'sMapProvider'              => $sMapProvider,
            'sGoogleMapKey'             => $sGoogleMapKey
        ];

        return $paramsArguments;
    }


    public function familyview (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        $iFamilyID = $args['famId'];

        if (!empty($args['famId'])) {
            $iFamilyID = InputUtils::LegacyFilterInput($args['famId'], 'int');
        }

        $mode = 'TimeLine';
        if (!empty($args['mode'])) {
            $mode = InputUtils::LegacyFilterInput($args['mode'], 'string');
        }
        
        $res = $this->argumentsPeopleFamilyViewArray($iFamilyID, $mode);

        //Deactivate/Activate Family
        if (SessionUser::getUser()->isDeleteRecordsEnabled() && !empty($_POST['FID']) && !empty($_POST['Action'])) {
            $family = FamilyQuery::create()->findOneById($_POST['FID']);
            if ($_POST['Action'] == "Deactivate") {
                $family->deactivate();
            } elseif ($_POST['Action'] == "Activate") {
                $family->activate();
            }
            $family->save();
            
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/people/family/view/' . $_POST['FID']);
        }

        if ( $res['error'] ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/' . $res['link']);
        }

        return $renderer->render($response, 'familyview.php', $res);
    }

    public function argumentsPeopleFamilyViewArray ($iFamilyID, $mode)
    {
        $family = FamilyQuery::create()->findPk($iFamilyID);

        // we get the TimelineService
        $maxMainTimeLineItems = 20; // max number

        $timelineService = new TimelineService();
        $timelineServiceItems = $timelineService->getForFamily($iFamilyID);
        $timelineNotesServiceItems = $timelineService->getNotesForFamily($iFamilyID);

        $mailchimp = new MailChimpService();
        $curYear = (new \DateTime)->format("Y");

        

        if (SessionUser::getUser()->isFinanceEnabled()) {
            $_SESSION['sshowPledges'] = 1;
            $_SESSION['sshowPayments'] = 1;
        }        

        // by default in the next previous person : dectivated are not showned
        // find the next FamilyID
        $previous_id = $last_id = 0;
        $next_id = 0;        

        $connection = Propel::getConnection();

        /*$sSQL = "SELECT t.next_id 
        FROM (
            SELECT fam_ID, 
                LEAD(fam_ID, 1) OVER (ORDER BY fam_Name ASC) AS next_id 
            FROM family_fam
            WHERE fam_DateDeactivated IS NULL 
        ) t 
        WHERE t.fam_ID = ".$iFamilyID.";";*/

        $sSQL = "SELECT t.next_id 
        FROM (
            SELECT s1.fam_ID, 
                LEAD(s1.fam_ID, 1) OVER (ORDER BY s1.fam_Name ASC) AS next_id 
            FROM family_fam as s1
   	        LEFT JOIN person_per 
            ON s1.fam_ID = person_per.per_ID
            WHERE s1.fam_ID in (
					SELECT res.fam_ID
                    FROM family_fam, (
                        SELECT s2.fam_ID, s2.fam_DateDeactivated,  s2.fam_ID AS FamId, COUNT(person_per.per_ID) AS cnt 
                        FROM family_fam as s2
                        INNER JOIN person_per  
                        ON (s2.fam_ID=person_per.per_fam_ID) 
                        WHERE person_per.per_DateDeactivated IS NULL  AND s2.fam_DateDeactivated IS NULL  
                        GROUP BY s2.fam_ID) AS res 
                        WHERE res.cnt>1 AND family_fam.fam_ID=res.FamId ORDER BY family_fam.fam_Name ASC	
                    )
            ) t 
            WHERE t.fam_ID =".$iFamilyID.";";

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $next = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (count($next) ) {
            $next_id = (int)$next[0]['next_id'];
        }

        // get previous id
        /*$sSQL = "SELECT t.previous_id 
        FROM (
            SELECT fam_ID, 
                LEAD(fam_ID, 1) OVER (ORDER BY fam_Name DESC) AS previous_id 
            FROM family_fam
            WHERE fam_DateDeactivated IS NULL 
        ) t 
        WHERE t.fam_ID = ".$iFamilyID.";";*/
        $sSQL = "SELECT t.previous_id 
        FROM (
            SELECT s1.fam_ID, 
                LEAD(s1.fam_ID, 1) OVER (ORDER BY s1.fam_Name DESC) AS previous_id 
            FROM family_fam as s1
   	        LEFT JOIN person_per 
            ON s1.fam_ID = person_per.per_ID
            WHERE s1.fam_ID in (
					SELECT res.fam_ID
                    FROM family_fam, (
                        SELECT s2.fam_ID, s2.fam_DateDeactivated,  s2.fam_ID AS FamId, COUNT(person_per.per_ID) AS cnt 
                        FROM family_fam as s2
                        INNER JOIN person_per  
                        ON (s2.fam_ID=person_per.per_fam_ID) 
                        WHERE person_per.per_DateDeactivated IS NULL  AND s2.fam_DateDeactivated IS NULL  
                        GROUP BY s2.fam_ID) AS res 
                        WHERE res.cnt>1 AND family_fam.fam_ID=res.FamId ORDER BY family_fam.fam_Name ASC	
                    )
            ) t 
            WHERE t.fam_ID =".$iFamilyID.";";

        $statement = $connection->prepare($sSQL);
        $statement->execute();

        $previous = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (count($previous) ) {
            $previous_id = (int)$previous[0]['previous_id'];
        }

        $iCurrentUserFamID = SessionUser::getUser()->getPerson()->getFamId();

        // Get the lists of custom person fields
        $ormFamCustomFields = FamilyCustomMasterQuery::Create()
            ->orderByCustomOrder()
            ->find();

        // get family with all the extra columns created
        $rawQry = FamilyCustomQuery::create();
        foreach ($ormFamCustomFields as $customfield) {
            $rawQry->withColumn($customfield->getCustomField());
        }

        if (!is_null($rawQry->findOneByFamId($iFamilyID))) {
            $aFamCustomDataArr = $rawQry->findOneByFamId($iFamilyID)->toArray();
        }
        
        if (is_null($family)) {
            return [
                'error' => true,
                'link'  => 'members/404.php'
            ];
        }


        if (!is_null($family->getDateDeactivated())) {
            $time = new \DateTime('now');
            $newtime = $time->modify('-' . SystemConfig::getValue('iGdprExpirationDate') . ' year')->format('Y-m-d');

            if ($newtime > $family->getDateDeactivated()) {
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


        //Get all the properties
        $ormProperties = PropertyQuery::Create()
            ->filterByProClass('f')
            ->orderByProName()
            ->find();

        //Get classifications
        $ormClassifications = ListOptionQuery::Create()
            ->orderByOptionSequence()
            ->findById(1);


        //Set the spacer cell width
        $iTableSpacerWidth = 10;

        // Format the phone numbers
        $sHomePhone = MiscUtils::ExpandPhoneNumber($family->getHomePhone(), $family->getCountry(), $dummy);
        $sWorkPhone = MiscUtils::ExpandPhoneNumber($family->getWorkPhone(), $family->getCountry(), $dummy);
        $sCellPhone = MiscUtils::ExpandPhoneNumber($family->getCellPhone(), $family->getCountry(), $dummy);

        $sFamilyEmails = array();

        $bOkToEdit = (SessionUser::getUser()->isEditRecordsEnabled() || (SessionUser::getUser()->isEditSelfEnabled() && ($iFamilyID == SessionUser::getUser()->getPerson()->getFamId())));

        /* location and MAP */
        $location_available = false;

        if (!is_null($family)) {
            $lat = str_replace(",", ".", $family->getLatitude());
            $lng = str_replace(",", ".", $family->getLongitude());

            $iLittleMapZoom = SystemConfig::getValue("iLittleMapZoom");
            $sMapProvider = SystemConfig::getValue('sMapProvider');
            $sGoogleMapKey = SystemConfig::getValue('sGoogleMapKey');

            if ( !empty($lat) && !empty($lng) ) {
                $location_available = true;
            }
        }

        // Set the page title and include HTML header
        $sPageTitle = _("Family View");
        $sPageTitleSpan = $sPageTitle . '<span style="float:right"><div class="btn-group">';
        if ($previous_id > 0) {
            $sPageTitleSpan .= '<button title="' . _('Previous Family') . '" class="btn btn-round btn-secondary mat-raised-button" mat-raised-button="" type="button" onclick="location.href=\'' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $previous_id . '\'">
        <span class="mat-button-wrapper"><i class="far fa-hand-point-left"></i></span>
        <div class="mat-button-ripple mat-ripple" matripple=""></div>
        <div class="mat-button-focus-overlay"></div>
        </button>';
        }

        $sPageTitleSpan .= '<button title="' . _('Family List') . '" class="btn btn-round btn-secondary mat-raised-button" mat-raised-button="" type="button" onclick="location.href=\'' . SystemURLs::getRootPath() . '/v2/familylist\'">
        <span class="mat-button-wrapper"><i class="fas fa-list-ul"></i></span>
        <div class="mat-button-ripple mat-ripple" matripple=""></div>
        <div class="mat-button-focus-overlay"></div>
        </button>';

        if ($next_id > 0) {
            $sPageTitleSpan .= '<button title="' . _('Next Family') . '" class="btn btn-round btn-secondary mat-raised-button" mat-raised-button="" type="button" onclick="location.href=\'' . SystemURLs::getRootPath() . '/v2/people/family/view/' . $next_id . '\'">
        <span class="mat-button-wrapper"><i class="far fa-hand-point-right"></i></span>
        <div class="mat-button-ripple mat-ripple" matripple=""></div>
        <div class="mat-button-focus-overlay"></div>
        </button>
        </div>';
        }

        $sPageTitleSpan .= '</span>';

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sCSPNonce       = SystemURLs::getCSPNonce();

        return [
            'error'                     => false,
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'sPageTitle'                => $sPageTitle,
            'sPageTitleSpan'            => $sPageTitleSpan,
            'sCSPNonce'                 => $sCSPNonce,
            'iFamilyID'                 => $iFamilyID,
            'family'                    => $family,
            'iCurrentUserFamID'         => $iCurrentUserFamID,
            'maxMainTimeLineItems'      => $maxMainTimeLineItems,
            'timelineService'           => $timelineService,
            'timelineServiceItems'      => $timelineServiceItems,
            'isMailChimpActive'         => $mailchimp->isActive(),
            'curYear'                   => $curYear,
            'iCurrentUserFamID'         => $iCurrentUserFamID,
            'ormAutoPayments'           => $ormAutoPayments,
            'ormProperties'             => $ormProperties,
            'ormClassifications'        => $ormClassifications,
            'iTableSpacerWidth'         => $iTableSpacerWidth,
            'sHomePhone'                => $sHomePhone,
            'sWorkPhone'                => $sWorkPhone,
            'sCellPhone'                => $sCellPhone,
            'sFamilyEmails'             => $sFamilyEmails,
            'bOkToEdit'                 => $bOkToEdit,
            'location_available'        => $location_available,
            'lat'                       => $lat,
            'lng'                       => $lng,
            'iLittleMapZoom'            => $iLittleMapZoom,
            'sMapProvider'              => $sMapProvider,
            'sGoogleMapKey'             => $sGoogleMapKey,
            'sPageTitle'                => $sPageTitle,
            'sPageTitleSpan'            => $sPageTitleSpan,
            'mode'                      => $mode,
            'timelineNotesServiceItems' => $timelineNotesServiceItems,
            'ormFamCustomFields'        => $ormFamCustomFields,
            'aFamCustomDataArr'         => $aFamCustomDataArr
        ];
    }

    public function UpdateAllLatLon (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isShowMapEnabled()) {            
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }
    
        return $renderer->render($response, 'UpdateAllLatLon.php', $this->argumentsPeopleUpdateAllLatLonArray());
    }

    public function argumentsPeopleUpdateAllLatLonArray () {
        $sRootDocument   = SystemURLs::getDocumentRoot();

        $sPageTitle = _("Update Latitude & Longitude");

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'sPageTitle'                => $sPageTitle        
        ];

    }    

    public function geopage (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isShowMapEnabled()) {            
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }
    
        return $renderer->render($response, 'geopage.php', $this->argumentsPeopleGeoPageArray());
    }

    public function argumentsPeopleGeoPageArray () {
        // Create array with Classification Information (lst_ID = 1)
        $classifications = ListOptionQuery::create()
        ->filterById(1)
        ->orderByOptionSequence()
        ->find();

        unset($aClassificationName);
        $aClassificationName[0] = _('Unassigned');
        foreach ($classifications as $classification) {
            $aClassificationName[intval($classification->getOptionId())] = $classification->getOptionName();
        }

        // Create array with Family Role Information (lst_ID = 2)
        $familyRoles = ListOptionQuery::create()
        ->filterById(2)
        ->orderByOptionSequence()
        ->find();

        unset($aFamilyRoleName);
        $aFamilyRoleName[0] = _('Unassigned');
        foreach ($familyRoles as $familyRole) {
            $aFamilyRoleName[intval($familyRole->getOptionId())] = $familyRole->getOptionName();
        }

        // Get the Family if specified in the query string
        $iFamily = -1;
        $iNumNeighbors = 15;
        $nMaxDistance = 10;
        if (array_key_exists('Family', $_GET)) {
            $iFamily = InputUtils::LegacyFilterInput($_GET['Family'], 'int');
        }
        if (array_key_exists('NumNeighbors', $_GET)) {
            $iNumNeighbors = InputUtils::LegacyFilterInput($_GET['NumNeighbors'], 'int');
        }

        $bClassificationPost = false;
        $sClassificationList = [];
        $sCoordFileFormat = '';
        $sCoordFileFamilies = '';
        $sCoordFileName = '';

        //Is this the second pass?
        if (isset($_POST['FindNeighbors']) || isset($_POST['DataFile']) || isset($_POST['PersonIDList'])) {
            //Get all the variables from the request object and assign them locally
            $delemiter = SessionUser::getUser()->CSVExportDelemiter();
            $charset   = SessionUser::getUser()->CSVExportCharset();

            $iFamily = InputUtils::LegacyFilterInput($_POST['Family']);
            $iNumNeighbors = InputUtils::LegacyFilterInput($_POST['NumNeighbors']);
            $nMaxDistance = InputUtils::LegacyFilterInput($_POST['MaxDistance']);
            $sCoordFileName = InputUtils::LegacyFilterInput($_POST['CoordFileName']);
            if (array_key_exists('CoordFileFormat', $_POST)) {
                $sCoordFileFormat = InputUtils::LegacyFilterInput($_POST['CoordFileFormat']);
            }
            if (array_key_exists('CoordFileFamilies', $_POST)) {
                $sCoordFileFamilies = InputUtils::LegacyFilterInput($_POST['CoordFileFamilies']);
            }

            foreach ($aClassificationName as $key => $value) {
                $sClassNum = 'Classification' . $key;
                if (isset($_POST["$sClassNum"])) {
                    $bClassificationPost = true;
                    $sClassificationList[] = $key;
                }
            }
            }

            if (isset($_POST['DataFile'])) {
            $resultsByDistance = GeoUtils::FamilyInfoByDistance($iFamily);

            $texttype = 'plain';

            if ($sCoordFileFormat == 'GPSVisualizer') {
                $filename = $sCoordFileName . '.csv';
                $texttype = 'csv';
            } elseif ($sCoordFileFormat == 'StreetAtlasUSA') {
                $filename = $sCoordFileName . '.txt';
                $texttype = 'plain';
            }

            

            // Export file
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Description: File Transfer');
            header('Content-Type: text'.$texttype.';charset='.$charset);
            header("Content-Disposition: attachment; filename=".$filename);
            header('Content-Transfer-Encoding: binary');


            if ($sCoordFileFormat == 'GPSVisualizer') {
                echo "Name".$delemiter."Latitude".$delemiter."Longitude\n";
            }

            $counter = 0;

            foreach ($resultsByDistance as $oneResult) {
                if ($sCoordFileFamilies == 'NeighborFamilies') {
                    if ($counter++ == $iNumNeighbors) {
                        break;
                    }
                    if ($oneResult['Distance'] > $nMaxDistance) {
                        break;
                    }
                }

                // Skip over the ones with no data
                if ($oneResult['fam_Latitude'] == 0) {
                    continue;
                }

                if ($sCoordFileFormat == 'GPSVisualizer') {
                    echo $oneResult['fam_Name'] . $delemiter . $oneResult['fam_Latitude'] . $delemiter . $oneResult['fam_Longitude'] . "\n";
                } elseif ($sCoordFileFormat == 'StreetAtlasUSA') {
                    echo "BEGIN SYMBOL\n";
                    echo $oneResult['fam_Latitude'] . ',' . $oneResult['fam_Longitude'] . ',' . $oneResult['fam_Name'] . ',' . "Green Star\n";
                    echo "END\n";
                }
            }

            exit;
        }

        $families = FamilyQuery::create()
            ->filterByDateDeactivated(null)
            ->orderByName()
            ->find();


        $sRootDocument   = SystemURLs::getDocumentRoot();

        $sPageTitle = _("Family Geographic Utilities");
                    
        $sCSPNonce = SystemURLs::getCSPNonce();    

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'sCSPNonce'                 => $sCSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iFamily'                   => $iFamily,
            'families'                  => $families,
            'iNumNeighbors'             => $iNumNeighbors,
            'nMaxDistance'              => $nMaxDistance,
            'bClassificationPost'       => $bClassificationPost,
            'sCoordFileFormat'          => $sCoordFileFormat,
            'sCoordFileFamilies'        => $sCoordFileFamilies,
            'sCoordFileName'            => $sCoordFileName,
            'sClassificationList'       => $sClassificationList,
            'aClassificationName'       => $aClassificationName
        ];
    }        

    public function directoryreport (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isCreateDirectoryEnabled()) {            
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $cartdir = "";

        if (isset ($args['cartdir'])) {
            $cartdir = $args['cartdir'];
        }
    
        return $renderer->render($response, 'directoryreport.php', $this->argumentsPeopleDirectoryReportArray($cartdir));
    }

    public function argumentsPeopleDirectoryReportArray ($cartdir) {
        $sRootDocument   = SystemURLs::getDocumentRoot();

        $sPageTitle = _("Directory reports");

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'sPageTitle'                => $sPageTitle,
            'cartdir'                   => $cartdir
        ];

    }  

    public function lettersandlabels (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isFinanceEnabled()) {            
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }
    
        return $renderer->render($response, 'lettersandlabels.php', $this->argumentsPeopleLettersAndLabelsArray());
    }

    public function argumentsPeopleLettersAndLabelsArray () {
        $sRootDocument   = SystemURLs::getDocumentRoot();

        $sPageTitle = _("Letters and mailing labels for data confirmations");

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'sPageTitle'                => $sPageTitle
        ];

    }  
    
    public function reminderreport (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isFinanceEnabled()) {            
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }
    
        return $renderer->render($response, 'reminderreport.php', $this->argumentsPeopleReminderReportArray());
    }

    public function argumentsPeopleReminderReportArray () {
        $sRootDocument   = SystemURLs::getDocumentRoot();

        $sPageTitle = _("Pledge Reminder Report");

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'sPageTitle'                => $sPageTitle
        ];

    }  

    public function personPrint (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        $iPersonID = InputUtils::LegacyFilterInput($args['personId'], 'int');

        if ( !(SessionUser::getUser()->isEditRecordsEnabled() ||
            (SessionUser::getUser()->isEditSelfEnabled() && $iPersonID == SessionUser::getUser()->getPersonId()) ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'personPrint.php', $this->argumentsPeoplePersonPrintArray($iPersonID));
    }

    public function argumentsPeoplePersonPrintArray ($iPersonID) {
        $sRootDocument   = SystemURLs::getDocumentRoot();

        $sPageTitle = _("Printable View");

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'sPageTitle'                => $sPageTitle,
            'iPersonID'                 => $iPersonID
        ];

    }  
    
    public function familyEditor (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        $iFamilyID = -1;

        if (isset ($args['famId'])) {
            $iFamilyID = InputUtils::LegacyFilterInput($args['famId'], 'int');
        }

        if ( !(SessionUser::getUser()->isEditRecordsEnabled() ||
            SessionUser::getUser()->isEditSelfEnabled() )  ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'familyEditor.php', $this->argumentsPeopleFamilyEditorArray($iFamilyID));
    }

    public function argumentsPeopleFamilyEditorArray ($iFamilyID) {
        $sPageTitle = _("Family Editor");

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => SystemURLs::getDocumentRoot(),
            'CSPNonce'                  => SystemURLs::getCSPNonce(),
            'sPageTitle'                => $sPageTitle,
            'iFamilyID'                 => $iFamilyID
        ];
    }      

    public function personCustomFieldEditor (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'personCustomEditor.php', $this->argumentsPeoplePersonCustomEditorArray());
    }

    public function argumentsPeoplePersonCustomEditorArray () {
        $sPageTitle = _("Custom Person Fields Editor");

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => SystemURLs::getDocumentRoot(),
            'CSPNonce'                  => SystemURLs::getCSPNonce(),
            'sPageTitle'                => $sPageTitle
        ];
    } 

    public function familyCustomFieldEditor (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isMenuOptionsEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'familyCustomFieldEditor.php', $this->argumentsFamilyCustomEditorArray());
    }

    public function argumentsFamilyCustomEditorArray () {
        $sPageTitle = _("Custom Family Fields Editor");

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => SystemURLs::getDocumentRoot(),
            'CSPNonce'                  => SystemURLs::getCSPNonce(),
            'sPageTitle'                => $sPageTitle
        ];
    } 
    
    public function canvassEditor (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isCanvasserEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $iFamily = -1;
        
        if (isset($args['FamilyID'])) {
            $iFamily = InputUtils::LegacyFilterInput($args['FamilyID'], 'int');
        }

        $iFYID = -1;
        
        if (isset($args['FYID'])) {
            $iFYID = InputUtils::LegacyFilterInput($args['FYID'], 'int');
        }

        $linkBack = '';
        
        if (isset($args['linkBack'])) {
            $linkBack = InputUtils::LegacyFilterInput($args['linkBack']);            
        }

        $iCanvassID = 0;
        if (isset($args['CanvassID'])) {
            $iCanvassID = InputUtils::LegacyFilterInput($args['CanvassID']);            
        }

        return $renderer->render($response, 'canvassEditor.php', $this->argumentsCanvassEditorArray($iFamily, $iFYID, $linkBack, $iCanvassID));
    }

    public function argumentsCanvassEditorArray ($iFamily, $iFYID, $linkBack, $iCanvassID) {

        //Get Family name
        $family = FamilyQuery::Create()->findOneById ($iFamily);

        $fyStr = MiscUtils::MakeFYString($iFYID);

        if ($family->getPeople()->count() == 1) {
            $sPageTitle = $fyStr.' : '._('Canvass Input for the').' '.$family->getName()." ".$family->getPeople()[0]->getFirstName().' ('._('Person').")";
        } else {
            $sPageTitle = $fyStr.' : '._('Canvass Input for the').' '.$family->getName().' ('._('family').')';
        }

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => SystemURLs::getDocumentRoot(),
            'CSPNonce'                  => SystemURLs::getCSPNonce(),
            'sPageTitle'                => $sPageTitle,
            'iFYID'                     => $iFYID, 
            'iFamily'                   => $iFamily, 
            'linkBack'                  => str_replace("-","/", $linkBack),
            'origLinkBack'              => $linkBack,
            'iCanvassID'                => $iCanvassID
        ];
    }     

    public function canvassAutomation (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isCanvasserEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'canvassAutomation.php', $this->argumentsCanvassAutomationArray());
    }

    public function argumentsCanvassAutomationArray () 
    {
        $sPageTitle = _('Canvass Automation');

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => SystemURLs::getDocumentRoot(),
            'CSPNonce'                  => SystemURLs::getCSPNonce(),
            'sPageTitle'                => $sPageTitle
        ];
    } 

    public function familyVerify (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        $iFamilyID = -1;

        if (isset ($args['famId'])) {
            $iFamilyID = InputUtils::LegacyFilterInput($args['famId'], 'int');
        }

        if (!SessionUser::getUser()->isAdmin() or $iFamilyID == -1) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'familyVerify.php', $this->argumentsFamilyVerifyArray($iFamilyID));
    }

    public function argumentsFamilyVerifyArray ($iFamilyID) 
    {        
        return ['iFamilyID' => $iFamilyID];
    } 


    public function peopleDelete (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/people/');

        if (!SessionUser::getUser()->isDeleteRecordsEnabled()) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        // default values to make the newer versions of php happy
        $iFamilyID = 0;
        $iDonationFamilyID = 0;
        $iPersonId = 0;
        $Members = 'No';
        $Confirmed = 'No';

        if (!empty($args['Confirmed'])) {
            $Confirmed = InputUtils::LegacyFilterInput($args['Confirmed'], 'string');
        }
        
        if (!empty($args['Members'])) {
            $Members = InputUtils::LegacyFilterInput($args['Members'], 'string');
        }

        if (!empty($args['FamilyID'])) {
            $iFamilyID = InputUtils::LegacyFilterInput($args['FamilyID'], 'int');
        }

        if (!empty($args['PersonID'])) {
            $iPersonId = InputUtils::LegacyFilterInput($args['PersonID'], 'int');
        }

        if (isset($_POST['DonationFamilyID'])) {
            $iDonationFamilyID = InputUtils::LegacyFilterInput($_POST['DonationFamilyID'], 'int');
        }
        
        if (isset($_POST['CancelFamily'])) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . "/v2/people/family/view/".$iFamilyID);
        }

        return $renderer->render($response, 'selectDelete.php', $this->argumentsPeopleDeleteArray($iFamilyID, $iDonationFamilyID, $iPersonId, $Members, $Confirmed));
    }

    public function argumentsPeopleDeleteArray ($iFamilyID, $iDonationFamilyID, $iPersonId, $Members, $Confirmed) 
    {        
        $numberPersons = 0;

        if ( $iPersonId > 0 ) {
            $person    = PersonQuery::Create()->findOneById($iPersonId);
            $iFamilyID = $person->getFamId();
        } else {
            $family = FamilyQuery::Create()->findOneById($iFamilyID);

            if (!is_null($family)) {
                $numberPersons = $family->getPeople()->count();
            }

            if (PersonQuery::Create()->findOneByFamId($iFamilyID)) {
                $iPersonId = PersonQuery::Create()->findOneByFamId($iFamilyID)->getId();
            }
        }

        //Set the Page Title
        if ($numberPersons > 1) {
            $sPageTitle = _('Family Delete Confirmation');
        } elseif ($numberPersons == 1) {
            $sPageTitle = _('Person Delete Confirmation');
        } else {
            $sPageTitle = _('Remove Address');
        }

        return [
            'sRootPath'                 => SystemURLs::getRootPath(),
            'sRootDocument'             => SystemURLs::getDocumentRoot(),
            'CSPNonce'                  => SystemURLs::getCSPNonce(),
            'sPageTitle'                => $sPageTitle,
            'iFamilyID'                 => $iFamilyID,
            'iDonationFamilyID'         => $iDonationFamilyID,
            'iPersonId'                 => $iPersonId,
            'numberPersons'             => $numberPersons,
            'Confirmed'                 => $Confirmed,
            'Members'                   => $Members
        ];
    } 
}
