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

use Slim\Views\PhpRenderer;

$app->group('/group', function () {
    $this->get('/list', 'groupList' );
    $this->get('/{groupID:[0-9]+}/view', 'groupView' );
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
        $_SESSION['bManageGroups'] = true;
    } else  {
        $_SESSION['bManageGroups'] = SessionUser::getUser()->isManageGroupsEnabled();
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
    $sPageTitle = _('Group View').' : '.$thisGroup->getName();

    $sRootDocument  = SystemURLs::getDocumentRoot();
    $CSPNonce = SystemURLs::getCSPNonce();

    $paramsArguments = ['sRootPath'    => SystemURLs::getRootPath(),
        'sRootDocument'    => $sRootDocument,
        'sPageTitle'       => $sPageTitle,
        'CSPNonce'         => $CSPNonce,
        'iGroupID'         => $iGroupID,
        'thisGroup'        => $thisGroup,
        'defaultRole'      => $defaultRole,
        'sGroupType'       => $sGroupType,
        'is_group_manager' => $is_group_manager,
        'ormProperties'    => $ormProperties,
        'ormPropList'      => $ormPropList];

    return $paramsArguments;
}