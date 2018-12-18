<?php

namespace EcclesiaCRM\Tasks;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

class HttpsTask implements iTask
{
  public function isActive(){
    return SessionUser::getUser()->isAdmin() && !isset($_SERVER['HTTPS']);
  }
  public function isAdmin(){
    return true;
  }
  public function getLink(){
    return SystemURLs::getSupportURL();
  }
  public function getTitle(){
    return gettext('Configure HTTPS');
  }
  public function getDesc(){
    return gettext('Your system could be more secure by installing an TLS/SSL Cert.');
  }

}
