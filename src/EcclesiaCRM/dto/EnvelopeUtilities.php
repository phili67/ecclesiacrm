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
use EcclesiaCRM\PersonQuery;

class EnvelopeUtilities
{

  public static function EnvelopeAssignAllFamilies($member_type)
  {
      $persons = PersonQuery::Create();
      if ($member_type) {
          $persons->filterByClsId ($member_type);
      }
      
      $persons->orderByLastName()->find();
      
      $ind = 0;
      $famArr = [];
      foreach ($persons as $person) {
        $famArr[$ind++] = $person->getFamId();
      }
      
      $famUnique = array_unique($famArr);
      
      $maxFamily = FamilyQuery::Create()->withColumn('MAX(fam_Envelope)','Max')->findOne();
      
      $envelopeNo = $maxFamily->getMax()+1;
      foreach ($famUnique as $oneFam) {
          $family = FamilyQuery::Create()->findOneById ($oneFam);
          if (!is_null($family) && $family->getEnvelope() == 0) {
             $family->setEnvelope($envelopeNo++);
             $family->save();
          }
      }
      if ($member_type) {
          return _('Assigned envelope numbers to all families with at least one member.');
      } else {
          return _('Assigned envelope numbers to all families.');
      }
  }

  // make an array of envelopes indexed by family id, subject to the classification filter if specified.
  public static function getEnvelopes ($classification) 
  {
    if ($classification) {
          $families = FamilyQuery::Create()->usePersonQuery()->filterByDateDeactivated(null)->filterByClsId ($classification)->endUse();// GDPR
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

