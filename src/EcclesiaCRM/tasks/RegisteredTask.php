<?php

namespace EcclesiaCRM\Tasks;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;


class RegisteredTask implements iTask
{
  public function isActive()
  {
    return SystemConfig::getValue('bRegistered') != 1;
  }

  public function isAdmin()
  {
    return false;
  }

  public function getLink()
  {
    return "#";
  }

  public function getTitle()
  {
    return _('Register Software');
  }

  public function getDesc()
  {
    return _('Let us know that you are using the software');
  }

}
