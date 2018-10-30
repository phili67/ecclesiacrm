<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\User as BaseUser;
use EcclesiaCRM\dto\SystemConfig;
use Propel\Runtime\Connection\ConnectionInterface;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\PrincipalsQuery;
use EcclesiaCRM\Principals;
use EcclesiaCRM\UserRoleQuery;
use EcclesiaCRM\UserConfigQuery;
use Propel\Runtime\ActiveQuery\Criteria;

use Sabre\DAV\Sharing;
use Sabre\DAV\Xml\Element\Sharee;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use EcclesiaCRM\MyPDO\CalDavPDO;
use Propel\Runtime\Propel;

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
          $pdo = Propel::getConnection();                 
          $principalBackend = new PrincipalPDO($pdo->getWrappedConnection());
          
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
        $pdo = Propel::getConnection();                 
        $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
        
        $calendars = $calendarBackend->getCalendarsForUser('principals/'.strtolower($userAdmin->getUserName()),"displayname",true);
                
        foreach ($calendars as $calendar) {
          $shares = $calendarBackend->getInvites($calendar['id']); 
          
          if ($calendar['grpid'] > 0) {// only Group Calendar are purged
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
        $pdo = Propel::getConnection();                 
        $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
        
        if ( $this->isManageGroupsEnabled() && $userAdmin->getPersonID() != $this->getPersonID()) {// an admin can't change itself and is ever tge main group manager
          // we have to add the groupCalendars
          $userAdmin = UserQuery::Create()->findOneByPersonId (1);
          
          $calendars = $calendarBackend->getCalendarsForUser('principals/'.strtolower($userAdmin->getUserName()),"displayname",true);
          
          foreach ($calendars as $calendar) {
            // we'll connect to sabre
            // Add a new invite
            if ($calendar['grpid'] > 0) {
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
      
    public function renameHomeDir($oldUserName,$newUserName)
    {
      if ($oldUserName != $newUserName) {
         try {
              rename(dirname(__FILE__)."/../../../".$this->getUserDir(strtolower($oldUserName)),dirname(__FILE__)."/../../../".$this->getUserDir(strtolower($newUserName)));
              $this->setHomedir($this->getUserDir());
              $this->save();
            
              // transfert the calendars to a user
              // now we code now in Sabre        
              $pdo = Propel::getConnection();                 
              $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
              $principalBackend = new PrincipalPDO($pdo->getWrappedConnection());
              
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
            $pdo = Propel::getConnection();                 
            $principalBackend = new PrincipalPDO($pdo->getWrappedConnection());
            
            $res = $principalBackend->getPrincipalByPath ("principals/".strtolower( $this->getUserName() ));
            $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
            
            if (empty($res)) {            
              $principalBackend->createNewPrincipal("principals/".strtolower( $this->getUserName() ), $this->getEmail(),strtolower($this->getUserName()));
            }
            
            if ($this->isManageGroupsEnabled()) {
              $this->createGroupAdminCalendars ();              
            }
            
       } catch (Exception $e) {
            throw new PropelException('Unable to create home dir for user'.strtolower($this->getUserName()).'.', 0, $e);
       }       
    }

    public function deleteHomeDir()
    {
      // we code first in Sabre
      $pdo = Propel::getConnection();
      $principalBackend = new PrincipalPDO($pdo->getWrappedConnection());
            
      $res = $principalBackend->deletePrincipal ("principals/".strtolower( $this->getUserName() ));

      // we code now in propel      
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
            case 'MenuOptions':
               $this->setMenuOptions($res[1]);
               break;
            case 'ManageGroups':
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
            case 'QueryMenu':
               $this->setShowMenuQuery($res[1]);
               break;
            case 'PastoralCare':
               $this->setShowMenuQuery($res[1]);
               break;
            case 'Style':
               $this->setStyle($res[1]);
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
            $user_cfg->setTooltip($global_cfg->getType());
          }
          
          $user_cfg->setPermission($permission[1]);
          
          if ($value[1] == 'semi_colon'){
            $user_cfg->setValue(';');
          } else {
            $user_cfg->setValue($value[1]);
          }
          
          $user_cfg->save();
        }
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
        if (!SystemConfig::getBooleanValue('bEnabledEmail'))
          return false;

        return $this->isAdmin() || $this->isMailChimp();
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
      if ($this->getPerson()->getDateDeactivated() != null) {
        return false;
      }
        
      return $this->getPassword() == $this->hashPassword($password);
    }

    public function hashPassword($password)
    {
        return hash('sha256', $password . $this->getPersonId());
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
        $note->setEntered((is_null($info))?$_SESSION['user']->getPersonId():$this->getPersonId());
        $note->setType('user');

        switch ($type) {
            case "created":
                $note->setText(gettext('system user created'));
                break;
            case "updated":
                $note->setText(gettext('system user updated'));
                break;
            case "deleted":
                $note->setText(gettext('system user deleted'));
                break;
            case "password-reset":
                $note->setText(gettext('system user password reset'));
                break;
            case "password-changed":
                $note->setText(gettext('system user changed password'));
                break;
            case "password-changed-admin":
                $note->setText(gettext('system user password changed by admin'));
                break;
            case "login-reset":
                $note->setText(gettext('system user login reset'));
                break;
            case "dav-create-file":
                $note->setText(str_replace("home/","",$info));
                $note->setType('file');
                $note->setInfo(gettext('Dav create file'));
                break;    
            case "dav-create-directory":
                $note->setText(str_replace("home/","",$info));
                $note->setType('folder');
                $note->setInfo(gettext('Dav create directory'));
                break;                           
            case "dav-update-file":
                $note->setText(str_replace("home/","",$info));
                $note->setType('file');
                $note->setInfo(gettext('Dav update file'));
                break;
            case "dav-move-copy-file":
                $note->setText(str_replace("home/","",$info));

                $path = dirname(__FILE__).'/../../../'.$this->getUserRootDir().str_replace("home/","",$info);
                
                if (!pathinfo($path, PATHINFO_EXTENSION)) {// we are with a directory
                  $note->setType('folder');
                } else {
                  $note->setType('file');
                }
                $note->setInfo(gettext('Dav move copy file'));

                break;            
            case "dav-delete-file":
                $note->setText(str_replace("home/","",$info));
                $note->setType('file');
                $note->setInfo(gettext('Dav delete file'));
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
      return "private/userdir/".$this->getWebDavKeyUUID();
    }
    
    public function getWebDavKeyUUID()
    {
      if ($this->getWebdavkey() == null) {
        $this->createWebDavUUID();
        
        // the new destination
        $new_dir = "private/userdir/".$this->getWebdavkey()."/".strtolower($this->getUserName());
        
        // in this case we have to create the create the folder
        mkdir(dirname(__FILE__)."/../../../".$new_dir, 0755, true);
        $this->setHomedir($new_dir);
        $this->save();
        
        // then we move the files
        if (file_exists(dirname(__FILE__)."/../../../".$old_dir) && is_dir(dirname(__FILE__)."/../../../".$old_dir)) { 
          $old_dir = "private/userdir/".strtolower($this->getUserName());
          
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