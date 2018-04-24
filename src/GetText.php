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

require_once 'Include/Header-function.php';
require_once 'Include/Header-Security.php';

// Turn ON output buffering
ob_start();

?>
<html>
<head>
   <title><?= gettext("Text from") ?> <?= $event->getId() ?></title>
   
   
  <?php
  require 'Include/Header-HTML-Scripts.php';
  Header_head_metatag();
  ?>

</head>
</html>

  <div class="box-header with-border">
      <h3><?= gettext('Text for Event ID')."   (".$event->getId().") : ".htmlentities(stripslashes($event->getTitle()), ENT_NOQUOTES, 'UTF-8') ?></h3></caption>
  </div>
  <div class="box-body">
    <?= $event->getText() ?>
    <center><input type="button" name="Action" value="<?= gettext("Close Window") ?>" class="btn btn-success" onclick="javascript:window.close()"></center>
  </div>
</html>
<?php
require 'Include/Footer-Short.php';
?>


