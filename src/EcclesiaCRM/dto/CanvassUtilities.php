<?php

/*******************************************************************************
 *
 *  filename    : /EcclesiaCRM/dto/CanvassUtilities.php
 *  last change : 2005-02-21
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2005 Michael Wilt
 *
 ******************************************************************************/

namespace EcclesiaCRM\dto;

use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use Propel\Runtime\Propel;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\FamilyQuery;

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
            $family->save();
            $family->setCanvasser(0);
        }
    }

    public function CanvassGetCanvassers($groupName)
    {
        // Find the canvassers group
        $group = GroupQuery::create()->findOneByName($groupName);

        if ( is_null ($group) ) {
            return 0;
        }

        $persons = PersonQuery::create()
                    ->usePerson2group2roleP2g2rQuery()
                        ->filterByGroupId($group->getId())
                    ->endUse()
                    ->orderByLastName()
                    ->orderByFirstName()
                    ->find();


        if ( $persons->count() == 0 ) {
            return 0;
        }

        return $persons;
    }

    public function CanvassAssignCanvassers($groupName)
    {
        $rsCanvassers = CanvassUtilities::CanvassGetCanvassers($groupName);

        // Get all the families that need canvassers
        $sSQL = "SELECT fam_ID FROM family_fam WHERE fam_OkToCanvass='TRUE' AND fam_Canvasser=0 ORDER BY RAND();";
        $rsFamilies = RunQuery($sSQL);
        $numFamilies = mysqli_num_rows($rsFamilies);
        if ($numFamilies == 0) {
            return gettext('No families need canvassers assigned');
        }

        while ($aFamily = mysqli_fetch_array($rsFamilies)) {
            if (!($aCanvasser = mysqli_fetch_array($rsCanvassers))) {
                mysqli_data_seek($rsCanvassers, 0);
                $aCanvasser = mysqli_fetch_array($rsCanvassers);
            }
            $sSQL = 'UPDATE family_fam SET fam_Canvasser='.$aCanvasser['per_ID'].' WHERE fam_ID= '.$aFamily['fam_ID'];
            RunQuery($sSQL);
        }

        $ret = sprintf(gettext('Canvassers assigned at random to %d families.'), $numFamilies);

        return $ret;
    }

    public function CanvassAssignNonPledging($groupName, $iFYID)
    {
        $rsCanvassers = CanvassUtilities::CanvassGetCanvassers($groupName);

        // Get all the families which need canvassing
        $sSQL = 'SELECT *, a.per_FirstName AS CanvasserFirstName, a.per_LastName AS CanvasserLastName FROM family_fam
               LEFT JOIN person_per a ON fam_Canvasser = a.per_ID
           WHERE fam_OkToCanvass="TRUE" ORDER BY RAND()';
        $rsFamilies = RunQuery($sSQL);

        $numFamilies = 0;

        while ($aFamily = mysqli_fetch_array($rsFamilies)) {
            // Get pledges for this fiscal year, this family
            $sSQL = 'SELECT plg_Amount FROM pledge_plg
             WHERE plg_FYID = '.$iFYID.' AND plg_PledgeOrPayment="Pledge" AND plg_FamID = '.$aFamily['fam_ID'].' ORDER BY plg_Amount DESC';
            $rsPledges = RunQuery($sSQL);

            $pledgeCount = mysqli_num_rows($rsPledges);
            if ($pledgeCount == 0) {
                ++$numFamilies;
                if (!($aCanvasser = mysqli_fetch_array($rsCanvassers))) {
                    mysqli_data_seek($rsCanvassers, 0);
                    $aCanvasser = mysqli_fetch_array($rsCanvassers);
                }
                $sSQL = 'UPDATE family_fam SET fam_Canvasser='.$aCanvasser['per_ID'].' WHERE fam_ID= '.$aFamily['fam_ID'];
                RunQuery($sSQL);
            }
        }
        $ret = sprintf(gettext('Canvassers assigned at random to %d non-pledging families.'), $numFamilies);

        return $ret;
    }
}
