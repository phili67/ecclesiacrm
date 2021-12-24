<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\UserRoleQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use Slim\Views\PhpRenderer;

$app->group('/users', function (RouteCollectorProxy $group) {
    $group->get('', 'renderUserList');
    $group->get('/', 'renderUserList');
});

function renderUserList (Request $request, Response $response, array $args) {
    $renderer = new PhpRenderer('templates/user/');

    if ( !( SessionUser::getUser()->isAdmin() ) ) {
      return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
    }

    return $renderer->render($response, 'userlist.php', argumentsrenderUserListArray());
}

function argumentsrenderUserListArray ()
{
    // Get all the User records
    $rsUsers = UserQuery::create()
               ->leftJoinWithUserRole()
               ->find();

    // we search all the available roles
    $userRoles = UserRoleQuery::Create()->find();

    $first_roleID = 0;
    foreach ($userRoles as $userRole) {
      $first_roleID = $userRole->getId();
      break;
    }

    if ($usr_role_id == null) {
      $usr_role_id = $first_roleID;
    }

    $paramsArguments = ['sRootPath'        => SystemURLs::getRootPath(),
                       'sRootDocument'     => SystemURLs::getDocumentRoot(),
                       'sPageTitle'        => _('System Users Listing'),
                       'first_roleID'      => $first_roleID,
                       'rsUsers'           => $rsUsers,
                       'userRoles'         => $userRoles,
                       'usr_role_id'       => $usr_role_id,
                       'sessionUserId'     => SessionUser::getUser()->getId(),
                       'dateFormatLong'    => SystemConfig::getValue('sDateFormatLong')." ".((SystemConfig::getBooleanValue('bTimeEnglish'))?"h:m A":"H:m")
                      ];

   return $paramsArguments;
}
