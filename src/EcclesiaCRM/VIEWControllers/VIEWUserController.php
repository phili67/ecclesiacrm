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
use EcclesiaCRM\User;
use EcclesiaCRM\PersonQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\LoggerUtils;

use EcclesiaCRM\Emails\NewAccountEmail;
use EcclesiaCRM\Emails\UpdateAccountEmail;
use EcclesiaCRM\Emails\PasswordChangeEmail;

use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\HttpFoundation\Session\Session;

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

        return $renderer->render($response, 'userlist.php', $this->argumentsRenderUserListArray() );
    }

    public function argumentsRenderUserListArray ($usr_role_id = null)
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

    public function renderUserEditor (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/user/');

        $PersonID = -1;

        if ( isset ($args['PersonID']) ) {
            $PersonID = $args['PersonID'];
        } elseif (isset($_POST['PersonID'])) {
            $PersonID = InputUtils::LegacyFilterInput($_POST['PersonID'], 'int');
            $bNewUser = false;
        }

        $errorMsg = '';

        if ( isset ($args['errorMsg'] )) {
            $errorMsg = $args['errorMsg'];
        }

        if ( !( SessionUser::getUser()->isAdmin() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $res = $this->argumentsrenderUserEditorArray($PersonID, false, $errorMsg );

        if ( $res['error'] ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/' . $res['link']);
        } else if ( isset ($res['link']) && $res['link'] == 'v2/users' ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/users' );
        }

        return $renderer->render($response, 'userseditor.php', $res );
    }

    public function renderNewUserEditorErrorMsg (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/user/');

        $PersonID = -1;
        $bNewUser = false;

        if ( isset ($args['NewPersonID']) ) {
            $PersonID = $args['NewPersonID'];
            $bNewUser = true;
        } elseif (isset($_POST['PersonID'])) {
            $PersonID = InputUtils::LegacyFilterInput($_POST['PersonID'], 'int');
            $bNewUser = false;
        }

        $errorMsg = '';

        if ( isset ($args['errorMsg'] )) {
            $errorMsg = $args['errorMsg'];
        }

        if ( !( SessionUser::getUser()->isAdmin() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }
        
        $res = $this->argumentsrenderUserEditorArray($PersonID, $bNewUser, $errorMsg);

        if ( $res['error'] ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . "/" . $res['link']);
        }

        return $renderer->render($response, 'userseditor.php', $res );
    }

    public function argumentsrenderUserEditorArray ($iPersonID = -1, $bNewUser = false, $sErrorText = '')
    {
        $vNewUser = false;
        $bShowPersonSelect = false;
        $usr_role_id = null;
        $people = null;


        // we search all the available roles
        $userRoles = UserRoleQuery::Create()->find();

        //Value to help determine correct return state on error
        if (isset($_POST['NewUser'])) {
            $NewUser = InputUtils::LegacyFilterInput($_POST['NewUser'], 'string');
        }


        if (isset($_POST['cancel'])) {
            return [
                'error' => true,
                'link'  => 'v2/users'
            ];
        }
        // Has the form been submitted?
        if (isset($_POST['save']) && $iPersonID > 0) {

            // Assign all variables locally
            $sAction = $_POST['Action'];

            $defaultFY = MiscUtils::CurrentFY();
            $sUserName = strtolower(InputUtils::LegacyFilterInput($_POST['UserName']));

            if (strlen($sUserName) < 3) {
                if ($NewUser == false) {
                    //Report error for current user creation
                    return [
                        'error' => true,
                        'link'  => 'v2/users/editor/'.$iPersonID.'/errormessage/'._("Login must be a least 3 characters!")
                    ];
                    //RedirectUtils::Redirect('UserEditor.php?PersonID=' . $iPersonID . '&ErrorText=' . _("Login must be a least 3 characters!"));
                } else {
                    //Report error for new user creation
                    return [
                        'error' => true,
                        'link'  => 'v2/users/editor/new/'.$iPersonID.'/errormessage/'._("Login must be a least 3 characters!")
                    ];
        
                    //RedirectUtils::Redirect('UserEditor.php?NewPersonID=' . $iPersonID . '&ErrorText=' . _("Login must be a least 3 characters!"));
                }
            } else {

                if (isset($_POST['roleID'])) {
                    $roleID = $_POST['roleID'];
                } else {
                    $roleID = 0;
                }
                if (isset($_POST['AddRecords'])) {
                    $AddRecords = 1;
                } else {
                    $AddRecords = 0;
                }
                if (isset($_POST['EditRecords'])) {
                    $EditRecords = 1;
                } else {
                    $EditRecords = 0;
                }
                if (isset($_POST['ShowCart'])) {
                    $ShowCart = 1;
                } else {
                    $ShowCart = 0;
                }
                if (isset($_POST['ShowMap'])) {
                    $ShowMap = 1;
                } else {
                    $ShowMap = 0;
                }
                if (isset($_POST['EDrive'])) {
                    $EDrive = 1;
                } else {
                    $EDrive = 0;
                }
                if (isset($_POST['DeleteRecords'])) {
                    $DeleteRecords = 1;
                } else {
                    $DeleteRecords = 0;
                }
                if (isset($_POST['MenuOptions'])) {
                    $MenuOptions = 1;
                } else {
                    $MenuOptions = 0;
                }
                if (isset($_POST['ManageGroups'])) {
                    $ManageGroups = 1;
                } else {
                    $ManageGroups = 0;
                }
                if (isset($_POST['ManageCalendarResources'])) {
                    $ManageCalendarResources = 1;
                } else {
                    $ManageCalendarResources = 0;
                }
                if (isset($_POST['HtmlSourceEditor'])) {
                    $HtmlSourceEditor = 1;
                } else {
                    $HtmlSourceEditor = 0;
                }
                if (isset($_POST['Finance'])) {
                    $Finance = 1;
                } else {
                    $Finance = 0;
                }
                if (isset($_POST['Notes'])) {
                    $Notes = 1;
                } else {
                    $Notes = 0;
                }
                if (isset($_POST['EditSelf'])) {
                    $EditSelf = 1;
                } else {
                    $EditSelf = 0;
                }
                if (isset($_POST['Canvasser'])) {
                    $Canvasser = 1;
                } else {
                    $Canvasser = 0;
                }

                if (isset($_POST['Admin'])) {
                    $Admin = 1;
                } else {
                    $Admin = 0;
                }

                if (isset($_POST['QueryMenu'])) {
                    $QueryMenu = 1;
                } else {
                    $QueryMenu = 0;
                }

                if (isset($_POST['CanSendEmail'])) {
                    $CanSendEmail = 1;
                } else {
                    $CanSendEmail = 0;
                }

                if (isset($_POST['ExportCSV'])) {
                    $ExportCSV = 1;
                } else {
                    $ExportCSV = 0;
                }

                if (isset($_POST['CreateDirectory'])) {
                    $CreateDirectory = 1;
                } else {
                    $CreateDirectory = 0;
                }

                if (isset($_POST['ExportSundaySchoolPDF'])) {
                    $ExportSundaySchoolPDF = 1;
                } else {
                    $ExportSundaySchoolPDF = 0;
                }

                if (isset($_POST['ExportSundaySchoolCSV'])) {
                    $ExportSundaySchoolCSV = 1;
                } else {
                    $ExportSundaySchoolCSV = 0;
                }

                if (isset($_POST['PastoralCare'])) {
                    $PastoralCare = 1;
                } else {
                    $PastoralCare = 0;
                }

                if (isset($_POST['MailChimp'])) {
                    $MailChimp = 1;
                } else {
                    $MailChimp = 0;
                }

                if (isset($_POST['MainDashboard'])) {
                    $MainDashboard = 1;
                } else {
                    $MainDashboard = 0;
                }

                if (isset($_POST['SeePrivacyData'])) {
                    $SeePrivacyData = 1;
                } else {
                    $SeePrivacyData = 0;
                }


                if (isset($_POST['GdrpDpo'])) {
                    $GdrpDpo = 1;
                } else {
                    $GdrpDpo = 0;
                }

                // Initialize error flag
                $bErrorFlag = false;

                // Were there any errors?
                if (!$bErrorFlag) {
                    $undupCount = UserQuery::create()->filterByUserName($sUserName)->_and()->filterByPersonId($iPersonID, Criteria::NOT_EQUAL)->count();

                    // Write the ORM depending on whether we're adding or editing
                    if ($sAction == 'add') {
                        if ($undupCount == 0) {
                            $rawPassword = User::randomPassword();
                            $sPasswordHashSha256 = hash('sha256', $rawPassword . $iPersonID);

                            $user = new User();

                            $user->setPersonId($iPersonID);
                            $user->setPassword($sPasswordHashSha256);
                            $user->setLastLogin(date('Y-m-d H:i:s'));

                            $user->setPastoralCare($PastoralCare);
                            $user->setMailChimp($MailChimp);
                            $user->setMainDashboard($MainDashboard);
                            $user->setSeePrivacyData($SeePrivacyData);
                            $user->setGdrpDpo($GdrpDpo);
                            $user->setAddRecords($AddRecords);
                            $user->setEditRecords($EditRecords);
                            $user->setDeleteRecords($DeleteRecords);

                            $user->setRoleId($roleID);

                            $user->setShowCart($ShowCart);
                            $user->setShowMap($ShowMap);
                            $user->setEDrive($EDrive);
                            $user->setMenuOptions($MenuOptions);

                            $user->setManageGroups($ManageGroups);
                            $user->setManageCalendarResources($ManageCalendarResources);
                            $user->setHtmlSourceEditor($HtmlSourceEditor);
                            $user->setFinance($Finance);
                            $user->setNotes($Notes);

                            $user->setAdmin($Admin);
                            $user->setShowMenuQuery($QueryMenu);
                            $user->setCanSendEmail($CanSendEmail);
                            $user->setExportCSV($ExportCSV);
                            $user->setCreatedirectory($CreateDirectory);
                            $user->setExportSundaySchoolPDF($ExportSundaySchoolPDF);
                            $user->setExportSundaySchoolCSV($ExportSundaySchoolCSV);
                            //$user->setDefaultFY($usr_defaultFY);
                            $user->setUserName($sUserName);

                            $user->setEditSelf($EditSelf);
                            $user->setCanvasser($Canvasser);

                            $user->save();

                            $user->createTimeLineNote("created");
                            $user->createHomeDir();

                            if ($ManageGroups) {// in the case the user is a group manager, we add all the group calendars
                                $user->createGroupAdminCalendars();
                            }

                            $email = new NewAccountEmail($user, $rawPassword);
                            $email->send();
                        } else {
                            // Set the error text for duplicate when new user
                            return [
                                'error' => true,
                                'link'  => 'v2/users/editor/new/'.$iPersonID.'/errormessage/'._("Login already in use, please select a different login!")
                            ];
                            //RedirectUtils::Redirect('UserEditor.php?NewPersonID=' . $iPersonID . '&ErrorText=' . _("Login already in use, please select a different login!"));
                        }
                    } else {
                        if ($undupCount == 0) {
                            //$user->createHomeDir();
                            $user = UserQuery::create()->findPk($iPersonID);

                            $old_ManageGroups = $user->isManageGroupsEnabled();
                            $oldUserName = $user->getUserName();

                            $user->setAddRecords($AddRecords);
                            $user->setPastoralCare($PastoralCare);
                            $user->setMailChimp($MailChimp);
                            if ($roleID > 0) {
                                $user->setRoleId($roleID);
                            }
                            $user->setMainDashboard($MainDashboard);
                            $user->setSeePrivacyData($SeePrivacyData);
                            $user->setGdrpDpo($GdrpDpo);
                            $user->setEditRecords($EditRecords);
                            $user->setDeleteRecords($DeleteRecords);
                            $user->setShowCart($ShowCart);
                            $user->setShowMap($ShowMap);
                            $user->setEDrive($EDrive);
                            $user->setMenuOptions($MenuOptions);
                            $user->setManageGroups($ManageGroups);
                            $user->setManageCalendarResources($ManageCalendarResources);
                            $user->setHtmlSourceEditor($HtmlSourceEditor);
                            $user->setFinance($Finance);
                            $user->setNotes($Notes);
                            $user->setAdmin($Admin);
                            $user->setShowMenuQuery($QueryMenu);
                            $user->setCanSendEmail($CanSendEmail);
                            $user->setExportCSV($ExportCSV);
                            $user->setCreatedirectory($CreateDirectory);
                            $user->setExportSundaySchoolPDF($ExportSundaySchoolPDF);
                            $user->setExportSundaySchoolCSV($ExportSundaySchoolCSV);

                            if (strtolower($oldUserName) != "admin") {
                                $user->setUserName($sUserName);
                            }

                            $user->setEditSelf($EditSelf);
                            $user->setCanvasser($Canvasser);
                            $user->save();

                            if (strtolower($oldUserName) != "admin") {
                                $user->renameHomeDir($oldUserName, $sUserName);
                            }

                            $user->createTimeLineNote("updated");// the calendars are moved from one username to another in the function : renameHomeDir

                            if ($ManageGroups || $Admin) {
                                if (!$old_ManageGroups) {// only when the user has now the role group manager
                                    $user->deleteGroupAdminCalendars();
                                    $user->createGroupAdminCalendars();
                                }
                            } else if ($old_ManageGroups) {// only delete group calendars in the case He was a group manager
                                $user->deleteGroupAdminCalendars();
                            }

                            $email = new UpdateAccountEmail($user, _("The same as before"));
                            $email->send();
                        } else {
                            // Set the error text for duplicate when currently existing
                            return [
                                'error' => true,
                                'link'  => 'v2/users/editor/'.$iPersonID.'/errormessage/'._("Login already in use, please select a different login!")
                            ];
                            //RedirectUtils::Redirect('UserEditor.php?PersonID=' . $iPersonID . '&ErrorText=' . _("Login already in use, please select a different login!"));
                        }
                    }
                }
            }
        } else {

            // Do we know which person yet?
            if ($iPersonID > 0) {
                $usr_per_ID = $iPersonID;

                if (!$bNewUser) {
                    // Get the data on this user
                    $user = UserQuery::create()
                        ->innerJoinWithPerson()
                        ->withColumn('Person.FirstName', 'FirstName')
                        ->withColumn('Person.LastName', 'LastName')
                        ->findOneByPersonId($iPersonID);

                    $sUser = $user->getLastName() . ', ' . $user->getFirstName();
                    $sUserName = $user->getUserName();

                    $usr_AddRecords = $user->getAddRecords();
                    $usr_PastoralCare = $user->getPastoralCare();
                    $usr_GDRP_DPO = $user->getGdrpDpo();
                    $usr_MailChimp = $user->getMailChimp();
                    $usr_MainDashboard = $user->getMainDashboard();
                    $usr_SeePrivacyData = $user->getSeePrivacyData();
                    $usr_EditRecords = $user->getEditRecords();
                    $usr_DeleteRecords = $user->getDeleteRecords();
                    $usr_ShowCart = $user->getShowCart();
                    $usr_ShowMap = $user->getShowMap();
                    $usr_EDrive = $user->getEdrive();
                    $usr_MenuOptions = $user->getMenuOptions();
                    $usr_ManageGroups = $user->getManageGroups();
                    $usr_ManageCalendarResources = $user->getManageCalendarResources();
                    $usr_HtmlSourceEditor = $user->getHtmlSourceEditor();
                    $usr_Finance = $user->getFinance();
                    $usr_Notes = $user->getNotes();
                    $usr_Admin = $user->getAdmin();
                    $usr_showMenuQuery = $user->getShowMenuQuery();
                    $usr_CanSendEmail = $user->getCanSendEmail();
                    $usr_ExportCSV = $user->getExportCSV();
                    $usr_CreateDirectory = $user->getCreatedirectory();
                    $usr_ExportSundaySchoolPDF = $user->getExportSundaySchoolPDF();
                    $usr_ExportSundaySchoolCSV = $user->getExportSundaySchoolCSV();
                    $usr_EditSelf = $user->getEditSelf();
                    $usr_Canvasser = $user->getCanvasser();

                    $sAction = 'edit';
                } else {
                    $dbPerson = PersonQuery::create()->findPk($iPersonID);
                    $sUser = $dbPerson->getFullName();
                    if ($dbPerson->getEmail() != '') {
                        $sUserName = $dbPerson->getEmail();
                    } else {
                        $sUserName = $dbPerson->getFirstName() . $dbPerson->getLastName();
                    }
                    $sAction = 'add';
                    $vNewUser = 'true';

                    $usr_AddRecords = 0;
                    $usr_PastoralCare = 0;
                    $usr_GDRP_DPO = 0;
                    $usr_MailChimp = 0;
                    $usr_MainDashboard = 0;
                    $usr_SeePrivacyData = 0;
                    $usr_EditRecords = 0;
                    $usr_DeleteRecords = 0;
                    $usr_ShowCart = 0;
                    $usr_ShowMap = 0;
                    $usr_EDrive = 0;
                    $usr_MenuOptions = 0;
                    $usr_ManageGroups = 0;
                    $usr_ManageCalendarResources = 0;
                    $usr_HtmlSourceEditor = 0;
                    $usr_Finance = 0;
                    $usr_Notes = 0;
                    $usr_Admin = 0;
                    $usr_showMenuQuery = 0;
                    $usr_CanSendEmail = 0;
                    $usr_ExportCSV = 0;
                    $usr_CreateDirectory = 0;
                    $usr_ExportSundaySchoolPDF = 0;
                    $usr_ExportSundaySchoolCSV = 0;
                    $usr_EditSelf = 1;
                    $usr_Canvasser = 0;
                }

                // New user without person selected yet
            } else {
                $sAction = 'add';
                $bShowPersonSelect = true;

                $usr_AddRecords = 0;
                $usr_PastoralCare = 0;
                $usr_GDRP_DPO = 0;
                $usr_MailChimp = 0;
                $usr_MainDashboard = 0;
                $usr_SeePrivacyData = 0;
                $usr_EditRecords = 0;
                $usr_DeleteRecords = 0;
                $usr_ShowCart = 0;
                $usr_ShowMap = 0;
                $usr_EDrive = 0;
                $usr_MenuOptions = 0;
                $usr_ManageGroups = 0;
                $usr_ManageCalendarResources = 0;
                $usr_HtmlSourceEditor = 0;
                $usr_Finance = 0;
                $usr_Notes = 0;
                $usr_Admin = 0;
                $usr_showMenuQuery = 0;
                $usr_CanSendEmail = 0;
                $usr_ExportCSV = 0;
                $usr_CreateDirectory = 0;
                $usr_ExportSundaySchoolPDF = 0;
                $usr_ExportSundaySchoolCSV = 0;
                $usr_EditSelf = 1;
                $usr_Canvasser = 0;

                $sUserName = '';
                $vNewUser = 'true';


                $people = PersonQuery::create()
                    ->leftJoinUser()
                    ->withColumn('User.PersonId', 'UserPersonId')
                    ->orderByLastName()
                    ->find();
            }
        }

        // Save Settings
        if (isset($_POST['save']) && ($iPersonID > 0)) {

            $plugins = PluginQuery::create()
                ->find();
            foreach ($plugins as $plugin) {
                $new_plugin = $_POST['new_plugin'];

                $sel_role = $new_plugin[$plugin->getId()];

                $role = PluginUserRoleQuery::create()
                    ->filterByUserId($iPersonID)
                    ->findOneByPluginId($plugin->getId());

                if (is_null($role)) {
                    $role = new PluginUserRole();
                    $role->setPluginId($plugin->getId());
                    $role->setUserId($iPersonID);
                }

                $plugin = $role->getPlugin();

                if ($plugin->getCategory() == 'Dashboard') {
                    $new_plugin_place = $_POST['new_plugin_place'];
                    $position = $new_plugin_place[$plugin->getId()];
                    $role->setDashboardVisible($sel_role);
                    $role->setDashboardOrientation($position);

                    if ( $plugin->getUserRoleDashboardAvailability() ) {
                        $new_plugin_role = $_POST['new_plugin_role'];

                        $sel_role = $new_plugin_role[$plugin->getId()];

                        $role->setRole($sel_role);
                    }
                } else {
                    $role->setRole($sel_role);
                }
                $role->save();
            }

            $new_value = $_POST['new_value'];
            $new_permission = $_POST['new_permission'];
            $type = $_POST['type'];

            ksort($type);
            reset($type);
            foreach ($type as $key => $value) {
                $id = $key;
                $current_type = $value;
                // Filter Input
                if ($current_type == 'text' || $current_type == 'textarea') {
                    $value = InputUtils::LegacyFilterInput($new_value[$id]);
                } elseif ($current_type == 'number') {
                    $value = InputUtils::LegacyFilterInput($new_value[$id], 'float');
                } elseif ($current_type == 'date') {
                    // todo dates !!!! PL
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

                if ($new_permission[$id] != 'TRUE') {
                    $permission = 'FALSE';
                } else {
                    $permission = 'TRUE';
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
                        $userConf->setType($current_type);
                        $userConf->setChoicesId($userDefault->getChoicesId());
                        $userConf->setTooltip(htmlentities(addslashes($userDefault->getTooltip()), ENT_NOQUOTES, 'UTF-8'));
                        $userConf->setPermission($permission);
                        $userConf->setCat($userDefault->getCat());

                        $userConf->save();
                    } else {
                        echo '<br> Error on line ' . __LINE__ . ' of file ' . __FILE__;
                        exit;
                    }
                } else {

                    $userConf->setValue($value);
                    $userConf->setPermission($permission);
                    $userConf->setType($current_type);

                    $userConf->save();

                }
            }

            return [
                'error' => true,
                'link'  => 'v2/users'
            ];
            //RedirectUtils::Redirect('v2/users');
        }

        // Set the page title and include HTML header
        $sPageTitle = _('User Editor');
        
        $first_roleID = 0;
        foreach ($userRoles as $userRole) {
            $first_roleID = $userRole->getId();
            break;
        }

        if ($usr_role_id == null) {
            $usr_role_id = $first_roleID;
        }

        $cSPNonce = SystemURLs::getCSPNonce();

        // Set the page title and include HTML header
        $sPageTitle = _('User Editor');

        $paramsArguments = [
            'error'             => false,
            'sErrorText'        => $sErrorText,
            'sRootPath'         => SystemURLs::getRootPath(),
            'sRootDocument'     => SystemURLs::getDocumentRoot(),
            'sPageTitle'        => $sPageTitle,
            'cSPNonce'          => $cSPNonce,
            'sAction'           => $sAction,
            'iPersonID'         => $iPersonID,
            'first_roleID'      => $first_roleID,
            'user'              => $user,
            'sUser'             => $sUser,
            'sUserName'         => $sUserName,
            'usr_role_id'       => $usr_role_id,
            'vNewUser'          => $vNewUser,
            'usr_per_ID'        => $usr_per_ID,            
            'bShowPersonSelect' => $bShowPersonSelect,
            'people'            => $people,
            'userRoles'         => $userRoles,
            'usr_AddRecords'    => $usr_AddRecords,
            'usr_PastoralCare'  => $usr_PastoralCare,
            'usr_GDRP_DPO'      => $usr_GDRP_DPO,
            'usr_MailChimp'     => $usr_MailChimp,
            'usr_MainDashboard'  => $usr_MainDashboard,
            'usr_SeePrivacyData' => $usr_SeePrivacyData,
            'usr_EditRecords'    => $usr_EditRecords,
            'usr_DeleteRecords'    => $usr_DeleteRecords,
            'usr_ShowCart'      => $usr_ShowCart,
            'usr_ShowMap'       => $usr_ShowMap,
            'usr_EDrive'        => $usr_EDrive,
            'usr_MenuOptions'   => $usr_MenuOptions,
            'usr_ManageGroups'  => $usr_ManageGroups,
            'usr_ManageCalendarResources'    => $usr_ManageCalendarResources,
            'usr_HtmlSourceEditor'    => $usr_HtmlSourceEditor,
            'usr_Finance'       => $usr_Finance,
            'usr_Notes'         => $usr_Notes,
            'usr_Admin'         => $usr_Admin,
            'usr_showMenuQuery' => $usr_showMenuQuery,
            'usr_CanSendEmail'  => $usr_CanSendEmail,
            'usr_ExportCSV'     => $usr_ExportCSV,
            'usr_CreateDirectory' => $usr_CreateDirectory,
            'usr_ExportSundaySchoolPDF'  => $usr_ExportSundaySchoolPDF,
            'usr_ExportSundaySchoolCSV'  => $usr_ExportSundaySchoolCSV,
            'usr_EditSelf'      => $usr_EditSelf,
            'usr_Canvasser'     => $usr_Canvasser,
        ];

        return $paramsArguments;
    }

    

    public function renderChangePassword (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/user/');

        if ( !( SessionUser::getUser()->isAdmin() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $iPersonID = -1;

        // Get the PersonID out of the querystring if they are an admin user; otherwise, use session.
        if (SessionUser::getUser()->isAdmin() && isset($args['PersonID'])) {
            $iPersonID = InputUtils::LegacyFilterInput($args['PersonID'], 'int');
            if ($iPersonID != SessionUser::getUser()->getPersonId()) {
                $bAdminOtherUser = true;
            }
        } else {
            $iPersonID = SessionUser::getUser()->getPersonId();
        }

        $res = $this->argumentsRenderChangePasswordArray($iPersonID, $bAdminOtherUser);

        if ( $res['error'] ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/' . $res['link']);
        }

        return $renderer->render($response, 'changepassword.php', $res );
    }

    public function renderChangePasswordFromUserList (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $renderer = new PhpRenderer('templates/user/');

        if ( !( SessionUser::getUser()->isAdmin() ) ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/v2/dashboard');
        }

        $iPersonID = -1;

        // Get the PersonID out of the querystring if they are an admin user; otherwise, use session.
        if (SessionUser::getUser()->isAdmin() && isset($args['PersonID'])) {
            $iPersonID = InputUtils::LegacyFilterInput($args['PersonID'], 'int');
            if ($iPersonID != SessionUser::getUser()->getPersonId()) {
                $bAdminOtherUser = true;
            }
        } else {
            $iPersonID = SessionUser::getUser()->getPersonId();
        }

        $res = $this->argumentsRenderChangePasswordArray($iPersonID, $bAdminOtherUser, true);

        if ( $res['error'] ) {
            return $response->withStatus(302)->withHeader('Location', SystemURLs::getRootPath() . '/' . $res['link']);
        }

        return $renderer->render($response, 'changepassword.php', $res );
    }

    public function argumentsRenderChangePasswordArray ($iPersonID, $bAdminOtherUser, $fromUserList = false)
    {
        $bError = false;
        $sOldPasswordError = false;
        $sNewPasswordError = false;

        // Was the form submitted?

        if (isset($_POST['Submit'])) {
            // Assign all the stuff locally
            $sOldPassword = '';
            if (array_key_exists('OldPassword', $_POST)) {
                $sOldPassword = $_POST['OldPassword'];
            }
            $sNewPassword1 = $_POST['NewPassword1'];
            $sNewPassword2 = $_POST['NewPassword2'];

            // Administrators can change other users' passwords without knowing the old ones.
            // No password strength test is done, we assume this administrator knows what the
            // user wants so there is no need to prompt the user to change it on next login.
            if ($bAdminOtherUser) {
                // Did they enter a new password in both boxes?
                if (strlen($sNewPassword1) == 0 && strlen($sNewPassword2) == 0) {
                    $sNewPasswordError = '<br><font color="red">'.gettext('You must enter a password in both boxes').'</font>';
                    $bError = true;
                }

                // Do the two new passwords match each other?
                elseif ($sNewPassword1 != $sNewPassword2) {
                    $sNewPasswordError = '<br><font color="red">'.gettext('You must enter the same password in both boxes').'</font>';
                    $bError = true;
                } else {
                    // Update the user record with the password hash
                    $curUser = UserQuery::create()->findPk($iPersonID);
                    $curUser->updatePassword($sNewPassword1);
                    $curUser->setNeedPasswordChange(false);
                    $curUser->save();
                    $curUser->createTimeLineNote("password-changed-admin");
                    // Set the session variable so they don't get sent back here
                    SessionUser::getUser()->setNeedPasswordChange(false);


                    if (!empty($curUser->getEmail())) {
                        $email = new PasswordChangeEmail($curUser, $sNewPassword1);
                        if (!$email->send()) {
                            LoggerUtils::getAppLogger()->warn($email->getError());
                        }
                    }

                    // Route back to the list
                    if ($fromUserList == true) {
                        return [
                            'error' => true,
                            'link'  => 'v2/users'
                        ];
                        //RedirectUtils::Redirect('v2/users');
                    } else {
                        return [
                            'error' => true,
                            'link'  => 'v2/dashboard'
                        ];
                        //RedirectUtils::Redirect('v2/dashboard');
                    }
                }
            }

            // Otherwise, a user must know their own existing password to change it.
            else {
                $curUser = UserQuery::create()->findPk($iPersonID);

                // Build the array of bad passwords
                $aBadPasswords = explode(',', strtolower(SystemConfig::getValue('aDisallowedPasswords')));
                $aBadPasswords[] = strtolower($curUser->getPerson()->getFirstName());
                $aBadPasswords[] = strtolower($curUser->getPerson()->getMiddleName());
                $aBadPasswords[] = strtolower($curUser->getPerson()->getLastName());

                $bPasswordMatch = $curUser->isPasswordValid($sOldPassword);

                // Does the old password match?
                if (!$bPasswordMatch) {
                    $sOldPasswordError = '<br><font color="red">'.gettext('Invalid password').'</font>';
                    $bError = true;
                }

                // Did they enter a new password in both boxes?
                elseif (strlen($sNewPassword1) == 0 && strlen($sNewPassword2) == 0) {
                    $sNewPasswordError = '<br><font color="red">'.gettext('You must enter your new password in both boxes').'</font>';
                    $bError = true;
                }

                // Do the two new passwords match each other?
                elseif ($sNewPassword1 != $sNewPassword2) {
                    $sNewPasswordError = '<br><font color="red">'.gettext('You must enter the same password in both boxes').'</font>';
                    $bError = true;
                }

                // Is the user trying to change to something too obvious?
                elseif (in_array(strtolower($sNewPassword1), $aBadPasswords)) {
                    $sNewPasswordError = '<br><font color="red">'.gettext('Your password choice is too obvious. Please choose something else.').'</font>';
                    $bError = true;
                }

                // Is the password valid for length?
                elseif (strlen($sNewPassword1) < SystemConfig::getValue('iMinPasswordLength')) {
                    $sNewPasswordError = '<br><font color="red">'.gettext('Your new password must be at least').' '.SystemConfig::getValue('iMinPasswordLength').' '.gettext('characters').'</font>';
                    $bError = true;
                }

                // Did they actually change their password?
                elseif ($sNewPassword1 == $sOldPassword) {
                    $sNewPasswordError = '<br><font color="red">'.gettext('You need to actually change your password (nice try, though!)').'</font>';
                    $bError = true;
                } elseif (levenshtein(strtolower($sNewPassword1), strtolower($sOldPassword)) < SystemConfig::getValue('iMinPasswordChange')) {
                    $sNewPasswordError = '<br><font color="red">'.gettext('Your new password is too similar to your old one.  Be more creative!').'</font>';
                    $bError = true;
                }

                // If no errors, update
                if (!$bError) {
                    // Update the user record with the password hash
                    $curUser->updatePassword($sNewPassword1);
                    $curUser->setNeedPasswordChange(false);
                    $curUser->save();
                    $curUser->createTimeLineNote("password-changed");
                    // Set the session variable so they don't get sent back here
                    SessionUser::getUser()->setNeedPasswordChange(false);
                    SessionUser::setMustChangePasswordRedirect(false);

                    // Route back to the list
                    if ($fromUserList == true) {
                        return [
                            'error' => true,
                            'link'  => 'v2/users'
                        ];
                        //RedirectUtils::Redirect('v2/users');
                    } else {
                        return [
                            'error' => true,
                            'link'  => 'v2/dashboard'
                        ];
                        //RedirectUtils::Redirect('v2/dashboard');
                    }
                }
            }
        } else {
            // initialize stuff since this is the first time showing the form
            $sOldPassword = '';
            $sNewPassword1 = '';
            $sNewPassword2 = '';
        }

        $cSPNonce = SystemURLs::getCSPNonce();

        // Set the page title and include HTML header
        $sPageTitle = _('User Editor');

        $paramsArguments = [
            'error'             => false,
            'sRootPath'         => SystemURLs::getRootPath(),
            'sRootDocument'     => SystemURLs::getDocumentRoot(),
            'sPageTitle'        => $sPageTitle,
            'cSPNonce'          => $cSPNonce,
            'iPersonID'         => $iPersonID,
            'FromUserList'      => $fromUserList,
            'bAdminOtherUser'   => $bAdminOtherUser,
            'sOldPassword'      => $sOldPassword,
            'sOldPasswordError' => $sOldPasswordError,
            'sNewPassword1'     => $sNewPassword1,
            'sNewPassword2'     => $sNewPassword2,
            'sNewPasswordError' => $sNewPasswordError



        ];

        return $paramsArguments;
    }

}
