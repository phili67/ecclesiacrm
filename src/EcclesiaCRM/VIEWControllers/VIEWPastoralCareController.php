<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\Service\PastoralCareService;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\Theme;


use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\PersonCustomQuery;


use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\PastoralCareTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\ListOptionIconTableMap;
use EcclesiaCRM\Map\Person2group2roleP2g2rTableMap;
use EcclesiaCRM\Map\GroupTableMap;

// nouveau code
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\FamilyCustomQuery;

use EcclesiaCRM\Utils\GeoUtils;
use EcclesiaCRM\Service\MailChimpService;


use Propel\Runtime\ActiveQuery\Criteria;

use Slim\Views\PhpRenderer;
use function DI\create;

class VIEWPastoralCareController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function renderPastoralCareListForUser (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/pastoralcare/');

        if ( !( SessionUser::getUser()->isPastoralCareEnabled() && SessionUser::getUser()->isMenuOptionsEnabled() || SessionUser::getId() == $args['UserID'] ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'pastoralcarelistforuser.php', $this->argumentsPastoralCareListForUserArray($args['UserID']));
    }

    public function argumentsPastoralCareListForUserArray ($UserID)
    {
        $user = UserQuery::create()->findOneByPersonId($UserID);

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $choice = SystemConfig::getValue('sPastoralcarePeriod');

        $date = new \DateTime('now');

        switch ($choice) {
            case 'Yearly 1':// choice 1 : Year-01-01 to Year-12-31
                $realDate = $date->format('Y') . "-01-01";

                $start = new \DateTime($realDate);

                $startPeriod = $start->format('Y-m-d');

                $start->add(new \DateInterval('P1Y'));
                $start->sub(new \DateInterval('P1D'));

                $endPeriod = $start->format('Y-m-d');
                break;
            case '365': // choice 2 : one year before now
                $date->add(new \DateInterval('P1D'));
                $endPeriod = $date->format('Y-m-d');
                $date->sub(new \DateInterval('P366D'));
                $startPeriod = $date->format('Y-m-d');
                break;
            case 'Yearly 2':// choice 3 : from september to september
                if ((int)$date->format('m') < 9) {
                    $realDate = ($date->format('Y') - 1) . "-09-01";
                } else {
                    $realDate = $date->format('Y') . "-09-01";
                }

                $start = new \DateTime($realDate);

                $startPeriod = $start->format('Y-m-d');

                $start->add(new \DateInterval('P1Y'));
                $start->sub(new \DateInterval('P1D'));

                $endPeriod = $start->format('Y-m-d');
                break;
        }

        $members = PastoralCareQuery::Create()
            ->filterByDate(array("min" => $startPeriod, "max" => $endPeriod))
            ->usePersonRelatedByPersonIdQuery()
            ->addAsColumn('FollowedPersonLastName', PersonTableMap::COL_PER_LASTNAME)
            ->addAsColumn('FollowedPersonFirstName', PersonTableMap::COL_PER_FIRSTNAME)
            ->addAsColumn('FollowedPersonPerId', PersonTableMap::COL_PER_ID)
            ->endUse()
            ->useFamilyQuery()
            ->addAsColumn('FollowedFamName', FamilyTableMap::COL_FAM_NAME)
            ->addAsColumn('FollowedFamID', FamilyTableMap::COL_FAM_ID)
            ->endUse()
            ->findByPastorId($UserID);

        $realStart = (new DateTime($startPeriod))->format(SystemConfig::getValue('sDateFormatLong'));
        $realEnd = (new DateTime($endPeriod))->format(SystemConfig::getValue('sDateFormatLong'));

        $sPageTitle = _("Pastoral care list of members for")." : ".$user->getPerson()->getFullName()."<br/>". _("Period  from") . " : " . $realStart . " " . _("to") . " " . $realEnd;

        $paramsArguments = [
            'sRootPath'            => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'currentPastorId'      => $UserID,
            'sCSPNonce'            => $sCSPNonce,
            'members'              => $members->toArray()
        ];
        return $paramsArguments;
    }

    public function renderPastoralCareDashboard (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/pastoralcare/');

        if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'pastoralcaredashboard.php', $this->argumentsPastoralDashboardArray());
    }

    public function argumentsPastoralDashboardArray ()
    {
        $currentPastorId = SessionUser::getUser()->getPerson()->getID();

        $sPageTitle = _("Pastoral care Dashboard");

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $pastoralService = new PastoralCareService();

        $pastoralServiceStats = $pastoralService->stats();


        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'currentPastorId'      => $currentPastorId,
            'sDateFormatLong'      => $sDateFormatLong,
            'sCSPNonce'            => $sCSPNonce,
            'Stats'                => $pastoralServiceStats
        ];
        return $paramsArguments;
    }

    public function renderPastoralCarePerson (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/pastoralcare/');

        $personId = $args['personId'];

        if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'pastoralcareperson.php', $this->argumentsPastoralPersonListArray($personId));
    }

    public function argumentsPastoralPersonListArray ($currentPersonID=0)
    {
        $currentPastorId = SessionUser::getUser()->getPerson()->getID();


        $ormPastoralCares = PastoralCareQuery::Create()
            ->orderByDate(Criteria::DESC)
            ->leftJoinWithPastoralCareType()
            ->findByPersonId($currentPersonID);

        $ormPastors = PastoralCareQuery::Create()
            ->groupBy(PastoralCareTableMap::COL_PST_CR_PASTOR_ID)
            ->orderByPastorName(Criteria::DESC)
            ->findByPersonId($currentPersonID);

        $ormPastoralTypeCares = PastoralCareTypeQuery::Create()
            ->find();

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
            ->filterById($currentPersonID)
            ->findOne();

        $family = FamilyQuery::Create()->findOneById ( $person->getFamId() );

// Get the Groups this Person is assigned to
        $ormAssignedGroups = Person2group2roleP2g2rQuery::Create()
            ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
            ->addMultipleJoin(
                array(
                    array(Person2group2roleP2g2rTableMap::COL_P2G2R_RLE_ID, ListOptionTableMap::COL_LST_OPTIONID),
                    array(GroupTableMap::COL_GRP_ROLELISTID, ListOptionTableMap::COL_LST_ID)),
                Criteria::LEFT_JOIN)
            ->add(ListOptionTableMap::COL_LST_OPTIONNAME, null, Criteria::ISNOTNULL)
            ->Where(Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID . ' = ' . $currentPersonID . ' ORDER BY grp_Name')
            ->addAsColumn('roleName', ListOptionTableMap::COL_LST_OPTIONNAME)
            ->addAsColumn('groupName', GroupTableMap::COL_GRP_NAME)
            ->addAsColumn('hasSpecialProps', GroupTableMap::COL_GRP_HASSPECIALPROPS)
            ->find();

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
        $plaintextMailingAddress = $person->getAddress();

        $can_see_privatedata = ($person->getId() == SessionUser::getUser()->getPersonId() || $person->getFamId() == SessionUser::getUser()->getPerson()->getFamId() || SessionUser::getUser()->isSeePrivacyDataEnabled() || SessionUser::getUser()->isEditRecordsEnabled()) ? true : false;
        $dBirthDate = OutputUtils::FormatBirthDate($person->getBirthYear(), $person->getBirthMonth(), $person->getBirthDay(), '-', $person->getFlags());
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

        /* location and MAP */
        $location_available = false;

        if ( ! is_null($person->getFamily()) ) {
            $lat = str_replace(",",".",$person->getFamily()->getLatitude());
            $lng = str_replace(",",".",$person->getFamily()->getLongitude());

            $iLittleMapZoom = SystemConfig::getValue("iLittleMapZoom");
            $sMapProvider = SystemConfig::getValue('sMapProvider');
            $sGoogleMapKey = SystemConfig::getValue('sGoogleMapKey');

            if ($lat != 0 && $lng != 0) {
                $location_available = true;
            }
        }

// Get the lists of custom person fields
        $ormPersonCustomFields = PersonCustomMasterQuery::Create()
            ->orderByCustomOrder()
            ->find();

// Get the custom field data for this person.
        $rawQry = PersonCustomQuery::create();
        foreach ($ormPersonCustomFields as $customfield) {
            $rawQry->withColumn($customfield->getCustomField());
        }

        if (!is_null($rawQry->findOneByPerId($person->getId()))) {
            $aCustomData = $rawQry->findOneByPerId($person->getId())->toArray();
        }

        $sPageTitle = _("Individual Pastoral care")."  : \"".$person->getFullName()."\"";

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'ormPastoralCares'     => $ormPastoralCares,
            'currentPersonID'      => $currentPersonID,
            'currentPastorId'      => $currentPastorId,
            'ormPastors'           => $ormPastors,
            'ormPastoralTypeCares' => $ormPastoralTypeCares,
            'person'               => $person,
            'family'               => $family,
            'sDateFormatLong'      => $sDateFormatLong,
            'sCSPNonce'            => $sCSPNonce,
            'ormAssignedGroups'    => $ormAssignedGroups,
            'famAddress1'          => $famAddress1,
            'famAddress2'          => $famAddress2,
            'famCity'              => $famCity,
            'famSate'              => $famSate,
            'famZip'               => $famZip,
            'famCountry'           => $famCountry,
            'famHompePhone'        => $famHompePhone,
            'famWorkPhone'         => $famWorkPhone,
            'famCellPhone'         => $famCellPhone,
            'famEmail'             => $famEmail,
            'plaintextMailingAddress' => $plaintextMailingAddress,
            'can_see_privatedata'  => $can_see_privatedata,
            'dBirthDate'           => $dBirthDate,
            'sCity'                => $sCity,
            'sState'               => $sState,
            'sZip'                 => $sZip,
            'sCountry'             => $sCountry,
            'formattedMailingAddress' => $formattedMailingAddress,
            'sPhoneCountry'        => $sPhoneCountry,
            'sHomePhone'           => $sHomePhone,
            'sHomePhoneUnformatted' => $sHomePhoneUnformatted,
            'sWorkPhone'            => $sWorkPhone,
            'sWorkPhoneUnformatted' => $sWorkPhoneUnformatted,
            'sCellPhone'            => $sCellPhone,
            'sCellPhoneUnformatted' => $sCellPhoneUnformatted,
            'sEmail'                => $sEmail,
            'sUnformattedEmail'     => $sUnformattedEmail,
            'location_available'    => $location_available,
            'lat'                   => $lat,
            'lng'                   => $lng,
            'iLittleMapZoom'        => $iLittleMapZoom,
            'sMapProvider'          => $sMapProvider,
            'sGoogleMapKey'         => $sGoogleMapKey,
            'ormPersonCustomFields' => $ormPersonCustomFields,
            'aCustomData'           => $aCustomData
        ];
        return $paramsArguments;
    }

    public function renderPastoralCareFamily (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/pastoralcare/');

        $familyId = $args['familyId'];

        if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'pastoralcarefamily.php', $this->argumentsPastoralFamilyListArray($familyId));
    }

    public function argumentsPastoralFamilyListArray ($currentFamilyID=0)
    {
        $currentPastorId = SessionUser::getUser()->getPerson()->getID();

        $ormPastoralCares = PastoralCareQuery::Create()
            ->orderByDate(Criteria::DESC)
            ->leftJoinWithPastoralCareType()
            ->findByFamilyId($currentFamilyID);

        $ormPastors = PastoralCareQuery::Create()
            ->groupBy(PastoralCareTableMap::COL_PST_CR_PASTOR_ID)
            ->orderByPastorName(Criteria::DESC)
            ->findByFamilyId($currentFamilyID);

        $ormPastoralTypeCares = PastoralCareTypeQuery::Create()
            ->find();

        // new code
        $mailchimp = new MailChimpService();

        $iCurrentUserFamID = SessionUser::getUser()->getPerson()->getFamId();

        $family = FamilyQuery::create()->findOneById($currentFamilyID);
        $iFamilyID = $family->getId();

        $can_see_privatedata = ($iCurrentUserFamID == $iFamilyID || SessionUser::getUser()->isSeePrivacyDataEnabled()) ? true : false;

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

// Format the phone numbers
        $sHomePhone = MiscUtils::ExpandPhoneNumber($family->getHomePhone(), $family->getCountry(), $dummy);
        $sWorkPhone = MiscUtils::ExpandPhoneNumber($family->getWorkPhone(), $family->getCountry(), $dummy);
        $sCellPhone = MiscUtils::ExpandPhoneNumber($family->getCellPhone(), $family->getCountry(), $dummy);

        /* location and MAP */
        $location_available = false;

        if (!is_null($family)) {
            $lat = str_replace(",", ".", $family->getLatitude());
            $lng = str_replace(",", ".", $family->getLongitude());

            $iLittleMapZoom = SystemConfig::getValue("iLittleMapZoom");
            $sMapProvider = SystemConfig::getValue('sMapProvider');
            $sGoogleMapKey = SystemConfig::getValue('sGoogleMapKey');

            if ($lat != 0 && $lng != 0) {
                $location_available = true;
            }
        }
        // end new code

        //Get name
        $family = FamilyQuery::Create()->findOneById ($currentFamilyID);

        $sPageTitle = _("Family Pastoral care")."  : \"".$family->getName()."\"";

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'         => $sRootDocument,
            'sPageTitle'            => $sPageTitle,
            'ormPastoralCares'      => $ormPastoralCares,
            'currentFamilyID'       => $currentFamilyID,
            'currentPastorId'       => $currentPastorId,
            'ormPastors'            => $ormPastors,
            'ormPastoralTypeCares'  => $ormPastoralTypeCares,
            'family'                => $family,
            'sDateFormatLong'       => $sDateFormatLong,
            'sCSPNonce'             => $sCSPNonce,
            'can_see_privatedata'   => $can_see_privatedata,
            'ormFamCustomFields'    => $ormFamCustomFields,
            'mailchimp'             => $mailchimp,
            'iFamilyID'             => $iFamilyID,
            'aFamCustomDataArr'     => $aFamCustomDataArr,
            'sHomePhone'            => $sHomePhone,
            'sWorkPhone'            => $sWorkPhone,
            'sCellPhone'            => $sCellPhone,
            'location_available'    => $location_available,
            'lat'                   => $lat,
            'lng'                   => $lng,
            'iLittleMapZoom'        => $iLittleMapZoom,
            'sMapProvider'          => $sMapProvider,
            'sGoogleMapKey'         => $sGoogleMapKey,
        ];
        return $paramsArguments;
    }

    public function renderPastoralCareMembersList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $renderer = new PhpRenderer('templates/pastoralcare/');


        if ( !( SessionUser::getUser()->isPastoralCareEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'pastoralcareMembersList.php', $this->argumentsPastoralCareMembersListListArray());
    }

    public function argumentsPastoralCareMembersListListArray ()
    {
        $currentPastorId = SessionUser::getUser()->getPerson()->getID();

        $ormPastoralTypeCares = PastoralCareTypeQuery::Create()
            ->find();

        //Get name
        $sPageTitle = _("Pastoral care members list by classification");

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $memberTypes     = ListOptionQuery::create()
            ->orderByOptionName()
            ->findById(1);

        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'currentPastorId'      => $currentPastorId,
            'ormPastoralTypeCares' => $ormPastoralTypeCares,
            'sDateFormatLong'      => $sDateFormatLong,
            'aMemberTypes'         => $memberTypes->toArray(),
            'sCSPNonce'            => $sCSPNonce
        ];

        return $paramsArguments;
    }
}
