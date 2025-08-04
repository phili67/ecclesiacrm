<?php

namespace EcclesiaCRM\Tasks;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

class ChurchAddress implements iTask
{
  public function isActive(){
    return SessionUser::getUser()->isAdmin() && empty(SystemConfig::getValue('sEntityAddress'));
  }
  public function isAdmin(){
    return true;
  }
  public function getLink(){
    return SystemURLs::getRootPath() . '/v2/systemsettings';
  }
  public function getTitle(){
    return gettext('Set Church Address');
  }
  public function getDesc(){
    return gettext("Church Address is not Set.");
  }

}
