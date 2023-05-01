<?php

namespace EcclesiaCRM\Tasks;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

class EmailTask implements iTask
{
  public function isActive()
  {
    return SessionUser::getUser()->isAdmin() && empty(SystemConfig::hasValidMailServerSettings());
  }

  public function isAdmin()
  {
    return true;
  }

  public function getLink()
  {
    return SystemURLs::getRootPath() . '/v2/systemsettings';
  }

  public function getTitle()
  {
    return gettext('Set Email Settings');
  }

  public function getDesc()
  {
    return gettext("SMTP Server info are blank");
  }

}
