<?php

use Slim\Http\Request;
use Slim\Http\Response;

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\dto\SystemURLs;

use EcclesiaCRM\GroupManagerPersonQuery;
use EcclesiaCRM\GroupPropMasterQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\CalendarinstancesQuery;

use Slim\Views\PhpRenderer;

$app->group('/group', function () {
    $this->get('/list', 'groupList' );
    $this->get('/{groupID:[0-9]+}/view', 'groupView' );
    $this->get('/{groupId:[0-9]+}/badge/{useCart:[0-9]+}/{type}', 'groupBadge' );
});


function groupList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/group/');

    return $renderer->render($response, 'grouplist.php', renderGroupListArray());
}

function renderGroupListArray ()
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

function groupView (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/group/');

    return $renderer->render($response, 'groupview.php', renderGroupViewArray($args['groupID']));
}


function renderGroupViewArray ($iGroupID)
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

function groupBadge (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/group/');

    $groupId = $args['groupId'];
    $useCart = $args['useCart'];

    if ( !( SessionUser::getUser()->isSundayShoolTeacherForGroup($groupId) || SessionUser::getUser()->isExportSundaySchoolPDFEnabled() ) ) {
        return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/Menu.php');
    }


    return $renderer->render($response, 'groupbadge.php', argumentsGroupBadgeArray($groupId,$useCart));
}

function argumentsGroupBadgeArray ($iGroupID,$useCart)
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
