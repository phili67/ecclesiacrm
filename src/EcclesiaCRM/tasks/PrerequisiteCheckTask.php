<?php

namespace EcclesiaCRM\Tasks;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Service\AppIntegrityService;


class PrerequisiteCheckTask implements iTask
{
  public function isActive()
  {
    return ! AppIntegrityService::arePrerequisitesMet();
  }

  public function isAdmin()
  {
    return true;
  }

  public function getLink()
  {
    return SystemURLs::getRootPath() . '/IntegrityCheck.php';
  }

  public function getTitle()
  {
    return gettext('Unmet Application Prerequisites');
  }

  public function getDesc()
  {
    return gettext('Unmet Application Prerequisites');
  }

}
