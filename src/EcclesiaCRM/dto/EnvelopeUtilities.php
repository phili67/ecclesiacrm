<?php
/*******************************************************************************
 *
 *  filename    : /Include/EnvelopeUtilities.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2006 Michael Wilt
  *
 ******************************************************************************/

namespace EcclesiaCRM\dto;

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\ListOptionQuery;

class EnvelopeUtilities
{

  // Figure out the class ID for "Member", should be one (1) unless they have been playing with the
  // classification manager.
  public static function FindMemberClassID()// in public static function.php at the beginning
  {
      $lists = ListOptionQuery::Create()->orderByOptionSequence()->findById (1);
    
      foreach ($lists as $list) {
        if ($list->getOptionName() == _('Member')) {
          return $lst_OptionID;
        }
      }
    
      return 1;
  }

  public static function EnvelopeAssignAllFamilies($bMembersOnly)
  {
      $sSQL = 'SELECT per_fam_ID, per_LastName FROM person_per';
      if ($bMembersOnly) {
          $sSQL .= ' WHERE per_cls_ID='.EnvelopeUtilities::FindMemberClassID();
      }
      $sSQL .= ' ORDER BY per_LastName';
      $rsPeople = RunQuery($sSQL);

      $ind = 0;
      $famArr = [];
      while ($aRow = mysqli_fetch_array($rsPeople)) {
          extract($aRow);
          $famArr[$ind++] = $per_fam_ID;
      }

      $famUnique = array_unique($famArr);

      $envelopeNo = 1;
      foreach ($famUnique as $oneFam) {
          $sSQL = "UPDATE family_fam SET fam_Envelope='".$envelopeNo++."' WHERE fam_ID='".$oneFam."';";
          RunQuery($sSQL);
      }
      if ($bMembersOnly) {
          return gettext('Assigned envelope numbers to all families with at least one member.');
      } else {
          return gettext('Assigned envelope numbers to all families.');
      }
  }

  // make an array of envelopes indexed by family id, subject to the classification filter if specified.
  public static function getEnvelopes ($classification) 
  {
    if ($classification) {
          $families = FamilyQuery::Create()->usePerson()->filterByClsId ($classification)->endUse();
    } else {
          $families = FamilyQuery::Create();
    }
  
    $families->orderByEnvelope()->find();
    $envelopes = [];
    foreach ($families as $family) {
      $envelopes[$family->getId()] = $family->getEnvelope();
    }
  
    return $envelopes;
  }
}

