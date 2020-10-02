<?php

/*******************************************************************************
 *
 *  filename    : /EcclesiaCRM/dto/CanvassUtilities.php
 *  last change : 2005-02-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2005 Michael Wilt
 *                Copyright 2019 Philippe Logel
 *
 ******************************************************************************/

namespace EcclesiaCRM\dto;

use EcclesiaCRM\PledgeQuery;

use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\FamilyQuery;

use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\PersonTableMap;

use Propel\Runtime\ActiveQuery\Criteria;

class CanvassUtilities
{

    public function CanvassSetDefaultFY($iFYID)
    {
        $users = UserQuery::create()->find();

        foreach ($users as $user) {
            $user->setDefaultFY($iFYID);
            $user->save();
        }
    }

    public function CanvassSetAllOkToCanvass()
    {
        $families = FamilyQuery::create()->find();

        foreach ($families as $family) {
            $family->setOkToCanvass('TRUE');
            $family->save();
        }
    }

    public function CanvassClearAllOkToCanvass()
    {
        $families = FamilyQuery::create()->find();

        foreach ($families as $family) {
            $family->setOkToCanvass('FALSE');
            $family->save();
        }
    }

    public function CanvassClearCanvasserAssignments()
    {
        $families = FamilyQuery::create()->find();

        foreach ($families as $family) {
            $family->setCanvasser(0);
            $family->save();
        }
    }

    public static function CanvassGetCanvassers($groupName)
    {
        // Find the canvassers group
        $group = GroupQuery::create()->findOneByName($groupName);

        if ( is_null ($group) ) {
            return null;
        }

        $persons = PersonQuery::create()
                    ->usePerson2group2roleP2g2rQuery()
                        ->filterByGroupId($group->getId())
                    ->endUse()
                    ->orderByLastName()
                    ->orderByFirstName()
                    ->find();


        if ( $persons->count() == 0 ) {
            return null;
        }

        return $persons;
    }

    public function CanvassAssignCanvassers($groupName)
    {
        $ormCanvassers = CanvassUtilities::CanvassGetCanvassers($groupName);

        if ( is_null($ormCanvassers) ) {
            return  _("Group Name") . " : " . _($groupName) . " " . _("doesn't exist !");
        }

        $canvassers = $ormCanvassers->toArray();
        $numCanvassers = count($canvassers);

        $families = FamilyQuery::create()
            ->filterByOkToCanvass('TRUE')
            ->filterByCanvasser(0)
            ->addAscendingOrderByColumn('rand()')
            ->find();

        $numFamilies = $families->count();

        if ($numFamilies == 0) {
            return _('No families need canvassers assigned');
        }

        $i=0;
        foreach ($families as $family) {
          $canvasser_per_ID = $canvassers[($i++)%$numCanvassers]['Id'];

          $family->setCanvasser($canvasser_per_ID);
          $family->save();
        }

        $ret = sprintf(_('Canvassers assigned at random to %d families.'), $numFamilies);

        return $ret;
    }

    public function CanvassAssignNonPledging($groupName, $iFYID)
    {
        $ormCanvassers = CanvassUtilities::CanvassGetCanvassers($groupName);

        if ( is_null($ormCanvassers) ) {
            return  _("Group Name") . " : \"" . _($groupName) . "\" " . _("doesn't exist !");
        }

        $canvassers = $ormCanvassers->toArray();
        $numCanvassers = count($canvassers);

        // Get all the families which need canvassing
        $families = FamilyQuery::create()
            ->addJoin(FamilyTableMap::COL_FAM_CANVASSER,PersonTableMap::COL_PER_ID,Criteria::LEFT_JOIN)
            ->filterByOkToCanvass('TRUE')
            ->addAscendingOrderByColumn('rand()')
            ->find();

        $numFamilies = 0;
        $i=0;
        foreach ($families as $family) {
            $pledges = PledgeQuery::create()
                ->filterByFyid($iFYID)
                ->filterByPledgeorpayment("Pledge")
                ->filterByFamId($family->getId())
                ->orderByAmount('desc')
                ->find();

            $pledgeCount = $pledges->count();

            if ($pledgeCount == 0) {
                ++$numFamilies;

                $canvasser_per_ID = $canvassers[($i++)%$numCanvassers]['Id'];

                $family->setCanvasser($canvasser_per_ID);
                $family->save();
            }
        }

        $ret = sprintf(_('Canvassers assigned at random to %d non-pledging families.'), $numFamilies);

        return $ret;
    }
}
