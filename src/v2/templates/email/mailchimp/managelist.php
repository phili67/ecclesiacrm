<?php
/*******************************************************************************
 *
 *  filename    : managelist.php
 *  last change : 2014-11-29
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019
 *                Philippe Logel not MIT 
 *
 ******************************************************************************/
 
use EcclesiaCRM\dto\SystemURLs;

require $sRootDocument . '/Include/Header.php';
?>

<div class="row">
  <div class="col-lg-12">
    <div class="box">
      <div class="box-header   with-border">
        <h3 class="box-title"><?= _('Manage Email List') ?></h3>
        <div style="float:right">
          <a href="https://mailchimp.com/<?= $lang ?>/" target="_blank"><img src="<?= $sRootPath ?>/Images/Mailchimp_Logo-Horizontal_Black.png" height=25/></a>
        </div>
      </div>
      <div class="box-body">
        <p>
          <button class="btn btn-app" id="CreateCampaign" data-listid="<?= $listId ?>">
            <i class="fa fa-list-alt"></i><?= _("Create a Campaign") ?>
          </button>
          <button id="deleteAllSubScribers" class="btn btn-app bg-orange" data-listid="<?= $listId ?>">
            <i class="fa fa-trash-o"></i><?= _("Delete All Subscribers") ?>
          </button>
          <button id="deleteList" class="btn btn-app align-right bg-maroon" data-listid="<?= $listId ?>">
            <i class="fa fa-trash"></i><?= _("Delete") ?>
          </button>
          <button class="btn btn-app align-right bg-blue" id="modifyList" data-name="<?= $list['name'] ?>" data-subject="<?= $list['campaign_defaults']['subject']?>" data-permissionreminder="<?= $list['permission_reminder'] ?>">
             <i class="fa fa-pencil"></i>
             <?= _('Modify Properties') ?>
          </button>
        </p>
      </div>
    </div>
  </div>
</div>

<?php 
  if ($isMailchimpActiv) {
?>
  <div class="row">
      <div class="col-lg-12">
        <div class="box" id="container">
        </div>
      </div>
  </div>
  
  <div class="row">  
      <div class="col-lg-12">
        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title"><?= _('Subscribers') ?></h3>
          </div>
          <div class="box-body">
              <div class="callout callout-info"><i class="fa fa-info" aria-hidden="true"></i> 
                <?= _("To add all the newsletter users, type <b>NewLetter</b> in the search field, to add all members of the CRM, use <b>*</b>") ?><br>
                <ul>
                  <li>
                    <?= _("The max numbers of members can be modified in the \"System Settings\" -> \"Integration\" -> \"field\" : iMailChimpApiMaxMembersCount ") ?>
                  </li>
                  <li>
                    <?= _("Increase this value is unstable with MailChimp API.") ?>
                  </li>
                </ul>
              </div>
              
              <table class="table table-striped table-bordered" id="memberListTable" cellpadding="5" cellspacing="0"  width="100%"></table>
              
              <div class="row">
                <div class="col-md-2">
                  <input type="checkbox" class="check_all" id="check_all"> 
                  <label for="check_all"><?= _("Check all") ?></label>
                </div>
                <div class="col-md-1">
                  Ajouter      </div>
                <div class="col-md-3">
                  <select name="person-group-Id-Share" class="person-group-Id-Share" class="form-control select2" style="width:100%" data-listid="<?= $list['id'] ?>"></select>
                </div>
                <div class="col-md-2">
                  <button type="button" id="deleteMembers" class="btn btn-danger" disabled><?= _("Delete") ?></button>
                </div>
                <div class="col-md-2">
                  <div class="btn-group">
                    <button type="button" class="subscribeButton btn btn-success" disabled><?= _("Subscribe" ) ?></button>
                    <button type="button" class="subscribeButtonDrop btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
                      <span class="caret"></span>
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                      <li><a class="subscribeButton" data-type="subscribed"><?= _("Subscribed")?></a></li>
                      <li><a class="subscribeButton" data-type="unsubscribed"><?= _("Unsubscribed")?></a></li>
                    </ul>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="btn-group">
                    <button type="button" class="addTagButton btn btn-success" disabled><?= _("Add Tag" ) ?></button>
                    <button type="button" class="addTagButtonDrop btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false" disabled>
                      <span class="caret"></span>
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu" id="allTags">
                      <li><a class="addTagButton" data-id="-1"><?= _("Add a new tag") ?></a></li>
                      
                      <?php
                         foreach ($list['tags'] as $tag) {
                      ?>
                            <li><a class="addTagButton" data-id="<?= $tag['id'] ?>" data-name="<?= $tag['name'] ?>"><?= $tag['name'] ?></a></li>
                      <?php
                         }
                      ?>
                      
                    </ul>
                  </div>
                </div>
              </div>
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
          <h4><i class="fa fa-ban"></i> MailChimp <?= _('is not configured') ?></h4>
          <?= _('Please update the') ?> MailChimp <?= _('API key in Setting->') ?><a href="<?= $sRootPath ?>/SystemSettings.php"><?= _('Edit General Settings') ?></a>,
          <?= _('then update') ?> sMailChimpApiKey. <?= _('For more info see our ') ?><a href="<?= $getSupportURL ?>"> MailChimp <?= _('support docs.') ?></a>
        </div>
      </div>
    </div>
  </div>

<?php
}
require $sRootDocument . '/Include/Footer.php'; 
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  window.CRM.list_ID           = "<?= $listId ?>";
  window.CRM.mailchimpIsActive = <?= ($isMailchimpActiv)?1:0 ?>;
  window.CRM.bWithAddressPhone = <?= ($bWithAddressPhone)?'true':'false' ?>;
  window.CRM.sDateFormatLong   = "<?= $sDateFormatLong ?>";
</script>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/ManageList.js"></script>
<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>
<script src="<?= $sRootPath ?>/skin/js/publicfolder.js"></script>