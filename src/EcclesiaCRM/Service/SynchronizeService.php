<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\PluginDependenciesQuery;

class SynchronizeService
{
  private static $resolvedDashboardItemClasses = array ();
  private static $pluginSynchronizeFiles = null;
  private static $pluginDashboardItemClasses = null;

  private static function getPluginSynchronizeFiles() {
    if (self::$pluginSynchronizeFiles !== null) {
      return self::$pluginSynchronizeFiles;
    }

    self::$pluginSynchronizeFiles = array ();

    foreach (glob(dirname(__DIR__, 2) . '/Plugins/*/core/Synchronize/*.php') as $pluginFile) {
      self::$pluginSynchronizeFiles[basename($pluginFile, '.php')] = $pluginFile;
    }

    return self::$pluginSynchronizeFiles;
  }

  private static function getPluginDashboardItemClasses() {
    if (self::$pluginDashboardItemClasses !== null) {
      return self::$pluginDashboardItemClasses;
    }

    if (empty(self::getPluginSynchronizeFiles())) {
      self::$pluginDashboardItemClasses = array ();

      return self::$pluginDashboardItemClasses;
    }

    self::$pluginDashboardItemClasses = array ();
    $pluginDependencies = PluginDependenciesQuery::create()->findByExtension("synchronize");

    foreach ($pluginDependencies as $pluginDependency) {
      self::$pluginDashboardItemClasses[] = $pluginDependency->getUrl();
    }

    return self::$pluginDashboardItemClasses;
  }

  private static function resolveDashboardItemClass($dashboardItemClass) {
    if (array_key_exists($dashboardItemClass, self::$resolvedDashboardItemClasses)) {
      return self::$resolvedDashboardItemClasses[$dashboardItemClass];
    }

    $candidateClasses = array ($dashboardItemClass);

    if (str_starts_with($dashboardItemClass, "Plugin\\")) {
      $candidateClasses = array (preg_replace('/^Plugin\\\\/', 'Plugins\\', $dashboardItemClass, 1));
    }

    foreach (array_unique($candidateClasses) as $candidateClass) {
      if (class_exists($candidateClass)) {
        self::$resolvedDashboardItemClasses[$dashboardItemClass] = $candidateClass;

        return $candidateClass;
      }
    }

    $pluginSynchronizeFiles = self::getPluginSynchronizeFiles();

    foreach (array_unique($candidateClasses) as $candidateClass) {
      $classNameParts = explode('\\', $candidateClass);
      $classFileName = end($classNameParts);

      if (!isset($pluginSynchronizeFiles[$classFileName])) {
        continue;
      }

      require_once $pluginSynchronizeFiles[$classFileName];

      if (class_exists($candidateClass, false)) {
        self::$resolvedDashboardItemClasses[$dashboardItemClass] = $candidateClass;

        return $candidateClass;
      }
    }

    self::$resolvedDashboardItemClasses[$dashboardItemClass] = null;

    return null;
  }

  public static function getDashboardItems($PageName) {
     $DashboardItems = array (
       "EcclesiaCRM\Synchronize\FamilyDashboardItem",
       "EcclesiaCRM\Synchronize\GroupsDashboardItem",
       "EcclesiaCRM\Synchronize\PersonDashboardItem",
       "EcclesiaCRM\Synchronize\PersonDemographicDashboardItem",
       "EcclesiaCRM\Synchronize\SundaySchoolDashboardItem",
       "EcclesiaCRM\Synchronize\EventsDashboardItem",
       "EcclesiaCRM\Synchronize\ClassificationDashboardItem",
       "EcclesiaCRM\Synchronize\CalendarPageItem",
       "EcclesiaCRM\Synchronize\EDrivePageItem",
       "EcclesiaCRM\Synchronize\AttendeesPageItem",
       "EcclesiaCRM\Synchronize\VolunteersDashboardItem"
    );

    $DashboardItems = array_merge($DashboardItems, self::getPluginDashboardItemClasses());

    $ReturnValues = array ();
    Foreach ($DashboardItems as $dashboardItemClass) {
      $resolvedDashboardItemClass = self::resolveDashboardItemClass($dashboardItemClass);

      if ($resolvedDashboardItemClass === null) {
        continue;
      }

      if ($resolvedDashboardItemClass::shouldInclude($PageName)){
        array_push($ReturnValues, $resolvedDashboardItemClass);
      }
    }

    return $ReturnValues;
  }
  public static function getValues($PageName) {
    $ReturnValues = array ();
    Foreach (self::getDashboardItems($PageName) as $dashboardItemClass) {
      $ReturnValues[$dashboardItemClass::getDashboardItemName()] = $dashboardItemClass::getDashboardItemValue();
    }
    return $ReturnValues;
  }

}
