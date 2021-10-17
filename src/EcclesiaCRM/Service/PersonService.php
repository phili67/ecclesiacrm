<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\map\Person2group2roleP2g2rTableMap;
use EcclesiaCRM\map\PersonTableMap;
use EcclesiaCRM\map\GroupTableMap;
use EcclesiaCRM\map\ListOptionTableMap;

use Propel\Runtime\Propel;


class PersonService
{
    public function search($searchTerm, $includeFamilyRole = true)
    {
        $searchTerm = filter_var($searchTerm, FILTER_SANITIZE_STRING);

        $searchLikeString = '%' . $searchTerm . '%';

        $people = PersonQuery::create()->
        filterByFirstName($searchLikeString, Criteria::LIKE)->
        _or()->filterByLastName($searchLikeString, Criteria::LIKE)->
        _or()->filterByEmail($searchLikeString, Criteria::LIKE)->
        limit(15)->find();
        $return = [];
        foreach ($people as $person) {
            $values['id'] = $person->getId();
            $values['familyID'] = $person->getFamId();
            $values['firstName'] = $person->getFirstName();
            $values['lastName'] = $person->getLastName();
            $values['displayName'] = $person->getFullName();
            $values['uri'] = $person->getViewURI();
            $values['thumbnailURI'] = $person->getThumbnailURI();
            $values['title'] = $person->getTitle();
            $values['address'] = $person->getAddress();
            $values['role'] = $person->getFamilyRoleName();

            if ($includeFamilyRole) {
                $familyRole = "(";
                if ($values['familyID']) {
                    if ($person->getFamilyRole()) {
                        $familyRole .= $person->getFamilyRoleName();
                    } else {
                        $familyRole .= gettext('Part');
                    }
                    $familyRole .= gettext(' of the') . ' <a href="FamilyView.php?FamilyID=' . $values['familyID'] . '">' . $person->getFamily()->getName() . '</a> ' . gettext('family') . ' )';
                } else {
                    $familyRole = gettext('(No assigned family)');
                }
                $values['familyRole'] = $familyRole;
            }
            array_push($return, $values);
        }

        return $return;
    }

    public function getPeopleEmailsAndGroups()
    {
        $persons = PersonQuery::Create()
            ->addJoin(PersonTableMap::COL_PER_ID, Person2group2roleP2g2rTableMap::COL_P2G2R_PER_ID, Criteria::LEFT_JOIN)
            ->addJoin(Person2group2roleP2g2rTableMap::COL_P2G2R_GRP_ID, GroupTableMap::COL_GRP_ID, Criteria::LEFT_JOIN)
            ->addMultipleJoin(array(array(GroupTableMap::COL_GRP_ROLELISTID, ListOptionTableMap::COL_LST_ID),
                array(Person2group2roleP2g2rTableMap::COL_P2G2R_RLE_ID, ListOptionTableMap::COL_LST_OPTIONID)),
                Criteria::LEFT_JOIN)
            ->addAsColumn("GroupName", GroupTableMap::COL_GRP_NAME)
            ->addAsColumn("OptionName", ListOptionTableMap::COL_LST_OPTIONNAME)
            ->filterByEmail('', Criteria::NOT_EQUAL)
            ->_and()->filterByDateDeactivated(null)
            ->orderById()
            ->find();

        $people = [];
        $lastPersonId = 0;
        $per = [];
        foreach ($persons as $person) {
            if ($lastPersonId != $person->getId()) {
                if ($lastPersonId != 0) {
                    $people[] = $per;
                }
                $per = [];
                $per['id'] = $person->getId();
                $per['email'] = $person->getEmail();
                $per['firstName'] = $person->getFirstName();
                $per['lastName'] = $person->getLastName();
            }

            if (!is_null($person->getGroupName()) && !is_null($person->getOptionName())) {
                $per[$person->getGroupName()] = _($person->getOptionName());
            }

            if ($lastPersonId != $person->getId()) {
                $lastPersonId = $person->getId();
            }
        }

        $people[] = $per;

        return $people;
    }
}
