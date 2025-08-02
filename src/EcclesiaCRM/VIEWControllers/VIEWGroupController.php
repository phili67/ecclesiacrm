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

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\GroupService;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\SessionUser;

use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\PersonQuery;

use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\CalendarinstancesQuery;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\PhpRenderer;

class VIEWGroupController {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function groupList (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/group/');

        return $renderer->render($response, 'grouplist.php', $this->renderGroupListArray());
    }

    public function renderGroupListArray ()
    {
        $rsGroupTypes = ListOptionQuery::create()->filterById('3')->find();

        //Set the page title
        $sPageTitle = _("Group Listing");

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
            'sRootDocument' => $sRootDocument,
            'sPageTitle'    => $sPageTitle,
            'CSPNonce'      => $CSPNonce,
            'rsGroupTypes'  => $rsGroupTypes];

        return $paramsArguments;
    }

    public function groupView (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/group/');

        $groupID = $args['groupID'];

        $thisGroup = GroupQuery::create()->findOneById($groupID);
        if (is_null($thisGroup)) {
            throw new HttpNotFoundException($request, "groupID: " . $groupID . " not found");
        }

        return $renderer->render($response, 'groupview.php', $this->renderGroupViewArray($groupID));
    }

    public function renderGroupViewArray ($iGroupID)
    {
        //Get the data on this group
        $thisGroup = GroupQuery::create()->findOneById($iGroupID);

//Look up the default role name
        $defaultRole = ListOptionQuery::create()->filterById($thisGroup->getRoleListId())->filterByOptionId($thisGroup->getDefaultRole())->findOne();

        $sGroupType = _('Unassigned');

        $manager = GroupManagerPersonQuery::Create()->filterByPersonID(SessionUser::getUser()->getPerson()->getId())->filterByGroupId($iGroupID)->findOne();

        $is_group_manager = false;

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

        //Set the page title
        $sPageTitle = _('Group').' : '.$thisGroup->getName();

        $calendar = CalendarinstancesQuery::create()->findOneByGroupId($iGroupID);

        $calendarID = null;
        if ( !is_null ($calendar) ) {
            $calendarID = [$calendar->getCalendarid(), $calendar->getId()];
        }

        $sRootDocument  = SystemURLs::getDocumentRoot();
        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
            'sRootDocument'    => $sRootDocument,
            'sPageTitle'       => $sPageTitle,
            'CSPNonce'         => $CSPNonce,
            'iGroupID'         => $iGroupID,
            'calendarID'       => $calendarID,
            'thisGroup'        => $thisGroup,
            'defaultRole'      => $defaultRole,
            'sGroupType'       => $sGroupType,
            'is_group_manager' => $is_group_manager,
            'ormProperties'    => $ormProperties,
            'ormPropList'      => $ormPropList];

        return $paramsArguments;
    }

    public function groupBadge (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/group/');

        $groupId = $args['groupId'];
        $useCart = $args['useCart'];

        $thisGroup = GroupQuery::create()->findOneById($groupId);
        if (is_null($thisGroup)) {
            throw new HttpNotFoundException($request, "groupID: " . $groupId . " not found");
        }

        if ( !( SessionUser::getUser()->isSundayShoolTeacherForGroup($groupId) || SessionUser::getUser()->isExportSundaySchoolPDFEnabled() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }


        return $renderer->render($response, 'groupbadge.php', $this->argumentsGroupBadgeArray($groupId,$useCart));
    }

    public function argumentsGroupBadgeArray ($iGroupID,$useCart)
    {
        $imgs = MiscUtils::getImagesInPath ('../Images/background');

        $group = GroupQuery::Create()->findOneById ($iGroupID);

        // Get all the sunday school classes
        $groups = GroupQuery::create()
            ->orderByName(Criteria::ASC)
            ->filterByType(4)
            ->find();

        // Set the page title and include HTML header
        if ( $group->isSundaySchool() ) {
            $sPageTitle = _('Sunday School Badges for') . ' : ' . $group->getName();
        } else {
            $sPageTitle = _('Badges for Group') . ' : ' . $group->getName();
        }

        $sRootDocument = SystemURLs::getDocumentRoot();
        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iGroupID'                  => $iGroupID,
            'useCart'                   => $useCart,
            'imgs'                      => $imgs,
            'group'                     => $group,
            'groups'                    => $groups,
            'isSundaySchool'            => $group->isSundaySchool()
        ];

        return $paramsArguments;
    }

    public function groupEdit (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/group/');

        if (!isset($args['groupId']) or !( SessionUser::getUser()->isGroupManagerEnabled() )) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/group/list');
        }

        $groupId = $args['groupId'];

        $thisGroup = GroupQuery::create()->findOneById($groupId);
        if (is_null($thisGroup)) {
            throw new HttpNotFoundException($request, "groupID: " . $groupId . " not found");
        }

        return $renderer->render($response, 'groupedit.php', $this->argumentsGroupEditArray($groupId));
    }

    public function argumentsGroupEditArray ($iGroupID)
    {
        $groupService = new GroupService();
        
        $theCurrentGroup = GroupQuery::create()
            ->findOneById($iGroupID);   //get this group from the group service.

        $optionId = $theCurrentGroup->getListOptionId();

        $rsGroupTypes = ListOptionQuery::create()
            ->filterById('3') // only the groups
            ->orderByOptionSequence()
            ->filterByOptionType(($theCurrentGroup->isSundaySchool())?'sunday_school':'normal')->find();     // Get Group Types for the drop-down

        $rsGroupRoleSeed = GroupQuery::create()->filterByRoleListId(['min'=>0])->find();         //Group Group Role List

        // Set the page title and include HTML header
        $sPageTitle = _('Group Editor');

        $sRootDocument = SystemURLs::getDocumentRoot();
        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iGroupID'                  => $iGroupID,
            'theCurrentGroup'           => $theCurrentGroup,
            'groupService'              => $groupService,
            'optionId'                  => $optionId,
            'rsGroupTypes'              => $rsGroupTypes,
            'rsGroupRoleSeed'           => $rsGroupRoleSeed
        ];

        return $paramsArguments;
    }    

    public function groupReport (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/group/');

        if ( !( SessionUser::getUser()->isManageGroupsEnabled() || $_SESSION['bManageGroups']  ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $groupName = "";
        $iGroupID = -1;

        if (isset($_POST['GroupID'])) {
            $iGroupID = InputUtils::LegacyFilterInput($_POST['GroupID'], 'int');
            if ($iGroupID > 0) {
                $groupName = " : ".GroupQuery::Create()->findOneById($_POST['GroupID'])->getName();
            }
        }

        return $renderer->render($response, 'groupreports.php', $this->argumentsGroupReportArray($groupName,$iGroupID));
    }

    public function argumentsGroupReportArray ($groupName, $iGroupID)
    {
        // Set the page title and include HTML header
        $sPageTitle = ('Group reports').$groupName;

        // Get all the groups
        $groups = GroupQuery::Create()->orderByName()->find();


        $sRootDocument = SystemURLs::getDocumentRoot();
        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iGroupID'                  => $iGroupID,
            'groupName'                 => $groupName,
            'groups'                    => $groups,
        ];

        return $paramsArguments;
    }

    public function renderGroupPropsEditor (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/group/');

        if ( !( SessionUser::getUser()->isManageGroupsEnabled() || $_SESSION['bManageGroups']  ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        // Get the Group from the querystring
        $iGroupID = -1;
        if (isset($args['GroupID'])) {
            $iGroupID = InputUtils::LegacyFilterInput($args['GroupID'], 'int');
        }

        $iPersonID = -1;
        if (isset($args['PersonID'])) {
            $iPersonID = InputUtils::LegacyFilterInput($args['PersonID'], 'int');
        }

        $person = PersonQuery::Create()->findOneById($iPersonID);

        // Get the group information
        $group = GroupQuery::Create()->findOneById ($iGroupID);

        // Abort if user tries to load with group having no special properties.
        if ($group->getHasSpecialProps() == false) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . 'v2/group/'.$iGroupID.'/view');
        }
        

        // Security: user must be allowed to edit records to use this page.
        if ( !( SessionUser::getUser()->isManageGroupsEnabled() || SessionUser::getUser()->getPersonId() == $iPersonID ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'groupPropsEditor.php', $this->argumentsGroupPropsEditorArray($iGroupID,$iPersonID, $person, $group));
    }

    public function argumentsGroupPropsEditorArray ($iGroupID, $iPersonID, $person, $group)
    {
        // Set the page title and include HTML header
        $sPageTitle = _('Group-Specific Properties Form Editor:').'  : "'.$group->getName().'" '._("for")." : ".$person->getFullName();

        // Get all the groups
        $groups = GroupQuery::Create()->orderByName()->find();

        $sRootDocument = SystemURLs::getDocumentRoot();
        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iGroupID'                  => $iGroupID,
            'iPersonID'                 => $iPersonID,
            'person'                    => $person,
            'group'                     => $group,
            'groups'                    => $groups
        ];

        return $paramsArguments;
    }

    

    public function renderGroupPropsFormEditor (ServerRequest $request, Response $response, array $args): Response {
        $renderer = new PhpRenderer('templates/group/');

        // Get the Group from the querystring
        $iGroupID = -1;
        if (isset($args['GroupID'])) {
            $iGroupID = InputUtils::LegacyFilterInput($args['GroupID'], 'int');
        }

        // Get manager infos
        $manager = GroupManagerPersonQuery::Create()->filterByPersonID(SessionUser::getUser()->getPerson()->getId())->filterByGroupId($iGroupID)->findOne();

        $is_group_manager = false;

        if (!empty($manager)) {
            $is_group_manager = true;
        }

        // Security: user must be allowed to edit records to use this page.
        if ( !(SessionUser::getUser()->isManageGroupsEnabled() || $is_group_manager == true) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        // Get the group information
        $groupInfo = GroupQuery::Create()->findOneById ($iGroupID);

        // Abort if user tries to load with group having no special properties.
        if ($groupInfo->getHasSpecialProps() == false) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . 'v2/group/'.$iGroupID.'/view');
        }

        return $renderer->render($response, 'groupPropsFormEditor.php', $this->argumentsGroupPropsFormEditorArray($iGroupID, $groupInfo, $is_group_manager));
    }

    public function argumentsGroupPropsFormEditorArray ($iGroupID, $groupInfo, $is_group_manager)
    {
        // Set the page title and include HTML header
        $sPageTitle = _('Group-Specific Properties Form Editor:').'  : '.$groupInfo->getName();

        $sRootDocument = SystemURLs::getDocumentRoot();
        $CSPNonce = SystemURLs::getCSPNonce();

        $paramsArguments = ['sRootPath' => SystemURLs::getRootPath(),
            'sRootDocument'             => $sRootDocument,
            'CSPNonce'                  => $CSPNonce,
            'sPageTitle'                => $sPageTitle,
            'iGroupID'                  => $iGroupID,
            'groupInfo'                 => $groupInfo,
            'is_group_manager'          => $is_group_manager
        ];

        return $paramsArguments;
    }
}
