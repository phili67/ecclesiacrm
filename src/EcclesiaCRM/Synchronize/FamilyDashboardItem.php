<?php

namespace EcclesiaCRM\Synchronize;

use EcclesiaCRM\Synchronize\DashboardItemInterface;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\SessionUser;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\FamilyTableMap;
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
  private static function getUpdatedFamilies($limit = 12) {
    $families = FamilyQuery::create()
                    ->filterByDateDeactivated(null)
                    ->filterByDateLastEdited(null, Criteria::NOT_EQUAL)
                    ->orderByDateLastEdited('DESC')
                    ->limit($limit)
                    ->select(array("Id","Name","Address1","DateEntered","DateLastEdited"))
                    ->find()->toArray();

    if (!SessionUser::getUser()->isSeePrivacyDataEnabled()) {
      $res = [];

      foreach ($families as $family) {
          $family["Address1"] = gettext("Private Data");
          $res[] = $family;
      }

      return $res;
    }

    return $families;
  }

  /**
   * Return newly added families. Only active families selected
   * @param int $limit
   * @return array|\EcclesiaCRM\Family[]|mixed|\Propel\Runtime\ActiveRecord\ActiveRecordInterface[]|\Propel\Runtime\Collection\ObjectCollection
   */
  private static function getLatestFamilies($limit = 12) {

    $families = FamilyQuery::create()
                    ->filterByDateDeactivated(null)
                    ->orderByDateEntered('DESC')
                    ->limit($limit)
                    ->select(array("Id","Name","Address1","DateEntered","DateLastEdited"))
                    ->find()->toArray();


    if (!SessionUser::getUser()->isSeePrivacyDataEnabled()) {
      $res = [];

      foreach ($families as $family) {
          $family["Address1"] = gettext("Private Data");
          $res[] = $family;
      }

      return $res;
    }

    return $families;
  }

  public static function shouldInclude($PageName) {
    return $PageName == "/Menu.php" || $PageName == "/menu" || $PageName == "/v2/people/dashboard"; // this ID would be found on all pages.
  }

}
