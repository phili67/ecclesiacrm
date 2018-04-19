<?php
/*******************************************************************************
 *
 *  filename    : GetText.php
 *  last change : 2005-09-08
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2005 Todd Pillars
 *
 *  function    : Get Text from Church Events Table in popup window
  *
 ******************************************************************************/
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\EventQuery;

$event = EventQuery::Create()->findOneById($_GET['EID']);

?>
<html>
<head><title><?= gettext("Text from") ?> <?= "coucou".$event->getId() ?></title></head>
</html>
<table cellpadding="4" align="center" cellspacing="0" width="100%">
  <caption>
    <h3><?= gettext('Text for Event ID: ').$event->getTitle()."   (".$event->getId().")" ?></h3>
  </caption>
  <tr>
    <td><?= $event->getText() ?></td>
  </tr>
  <tr>
    <td align="center" valign="bottom">
      <input type="button" name="Action" value="<?= gettext("Close Window") ?>" class="btn btn-success" onclick="javascript:window.close()">
    </td>
  </tr>
</html>


