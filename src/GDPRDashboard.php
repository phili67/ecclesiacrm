<?php

/*******************************************************************************
 *
 *  filename    : GDPR.php
 *  last change : 2018-07-13
 *  description : manage the full GDPR
 *
 *  http://www.ecclesiacrm.com/
 *
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorizaion
 *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Service\CalendarService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\EventTypesQuery;
use EcclesiaCRM\dto\ChurchMetaData;

use EcclesiaCRM\NoteQuery;

// Set the page title and include HTML header
$sPageTitle = gettext('GDPR Dashboard');
require 'Include/Header.php';

if (!($_SESSION['user']->isGdrpDpoEnabled())) {
  Redirect('Menu.php');
  exit;
}
      
$notes = NoteQuery::Create()
      ->filterByPerId(array('min' => 1))
      ->filterByEnteredBy(array('min' => 2))
      ->find();
?>

<div class="box box-primary box-body">
  <div class="row ">
      <div class="col-sm-2" style="vertical-align: middle;">
         <a class="btn btn-app" href="<?= SystemURLs::getRootPath() ?>/GDPRListExport.php"><i class="fa fa-print"></i> <?= gettext("Printable Page") ?></a>
      </div>
      <div class="col-sm-10" style="vertical-align: middle;">
        <table>
          <tr>
            <td><label><?= gettext("GDPR DPO Signer") ?></label></td><td>&nbsp;:&nbsp;</td><td><?= SystemConfig::getValue('sGdprDpoSigner') ?></td>
          </tr>
          <tr>
            <td><label><?= gettext("GDPR DPO Signer Email") ?></label></td><td>&nbsp;:&nbsp;</td><td><?= SystemConfig::getValue('sGdprDpoSignerEmail') ?></td>
          </tr>
        </table>
      </div>
    </div>
</div>

<div class="box box-body">
<div class="box-header with-border">
  <i class="fa fa-user"></i>
  <h3 class="box-title"><?= gettext("GDPR Person status") ?></h3>
</div>
  <table class="table table-striped table-bordered" id="GDRP-Table" cellpadding="5" cellspacing="0"  width="100%"></table>
</div>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/GDRPDashboard.js" ></script>

<?php require 'Include/Footer.php'; ?>

