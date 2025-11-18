<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Psr\Container\ContainerInterface;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\PersonVolunteerOpportunityQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\VolunteerOpportunityQuery;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\PhpRenderer;

class VIEWVolunteerOpportunityController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderVolunteerOpportunity (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/volunteer/');

        if ( !( SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'volunteeropportunity.php', $this->argumentsVolunteerOpportunityArray());
    }

    function argumentsVolunteerOpportunityArray ()
    {
        //Set the page title
        $sPageTitle = _("Volunteer Opportunities");

        $sRootDocument  = SystemURLs::getDocumentRoot();

        $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
            'sRootDocument' => $sRootDocument,
            'sPageTitle'    => $sPageTitle,
            'isVolunteerOpportunityEnabled' => SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()
        ];
        return $paramsArguments;
    }

    public function volunteerView (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/volunteer/');

        $volID = $args['volunteerID'];

        $vo = VolunteerOpportunityQuery::Create()->findOneById($volID);
        if (is_null($vo)) {
            throw new HttpNotFoundException($request, "volID: " . $volID . " not found");
        }

        return $renderer->render($response, 'volunteerview.php', $this->renderVolunteerViewArray($volID));
    }

    public function renderVolunteerViewArray ($volID): array
    {
        //Get the data on this group
        $vo = VolunteerOpportunityQuery::Create()->findOneById($volID);

        $persons = PersonVolunteerOpportunityQuery::create()
            ->usePersonQuery()
            ->addAsColumn('FirstName', PersonTableMap::COL_PER_FIRSTNAME)
            ->addAsColumn('LastName', PersonTableMap::COL_PER_LASTNAME)
            ->addAsColumn('PersonId', PersonTableMap::COL_PER_ID)
            ->endUse()
            ->addAscendingOrderByColumn('person_per.per_LastName')
            ->addAscendingOrderByColumn('person_per.per_FirstName')
            ->findByVolunteerOpportunityId($volID);

        
                /*
//Look up the default role name
        if (!empty($manager)) {
            $is_group_manager = true;
            $_SESSION['bManageGroups'] = true;// use session variable for an current group manager
        } else  {
            $_SESSION['bManageGroups'] = SessionUser::getUser()->isManageGroupsEnabled();// use session variable for an current group manager
        }

//Get the group's type name
        if ($thisGroup->getType() > 0) {
            $groupeType = ListOptionQuery::create()->filterById(3)->filterByOptionId($thisGroup->getListOptionId())->findOne();
            if (!empty($groupeType)) {
                $sGroupType = $groupeType->getOptionName();
            }
        }

//Get all the properties
        $ormProperties = PropertyQuery::Create()
            ->filterByProClass('g')
            ->orderByProName()
            ->find();


// Get data for the form as it now exists..
        $ormPropList = GroupPropMasterQuery::Create()->orderByPropId()->findByGroupId($iGroupID);

        
        $calendar = CalendarinstancesQuery::create()->findOneByGroupId($iGroupID);

        $calendarID = null;
        if ( !is_null ($calendar) ) {
            $calendarID = [$calendar->getCalendarid(), $calendar->getId()];
        }
    */
        //Set the page title
        $sPageTitle = _('Group').' : '.$vo->getName();


        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
            'sRootDocument'    => $sRootDocument,
            'sPageTitle'       => $sPageTitle,
            'CSPNonce'         => $CSPNonce,
            'volID'            => $volID,
            'persons'          => $persons,
            /*'calendarID'       => $calendarID,
            'thisGroup'        => $thisGroup,
            'defaultRole'      => $defaultRole,
            'sGroupType'       => $sGroupType,
            'is_group_manager' => $is_group_manager,
            'ormProperties'    => $ormProperties,
            'ormPropList'      => $ormPropList*/
        ];

        return $paramsArguments;
    }
}
