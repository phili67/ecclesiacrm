<?php

namespace EcclesiaCRM\Tasks;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;


class LatestReleaseTask implements iTask
{
  private $installedVersion;
  private $latestVersion;

  public function __construct()
  {
    $this->latestVersion = $_SESSION['latestVersion'];
    $this->installedVersion = $_SESSION['sSoftwareInstalledVersion'];
  }

  public function isActive()
  {
      $compare = version_compare($this->installedVersion, $this->latestVersion['name']);
      return $this->latestVersion != null && $compare != 1;
  }

  public function isAdmin()
  {
    return false;
  }

  public function getLink()
  {
    if (SessionUser::getUser()->isAdmin()) {
      return SystemURLs::getRootPath() . '/v2/system/upgrade/crm';
    } else {
      return 'https://github.com/phili67/ecclesiacrm/releases/latest';
    }
  }

  public function getTitle()
  {
    return gettext('New Release') . ' ' . $this->latestVersion['name'];
  }

  public function getDesc()
  {
    return $this->latestVersion['body'];
  }

}
