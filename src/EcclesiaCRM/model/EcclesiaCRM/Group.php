<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Group as BaseGroup;
use EcclesiaCRM\Person2group2roleP2g2r as ChildPerson2group2roleP2g2r;
use EcclesiaCRM\Utils\MiscUtils;

use Propel\Runtime\ActiveQuery\Criteria;

use Sabre;
use Sabre\CalDAV;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\DAV\PropPatch;

use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\CardDavPDO;

use EcclesiaCRM\CardDav\VcardUtils;



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
    protected $typeSundaySchool = 4;// historically a sunday group is of type of 4
    protected $typeNormal = 3;// the other types are normally the type of the group : list_lst ->  lst_OptionID

    // but, now erverything is in the table : group_type -> grptp_lst_OptionID

    public function isSundaySchool()
    {
        return $this->getType() == $this->typeSundaySchool;
    }

    public function addPerson2group2roleP2g2r(ChildPerson2group2roleP2g2r $l)
    {
        // We set the BackEnd for sabre Backends
        $carddavBackend = new CardDavPDO();

        $groupId = $l->getGroupId();
        $personId = $l->getPersonId();
        $person = $l->getPerson();

        $addressbook = $carddavBackend->getAddressBookForGroup($groupId);

        if ($addressbook['id'] != 0 && !$carddavBackend->getCardForPerson($addressbook['id'], $personId)) {
            // we've checked that we'll insert only one card per user
            $vcard = VcardUtils::Person2Vcard($person);

            $card = $vcard->serialize();            

            $carddavBackend->createCard($addressbook['id'], 'UUID-' . \Sabre\DAV\UUIDUtil::getUUID(), $card, $person->getId());
        }

        return parent::addPerson2group2roleP2g2r($l);
    }

    public function preSave(\Propel\Runtime\Connection\ConnectionInterface $con = null): bool
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');        

        return parent::preSave($con);;
    }

    public function preUpdate(\Propel\Runtime\Connection\ConnectionInterface $con = null): bool
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');        

        return parent::preUpdate($con);;
    }

    public function preDelete(\Propel\Runtime\Connection\ConnectionInterface $con = null): bool
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');

        // we first delete the calendar
        $calendarInstances = CalendarinstancesQuery::Create()->findByGroupId($this->getId());

        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();
        $carddavBackend = new CardDavPDO();

        // we delete the calendar
        foreach ($calendarInstances as $calendarInstance) {
            $calendarBackend->deleteCalendar([$calendarInstance->getCalendarid(), $calendarInstance->getId()]);
        }

        // we delete the address book
        $addressbook = $carddavBackend->getAddressBookForGroup($this->getId());
        $carddavBackend->deleteAddressBook($addressbook['id']);
        // this will delete the addressbookshare with constraint ON DELETE CASCADE

        // we delete the associated listOptions
        $lists = ListOptionQuery::create()->findById($this->getRoleListId());

        if (!is_null($lists)) {
            $lists->delete();
        }

        $persons = Person2group2roleP2g2rQuery::create()->findByGroupId($this->getId());
        if (!is_null($persons)) {
            $persons->delete();
        }        

        return parent::preDelete($con);;
    }

    public function preInsert(\Propel\Runtime\Connection\ConnectionInterface $con = null): bool
    {
        MiscUtils::requireUserGroupMembership('bManageGroups');
        $defaultRole = 1;
        if ($this->isSundaySchool()) {
            $defaultRole = 2;
        }
        $newListID = ListOptionQuery::create()->withColumn('MAX(ListOption.Id)', 'newListId')->find()->getColumnValues('newListId')[0] + 1;

        do { // we loop to find a good listID to avoid a bug when a list is empty : not present in list_lst
            $group = GroupQuery::create()->findOneByRoleListId($newListID);

            if (is_null($group)) {
                break;
            }

            $newListID++;
        } while (1);


        $this->setRoleListId($newListID);
        $this->setDefaultRole($defaultRole);        

        return parent::preInsert($con);;
    }

    public function postInsert(\Propel\Runtime\Connection\ConnectionInterface $con = null): void
    {
        $optionList = [_('Member')];
        if ($this->isSundaySchool()) {
            $optionList = ['Teacher', 'Student'];// this roles are specifics to the CRM and can't be endered
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

        // We set the BackEnd for sabre Backends
        $calendarBackend = new CalDavPDO();

        // we create the uuid name
        $uuid = strtoupper(\Sabre\DAV\UUIDUtil::getUUID());

        // get all the calendars for the current user
        // we have to add the groupCalendars
        $userAdmin = UserQuery::Create()->findOneByPersonId(1);

        // all the group calendars are binded to the principal admin
        $calendar = $calendarBackend->createCalendar(
            'principals/' . strtolower($userAdmin->getUserName()),
            $uuid,
            [
                '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
                '{DAV:}displayname' => $this->getName(),
                '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp('transparent'),
            ],
            1,
            '',
            $this->getId()
        );

        // we add the addressbook
        $carddavBackend = new CardDavPDO();

        $uuid = strtoupper(\Sabre\DAV\UUIDUtil::getUUID());

        $addresbookid = $carddavBackend->createAddressBook(
            'principals/' . strtolower($userAdmin->getUserName()),
            $uuid,
            [
                '{DAV:}displayname' => $this->getName(),
                '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => $this->getDescription()
            ],
            $this->getId()
        );


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
                        'href' => 'mailto:' . $user->getEmail(),
                        'principal' => 'principals/' . strtolower($user->getUserName()),
                        'access' => \Sabre\DAV\Sharing\Plugin::ACCESS_READWRITE,
                        'inviteStatus' => \Sabre\DAV\Sharing\Plugin::INVITE_ACCEPTED,
                        'properties' => ['{DAV:}displayname' => strtolower($user->getUserName())],
                    ])
                ]
            );

            $carddavBackend->createAddressBookShare(
                'principals/'.$user->getUserName(),
                [
                    'addressbookid'=> $addresbookid, // require
                    '{DAV:}displayname'  => $this->getName(),
                    '{' . \Sabre\CardDAV\Plugin::NS_CARDDAV . '}addressbook-description'  => $this->getDescription(),
                    'href'         => 0,
                    'user_id'      => $user->getId(), // require
                    'access'       => 3 // '1 = owner, 2 = read, 3 = readwrite',                    
                ]
            );
        }
    }

    public function postSave(\Propel\Runtime\Connection\ConnectionInterface $con = null): void
    {
        if (is_callable('parent::postSave')) {
            parent::postSave($con);
        }
    }

    public function checkAgainstCart()
    {
        $groupMemberships = Person2group2roleP2g2rQuery::create()
            ->usePersonQuery()
            ->filterByDateDeactivated(null)// GDRP, when a person is completely deactivated
            ->endUse()
            ->filterByGroupId($this->getId())
            ->find();

        $bNoneInCart = true;
        $bAllInCart = true;
        //Loop through the recordset
        foreach ($groupMemberships as $groupMembership) {
            if ( !is_null($groupMembership->getPerson()->getDateDeactivated()) ) {
                continue;
            }
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

    public function setName($name)
    {
        // we first delete the calendar
        $calendarInstance = CalendarinstancesQuery::Create()->findOneByGroupId($this->getId());

        if (!is_null($calendarInstance) ) {

            // We set the BackEnd for sabre Backends
            $calendarInstances = CalendarinstancesQuery::Create()->findByGroupId($this->getId());

            $calendarBackend = new CalDavPDO();
            $carddavBackend = new CardDavPDO();

            foreach ($calendarInstances as $calendarInstance) {
                // Updating the calendar
                $propPatch = new PropPatch([
                    '{DAV:}displayname' => $name
                ]);

                $calendarBackend->updateCalendar([$calendarInstance->getCalendarid(), $calendarInstance->getId()], $propPatch);

                $result = $propPatch->commit();
            }

            $addressbooks = AddressbooksQuery::create()->findByGroupid($this->getId());

            foreach($addressbooks as $addressbook) {
                $addressBookId = $addressbook->getId();

                $propPatch = new PropPatch([
                    '{DAV:}displayname' => $name,
                    '{'.\Sabre\CardDAV\Plugin::NS_CARDDAV.'}addressbook-description' => $this->getDescription()
                ]);

                $ret = $carddavBackend->updateAddressBook(
                    $addressBookId,$propPatch
                );

                $result = $propPatch->commit();

                $addressbookShares = AddressbookshareQuery::create()->findByAddressbookid($addressBookId);

                foreach ($addressbookShares as $addressbookShare) {
                    $propPatch = new PropPatch([
                        '{DAV:}displayname' => $name,
                        '{'.\Sabre\CardDAV\Plugin::NS_CARDDAV.'}addressbook-description' => $this->getDescription()
                    ]);
                    $ret = $carddavBackend->updateAddressBookShare(
                        $addressbookShare->getId(),$propPatch
                    );  
                    $result = $propPatch->commit();
                }
            }
        }

        parent::setName($name);
    }
}
