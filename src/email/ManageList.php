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

if ( !isset($_GET['list_id']) ) {
    Redirect('Menu.php');
    exit;
}

$list_id = $_GET['list_id'];

$mailchimp = new MailChimpService();

$campaigns = $mailchimp->getCampaigns();
//print_r (count($campaigns));

//Set the page title
$sPageTitle = gettext('Manage List');

require '../Include/Header.php';

//print_r ($mailchimp->getListMembersFromListId($list_id));
?>

<div class="row">
  <div class="col-lg-12">
    <div class="box">
      <div class="box-header   with-border">
        <h3 class="box-title"><?= gettext('Manage Mailing List') ?></h3>
      </div>
      <div class="box-body">
        <p>
          <a href="#" class="btn btn-app" id="CreateCampaign" data-listid="<?= $list_id ?>">
            <i class="fa fa-list-alt"></i><?= gettext("Create a Campaign") ?>
          </a>
          <a href="#" id="deleteList" class="btn btn-app" data-listid="<?= $list_id ?>">
            <i class="fa fa-trash"></i><?= gettext("Delete") ?>
          </a>
        </p>
      </div>
    </div>
  </div>
</div>

<?php if ($mailchimp->isActive()) {
    $mcLists = $mailchimp->getLists(); ?>
  <div class="row">
    <?php 
      foreach ($mcLists as $list) {
        if ($list['id'] == $list_id) {
        //print_r($list);
    ?>
      <div class="col-lg-12">
        <div class="box">
          <div class="box-header   with-border">
            <h3 class="box-title"><?= gettext('Mailing List') ?>: <?= $list['name'] ?></h3> 
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
    <?php
          break;
        }
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
var list_ID = "<?= $list_id ?>";

    $(".person-group-Id-Share").select2({ 
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        placeholder: " -- "+i18next.t("Person or Family or Group")+" -- ",
        allowClear: true, // This is for clear get the clear button if wanted 
        ajax: {
            url: function (params){
              return window.CRM.root + "/api/people/search/" + params.term;
            },
            dataType: 'json',
            delay: 250,
            data: "",
            processResults: function (data, params) {
              return {results: data};
            },
            cache: true
        }
    });


     $(".person-group-Id-Share").on("select2:select",function (e) { 
       var list_id=$(this).data("listid");
       
       if (e.params.data.personID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addperson',
                data: JSON.stringify({"list_id":list_id ,"personID": e.params.data.personID})
           }).done(function(data) { 
             if (data.success) {
               location.reload();
             }
           });
        } else if (e.params.data.groupID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addgroup',
                data: JSON.stringify({"list_id":list_id ,"groupID": e.params.data.groupID})
           }).done(function(data) {
             if (data.success) {
               location.reload();
             }
           });
        } else if (e.params.data.familyID !== undefined) {
           window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/addfamily',
                data: JSON.stringify({"list_id":list_id ,"familyID": e.params.data.familyID})
           }).done(function(data) { 
             if (data.success) {
               location.reload();
             }
           });
        }
     });
     
  window.CRM.dataListTable = $("#memberListTable").DataTable({
    ajax:{
      url: window.CRM.root + "/api/mailchimp/listmembers/" + list_ID,
      type: 'GET',
      contentType: "application/json",
      dataSrc: "MailChimpMembers"
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('Actions'),
        data:'id',
        render: function(data, type, full, meta) {
          return '<a class="edit-member" data-id="'+full.email_address+'"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp;&nbsp;&nbsp;<a class="delete-member" data-id="'+full.email_address+'"><i class="fa fa-trash-o" aria-hidden="true" style="color:red"></i></a>';
        }
      },      
      {
        width: 'auto',
        title:i18next.t('Email'),
        data:'email_address',
        render: function(data, type, full, meta) {
          return data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('First Name'),
        data:'merge_fields',
        render: function(data, type, full, meta) {
          return data.FNAME;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Last Name'),
        data:'merge_fields',
        render: function(data, type, full, meta) {
          return data.LNAME;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Email Marketing'),
        data:'status',
        render: function(data, type, full, meta) {
          return data;
        }
      }
    ],
    responsive: true,
    createdRow : function (row,data,index) {
      $(row).addClass("duplicateRow");
    }
  });
  
  
    $(document).on("click",".edit-member", function(){
      var email = $(this).data("id");
      
      bootbox.prompt({
        title: "Select status for : " + email,
        inputType: 'select',
        inputOptions: [
            {
                text: 'Subscribed',
                value: 'subscribed',
            },
            {
                text: 'Unsubscribed',
                value: 'unsubscribed',
            }
        ],
        callback: function (status) {
          window.CRM.APIRequest({
                method: 'POST',
                path: 'mailchimp/status',
                data: JSON.stringify({"list_id":list_ID ,"status": status,"email": email})
          }).done(function(data) { 
             if (data.success) {
               location.reload();
             }
          });
        }
      });
    });

    $(document).on("click",".delete-member", function(){
      var email = $(this).data("id");
      
      bootbox.confirm({
        message: "This is a confirm with custom button text and color! Do you like it?",
        buttons: {
            confirm: {
                label: 'Yes',
                className: 'btn-success'
            },
            cancel: {
                label: 'No',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
          if (result) {
            window.CRM.APIRequest({
                  method: 'POST',
                  path: 'mailchimp/suppress',
                  data: JSON.stringify({"list_id":list_ID ,"email": email})
            }).done(function(data) { 
               if (data.success) {
                 location.reload();
               }
            });
          }
        }
      });
    });
    
    $(document).on("click","#deleteList", function(){
      var list_id = $(this).data("listid");
      
      bootbox.confirm({
        message: "This is a confirm with custom button text and color! Do you like it?",
        buttons: {
            confirm: {
                label: 'Yes',
                className: 'btn-success'
            },
            cancel: {
                label: 'No',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
          if (result) {
            window.CRM.APIRequest({
                  method: 'POST',
                  path: 'mailchimp/deletelist',
                  data: JSON.stringify({"list_id":list_ID})
            }).done(function(data) { 
               if (data.success) {
                 window.location.href = window.CRM.root + "/email/Dashboard.php";
               }
            });
          }
        }
      });
    });
    
    $(document).on("click","#CreateCampaign", function(){
      var list_id = $(this).data("listid");
      
      bootbox.confirm({
        message: "This is a confirm with custom button text and color! Do you like it?",
        buttons: {
            confirm: {
                label: 'Yes',
                className: 'btn-success'
            },
            cancel: {
                label: 'No',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
          if (result) {
            window.CRM.APIRequest({
                  method: 'POST',
                  path: 'mailchimp/createcampaign',
                  data: JSON.stringify({"list_id":list_ID})
            }).done(function(data) { 
               if (data.success) {
                 location.reload();
               }
            });
          }
        }
      });
    });
    
    

  
</script>

