<?php

namespace EcclesiaCRM;

use EcclesiaCRM\calendarInstance;
use EcclesiaCRM\Base\Group as BaseGroup;
use EcclesiaCRM\UserQuery;
use Propel\Runtime\ActiveQuery\Criteria;


use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Sharing;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\VObject;
use EcclesiaCRM\MyVCalendar;
use Sabre\DAV\PropPatch;
use Sabre\DAVACL;

use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use Propel\Runtime\Propel;

/**
 * Skeleton subclass for representing a row from the 'group_grp' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Group extends BaseGroup
{
    protected $typeSundaySchool = 4;

    public function isSundaySchool()
    {
        return $this->getType() == $this->typeSundaySchool;
    }

    public function makeSundaySchool()
    {
        $this->setType($this->typeSundaySchool);
        
        // we fix first the role
        $defaultRole = 2;
        $newListID = ListOptionQuery::create()->withColumn('MAX(ListOption.Id)', 'newListId')->find()->getColumnValues('newListId')[0] + 1;
        $this->setRoleListId($newListID);
        $this->setDefaultRole($defaultRole);

        // then we add the role        
        $optionList = ['Teacher', 'Student'];

        $i = 1;
        foreach ($optionList as $option) {
            $listOption = new ListOption();
            $listOption->setId($this->getRoleListId());
            $listOption->setOptionId($i);
            $listOption->setOptionSequence($i);
            $listOption->setOptionName($option);
            $listOption->save();
            $i++;
        }
    }

    public function preSave(\Propel\Runtime\Connection\ConnectionInterface $con = null)
    {
        requireUserGroupMembership('bManageGroups');
        parent::preSave($con);

        return true;
    }

    public function preUpdate(\Propel\Runtime\Connection\ConnectionInterface $con = null)
    {
        requireUserGroupMembership('bManageGroups');
        parent::preUpdate($con);

        return true;
    }

    public function preDelete(\Propel\Runtime\Connection\ConnectionInterface $con = null)
    {
        requireUserGroupMembership('bManageGroups');
        
        // we first delete the calendar
        $calendarInstance = CalendarinstancesQuery::Create()->findOneByGroupId( $this->getId() );
        
        // we'll connect to sabre to create the group
        $pdo = Propel::getConnection();         
        
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
        
        $calendarBackend->deleteCalendar([$calendarInstance->getCalendarid(),$calendarInstance->getId()]);
        
        parent::preDelete($con);

        return true;
    }

    public function preInsert(\Propel\Runtime\Connection\ConnectionInterface $con = null)
    {
        requireUserGroupMembership('bManageGroups');
        $defaultRole = 1;
        if ($this->isSundaySchool()) {
            $defaultRole = 2;
        }
        $newListID = ListOptionQuery::create()->withColumn('MAX(ListOption.Id)', 'newListId')->find()->getColumnValues('newListId')[0] + 1;
        $this->setRoleListId($newListID);
        $this->setDefaultRole($defaultRole);
        parent::preInsert($con);

        return true;
    }

    public function postInsert(\Propel\Runtime\Connection\ConnectionInterface $con = null)
    {
        $optionList = ['Member'];
        if ($this->isSundaySchool()) {
            $optionList = ['Teacher', 'Student'];
        }

        $i = 1;
        foreach ($optionList as $option) {
            $listOption = new ListOption();
            $listOption->setId($this->getRoleListId());
            $listOption->setOptionId($i);
            $listOption->setOptionSequence($i);
            $listOption->setOptionName($option);
            $listOption->save();
            $i++;
        }

        parent::postInsert($con);
        
        // a group is binded to a calendar
        // we'll connect to sabre to create the group
        $pdo = Propel::getConnection();         
        
        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
          
        // we create the uuid name          
        $uuid = strtoupper( \Sabre\DAV\UUIDUtil::getUUID() );
          
        // get all the calendars for the current user
        // we have to add the groupCalendars
        $userAdmin = UserQuery::Create()->findOneByPersonId (1);

        // all the group calendars are binded to the principal admin
        $calendar = $calendarBackend->createCalendar('principals/'.strtolower($userAdmin->getUserName()), $uuid, [
            '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
            '{DAV:}displayname'                                               => $this->getName(),
            '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp'         => new CalDAV\Xml\Property\ScheduleCalendarTransp('transparent'),            
          ]);
          
        
        $calendarInstance = CalendarinstancesQuery::Create()->findOneByCalendarid($calendar[0]);
        $calendarInstance->setGroupId($this->getId());
        $calendarInstance->save();
        
        // we filter all the user who are admin or group manager and not the principal admin
        $users = UserQuery::Create()
            ->filterByManageGroups(true)
            ->_or()->filterByAdmin(true)
            ->filterByPersonId(1, CRITERIA::NOT_EQUAL)
            ->find();
        
        // now we can share the new calendar to the users
        foreach ($users as $user) {
          $calendarBackend->updateInvites(
            $calendar,
            [
              new Sharee([
                    'href'         => 'mailto:'.$user->getEmail(),
                    'principal'    => 'principals/'.strtolower( $user->getUserName() ),
                    'access'       => \Sabre\DAV\Sharing\Plugin::ACCESS_READWRITE,
                    'inviteStatus' => \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED,
                    'properties'   => ['{DAV:}displayname' => strtolower( $user->getFullName() )],
                  ])
            ]
          );
        }

        return true;
    }
    
    public function postSave(\Propel\Runtime\Connection\ConnectionInterface $con = null)
    {
        if (is_callable('parent::postSave')) {
            parent::postSave($con);

            // Now a group is binded to a calendar !!!
            $calendarInstance = CalendarinstancesQuery::Create()->findOneByGroupId( $this->getId() );
        
            // we'll connect to sabre to create the group
            $pdo = Propel::getConnection();         
        
            // We set the BackEnd for sabre Backends
            $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
        
            // Updating the calendar
            $propPatch = new PropPatch([
                '{DAV:}displayname'                                       => $this->getName()
              ]);
          
            $calendarBackend->updateCalendar([$calendarInstance->getCalendarid(),$calendarInstance->getId()], $propPatch);
         
            $result = $propPatch->commit();
        }
    }

    public function checkAgainstCart()
    {
        $groupMemberships = $this->getPerson2group2roleP2g2rsJoinPerson();
        $bNoneInCart = true;
        $bAllInCart = true;
    //Loop through the recordset
    foreach ($groupMemberships as $groupMembership) {
        if (!isset($_SESSION['aPeopleCart'])) {
            $bAllInCart = false;
        } // Cart does not exist.  This person is not in cart.
      elseif (!in_array($groupMembership->getPersonId(), $_SESSION['aPeopleCart'], false)) {
          $bAllInCart = false;
      } // This person is not in cart.
      elseif (in_array($groupMembership->getPersonId(), $_SESSION['aPeopleCart'], false)) {
          $bNoneInCart = false;
      } // This person is in the cart
    }

        if (!$bAllInCart) {
            //there is at least one person in this group who is not in the cart.  Return false
      return false;
        }
        if (!$bNoneInCart) {
            //every member of this group is in the cart.  Return true
      return true;
        }

        return false;
    }
}
