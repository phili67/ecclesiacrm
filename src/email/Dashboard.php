<?php
/*******************************************************************************
 *
 *  filename    : Dashboard.php
 *  last change : 2014-11-29
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2014
 *
 ******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\Service\MailChimpService;
use EcclesiaCRM\dto\SystemURLs;

if (!($_SESSION['user']->isMailChimpEnabled())) {
    Redirect('Menu.php');
    exit;
}

$mailchimp = new MailChimpService();

//Set the page title
$sPageTitle = gettext('eMail Dashboard');

require '../Include/Header.php';

//print_r ($_SESSION['MailChimpLists']);
?>
<div class="row">
  <div class="col-lg-12">
    <div class="box">
      <div class="box-header   with-border">
        <h3 class="box-title"><?= gettext('Email Export') ?></h3>
      </div>
      <div class="box-body">
        <p>
          <a href="#" class="btn btn-app" id="CreateList">
            <i class="fa fa-list-alt"></i><?= gettext("Create a Mailing list") ?>
          </a>
          <a class="btn btn-app" href="MemberEmailExport.php">
            <i class="fa fa fa-table"></i> <?= gettext('Generate CSV') ?>
          </a>
          <a href="<?= SystemURLs::getRootPath() ?>/email/DuplicateEmails.php" class="btn btn-app">
            <i class="fa fa-exclamation-triangle"></i> <?= gettext("Find Duplicate Emails") ?>
          </a>
          <a href="<?= SystemURLs::getRootPath() ?>/email/NotInMailChimpEmails.php" class="btn btn-app">
            <i class="fa fa-bell-slash"></i><?= gettext("Families Without NewsLetters") ?>
          </a>
        </p>
        <?= gettext('You can import the generated CSV file to external email system.') ?>
            <?= _("For MailChimp see") ?> <a href="http://kb.mailchimp.com/lists/growth/import-subscribers-to-a-list"
                                   target="_blank"><?= gettext('import subscribers to a list.') ?></a>
      </div>
    </div>
  </div>
</div>

<?php if ($mailchimp->isActive()) {
    $mcLists = $mailchimp->getLists(); ?>
  <div class="row">
    <?php 
      foreach ($mcLists as $list) {
    ?>
      <div class="col-lg-12">
        <div class="box">
          <div class="box-header   with-border">
            <h3 class="box-title"><?= gettext('MailChimp List') ?>: <?= $list['name'] ?></h3> <a href="<?= SystemURLs::getRootPath() ?>/email/ManageList.php?list_id=<?= $list['id'] ?>"><i class="fa pull-right fa-gear" style="font-size: 1.2em"></i></a>
          </div>
          <div class="box-body">
             <table width='300px'>
                <tr><td><b><?= gettext('Members:') ?></b> </td><td><?= $list['stats']['member_count'] ?></td></tr>
                <tr><td><b><?= gettext('Campaigns:') ?></b> </td><td><?= $list['stats']['campaign_count'] ?></td></tr>
                <tr><td><b><?= gettext('Unsubscribed count:') ?></b> </td><td><?= $list['stats']['unsubscribe_count'] ?></td></tr>
                <tr><td><b><?= gettext('Unsubscribed count since last send:') ?></b> </td><td><?= $list['stats']['unsubscribe_count_since_send'] ?></td></tr>
                <tr><td><b><?= gettext('Cleaned count:') ?></b> </td><td><?= $list['stats']['cleaned_count'] ?></td></tr>
                <tr><td><b><?= gettext('Cleaned count since last send:') ?></b> </td><td><?= $list['stats']['cleaned_count_since_send']?> </td></tr>
              </table>
          </div>
        </div>
      </div>
    <?php
      } 
    ?>
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
          <?= gettext('Please update the') ?> MailChimp <?= gettext('API key in Setting->') ?><a href="../SystemSettings.php"><?= gettext('Edit General Settings') ?></a>,
          <?= gettext('then update') ?> sMailChimpApiKey. <?= gettext('For more info see our ') ?><a href="<?= SystemURLs::getSupportURL() ?>"> MailChimp <?= gettext('support docs.') ?></a>
        </div>
      </div>
    </div>
  </div>


<?php
}
require '../Include/Footer.php';
?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  function BootboxContent(){  
    
    var frm_str = '<h3 style="margin-top:-5px">'+i18next.t("List Creation")+'</h3><form id="some-form">'
       + '<div>'
            +'<div class="row div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('List Title') + ":</div>"
              +'<div class="col-md-9">'
                +"<input type='text' id='ListTitle' placeholder='" + i18next.t("Your List Title") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
              +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Subject') + ":</div>"
              +'<div class="col-md-9">'
                +"<input type='text' id='Subject' placeholder='" + i18next.t("Your Subject") + "' size='30' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required>"
              +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-3"><span style="color: red">*</span>' + i18next.t('Permission Reminder') + ":</div>"
              +'<div class="col-md-9">'
                +"<textarea id='PermissionReminder' rows='3' maxlength='100' class='form-control input-sm'  width='100%' style='width: 100%' required placeholder='" + i18next.t("Permission Reminder") + "'></textarea>"
              +'</div>'
            +'</div>'
            +'<div class="row div-title">'
              +'<div class="col-md-5">'
                 +'<div class="checkbox">'
                   +'<label>'
                    +'<input type="checkbox" id="ArchiveBars"> '+ i18next.t('Archive Bars')
                  +'</label>'
                +'</div>'
              +'</div>'
            +'</div>'
            +'<div class="row  div-title">'
              +'<div class="status-event-title">'
                +'<span style="color: red">*</span>'+i18next.t('Status')
              +'</div>'
              +'<div class="status-event">'
                +'<input type="radio" name="Status" value="prv" checked/> '+i18next.t('Private')
              +'</div>'
              +'<div class="status-event">'
                +'<input type="radio" name="Status" value="pub" /> '+i18next.t('Public')
              +'</div>'
            +'</div>'
          +'</div>'
       + '</form>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }
    function createListEditorWindow ()
    {
      
      var modal = bootbox.dialog({
         message: BootboxContent(),
         buttons: [
          {
           label: i18next.t("Close"),
           className: "btn btn-default",
           callback: function() {
              console.log("just do something on close");
           }
          },
          {
           label: i18next.t("Save"),
           className: "btn btn-primary",
           callback: function() {
              var ListTitle =  $('form #ListTitle').val();
              
              if (ListTitle) {
                  var Subject      = $('form #Subject').val();
                  var PermReminder = $('form #PermissionReminder').val();
                  var ArchiveBars  = $('#ArchiveBars').is(":checked");
                  var Status       = $('input[name="Status"]:checked').val();

                  window.CRM.APIRequest({
                    method: 'POST',
                    path: 'mailchimp/createlist',
                    data: JSON.stringify({"ListTitle": ListTitle,"Subject" : Subject, "PermissionReminder":PermReminder,"ArchiveBars":ArchiveBars,"Status":Status})
                  }).done(function(data) {
                    if (data.success) {
                       location.reload();
                    } else if (data.error) {
                      window.CRM.DisplayAlert(i18next.t("Error"),i18next.t(data.error.detail));
                    }
                  });

                  return add;  
              } else {
                  window.CRM.DisplayAlert("Error","You have to set a List Title for your eMail List");
                
                  return false;
              }    
            }
          }
         ],
         show: false/*,
         onEscape: function() {
            modal.modal("hide");
         }*/
       });
       
       // this will ensure that image and table can be focused
       $(document).on('focusin', function(e) {e.stopImmediatePropagation();});       
              
       return modal;
    }
  $(document).on("click","#CreateList", function(){
    var modal = createListEditorWindow();
    
    modal.modal("show");
  });  



</script>
