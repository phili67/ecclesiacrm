<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\User as BaseUser;
use EcclesiaCRM\dto\SystemConfig;
use Propel\Runtime\Connection\ConnectionInterface;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;

use Firebase\JWT\JWT;

use Sabre\DAV\Xml\Element\Sharee;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\CardDavPDO;

use EcclesiaCRM\Person2group2roleP2g2rQuery;

use EcclesiaCRM\Service\NotificationService;
use EcclesiaCRM\Service\SystemService;

use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;

use DateTime;
use DateTimeZone;

// to define new plugin add the securities to : 2+4=6 for example to have pastoral + mailchimp security options

abstract class SecurityOptions
{
    const bNoDashBordItem = 0;
    const bAdmin = 1; // bit 0
    const bPastoralCare = 2; // bit 1
    const bMailChimp = 4; // bit 2
    const bGdrpDpo = 8; // bit 3
    const bMainDashboard = 16; // bit 4
    const bSeePrivacyData = 32; // bit 5
    const bAddRecords = 64; // bit 6
    const bEditRecords = 128; // bit 7
    const bDeleteRecords = 256; // bit 8
    const bMenuOptions = 512; // bit 9
    const bManageGroups = 1024; // bit 10
    const bFinance = 2048; // bit 11
    const bNotes = 4096; // bit 12
    const bCanvasser = 8192; // bit 13
    const bEditSelf = 16384; // bit 14
    const bShowCart = 32768; // bit 15
    const bShowMap = 65536; // bit 16
    const bEDrive = 131072; // bit 17
    const bShowMenuQuery = 262144; // bit 18
    const bSundaySchool = 524288; // bit 19
    const bDashBoardUser = 1073741824; // bit 30
}


/**
 * Skeleton subclass for representing a row from the 'user_usr' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class User extends BaseUser
{
    private $public_path  = "public/userdir/";
    private $private_path = "private/userdir/";

    public function getId()
    {
        return $this->getPersonId();
    }

    public function preDelete(\Propel\Runtime\Connection\ConnectionInterface $con = NULL): bool
    {
        if (parent::preDelete($con)) {
            $this->deleteHomeDir();

            // transfert the calendars to a user
            // now we code now in Sabre
            $principalBackend = new PrincipalPDO();

            // puis on delete le user
            $principalBackend->deletePrincipal('principals/' . $this->getUserName());

            $this->deleteGroupAdminCalendarsAndAddressbooks();

            return true;
        }

        return false;
    }

    public function createGroupAdminAddressBookShared()
    {
        $userAdmin = UserQuery::Create()->findOneByPersonId(1);

        $carddavBackend   = new CardDavPDO();   


        if ($this->isManageGroupsEnabled() && $userAdmin->getPersonID() != $this->getPersonID()) { // an admin can't change itself and is ever the main group manager
            $groups = GroupQuery::create()->find();

            foreach ($groups as $group) {
                $addressbook = $carddavBackend->getAddressBookForGroup($group->getId());

                $addressbookId = $addressbook['id'];

                // now we can share the new calendar to the users
                $carddavBackend->createAddressBookShare(
                    'principals/' . $this->getUserName(),
                    [
                        'addressbookid' => $addressbookId, // require
                        '{DAV:}displayname'  => $group->getName(),
                        '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description'  => '',
                        'href'         => 0,
                        'user_id'      => $this->getId(), // require
                        'access'       => 3 // '1 = owner, 2 = read, 3 = readwrite',                    
                    ]
                );                                
            }
        }
    }

    public function deleteGroupAdminAddressBookShared()
    {
        $carddavBackend = new CardDavPDO();   

        $addressbookshared = $carddavBackend->getAddressBooksShareForUser('principals/' . $this->getUserName());

        foreach ($addressbookshared as $addrebookshared) {
            $carddavBackend->deleteAddressBookShare($addrebookshared['id']);
        }            
        
    }

    public function createGroupAdminCalendars()
    {
        $userAdmin = UserQuery::Create()->findOneByPersonId(1);

        // transfert the calendars to a user
        // now we code now in Sabre
        $calendarBackend = new CalDavPDO();

        if ($this->isManageGroupsEnabled() && $userAdmin->getPersonID() != $this->getPersonID()) { // an admin can't change itself and is ever the main group manager
            // we have to add the groupCalendars
            $calendars = $calendarBackend->getCalendarsForUser('principals/' . strtolower($userAdmin->getUserName()), "displayname", true);

            foreach ($calendars as $calendar) {
                // we'll connect to sabre
                // Add a new invite
                if ($calendar['grpid'] > 0 || $calendar['cal_type'] > 1) {
                    $calendarBackend->updateInvites(
                        $calendar['id'],
                        [
                            new Sharee([
                                'href'         => 'mailto:' . $this->getEmail(),
                                'principal'    => 'principals/' . strtolower($this->getUserName()),
                                'access'       => \Sabre\DAV\Sharing\Plugin::ACCESS_READWRITE,
                                'inviteStatus' => \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED,
                                'properties'   => ['{DAV:}displayname' => strtolower($this->getUserName())],
                            ])
                        ]
                    );
                }
            }
        }
    }

    public function deleteGroupAdminCalendars()
    {
        $userAdmin = UserQuery::Create()->findOneByPersonId(1);

        // transfert the calendars to a user
        // now we code now in Sabre
        $calendarBackend = new CalDavPDO();

        $calendars = $calendarBackend->getCalendarsForUser('principals/' . strtolower($userAdmin->getUserName()), "displayname", true);

        foreach ($calendars as $calendar) {
            $shares = $calendarBackend->getInvites($calendar['id']);

            if ($calendar['grpid'] > 0 || $calendar['cal_type'] > 1) { // only Group Calendar are purged
                foreach ($shares as $share) {
                    if ($share->principal == 'principals/' . strtolower($this->getUserName())) {
                        $share->access = \Sabre\DAV\Sharing\Plugin::ACCESS_NOACCESS;
                    }
                }
            }

            $calendarBackend->updateInvites($calendar['id'], $shares);
        }
    }

    public function createGroupAdminCalendarsAndAddressbooks()
    {
        $this->createGroupAdminCalendars();
        $this->createGroupAdminAddressBookShared();

    }


    public function deleteGroupAdminCalendarsAndAddressbooks()
    {
        $this->deleteGroupAdminCalendars();
        $this->deleteGroupAdminAddressBookShared();

    }

    public function changePrincipalEmail($newEmail)
    {
        if ($newEmail != $this->getEmail()) {
            try {

                $principal = PrincipalsQuery::Create()->findOneByEmail($this->getEmail());

                if (!is_null($principal)) {
                    $principal->setEmail($newEmail);
                    $principal->save();
                }
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->info('Unable to change email for : ' . strtolower($this->getUserName()) . '.' . $e->getMessage());
            }
        }
    }

    public function renameHomeDir($oldUserName, $newUserName)
    {
        $oldUserName = strtolower($oldUserName);
        $newUserName = strtolower($newUserName);

        if ($oldUserName != "admin") { // the calendars of the principal admin should be preserved
            return;
        }

        if ($oldUserName != $newUserName) {
            try {
                rename(dirname(__FILE__) . "/../../../" . $this->getUserDir(strtolower($oldUserName)), dirname(__FILE__) . "/../../../" . $this->getUserDir(strtolower($newUserName)));
                $this->setHomedir($this->getUserDir());
                $this->save();

                // transfert the calendars to a user
                // now we code now in Sabre
                $calendarBackend = new CalDavPDO();
                $principalBackend = new PrincipalPDO();

                $principalBackend->createNewPrincipal('principals/' . $newUserName, $this->getEmail(), $newUserName);
                $calendarBackend->moveCalendarToNewPrincipal('principals/' . $oldUserName, 'principals/' . $newUserName);

                // We delete the principal user => it will delete the calendars and events too.
                $principalBackend->deletePrincipal('principals/' . $oldUserName);
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->info('Unable to rename home dir for user: ' . strtolower($this->getUserName()) . '.' . $e->getMessage());
            }
        }
    }

    public function getUserName()
    {
        $userName = parent::getUserName();

        return strtolower($userName);
    }

    public function createHomeDir()
    {
        try {
            if (!is_dir(dirname(__FILE__) . "/../../../" . $this->getUserDir())) {
                mkdir(dirname(__FILE__) . "/../../../" . $this->getUserDir(), 0755, true);
            }
            $this->setHomedir($this->getUserDir());
            $this->save();

            // now we code in Sabre
            $principalBackend = new PrincipalPDO();

            $res = $principalBackend->getPrincipalByPath("principals/" . strtolower($this->getUserName()));
            $calendarBackend = new CalDavPDO();

            if (empty($res)) {
                $principalBackend->createNewPrincipal("principals/" . strtolower($this->getUserName()), $this->getEmail(), strtolower($this->getUserName()));
            }

            if ($this->isManageGroupsEnabled()) {
                $this->createGroupAdminCalendarsAndAddressbooks();                
            }

            // create the home public folder
            $this->createHomePublicDir();
        } catch (\Exception $e) {
            LoggerUtils::getAppLogger()->info('Unable to create home dir for user: ' . strtolower($this->getUserName()) . '.' . $e->getMessage());
        }
    }

    public function createHomePublicDir()
    {
        $path = $this->getUserPublicDir();
    }

    public function deleteHomeDir()
    {
        // we code first in Sabre
        $principalBackend = new PrincipalPDO();

        $res = $principalBackend->deletePrincipal("principals/" . strtolower($this->getUserName()));

        // we code now in propel
        MiscUtils::delTree(dirname(__FILE__) . "/../../../" . $this->getUserPublicDir());
        MiscUtils::delTree(dirname(__FILE__) . "/../../../" . $this->getUserRootDir());

        $this->setHomedir(null);
        $this->save();
    }

    public function ApplyRole($roleID)
    {
        $role = UserRoleQuery::Create()->findOneById($roleID);

        if (!is_null($role)) {
            // we first apply the global settings to the user
            $globals = explode(";", $role->getGlobal());

            foreach ($globals as $val) {
                $res = explode(":", $val);

                switch ($res[0]) {
                    case 'AddRecords':
                        $this->setAddRecords($res[1]);
                        break;
                    case 'EditRecords':
                        $this->setEditRecords($res[1]);
                        break;
                    case 'DeleteRecords':
                        $this->setDeleteRecords($res[1]);
                        break;
                    case 'ShowCart':
                        $this->setShowCart($res[1]);
                        break;
                    case 'ShowMap':
                        $this->setShowMap($res[1]);
                        break;
                    case 'EDrive':
                        $this->setEDrive($res[1]);
                        break;
                    case 'MenuOptions':
                        $this->setMenuOptions($res[1]);
                        break;
                    case 'ManageGroups':
                        $old_ManageGroups = $this->isManageGroupsEnabled();
                        $ManageGroups = $res[1];

                        if ($ManageGroups || $this->isAdmin()) {
                            if (!$old_ManageGroups) { // only when the user has now the role group manager
                                // calendars & shared addressbooks
                                $this->deleteGroupAdminCalendarsAndAddressbooks();
                                $this->createGroupAdminCalendarsAndAddressbooks();                                
                            }
                        } else if ($old_ManageGroups) { // only delete group calendars and addressboks in the case He was a group manager
                            $this->deleteGroupAdminCalendarsAndAddressbooks();
                        }
                        $this->setManageGroups($res[1]);
                        break;
                    case 'Finance':
                        $this->setFinance($res[1]);
                        break;
                    case 'Notes':
                        $this->setNotes($res[1]);
                        break;
                    case 'EditSelf':
                        $this->setEditSelf($res[1]);
                        break;
                    case 'Canvasser':
                        $this->setCanvasser($res[1]);
                        break;
                    case 'Admin':
                        $this->setAdmin($res[1]);
                        break;
                    case 'QueryMenu':
                        $this->setShowMenuQuery($res[1]);
                        break;
                    case 'CanSendEmail':
                        $this->setCanSendEmail($res[1]);
                        break;
                    case 'ExportCSV':
                        $this->setExportCSV($res[1]);
                        break;
                    case 'CreateDirectory':
                        $this->setCreatedirectory($res[1]);
                        break;
                    case 'ExportSundaySchoolPDF':
                        $this->setExportSundaySchoolPDF($res[1]);
                        break;
                    case 'ExportSundaySchoolCSV':
                        $this->setExportSundaySchoolCSV($res[1]);
                        break;
                    case 'MainDashboard':
                        $this->setMainDashboard($res[1]);
                        break;
                    case 'SeePrivacyData':
                        $this->setSeePrivacyData($res[1]);
                        break;
                    case 'MailChimp':
                        $this->setMailChimp($res[1]);
                        break;
                    case 'GdrpDpo':
                        $this->setGdrpDpo($res[1]);
                        break;
                    case 'PastoralCare':
                        $this->setPastoralCare($res[1]);
                        break;
                }
            }

            $this->setRoleId($roleID);
            $this->save();

            // now we loop to the permissions
            $permissions = explode(";", $role->getPermissions());
            $values      = explode(";", $role->getValue());

            for ($place = 0; $place < count($permissions); $place++) {
                // we search the default value
                $permission = explode(":", $permissions[$place]);
                $value = explode(":", $values[$place]);

                $global_cfg = UserConfigQuery::Create()->filterByName($permission[0])->findOneByPersonId(0);

                if (is_null($global_cfg)) continue;

                // we search if the config exist
                $user_cfg = UserConfigQuery::Create()->filterByName($permission[0])->findOneByPersonId($this->getPersonId());

                if (is_null($user_cfg)) {
                    $user_cfg = new UserConfig();

                    $user_cfg->setPersonId($this->getPersonId());
                    $user_cfg->setId($global_cfg->getId());
                    $user_cfg->setName($global_cfg->getName());
                    $user_cfg->setType($global_cfg->getType());
                    $user_cfg->setTooltip($global_cfg->getType());
                }

                $user_cfg->setChoicesId($global_cfg->getChoicesId());
                $user_cfg->setPermission($permission[1]);

                if ($value[1] == 'semi_colon') {
                    $user_cfg->setValue(';');
                } else {
                    $user_cfg->setValue($value[1]);
                }

                $user_cfg->save();
            }

            return $role->getName();
        }

        return false;
    }



    public function getName($menu = false)
    {
        return $this->getPerson()->getFullName($menu);
    }

    public function getEmail()
    {
        return $this->getPerson()->getEmail();
    }

    public function getFullName()
    {
        return $this->getPerson()->getFullName();
    }

    public function isSundayShoolTeacherForGroup($iGroupID)
    {
        if ($this->isAdmin() || $this->isAddRecords()) {
            return true;
        }

        $group = GroupQuery::Create()->findOneById($iGroupID);

        $groupRoleMembership = Person2group2roleP2g2rQuery::create()
            ->filterByPersonId($this->getPersonId())
            ->filterByGroupId($iGroupID)
            ->findOne();

        if (!empty($groupRoleMembership)) {
            $groupRole = ListOptionQuery::create()->filterById($group->getRoleListId())->filterByOptionId($groupRoleMembership->getRoleId())->findOne();
            $lst_OptionName = $groupRole->getOptionName();

            if ($lst_OptionName == 'Teacher') {
                return true;
            }
        }

        return false;
    }

    public function belongsToGroup($iGroupID)
    {
        if ($this->isAdmin() || $this->isAddRecords()) {
            return true;
        }

        $group = GroupQuery::Create()->findOneById($iGroupID);

        $groupRoleMembership = Person2group2roleP2g2rQuery::create()
            ->filterByPersonId($this->getPersonId())
            ->filterByGroupId($iGroupID)
            ->findOne();

        if (!empty($groupRoleMembership)) {
            return true;
        }

        return false;
    }

    public function isShowCartEnabled()
    {
        return $this->isAdmin() || $this->isShowCart();
    }

    public function isShowMapEnabled()
    {
        return $this->isAdmin() || $this->isShowMap();
    }

    public function isEDriveEnabled($iPersonID = 0)
    {
        if (!SystemConfig::getBooleanValue('bEnabledEdrive')) return false;
        
        if ($iPersonID == 0) {
            $iPersonID = SessionUser::getUser()->getPersonId();
        }

        if (SystemConfig::getBooleanValue('bGDPR')) {
            // GDPR : only the user can see his EDRIVE
            return $this->isEDrive() && SessionUser::getUser()->getPersonId() == $iPersonID;
        } else {
            // not GDPR
            $user = UserQuery::Create()->findPk($iPersonID);

            return (!is_null($user) &&
                ($user->getPerson()->getId() == SessionUser::getUser()->getPersonId()
                    || $user->getPerson()->getFamId() == SessionUser::getUser()->getPerson()->getFamId())) || $this->isAdmin();
        }
    }

    public function isAddRecordsEnabled()
    {
        return $this->isAdmin() || $this->isAddRecords();
    }

    public function isEditRecordsEnabled()
    {
        return $this->isAdmin() || $this->isEditRecords();
    }

    public function isDeleteRecordsEnabled()
    {
        return $this->isAdmin() || $this->isDeleteRecords() || $this->isGdrpDpo();
    }

    public function isMenuOptionsEnabled()
    {
        return $this->isAdmin() || $this->isMenuOptions();
    }

    public function getGroupManagerIds()
    {
        $groups = GroupManagerPersonQuery::create()
            ->findByPersonId($this->getId());

        $ids = [];
        foreach ($groups as $group) {
            $ids[] = $group->getGroupId();
        }

        return $ids;
    }

    public function isGroupManagerEnabled()
    {
        if ($this->isManageGroups()) {
            return true;
        }
        $groups = GroupManagerPersonQuery::create()
            ->findByPersonId(SessionUser::getId());

        if ($groups->count()) {
            return true;
        }

        return false;
    }

    public function isGroupManagerEnabledForId($groupId)
    {
        if ($this->isManageGroups()) {
            return true;
        }
        $groups = GroupManagerPersonQuery::create()
            ->filterByGroupId($groupId)
            ->findByPersonId(SessionUser::getId());

        if ($groups->count()) {
            return true;
        }

        return false;
    }

    public function isManageGroupsEnabled()
    {
        return $this->isAdmin() || $this->isManageGroups();
    }

    public function isFinanceEnabled()
    {
        return ($this->isAdmin() || $this->isFinance()) and SystemConfig::getBooleanValue('bEnabledFinance');
    }

    public function isNotesEnabled()
    {
        return $this->isAdmin() || $this->isNotes();
    }

    public function isEditSelfEnabled()
    {
        return $this->isAdmin() || $this->isEditSelf();
    }

    public function isCanvasserEnabled()
    {
        return $this->isAdmin() || $this->isCanvasser();
    }

    public function isPastoralCareEnabled()
    {
        return ($this->isAdmin() || $this->isPastoralCare()) and SystemConfig::getBooleanValue('bEnabledPastoralCare');
    }

    public function isMailChimpEnabled()
    {
        // an administrator shouldn't be an mailchimp manager
        return /*$this->isAdmin() || */ $this->isMailChimp();
    }

    public function isHtmlSourceEditorEnabled()
    {
        return $this->isAdmin() || $this->isHtmlSourceEditor();
    }

    public function isGdrpDpoEnabled()
    {
        return ($this->isAdmin() || $this->isGdrpDpo()) and SystemConfig::getBooleanValue('bGDPR');
    }

    public function isMainDashboardEnabled()
    {
        return $this->isAdmin() || $this->isMainDashboard();
    }

    public function isSeePrivacyDataEnabled()
    {
        return $this->isAdmin() || $this->isSeePrivacyData();
    }

    public function isShowMenuQueryEnabled()
    {
        return $this->isAdmin() || $this->isShowMenuQuery();
    }

    public function isShowSundaySchool()
    {
        return SystemConfig::getBooleanValue('bEnabledSundaySchool');
    }

    public function updatePassword($password)
    {
        $this->setPassword($this->hashPassword($password));
    }

    public function isPasswordValid($password)
    {
        if ($this->getIsDeactivated()) {
            return false;
        }

        return $this->getPassword() == $this->hashPassword($password);
    }

    public function hashPassword($password)
    {
        return hash('sha256', $password . $this->getPersonId());
    }

    public function isEmailEnabled()
    {
        return ($this->isAdmin() || $this->getCanSendEmail()) and SystemConfig::getBooleanValue('bEnabledEmail');
    }

    public function isExportSundaySchoolCSVEnabled()
    {
        return ($this->isAdmin() || $this->isExportSundaySchoolCSV()) and SystemConfig::getBooleanValue('bEnabledSundaySchool');
    }

    public function isExportSundaySchoolPDFEnabled()
    {
        return ($this->isAdmin() || $this->isExportSundaySchoolPDF()) and SystemConfig::getBooleanValue('bEnabledSundaySchool');
    }

    public function isCreateDirectoryEnabled()
    {
        return $this->isAdmin() || $this->isCreatedirectory();
    }

    public function isCSVExportEnabled()
    {
        return $this->isAdmin() || $this->isExportCSV();
    }

    public function MailtoDelimiter()
    {
        return $this->getUserConfigString('sMailtoDelimiter');
    }

    public function isEmailToEnabled()
    {
        return $this->getUserConfigString('bEmailMailto');
    }

    public function isShowTooltipEnabled()
    {
        return $this->getUserConfigString('bShowTooltip');
    }

    public function MapExternalProvider()
    {
        return $this->getUserConfigString('sMapExternalProvider');
    }

    public function CSVExportDelemiter()
    {
        return $this->getUserConfigString('sCSVExportDelemiter');
    }

    public function CSVExportCharset()
    {
        return $this->getUserConfigString('sCSVExportCharset');
    }

    public function isLocked()
    {
        return SystemConfig::getValue('iMaxFailedLogins') > 0 && $this->getFailedLogins() >= SystemConfig::getValue('iMaxFailedLogins');
    }

    public function resetPasswordToRandom()
    {
        $password = User::randomPassword();
        $this->updatePassword($password);
        $this->setNeedPasswordChange(true);
        $this->setFailedLogins(0);
        return $password;
    }

    public static function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < SystemConfig::getValue('iMinPasswordLength'); $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function postInsert(ConnectionInterface $con = null): void
    {
        $this->createTimeLineNote("created");
    }

    public function postDelete(ConnectionInterface $con = null): void
    {
        $this->createTimeLineNote("deleted");
    }

    public function createTimeLineNote($type, $info = null)
    {
        $note = new Note();
        $note->setPerId($this->getPersonId());
        $note->setEntered((is_null($info)) ? SessionUser::getUser()->getPersonId() : $this->getPersonId());
        $note->setType('user');

        switch ($type) {
            case "created":
                $note->setText(_('system user created'));
                break;
            case "updated":
                $note->setText(_('system user updated'));
                break;
            case "deleted":
                $note->setText(_('system user deleted'));
                break;
            case "password-reset":
                $note->setText(_('system user password reset'));
                break;
            case "password-changed":
                $note->setText(_('system user changed password'));
                break;
            case "password-changed-admin":
                $note->setText(_('system user password changed by admin'));
                break;
            case "login-reset":
                $note->setText(_('system user login reset'));
                break;
            case "dav-create-file":
                $note->setText(str_replace("home/", "", $info));
                $note->setTitle(str_replace("home/", "", $info));
                $note->setType('file');
                $note->setInfo(_('Dav create file'));
                break;
            case "dav-create-directory":
                $note->setText(str_replace("home/", "", $info));
                $note->setTitle(str_replace("home/", "", $info));
                $note->setType('folder');
                $note->setInfo(_('Dav create directory'));
                break;
            case "dav-update-file":
                $note->setText(str_replace("home/", "", $info));
                $note->setTitle(str_replace("home/", "", $info));
                $note->setType('file');
                $note->setInfo(_('Dav update file'));
                break;
            case "dav-move-copy-file":
                $note->setText(str_replace("home/", "", $info));
                $note->setTitle(str_replace("home/", "", $info));

                $path = dirname(__FILE__) . '/../../../' . $this->getUserRootDir() . str_replace("home/", "", $info);

                if (!pathinfo($path, PATHINFO_EXTENSION)) { // we are with a directory
                    $note->setType('folder');
                } else {
                    $note->setType('file');
                }
                $note->setInfo(_('Dav move copy file'));

                break;
            case "dav-delete-file":
                $note->setText(str_replace("home/", "", $info));
                $note->setTitle(str_replace("home/", "", $info));
                $note->setType('file');
                $note->setInfo(_('Dav delete file'));
                break;
        }

        $note->save();
    }

    public function getUserDir($username = '')
    {
        if ($username == '') {
            return $this->getUserRootDir() . "/" . strtolower($this->getUserName());
        }

        return $this->getUserRootDir() . "/" . strtolower($username);
    }

    public function getUserRootDir()
    {
        return $this->private_path . $this->getWebDavKeyUUID();
    }

    public function getWebDavKeyUUID()
    {
        if ($this->getWebdavkey() == null) {
            $old_dir = $this->private_path . $this->getWebdavkey() . "/" . strtolower($this->getUserName());

            $this->createWebDavUUID();

            // the new destination
            $new_dir = $this->private_path . $this->getWebdavkey() . "/" . strtolower($this->getUserName());

            // in this case we have to create the create the folder
            if (!is_dir(dirname(__FILE__) . "/../../../" . $new_dir)) {
                mkdir(dirname(__FILE__) . "/../../../" . $new_dir, 0755, true);
            }
            $this->setHomedir($new_dir);
            $this->save();

            // then we move the files
            if (file_exists(dirname(__FILE__) . "/../../../" . $old_dir) && is_dir(dirname(__FILE__) . "/../../../" . $old_dir)) {
                $old_dir = $this->private_path . strtolower($this->getUserName());

                rename(dirname(__FILE__) . "/../../../" . $old_dir, dirname(__FILE__) . "/../../../" . $new_dir);
            }
        }

        return $this->getWebdavkey();
    }

    private function createWebDavUUID()
    {
        if ($this->getWebdavkey() == null) {
            // we create the uuid name
            $uuid = strtoupper(\Sabre\DAV\UUIDUtil::getUUID());

            // we store the uuid
            $this->setWebdavkey($uuid);
            $this->save();
        }
    }

    public function getUserPublicDir()
    {
        return $this->public_path . $this->getWebDavKeyPublicUUID();
    }

    public function getWebDavKeyPublicUUID()
    {
        if ($this->getWebdavPublickey() == null) {
            $old_dir = $this->private_path . $this->getWebdavkey() . "/" . strtolower($this->getUserName());

            $this->createWebDavPublicUUID();

            // the new destination
            $new_dir = $this->public_path . $this->getWebdavPublickey() . "/";

            // in this case we have to create the create the folder
            if (!is_dir(dirname(__FILE__) . "/../../../" . $new_dir)) {
                mkdir(dirname(__FILE__) . "/../../../" . $new_dir, 0755, true);
            }

            // then we move the files
            if (file_exists(dirname(__FILE__) . "/../../../" . $old_dir) && is_dir(dirname(__FILE__) . "/../../../" . $old_dir)) {
                $old_dir = $this->public_path;

                rename(dirname(__FILE__) . "/../../../" . $old_dir, dirname(__FILE__) . "/../../../" . $new_dir);
            }
        } else { // in the case the public folder is referenced in the DB but not present on the hard drive
            if (!is_dir($this->public_path . $this->getWebdavPublickey())) { // we've to create it
                $new_dir = $this->public_path . $this->getWebdavPublickey() . "/";
                mkdir(dirname(__FILE__) . "/../../../" . $new_dir, 0755, true);
            }
        }

        // now we can create the symlink in the real home folder
        $public_dir = dirname(__FILE__) . "/../../../" . $this->public_path . $this->getWebdavPublickey();
        $public_dir_target_link = dirname(__FILE__) . "/../../../" . $this->getUserDir() . "/public";

        if (is_dir($public_dir_target_link) and !is_link($public_dir_target_link)) {
            MiscUtils::delTree($public_dir_target_link);
        }

        if (!is_link($public_dir_target_link)) {
            symlink($public_dir . "/", $public_dir_target_link);
        }

        return $this->getWebdavPublickey();
    }


    private function createWebDavPublicUUID()
    {
        if ($this->getWebdavPublickey() == null) {
            // we create the uuid name
            $uuid = strtoupper(\Sabre\DAV\UUIDUtil::getUUID());

            // we store the uuid
            $this->setWebdavPublickey($uuid);
            $this->save();
        }
    }

    public function deleteTimeLineNote($type, $info = null)
    {
        $notes = NoteQuery::Create()->filterByPerId($this->getPersonId())->findByText(str_replace("home/", "", $info));

        if (!empty($notes)) {
            $notes->delete();
        }
    }

    // this part is called in EcclesiaCRMServer from
    public function updateFolder($oldPath, $newPath)
    {
        $realOldPath = str_replace("home/", "", $oldPath);
        $realNewPath = str_replace("home/", "", $newPath);

        $notes = NoteQuery::create()
            ->filterByText("%$realOldPath%", Criteria::LIKE)
            ->find();

        if (!is_null($notes)) {
            foreach ($notes as $note) {
                $oldName = $note->getText();
                $newName = str_replace($oldPath, $newPath, $note->getText());

                $newNote = NoteQuery::Create()->findOneById($note->getId());
                $newNote->setText(str_replace($realOldPath, $realNewPath, $note->getText()));
                $newNote->setCurrentEditedBy(0);
                $newNote->save();
            }
        }
    }

    public function isEnabledSecurity($securityConfigName)
    {
        if ($this->isAdmin()) {
            return true;
        }
        foreach ($this->getUserConfigs() as $userConfig) {
            if ($userConfig->getName() == $securityConfigName) {
                return $userConfig->getPermission() == "TRUE";
            }
        }
        return false;
    }

    public function getUserMainSettingByString($value)
    {
        $res = ($this->{$value}) ? true : false;
        return ($res or $this->isAdmin());
    }

    public function getUserConfigString($userConfigName)
    {
        // we search if the config exist
        $userConf = UserConfigQuery::Create()->filterByName($userConfigName)->findOneByPersonId($this->getPersonId());

        if (is_null($userConf)) {
            $userDefault = UserConfigQuery::create()->filterByName($userConfigName)->findOneByPersonId(0);

            if (!is_null($userDefault)) {
                $userConf = new UserConfig();

                $userConf->setPersonId($this->getPersonId());
                $userConf->setId($userDefault->getId());
                $userConf->setName($userConfigName);
                $userConf->setValue($userDefault->getValue());
                $userConf->setType($userDefault->getType());
                $userConf->setChoicesId($userDefault->getChoicesId());
                $userConf->setTooltip(htmlentities(addslashes($userDefault->getTooltip()), ENT_NOQUOTES, 'UTF-8'));
                $userConf->setPermission('FALSE');
                $userConf->setCat($userDefault->getCat());

                $userConf->save();
            }
        }

        return $userConf->getValue();
    }

    public function LoginPhaseActivations($takeControl = false)
    {
        // Set the LastLogin and Increment the LoginCount
        if ($takeControl == false) {
            $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
            $this->setLastLogin($date->format('Y-m-d H:i:s'));
            $this->setLoginCount($this->getLoginCount() + 1);
            $this->setFailedLogins(0);

            $this->setIsLoggedIn(true);
        }
        $this->save();

        SessionUser::setMustChangePasswordRedirect(false);

        $_SESSION['user'] = $this;

        // Set the UserID
        $_SESSION['iUserID'] = $this->getPersonId();

        // Set the User's family id in case EditSelf is enabled
        $_SESSION['iFamID'] = $this->getPerson()->getFamId();

        // for webDav we've to create the Home directory
        $this->createHomeDir();

        // If user has administrator privilege, override other settings and enable all permissions.
        // this is usefull for : MiscUtils::requireUserGroupMembership in Include/Functions.php

        $_SESSION['bAdmin'] = $this->isAdmin();                             //ok
        $_SESSION['bPastoralCare'] = $this->isPastoralCareEnabled();        //ok
        $_SESSION['bMailChimp'] = $this->isMailChimpEnabled();              //ok
        $_SESSION['bGdrpDpo'] = $this->isGdrpDpoEnabled();                  //ok
        $_SESSION['bMainDashboard'] = $this->isMainDashboardEnabled();      //ok
        $_SESSION['bSeePrivacyData'] = $this->isSeePrivacyDataEnabled();    //ok
        $_SESSION['bAddRecords'] = $this->isAddRecordsEnabled();            //ok
        $_SESSION['bEditRecords'] = $this->isEditRecordsEnabled();          //ok
        $_SESSION['bDeleteRecords'] = $this->isDeleteRecordsEnabled();      //ok
        $_SESSION['bMenuOptions'] = $this->isMenuOptionsEnabled();          //ok
        $_SESSION['bManageGroups'] = $this->isManageGroupsEnabled();        //usefull in GroupView and in Properties
        $_SESSION['bFinance'] = $this->isFinanceEnabled();                  //ok
        $_SESSION['bNotes'] = $this->isNotesEnabled();                      //ok
        $_SESSION['bCanvasser'] = $this->isCanvasserEnabled();              //ok
        $_SESSION['bEditSelf'] = $this->isEditSelfEnabled();                //ok
        $_SESSION['bShowCart'] = $this->isShowCartEnabled();                //ok
        $_SESSION['bShowMap'] = $this->isShowMapEnabled();                  //ok
        $_SESSION['bEDrive'] = $this->isEDriveEnabled();                    //ok
        $_SESSION['bShowMenuQuery'] = $this->isShowMenuQueryEnabled();      //ok

        // for https : usefull in apis
        $_SESSION['isSecure'] = MiscUtils::isSecure();

        // Create the Cart
        $_SESSION['aPeopleCart'] = [];

        // Initialize the last operation time
        $_SESSION['tLastOperation'] = time();

        $_SESSION['bHasMagicQuotes'] = 0;

        // Pledge and payment preferences
        $_SESSION['sshowPledges'] = $this->getShowPledges();
        $_SESSION['sshowPayments'] = $this->getShowPayments();

        $_SESSION['photos'] = [
            'persons' => [],
            'families' => []
        ];

        // set the jwt token
        // we create the token and secret, only when login in not as ControllerAdminUserId
        if (!isset($_SESSION['ControllerAdminUserId'])) {
            $secretKey = MiscUtils::gen_uuid();
            $issuedAt = (new DateTime("now"))->getTimestamp();
            $expire = (new DateTime("now +24 hours"))->getTimestamp();      // Ajoute 60 secondes
            $serverName = $_SERVER['HTTP_ORIGIN'];
            $username = $this->getUserName();                                           // Récupéré à partir des données POST filtré

            $data = [
                'iat' => $issuedAt,         // Issued at:  : heure à laquelle le jeton a été généré
                'iss' => $serverName,                       // Émetteur
                'exp' => $expire,                           // Expiration
                'userName' => $username,                    // Nom d'utilisateur
            ];

            $jwt = JWT::encode(
                $data,
                $secretKey,
                'HS256'
            );

            if (is_null($this->getJwtSecret()) or $takeControl == false) {
                $this->setJwtSecret($secretKey);
            }
            if (is_null($this->getJwtToken())  or $takeControl == false) {
                $this->setJwtToken($jwt);
            }

            $this->save();

            setcookie($this->getUserName(), $this->getJwtToken(), time() + 24 * 3600);
        }
        // end of JWT token activation

        $this->save();

        if (is_null($_SESSION['user']->getShowSince())) {
            $_SESSION['user']->setShowSince(date("Y-m-d", strtotime('-1 year')));
            $this->save();
        }

        if (is_null($_SESSION['user']->getShowTo())) {
            $_SESSION['user']->setShowTo(date('Y-m-d'));
            $this->save();
        }

        $_SESSION['idefaultFY'] = MiscUtils::CurrentFY(); // Improve the chance of getting the correct fiscal year assigned to new transactions
        $_SESSION['iCurrentDeposit'] = $this->getCurrentDeposit();

        $systemService = new SystemService();
        $_SESSION['latestVersion'] = $systemService->getLatestRelese();
        NotificationService::updateNotifications();

        $_SESSION['isUpdateRequired'] = NotificationService::isUpdateRequired();

        $_SESSION['isSoftwareUpdateTestPassed'] = false;

        // We check whether each active plugin has a role for the currently logged-in user.
        $dashPlugins = PluginQuery::create()
            ->filterByActiv(1)
            ->filterByCategory('Dashboard')
            ->find();

        foreach ($dashPlugins as $plugin) {
            $plgnRole = PluginUserRoleQuery::create()
                ->filterByPluginId($plugin->getId())
                ->findOneByUserId(SessionUser::getId());

            if (is_null($plgnRole)) {
                $plgnRole = new PluginUserRole();

                $plgnRole->setPluginId($plugin->getId());
                $plgnRole->setUserId(SessionUser::getId());
                $plgnRole->setDashboardColor($plugin->getDashboardDefaultColor());
                $plgnRole->setDashboardOrientation($plugin->getDashboardDefaultOrientation());
                $plgnRole->setDashboardVisible(true);

                $plgnRole->save();
            }
        }
    }

    public function isEnableForPlugin($name)
    {
        $plugin = PluginQuery::create()->findOneByName($name);

        if (is_null($plugin)) {
            return false;
        }

        $role = PluginUserRoleQuery::create()
            ->filterByUserId($this->getId())
            ->findOneByPluginId($plugin->getId());

        if (!is_null($role)) {
            return ($role->getRole() == 'user' or $role->getRole() == 'admin') ? true : false;
        }

        return false;
    }

    public function isAdminEnableForPlugin($name)
    {
        $plugin = PluginQuery::create()->findOneByName($name);

        if (is_null($plugin)) {
            return false;
        }

        $role = PluginUserRoleQuery::create()
            ->filterByUserId($this->getId())
            ->findOneByPluginId($plugin->getId());

        if (!is_null($role)) {
            return ($role->getRole() == 'admin') ? true : false;
        }

        return false;
    }

    public function allSecuritiesBits()
    {
        $bits = SecurityOptions::bNoDashBordItem;

        if ($this->isAdmin()) { // bit 0
            $bits |= SecurityOptions::bAdmin;
        }
        if ($this->isPastoralCareEnabled()) { // bit 1
            $bits |= SecurityOptions::bPastoralCare;
        }
        if ($this->isMailChimpEnabled()) { // bit 2
            $bits |= SecurityOptions::bMailChimp;
        }
        if ($this->isGdrpDpoEnabled()) { // bit 3
            $bits |= SecurityOptions::bGdrpDpo;
        }
        if ($this->isMainDashboardEnabled()) { // bit 4
            $bits |= SecurityOptions::bMainDashboard;
        }
        if ($this->isSeePrivacyDataEnabled()) { // bit 5
            $bits |= SecurityOptions::bSeePrivacyData;
        }
        if ($this->isAddRecordsEnabled()) { // bit 6
            $bits |= SecurityOptions::bAddRecords;
        }
        if ($this->isEditRecordsEnabled()) { // bit 7
            $bits |= SecurityOptions::bEditRecords;
        }
        if ($this->isDeleteRecordsEnabled()) { // bit 8
            $bits |= SecurityOptions::bDeleteRecords;
        }
        if ($this->isMenuOptionsEnabled()) { // bit 9
            $bits |= SecurityOptions::bMenuOptions;
        }
        if ($this->isManageGroupsEnabled()) { // bit 10
            $bits |= SecurityOptions::bManageGroups;
        }
        if ($this->isFinanceEnabled()) { // bit 11
            $bits |= SecurityOptions::bFinance;
        }
        if ($this->isNotesEnabled()) { // bit 12
            $bits |= SecurityOptions::bNotes;
        }
        if ($this->isCanvasserEnabled()) { // bit 13
            $bits |= SecurityOptions::bCanvasser;
        }
        if ($this->isEditSelf()) { // bit 14
            $bits |= SecurityOptions::bEditSelf;
        }
        if ($this->isShowCartEnabled()) { // bit 15
            $bits |= SecurityOptions::bShowCart;
        }
        if ($this->isShowMapEnabled()) { // bit 16
            $bits |= SecurityOptions::bShowMap;
        }
        if ($this->isEDriveEnabled()) { // bit 17
            $bits |= SecurityOptions::bEDrive;
        }
        if ($this->isShowMenuQueryEnabled()) { // bit 18
            $bits |= SecurityOptions::bShowMenuQuery;
        }
        if ($this->isShowMenuQueryEnabled()) { // bit 19
            $bits |= SecurityOptions::bSundaySchool;
        }

        $bits |= SecurityOptions::bDashBoardUser;

        return $bits;
    }

    public function isSecurityEnableForPlugin($name, $sec = 1073741824)
    {
        $plugin = PluginQuery::create()->findOneByName($name);

        if ($plugin->getSecurities() & $sec) { // when the bit sec is activated
            switch ($sec) {
                case 1: // bAdmin bit 0
                    return $this->isAdmin();
                case 2: // bPastoralCare bit 1
                    return $this->isPastoralCareEnabled();
                case 4: // see : SecurityOptions bit 2
                    return $this->isMailChimpEnabled();
                case 8: // bit 3
                    return $this->isGdrpDpoEnabled();
                case 16: // bit 4
                    return $this->isMainDashboardEnabled();
                case 32: // bit 5
                    return $this->isSeePrivacyData();
                case 64: // bit 6
                    return $this->isAddRecordsEnabled();
                case 128: // bit 7
                    return $this->isEditRecordsEnabled();
                case 256: // bit 8
                    return $this->isDeleteRecordsEnabled();
                case 512: // bit 9
                    return $this->isMenuOptionsEnabled();
                case 1024: // bit 10
                    return $this->isManageGroupsEnabled();
                case 2048: // bit 11
                    return $this->isFinanceEnabled();
                case 4096: // bit 12
                    return $this->isNotesEnabled();
                case 8192: // bit 13
                    return $this->isCanvasserEnabled();
                case 16384: // bit 14
                    return $this->isEditSelf();
                case 32768: // bit 15
                    return $this->isShowCartEnabled();
                case 65536: // bit 16
                    return $this->isShowMapEnabled();
                case 131072: // bit 17
                    return $this->isEDriveEnabled();
                case 262144: // bit 18
                    return $this->isShowMenuQueryEnabled();
                case 524288: // bit 19
                    return $this->isShowSundaySchool();
                case 1073741824: // ever true
                    return true;
            }
            return true;
        }

        return false;
    }

    public function getJwtSecretForApi()
    {
        if (isset($_SESSION['ControllerAdminUserSecret'])) {
            return $_SESSION['ControllerAdminUserSecret'];
        }

        return parent::getJwtSecret();
    }

    public function getJwtTokenForApi()
    {
        if (isset($_SESSION['ControllerAdminUserToken'])) {
            return $_SESSION['ControllerAdminUserToken'];
        }

        return parent::getJwtToken();
    }

    public function getUserNameForApi()
    {
        if (isset($_SESSION['ControllerAdminUserName'])) {
            $userName = $_SESSION['ControllerAdminUserName'];
        } else {
            $userName = $this->getUserName();
        }

        return strtolower($userName);
    }

    public function isSecure()
    {
        // check if https is active
        if (isset($_SESSION['isSecure'])) {
            return $_SESSION['isSecure'];
        }

        return false;
    }

    /**
     * Get the [usr_needpasswordchange] column value.
     *
     * @return boolean
     */
    public function getNeedPasswordChange()
    {
        if (isset($_SESSION['ControllerAdminUserId'])) return false;

        return parent::getNeedPasswordChange();
    }

    public function checkEdrive() : bool 
    {
        $ownerPath =  SystemURLs::getDocumentRoot() . "/" . $this->getUserDir();

        return is_dir($ownerPath);
    }
}
