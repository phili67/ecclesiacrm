<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\User as BaseUser;
use EcclesiaCRM\dto\SystemConfig;
use Propel\Runtime\Connection\ConnectionInterface;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;

use Sabre\DAV\Xml\Element\Sharee;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use EcclesiaCRM\MyPDO\CalDavPDO;

use EcclesiaCRM\Service\NotificationService;
use EcclesiaCRM\Service\SystemService;

use DateTime;
use DateTimeZone;

abstract class SecurityOptions
{
    const bAdmin = 1; // 2^0
    const bPastoralCare = 2;
    const bMailChimp = 4;
    const bGdrpDpo = 8;
    const bMainDashboard = 16;
    const bSeePrivacyData = 32;
    const bAddRecords = 64;
    const bEditRecords = 128;
    const bDeleteRecords = 256;
    const bMenuOptions = 512;
    const bManageGroups = 1024;
    const bFinance = 2048;
    const bNotes = 4096;
    const bCanvasser = 9192;
    const bEditSelf = 16384;
    const bShowCart = 32768;
    const bShowMap = 65536;
    const bEDrive = 131072;
    const bShowMenuQuery = 262144; // 2^18
    const bNone = 1073741824; // 2^30
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

    public function preDelete(\Propel\Runtime\Connection\ConnectionInterface $con = NULL)
    {
        if (parent::preDelete($con)) {
          $this->deleteHomeDir();

          // transfert the calendars to a user
          // now we code now in Sabre
          $principalBackend = new PrincipalPDO();

          // puis on delete le user
          $principalBackend->deletePrincipal('principals/'.$this->getUserName());

          return true;
        }

        return false;
    }

    public function deleteGroupAdminCalendars ()
    {
        $userAdmin = UserQuery::Create()->findOneByPersonId (1);

        // transfert the calendars to a user
        // now we code now in Sabre
        $calendarBackend = new CalDavPDO();

        $calendars = $calendarBackend->getCalendarsForUser('principals/'.strtolower($userAdmin->getUserName()),"displayname",true);

        foreach ($calendars as $calendar) {
          $shares = $calendarBackend->getInvites($calendar['id']);

          if ($calendar['grpid'] > 0 || $calendar['cal_type'] > 1) {// only Group Calendar are purged
            foreach ($shares as $share) {
                if ($share->principal == 'principals/'.strtolower($this->getUserName())) {
                  $share->access = \Sabre\DAV\Sharing\Plugin::ACCESS_NOACCESS;
                }
            }
          }

          $calendarBackend->updateInvites($calendar['id'],$shares);
        }
    }

    public function createGroupAdminCalendars ()
    {
        $userAdmin = UserQuery::Create()->findOneByPersonId (1);

        // transfert the calendars to a user
        // now we code now in Sabre
        $calendarBackend = new CalDavPDO();

        if ( $this->isManageGroupsEnabled() && $userAdmin->getPersonID() != $this->getPersonID()) {// an admin can't change itself and is ever tge main group manager
          // we have to add the groupCalendars
          $calendars = $calendarBackend->getCalendarsForUser('principals/'.strtolower($userAdmin->getUserName()),"displayname",true);

          foreach ($calendars as $calendar) {
            // we'll connect to sabre
            // Add a new invite
            if ($calendar['grpid'] > 0 || $calendar['cal_type'] > 1) {
              $calendarBackend->updateInvites(
                $calendar['id'],
                [
                    new Sharee([
                        'href'         => 'mailto:'.$this->getEmail(),
                        'principal'    => 'principals/'.strtolower( $this->getUserName() ),
                        'access'       => \Sabre\DAV\Sharing\Plugin::ACCESS_READWRITE,
                        'inviteStatus' => \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED,
                        'properties'   => ['{DAV:}displayname' => strtolower( $this->getFullName() )],
                    ])
                ]
              );
            }
          }
        }
    }

    public function changePrincipalEmail ($newEmail)
    {
      if ($newEmail != $this->getEmail()) {
         try {

              $principal = PrincipalsQuery::Create()->findOneByEmail ($this->getEmail());

              if ( !is_null ($principal) ) {
                $principal->setEmail ($newEmail);
                $principal->save();
              }

         } catch (Exception $e) {
              throw new PropelException('Unable to change email for : '.strtolower($this->getUserName()).'.', 0, $e);
         }
      }
    }

    public function renameHomeDir($oldUserName,$newUserName)
    {
      $oldUserName = strtolower ($oldUserName);
      $newUserName = strtolower ($newUserName);

      if ($oldUserName != "admin") {// the calendars of the principal admin should be preserved
        return;
      }

      if ($oldUserName != $newUserName) {
         try {
              rename(dirname(__FILE__)."/../../../".$this->getUserDir(strtolower($oldUserName)),dirname(__FILE__)."/../../../".$this->getUserDir(strtolower($newUserName)));
              $this->setHomedir($this->getUserDir());
              $this->save();

              // transfert the calendars to a user
              // now we code now in Sabre
              $calendarBackend = new CalDavPDO();
              $principalBackend = new PrincipalPDO();

              $principalBackend->createNewPrincipal('principals/'.$newUserName, $this->getEmail() ,$newUserName);
              $calendarBackend->moveCalendarToNewPrincipal('principals/'.$oldUserName,'principals/'.$newUserName);

              // We delete the principal user => it will delete the calendars and events too.
              $principalBackend->deletePrincipal('principals/'.$oldUserName);

         } catch (Exception $e) {
              throw new PropelException('Unable to rename home dir for user'.strtolower($this->getUserName()).'.', 0, $e);
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

            mkdir(dirname(__FILE__)."/../../../".$this->getUserDir(), 0755, true);
            $this->setHomedir($this->getUserDir());
            $this->save();

            // now we code in Sabre
            $principalBackend = new PrincipalPDO();

            $res = $principalBackend->getPrincipalByPath ("principals/".strtolower( $this->getUserName() ));
            $calendarBackend = new CalDavPDO();

            if (empty($res)) {
              $principalBackend->createNewPrincipal("principals/".strtolower( $this->getUserName() ), $this->getEmail(),strtolower($this->getUserName()));
            }

            if ($this->isManageGroupsEnabled()) {
              $this->createGroupAdminCalendars ();
            }

            // create the home public folder
            $this->createHomePublicDir();

       } catch (Exception $e) {
            throw new PropelException('Unable to create home dir for user'.strtolower($this->getUserName()).'.', 0, $e);
       }
    }

    public function createHomePublicDir ()
    {
      $path = $this->getUserPublicDir();
    }

    public function deleteHomeDir()
    {
      // we code first in Sabre
      $principalBackend = new PrincipalPDO();

      $res = $principalBackend->deletePrincipal ("principals/".strtolower( $this->getUserName() ));

      // we code now in propel
      MiscUtils::delTree(dirname(__FILE__)."/../../../".$this->getUserPublicDir());
      MiscUtils::delTree(dirname(__FILE__)."/../../../".$this->getUserRootDir());

      $this->setHomedir(null);
      $this->save();
    }

    public function ApplyRole($roleID)
    {
      $role = UserRoleQuery::Create()->findOneById($roleID);

      if (!is_null($role)) {
        // we first apply the global settings to the user
        $globals = explode(";",$role->getGlobal());

        foreach ($globals as $val) {
          $res = explode (":",$val);

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

               if ( $ManageGroups || $this->isAdmin() ) {
                 if ( !$old_ManageGroups ) {// only when the user has now the role group manager
                    $this->deleteGroupAdminCalendars();
                    $this->createGroupAdminCalendars();
                 }
               } else if ($old_ManageGroups) {// only delete group calendars in the case He was a group manager
                 $this->deleteGroupAdminCalendars();
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
        $permissions = explode(";",$role->getPermissions());
        $values      = explode(";",$role->getValue());

        for ($place=0;$place<count($permissions);$place++) {
          // we search the default value
          $permission = explode (":",$permissions[$place]);
          $value = explode (":",$values[$place]);

          $global_cfg = UserConfigQuery::Create()->filterByName($permission[0])->findOneByPersonId(0);

          if ( is_null($global_cfg) ) continue;

          // we search if the config exist
          $user_cfg = UserConfigQuery::Create()->filterByName($permission[0])->findOneByPersonId($this->getPersonId());

          if ( is_null($user_cfg) ) {
            $user_cfg = new UserConfig();

            $user_cfg->setPersonId($this->getPersonId());
            $user_cfg->setId($global_cfg->getId());
            $user_cfg->setName($global_cfg->getName());
            $user_cfg->setType($global_cfg->getType());
            $user_cfg->setTooltip($global_cfg->getType());
          }

          $user_cfg->setChoicesId($global_cfg->getChoicesId());
          $user_cfg->setPermission($permission[1]);

          if ($value[1] == 'semi_colon'){
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



    public function getName()
    {
        return $this->getPerson()->getFullName();
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
                            ->filterByPersonId ($this->getPersonId())
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
                            ->filterByPersonId ($this->getPersonId())
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

    public function isEDriveEnabled($iPersonID=0)
    {
        if ($iPersonID == 0) {
          $iPersonID = SessionUser::getUser()->getPersonId();
        }

        if (SystemConfig::getBooleanValue('bGDPR')) {
          // GDPR : only the user can see his EDRIVE
          return $this->isEDrive() && SessionUser::getUser()->getPersonId() == $iPersonID;
        } else {
          // not GDPR
          $user = UserQuery::Create()->findPk($iPersonID);

          return ( !is_null($user) &&
              ( $user->getPerson()->getId() == SessionUser::getUser()->getPersonId()
              || $user->getPerson()->getFamId() == SessionUser::getUser()->getPerson()->getFamId() )) || $this->isAdmin();
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
        return $this->isAdmin() || $this->isDeleteRecords();
    }

    public function isMenuOptionsEnabled()
    {
        return $this->isAdmin() || $this->isMenuOptions();
    }

    public function isManageGroupsEnabled()
    {
        return $this->isAdmin() || $this->isManageGroups();
    }

    public function isFinanceEnabled()
    {
        /*if (!SystemConfig::getBooleanValue('bEnabledFinance'))
          return false;*/

        return $this->isAdmin() || $this->isFinance();
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
        return $this->isAdmin() || $this->isPastoralCare();
    }

    public function isMailChimpEnabled()
    {
        // an administrator shouldn't be an mailchimp manager
        return /*$this->isAdmin() || */$this->isMailChimp();
    }

    public function isHtmlSourceEditorEnabled()
    {
        return $this->isAdmin() || $this->isHtmlSourceEditor();
    }

    public function isGdrpDpoEnabled()
    {
        return $this->isAdmin() || $this->isGdrpDpo();
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
        return $this->isAdmin() || $this->getCanSendEmail();
    }

    public function isExportSundaySchoolCSVEnabled()
    {
        return $this->isAdmin() || $this->isExportSundaySchoolCSV();
    }

    public function isExportSundaySchoolPDFEnabled()
    {
        return $this->isAdmin() || $this->isExportSundaySchoolPDF();
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

    public function isUSAddressVerificationEnabled()
    {
        return $this->isAdmin() || $this->getUserConfigString('bUSAddressVerification');
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

    public function resetPasswordToRandom() {
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

    public function postInsert(ConnectionInterface $con = null)
    {
        $this->createTimeLineNote("created");
    }

    public function postDelete(ConnectionInterface $con = null)
    {
        $this->createTimeLineNote("deleted");
    }

    public function createTimeLineNote($type,$info = null)
    {
        $note = new Note();
        $note->setPerId($this->getPersonId());
        $note->setEntered((is_null($info))?SessionUser::getUser()->getPersonId():$this->getPersonId());
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
                $note->setText(str_replace("home/","",$info));
                $note->setTitle(str_replace("home/","",$info));
                $note->setType('file');
                $note->setInfo(_('Dav create file'));
                break;
            case "dav-create-directory":
                $note->setText(str_replace("home/","",$info));
                $note->setTitle(str_replace("home/","",$info));
                $note->setType('folder');
                $note->setInfo(_('Dav create directory'));
                break;
            case "dav-update-file":
                $note->setText(str_replace("home/","",$info));
                $note->setTitle(str_replace("home/","",$info));
                $note->setType('file');
                $note->setInfo(_('Dav update file'));
                break;
            case "dav-move-copy-file":
                $note->setText(str_replace("home/","",$info));
                $note->setTitle(str_replace("home/","",$info));

                $path = dirname(__FILE__).'/../../../'.$this->getUserRootDir().str_replace("home/","",$info);

                if (!pathinfo($path, PATHINFO_EXTENSION)) {// we are with a directory
                  $note->setType('folder');
                } else {
                  $note->setType('file');
                }
                $note->setInfo(_('Dav move copy file'));

                break;
            case "dav-delete-file":
                $note->setText(str_replace("home/","",$info));
                $note->setTitle(str_replace("home/","",$info));
                $note->setType('file');
                $note->setInfo(_('Dav delete file'));
                break;
        }

        $note->save();
    }

    public function getUserDir($username = '')
    {
      if ($username == '') {
        return $this->getUserRootDir()."/".strtolower($this->getUserName());
      }

      return $this->getUserRootDir()."/".strtolower($username);
    }

    public function getUserRootDir()
    {
      return $this->private_path.$this->getWebDavKeyUUID();
    }

    public function getWebDavKeyUUID()
    {
      if ($this->getWebdavkey() == null) {
        $old_dir = $this->private_path.$this->getWebdavkey()."/".strtolower($this->getUserName());

        $this->createWebDavUUID();

        // the new destination
        $new_dir = $this->private_path.$this->getWebdavkey()."/".strtolower($this->getUserName());

        // in this case we have to create the create the folder
        mkdir(dirname(__FILE__)."/../../../".$new_dir, 0755, true);
        $this->setHomedir($new_dir);
        $this->save();

        // then we move the files
        if (file_exists(dirname(__FILE__)."/../../../".$old_dir) && is_dir(dirname(__FILE__)."/../../../".$old_dir)) {
          $old_dir = $this->private_path.strtolower($this->getUserName());

          rename(dirname(__FILE__)."/../../../".$old_dir,dirname(__FILE__)."/../../../".$new_dir);
        }
      }

      return $this->getWebdavkey();
    }

    private function createWebDavUUID()
    {
      if ($this->getWebdavkey() == null) {
        // we create the uuid name
        $uuid = strtoupper( \Sabre\DAV\UUIDUtil::getUUID() );

        // we store the uuid
        $this->setWebdavkey($uuid);
        $this->save();
      }
    }

    public function getUserPublicDir()
    {
      return $this->public_path.$this->getWebDavKeyPublicUUID();
    }

    public function getWebDavKeyPublicUUID()
    {
      if ($this->getWebdavPublickey() == null) {
        $old_dir = $this->private_path.$this->getWebdavkey()."/".strtolower($this->getUserName());

        $this->createWebDavPublicUUID();

        // the new destination
        $new_dir = $this->public_path.$this->getWebdavPublickey()."/";

        // in this case we have to create the create the folder
        mkdir(dirname(__FILE__)."/../../../".$new_dir, 0755, true);

        // then we move the files
        if (file_exists(dirname(__FILE__)."/../../../".$old_dir) && is_dir(dirname(__FILE__)."/../../../".$old_dir)) {
          $old_dir = $this->public_path;

          rename(dirname(__FILE__)."/../../../".$old_dir,dirname(__FILE__)."/../../../".$new_dir);
        }
      } else { // in the case the public folder is referenced in the DB but not present on the hard drive
        if ( !is_dir ($this->public_path.$this->getWebdavPublickey() ) ) {// we've to create it
          $new_dir = $this->public_path.$this->getWebdavPublickey()."/";
          mkdir(dirname(__FILE__)."/../../../".$new_dir, 0755, true);
        }
      }

      // now we can create the symlink in the real home folder
      $public_dir = dirname(__FILE__)."/../../../".$this->public_path.$this->getWebdavPublickey();
      $public_dir_target_link = dirname(__FILE__)."/../../../".$this->getUserDir()."/public";
      if ( !is_link($public_dir_target_link) or is_dir($public_dir_target_link) or !is_dir($public_dir_target_link) ) {
          MiscUtils::delTree($public_dir_target_link);
          symlink($public_dir."/", $public_dir_target_link);
      }

      return $this->getWebdavPublickey();
    }


    private function createWebDavPublicUUID()
    {
      if ($this->getWebdavPublickey() == null) {
        // we create the uuid name
        $uuid = strtoupper( \Sabre\DAV\UUIDUtil::getUUID() );

        // we store the uuid
        $this->setWebdavPublickey($uuid);
        $this->save();
      }
    }

    public function deleteTimeLineNote($type,$info = null)
    {
      $notes = NoteQuery::Create ()->filterByPerId ($this->getPersonId())->findByText (str_replace("home/","",$info));

      if (!empty($notes)) {
        $notes->delete();
      }
    }

    // this part is called in EcclesiaCRMServer from
    public function updateFolder($oldPath,$newPath)
    {
      $realOldPath = str_replace("home/","",$oldPath);
      $realNewPath = str_replace("home/","",$newPath);

      $notes = NoteQuery::create()
              ->filterByText("%$realOldPath%", Criteria::LIKE)
              ->find();

      if (!is_null ($notes)) {
        foreach ($notes as $note) {
          $oldName = $note->getText();
          $newName = str_replace($oldPath,$newPath,$note->getText());

          $newNote = NoteQuery::Create()->findOneById($note->getId());
          $newNote->setText(str_replace($realOldPath,$realNewPath,$note->getText()));
          $newNote->setCurrentEditedBy(0);
          $newNote->save();
        }
      }
    }

    public function isEnabledSecurity($securityConfigName){
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

    public function getUserMainSettingByString($value) {
        $res = ($this->{$value})?true:false;
        return ($res or $this->isAdmin());
    }

    public function getUserConfigString($userConfigName) {
      // we search if the config exist
        $userConf = UserConfigQuery::Create()->filterByName($userConfigName)->findOneByPersonId($this->getPersonId());

        if ( is_null($userConf) ) {
          $userDefault = UserConfigQuery::create()->filterByName($userConfigName)->findOneByPersonId (0);

          if ( !is_null ($userDefault) ) {
            $userConf = new UserConfig();

            $userConf->setPersonId ($this->getPersonId());
            $userConf->setId ($userDefault->getId());
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

    public function LoginPhaseActivations()
    {
        $token = TokenQuery::Create()->findOneByType("secret");

        if (is_null($token)) {
            $token = new Token ();
            $token->buildSecret();
            $token->save();
        }

        $dateNow = new DateTime("now");

        if ($dateNow > $token->getValidUntilDate()) {// the token expire
            // we delete the old token
            $token->delete();
            // we create a new one
            $token = new Token ();
            $token->buildSecret();
            $token->save();
        }

        // Set the LastLogin and Increment the LoginCount
        $date = new DateTime('now', new DateTimeZone(SystemConfig::getValue('sTimeZone')));
        $this->setLastLogin($date->format('Y-m-d H:i:s'));
        $this->setLoginCount($this->getLoginCount() + 1);
        $this->setFailedLogins(0);
        $this->save();

        $_SESSION['user'] = $this;

        // Set the UserID
        $_SESSION['iUserID'] = $this->getPersonId();

        // Set the User's family id in case EditSelf is enabled
        $_SESSION['iFamID'] = $this->getPerson()->getFamId();

        // for webDav we've to create the Home directory
        $this->createHomeDir();

        // If user has administrator privilege, override other settings and enable all permissions.
        // this is usefull for : MiscUtils::requireUserGroupMembership in Include/Functions.php

        $_SESSION['bAdmin'] = $this->isAdmin();                       //ok
        $_SESSION['bPastoralCare'] = $this->isPastoralCareEnabled();         //ok
        $_SESSION['bMailChimp'] = $this->isMailChimpEnabled();            //ok
        $_SESSION['bGdrpDpo'] = $this->isGdrpDpoEnabled();              //ok
        $_SESSION['bMainDashboard'] = $this->isMainDashboardEnabled();        //ok
        $_SESSION['bSeePrivacyData'] = $this->isSeePrivacyDataEnabled();       //ok
        $_SESSION['bAddRecords'] = $this->isAddRecordsEnabled();           //ok
        $_SESSION['bEditRecords'] = $this->isEditRecordsEnabled();          //ok
        $_SESSION['bDeleteRecords'] = $this->isDeleteRecordsEnabled();        //ok
        $_SESSION['bMenuOptions'] = $this->isMenuOptionsEnabled();          //ok
        $_SESSION['bManageGroups'] = $this->isManageGroupsEnabled();         //usefull in GroupView and in Properties
        $_SESSION['bFinance'] = $this->isFinanceEnabled();              //ok
        $_SESSION['bNotes'] = $this->isNotesEnabled();                //ok
        $_SESSION['bCanvasser'] = $this->isCanvasserEnabled();            //ok
        $_SESSION['bEditSelf'] = $this->isEditSelfEnabled();             //ok
        $_SESSION['bShowCart'] = $this->isShowCartEnabled();             //ok
        $_SESSION['bShowMap'] = $this->isShowMapEnabled();              //ok
        $_SESSION['bEDrive'] = $this->isEDriveEnabled();               //ok
        $_SESSION['bShowMenuQuery'] = $this->isShowMenuQueryEnabled();        //ok


        // Create the Cart
        $_SESSION['aPeopleCart'] = [];

        // Create the variable for the Global Message
        $_SESSION['sGlobalMessage'] = '';

        // Initialize the last operation time
        $_SESSION['tLastOperation'] = time();

        $_SESSION['bHasMagicQuotes'] = 0;

        // Pledge and payment preferences
        $_SESSION['sshowPledges'] = $this->getShowPledges();
        $_SESSION['sshowPayments'] = $this->getShowPayments();

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
    }

    public function isEnableForPlugin($name) {
        if ( $this->isAdmin() ) {
            return true;
        }

        $plugin = PluginQuery::create()->findOneByName($name);

        if (is_null($plugin)) {
            return false;
        }

        $role = PluginUserRoleQuery::create()
            ->filterByUserId($this->getId())
            ->findOneByPluginId($plugin->getId());

        if (!is_null($role)) {
            return ( $role->getRole() == 'user' or $role->getRole() == 'admin' )?true:false;
        }

        return false;
    }

    public function isAdminEnableForPlugin($name) {
        if ( $this->isAdmin() ) {
            return true;
        }

        $plugin = PluginQuery::create()->findOneByName($name);

        if (is_null($plugin)) {
            return false;
        }

        $role = PluginUserRoleQuery::create()
            ->filterByUserId($this->getId())
            ->findOneByPluginId($plugin->getId());

        if (!is_null($role)) {
            return ($role->getRole() == 'admin')?true:false;
        }

        return false;
    }

    public function isSecurityEnableForPlugin ($name, $sec = 1073741824) {
        //$sec = SecurityOptions::bNone => 1073741824; by default

        if ( $this->isAdmin() ) {
            return true;
        }

        $plugin = PluginQuery::create()->findOneByName($name);

        if ($plugin->getSecurities() & $sec) {// when the bit sec is activated
            return true;
        }

        return false;
    }
}
