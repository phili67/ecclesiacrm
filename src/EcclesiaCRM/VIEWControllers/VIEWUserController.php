<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2022 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2022/01/05
//

namespace EcclesiaCRM\VIEWControllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Slim\Views\PhpRenderer;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\UserRoleQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

class VIEWUserController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function renderUserList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/user/');

        if ( !( SessionUser::getUser()->isAdmin() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'userlist.php', $this->argumentsrenderUserListArray() );
    }

    public function argumentsrenderUserListArray ($usr_role_id = null)
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
}
