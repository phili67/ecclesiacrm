<?php
/*******************************************************************************
 *
 *  filename    : NoteEditor.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker, 2018 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\ListOptionQuery;

// Set the page title and include HTML header
$sPageTitle = gettext('GDPR Data Structure');

if (!($_SESSION['user']->isGdrpDpoEnabled())) {
  Redirect('Menu.php');
  exit;
}
      
$personCustMasts = PersonCustomMasterQuery::Create()
      ->orderByCustomName()
      ->find();
      
require 'Include/Header.php';

?>

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">
      <label><?= gettext("Informations about the Data Structure") ?></label>
    </h3>
  </div>
  <div class="box-body">
    <table class="table table-hover dt-responsive" id="gdpr-data-structure-table" style="width:100%;">
      <thead>
        <tr>
            <th><b><?= gettext('Informations') ?></b></th>
            <th><b><?= gettext('Type') ?></b></th>
            <th><b><?= gettext('Comment') ?></b></th>
        </tr>
      </thead>
      <tbody>
      <?php
        $sEmailLink = '';
        $iEmailNum = 0;
        $email_array = [];

        foreach ($personCustMasts as $personCustMast) { 
          $dataType = ListOptionQuery::Create()
            ->filterByOptionId($personCustMast->getTypeId())
            ->findOneById(4);
      ?>
            <tr>
                <td><?= $personCustMast->getCustomName() ?></td>
                <td><?= gettext($dataType->getOptionName()) ?></td>
                <td><input type="text" name="<?= $personCustMast->getId() ?>" size="35" maxlength="40" class="form-control" value="<?= $personCustMast->getCustomComment() ?>" data-id="<?= $personCustMast->getId() ?>"></td>
            </tr>
      <?php
        } 
      ?>
        </tbody>
    </table>
  </div>
</div>

<?php require 'Include/Footer.php' ?>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  $(document).ready(function () {
      $("#gdpr-data-structure-table").DataTable({
       "language": {
         "url": window.CRM.plugin.dataTable.language.url
       },
       responsive: true,
       pageLength: 100,
      });
      
      $('input').keydown( function(e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        var val = $(this).val();
        var id  = $(this).data("id");
        
        if (key == 9 || key == 13) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'gdrp/setComment',
            data: JSON.stringify({"person_custom_id": id,"comment" : val})
          }).done(function(data) {
            if (key == 13) {
              var dialog = bootbox.dialog({
                message  : i18next.t("Your operation completed successfully."),
              });
            
              setTimeout(function(){ 
                  dialog.modal('hide');
              }, 1000);
            }
          });
        }
      });
  });
</script>
