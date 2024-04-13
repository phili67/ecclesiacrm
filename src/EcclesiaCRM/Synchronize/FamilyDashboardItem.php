<?php

namespace EcclesiaCRM\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\FamilyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Service\PastoralCareService;

class FamilyDashboardItem implements DashboardItemInterface {

  public static function getDashboardItemName() {
    return "FamilyCount";
  }

  public static function getDashboardItemValue() {

    $data = array('familyCount' => self::getCountFamilies(),
        'LatestFamilies' => self::getLatestFamilies(),
        'UpdatedFamilies' => self::getUpdatedFamilies()
        );

    return $data;
  }

  private static function getCountFamilies()
  {
      $pcS = new PastoralCareService();

      $allCNT = $pcS->getAllFamiliesAndSingle()->count();

      $allSingleCNT = $pcS->getAllSingle()->count();

      $allRealFamilyleCNT =  $pcS->getAllRealFamilies()->count();

      return [$allCNT, $allRealFamilyleCNT, $allSingleCNT];
  }

  /**
   * //Return last edited families. only active families selected
   * @param int $limit
   * @return array|\EcclesiaCRM\Family[]|mixed|\Propel\Runtime\ActiveRecord\ActiveRecordInterface[]|\Propel\Runtime\Collection\ObjectCollection
   */
  private static function getUpdatedFamilies($limit = 6) : array {
    $families = FamilyQuery::create()
                    ->filterByDateDeactivated(null)
                    ->filterByDateLastEdited(null, Criteria::NOT_EQUAL)
                    ->orderByDateLastEdited('DESC')
                    ->limit($limit)
                    ->find();

    $res = [];
    foreach ($families as $family) {
        $res[] = [
          'Id' => $family->getId(),                
          'Name' => $family->getName(),
          'Address1' => $family->getAddress(),
          'DateEntered' => (!is_null($family->getDateEntered())?$family->getDateEntered()->format('Y-m-d h:i'):''),
          'DateLastEdited' => (!is_null($family->getDateLastEdited())?$family->getDateLastEdited()->format('Y-m-d h:i'):''),
          'img' => $family->getJPGPhotoDatas()
      ];
    }

    return $res;
  }

  /**
   * Return newly added families. Only active families selected
   * @param int $limit
   * @return array|\EcclesiaCRM\Family[]|mixed|\Propel\Runtime\ActiveRecord\ActiveRecordInterface[]|\Propel\Runtime\Collection\ObjectCollection
   */
  private static function getLatestFamilies($limit = 6) : array {

    $families = FamilyQuery::create()
                    ->filterByDateDeactivated(null)
                    ->orderByDateEntered('DESC')
                    ->limit($limit)
                    ->find();


    $res = [];
    foreach ($families as $family) {
        $res[] = [
          'Id' => $family->getId(),                
          'Name' => $family->getName(),
          'Address1' => $family->getAddress(),
          'DateEntered' => (!is_null($family->getDateEntered())?$family->getDateEntered()->format('Y-m-d h:i'):''),
          'DateLastEdited' => (!is_null($family->getDateLastEdited())?$family->getDateLastEdited()->format('Y-m-d h:i'):''),
          'img' => $family->getJPGPhotoDatas()
      ];
    }

    return $res;
  }

  public static function shouldInclude($PageName) {
    return $PageName == "/v2/dashboard" || $PageName == "/menu"|| $PageName == "/v2/people/dashboard"; // this ID would be found on all pages.
  }

}