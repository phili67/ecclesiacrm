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
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


if (!(SessionUser::getUser()->isMailChimpEnabled())) {
    RedirectUtils::Redirect('Menu.php');
    exit;
}

$mailchimp       = new MailChimpService();
$mailChimpStatus = $mailchimp->getConnectionStatus();

//Set the page title
$sPageTitle = gettext('MailChimp Dashboard');

require '../../Include/Header.php';

?>
<?php
  if ( $mailChimpStatus['title'] == 'Forbidden' ) {
?>
  <div class="callout callout-danger">
    <h4><i class="fa fa-ban"></i> <?= _('MailChimp Problem') ?></h4>
    <?= _("Mailchimp Status") ?> : Title : <?= $mailChimpStatus['title'] ?> status : <?= $mailChimpStatus['status'] ?> detail : <?= $mailChimpStatus['detail'] ?> 
    <?php
      if (!empty($mailChimpStatus['errors']) ) {
    ?>
    <ul>
      <?php
        foreach ($mailChimpStatus['errors'] as $error) {
      ?>
          <li>
            field : <?= $error['field'] ?> Message : <?= $error['message'] ?>
          </li>
      <?php
        } 
      ?>
    </ul>
    <?php
      }
    ?>
  </div>
<?php
  } else {
?>
  <div class="callout callout-info">
    <h4><i class="fa fa-info"></i> <?= _('MailChimp is activated') ?></h4>
    <?= _('MailChimp is working correctly') ?>
  </div>
<?php
  }
?>
<div class="row">
  <div class="col-lg-12">
    <div class="box">
      <div class="box-header   with-border">
        <h3 class="box-title"><?= gettext('MailChimp Management') ?></h3><div style="float:right"><a href="https://mailchimp.com/<?= substr(SystemConfig::getValue('sLanguage'),0,2) ?>/"><img src="<?= SystemURLs::getRootPath() ?>/Images/Mailchimp_Logo-Horizontal_Black.png" height=25/></a></div>
      </div>
      <div class="box-body">
        <p>
          <button class="btn btn-app" id="CreateList" <?= ($mailchimp->isActive())?'':'disabled' ?>>
            <i class="fa fa-list-alt"></i><?= gettext("Create a Mailing list") ?>
          </button>
          <a class="btn btn-app bg-green" href="<?= SystemURLs::getRootPath() ?>/email/MailChimp/MemberEmailExport.php">
            <i class="fa fa fa-table"></i> <?= gettext('Generate CSV') ?>
          </a>
          <a href="<?= SystemURLs::getRootPath() ?>/email/MailChimp/DuplicateEmails.php" class="btn btn-app">
            <i class="fa fa-exclamation-triangle"></i> <?= gettext("Find Duplicate Emails") ?>
          </a>
          <a href="<?= SystemURLs::getRootPath() ?>/email/MailChimp/NotInMailChimpEmails.php" class="btn btn-app">
            <i class="fa fa-bell-slash"></i><?= gettext("Families Not In MailChimp") ?>
          </a>
          <a href="<?= SystemURLs::getRootPath() ?>/email/MailChimp/Debug.php" class="btn btn-app">
            <i class="fa fa-stethoscope"></i><?= gettext("Debug") ?>
          </a>
        </p>
        <?= gettext('You can import the generated CSV file to external email system.') ?>
            <?= _("For MailChimp see") ?> <a href="http://kb.mailchimp.com/lists/growth/import-subscribers-to-a-list"
                                   target="_blank"><?= gettext('import subscribers to a list.') ?></a>
      </div>
    </div>
  </div>
</div>

<div id="container"></div>

<?php
require '../../Include/Footer.php';
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
 window.CRM.mailchimpIsActive = <?= ($mailchimp->isActive())?1:0 ?>;
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/email/MailChimp/Dashboard.js"></script>