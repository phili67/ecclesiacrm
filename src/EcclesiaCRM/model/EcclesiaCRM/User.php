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
              rename(dirname(__FILE__)."/../../../"."private/userdir/".strtolower($oldUserName),dirname(__FILE__)."/../../../"."private/userdir/".strtolower($newUserName));
              $this->setHomedir("private/userdir/".strtolower($newUserName));
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
    
    public function createHomeDir()
    {
       try {
       
            mkdir(dirname(__FILE__)."/../../../"."private/userdir/".strtolower($this->getUserName()), 0755, true);
            $this->setHomedir("private/userdir/".strtolower($this->getUserName()));
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
              $this->deleteGroupAdminCalendars ();              
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
      MiscUtils::delTree(dirname(__FILE__)."/../../../"."private/userdir/".strtolower($this->getUserName()));
      
      $this->setHomedir(null);
      $this->save();
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

    public function isSundayShoolTeachForGroup($iGroupID)
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

    public function updatePassword($password)
    {
        $this->setPassword($this->hashPassword($password));
    }

    public function isPasswordValid($password)
    {
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
                $note->setType('file');
                $note->setInfo(gettext('Dav create directory'));
                break;                           
            case "dav-update-file":
                $note->setText(str_replace("home/","",$info));
                $note->setType('file');
                $note->setInfo(gettext('Dav update file'));
                break;
            case "dav-move-copy-file":
                $note->setText(str_replace("home/","",$info));
                $note->setType('file');
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
