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

use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\PastoralCareTypeQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\UserQuery;

use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\PastoralCareTableMap;

use Slim\Views\PhpRenderer;

use Propel\Runtime\ActiveQuery\Criteria;

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

        //Get name
        $person = PersonQuery::Create()->findOneById ($currentPersonID);

        $family = FamilyQuery::Create()->findOneById ( $person->getFamId() );

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
            'sCSPNonce'            => $sCSPNonce
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

        //Get name
        $family = FamilyQuery::Create()->findOneById ($currentFamilyID);

        $sPageTitle = _("Family Pastoral care")."  : \"".$family->getName()."\"";

        $sRootDocument   = SystemURLs::getDocumentRoot();
        $sDateFormatLong = SystemConfig::getValue('sDateFormatLong');
        $sCSPNonce       = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'           => SystemURLs::getRootPath(),
            'sRootDocument'        => $sRootDocument,
            'sPageTitle'           => $sPageTitle,
            'ormPastoralCares'     => $ormPastoralCares,
            'currentFamilyID'      => $currentFamilyID,
            'currentPastorId'      => $currentPastorId,
            'ormPastors'           => $ormPastors,
            'ormPastoralTypeCares' => $ormPastoralTypeCares,
            'family'               => $family,
            'sDateFormatLong'      => $sDateFormatLong,
            'sCSPNonce'            => $sCSPNonce
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
