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

use EcclesiaCRM\UserQuery;
use EcclesiaCRM\UserConfig;
use EcclesiaCRM\UserConfigQuery;
use EcclesiaCRM\UserRoleQuery;
use EcclesiaCRM\PluginQuery;
use EcclesiaCRM\PluginUserRole;
use EcclesiaCRM\PluginUserRoleQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\InputUtils;

use Propel\Runtime\ActiveQuery\Criteria;

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

    public function renderUserSettings (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/user/');

        if ( !( SessionUser::getUser()->isAdmin() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        return $renderer->render($response, 'usersettings.php', $this->argumentsrenderUserSettingsArray() );
    }

    public function argumentsrenderUserSettingsArray ()
    {
        $iPersonID = SessionUser::getUser()->getPersonId();

        if (isset($_POST['save'])) {
            $new_value = $_POST['new_value'];
            $type = $_POST['type'];
            ksort($type);
            reset($type);
            while ($current_type = current($type)) {
                $id = key($type);
                // Filter Input
                if ($current_type == 'text' || $current_type == 'textarea') {
                    $value = InputUtils::LegacyFilterInput($new_value[$id]);
                } elseif ($current_type == 'number') {
                    $value = InputUtils::LegacyFilterInput($new_value[$id], 'float');
                } elseif ($current_type == 'date') {
                    $value = InputUtils::LegacyFilterInput($new_value[$id], 'date');
                } elseif ($current_type == 'boolean') {
                    if ($new_value[$id] != '1') {
                        $value = '';
                    } else {
                        $value = '1';
                    }
                } elseif ($current_type == 'choice') {
                    $value = $new_value[$id];
                }
        
                // We can't update unless values already exist.
                $userConf = UserConfigQuery::create()->filterById($id)->findOneByPersonId($iPersonID);
        
                if (is_null($userConf)) { // If Row does not exist then insert default values.
                    // Defaults will be replaced in the following Update
                    $userDefault = UserConfigQuery::create()->filterById($id)->findOneByPersonId(0);
        
                    if (!is_null($userDefault)) {
                        $userConf = new UserConfig();
        
                        $userConf->setPersonId($iPersonID);
                        $userConf->setId($id);
                        $userConf->setName($userDefault->getName());
                        $userConf->setValue($value);
                        $userConf->setType($userDefault->getType());
                        $userConf->setChoicesId($userDefault->getChoicesId());
                        $userConf->setTooltip(htmlentities(addslashes($userDefault->getTooltip()), ENT_NOQUOTES, 'UTF-8'));
                        $userConf->setPermission($userDefault->getPermission());
                        $userConf->setCat($userDefault->getCat());
        
                        $userConf->save();
                    } else {
                        echo '<br> Error on line ' . __LINE__ . ' of file ' . __FILE__;
                        exit;
                    }
                } else {
        
                    $userConf->setValue($value);
        
                    $userConf->save();
        
                }
                next($type);
            }
        
            $new_plugin = $_POST['new_plugin'];
            $new_plugin_place = $_POST['new_plugin_place'];
        
            $plugins = PluginQuery::create()
                ->filterByCategory('Dashboard',Criteria::EQUAL)
                ->orderByName()
                ->find();
        
            foreach ($plugins as $plugin) {
                $sel_role = $new_plugin[$plugin->getId()];
                $position = $new_plugin_place[$plugin->getId()];
        
                if ( is_null($position) ) continue;
        
                $role = PluginUserRoleQuery::create()
                    ->filterByUserId($iPersonID)
                    ->findOneByPluginId($plugin->getId());
        
                if (is_null($role)) {
                    $role = new PluginUserRole();
                    $role->setPluginId($plugin->getId());
                    $role->setUserId($iPersonID);
                }
        
                $plugin = $role->getPlugin();
        
                $role->setDashboardVisible(($sel_role)?true:false);
                $role->setDashboardOrientation($position);
                $role->save();
            }
        }

        $cSPNonce = SystemURLs::getCSPNonce();

        // Get settings
        $configs = UserConfigQuery::create()->orderById()->findByPersonId($iPersonID);

        $numberRow = 0;
        
        // Set the page title and include HTML header
        $sPageTitle = _('My User Settings');

        $paramsArguments = [
            'exit'              => false,
            'sRootPath'         => SystemURLs::getRootPath(),
            'sRootDocument'     => SystemURLs::getDocumentRoot(),
            'sPageTitle'        => _('System Users Listing'),
            'cSPNonce'          => $cSPNonce,
            'iPersonID'         => $iPersonID,
            'sPageTitle'        => $sPageTitle,
            'configs'           => $configs,
            'numberRow'         => $numberRow,
        ];

        return $paramsArguments;
    }
}
