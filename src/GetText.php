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
    <title><?= _("Text from") ?> <?= $event->getId() ?></title>


    <?php
    require 'Include/Header-HTML-Scripts.php';
    Header_head_metatag(_("Text from") . " " . $event->getId());
    ?>

</head>
<body style="margin: 20px">
<div class="card">
    <div class="card-header with-border">
        <div class="card-title">
            <h5><?= _('Text for Event ID') . "   (" . $event->getId() . ") : " . htmlentities(stripslashes($event->getTitle()), ENT_NOQUOTES, 'UTF-8') ?></h5></caption>
        </div>
    </div>
    <div class="card-body">
        <?= $event->getText() ?>
    </div>
    <div class="card-footer">
        <p class="text-center">
            <input type="button" name="Action" value="<?= _("Close Window") ?>" class="btn btn-success"
                   onclick="javascript:window.close()">
        </p>
    </div>
</div>
</body>
</html>
<?php
require 'Include/Footer-Short.php';
?>
