<?php
/*******************************************************************************
 *
 *  filename    : Dashboard.php
 *  last change : 2014-11-29
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2014
 *
 ******************************************************************************/

require '../../Include/Config.php';
require '../../Include/Functions.php';

use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\SystemConfig;

$mailchimp = new MailChimpService();

if ( !($_SESSION['user']->isMailChimpEnabled() && $mailchimp->isActive()) ) {
    Redirect('Menu.php');
    exit;
}

if ( !isset($_GET['list_id']) ) {
    Redirect('Menu.php');
    exit;
}

$list_id = $_GET['list_id'];

$campaigns = $mailchimp->getCampaignsFromListId($list_id);

//Set the page title
$sPageTitle = gettext('Manage List');

require '../../Include/Header.php';
?>

<div class="row">
  <div class="col-lg-12">
    <div class="box">
      <div class="box-header   with-border">
        <h3 class="box-title"><?= gettext('Manage Mailing List') ?></h3><div style="float:right"><a href="https://mailchimp.com/<?= substr(SystemConfig::getValue('sLanguage'),0,2) ?>/"><img src="<?= SystemURLs::getRootPath() ?>/Images/Mailchimp_Logo-Horizontal_Black.png" height=25/></a></div>
      </div>
      <div class="box-body">
        <p>
          <button class="btn btn-app" id="CreateCampaign" data-listid="<?= $list_id ?>">
            <i class="fa fa-list-alt"></i><?= gettext("Create a Campaign") ?>
          </button>
          <button id="deleteAllSubScribers" class="btn btn-app bg-orange" data-listid="<?= $list_id ?>">
            <i class="fa fa-trash-o"></i><?= gettext("Delete All Subscribers") ?>
          </button>
          <button id="deleteList" class="btn btn-app align-right bg-maroon" data-listid="<?= $list_id ?>">
            <i class="fa fa-trash"></i><?= gettext("Delete") ?>
          </button>
        </p>
      </div>
    </div>
  </div>
</div>

<?php 
  if ($mailchimp->isActive()) {
?>
  <div class="row">
    <?php 
      $list = $mailchimp->getListFromListId($list_id);
    ?>
      <div class="col-lg-12">
        <div class="box" id="container">
        </div>
      </div>
  </div>
  
  <div class="callout callout-info"><i class="fa fa-info" aria-hidden="true"></i> <?= _("To add all the newsletter users, type NewLetter in the search field, to add all members of the CRM, use '*'") ?></div>
  
  <div class="row">  
      <div class="col-lg-12">
        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title"><?= gettext('Subscribers') ?></h3>
          </div>
          <div class="box-body">
              <table class="table table-striped table-bordered" id="memberListTable" cellpadding="5" cellspacing="0"  width="100%"></table>
              <select name="person-group-Id-Share" class="person-group-Id-Share" class="form-control select2" style="width:100%" data-listid="<?= $list['id'] ?>"></select>
          </div>
        </div>
      </div>
    <br>
  </div>
<?php
} else {
?>
  <div class="row">
    <div class="col-lg-12">
      <div class="box box-body">
        <div class="alert alert-danger alert-dismissible">
          <h4><i class="fa fa-ban"></i> MailChimp <?= gettext('is not configured') ?></h4>
          <?= gettext('Please update the') ?> MailChimp <?= gettext('API key in Setting->') ?><a href="../../SystemSettings.php"><?= gettext('Edit General Settings') ?></a>,
          <?= gettext('then update') ?> sMailChimpApiKey. <?= gettext('For more info see our ') ?><a href="<?= SystemURLs::getSupportURL() ?>"> MailChimp <?= gettext('support docs.') ?></a>
        </div>
      </div>
    </div>
  </div>

<?php
}
require '../../Include/Footer.php';
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.list_ID = "<?= $list_id ?>";
  window.CRM.mailchimpIsActive = <?= ($mailchimp->isActive())?1:0 ?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/email/MailChimp/ManageList.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditorextension.js"></script>
