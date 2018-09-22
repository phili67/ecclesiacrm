<?php
/*******************************************************************************
 *
 *  filename    : BackupDatabase.php
 *  last change : 2016-01-04
 *  description : Creates a backup file of the database.
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2003 Chris Gebhardt
  *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;

// Security: User must be an Admin to access this page.
// Otherwise, re-direct them to the main menu.
if (!$_SESSION['user']->isAdmin()) {
    Redirect('Menu.php');
    exit;
}

if (strtoupper(mb_substr(PHP_OS, 0, 3)) === 'WIN') {
    die('The Backup Utility will not work on a Windows based Server');
}

if (SystemConfig::getValue('sGZIPname')) {
    $hasGZIP = true;
}
if (SystemConfig::getValue('sZIPname')) {
    $hasZIP = true;
}
if (SystemConfig::getValue('sPGPname')) {
    $hasPGP = true;
}

// Set the page title and include HTML header
$sPageTitle = gettext('Backup Database');
require 'Include/Header.php';

?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= gettext('This tool will assist you in manually backing up the EcclesiaCRM database.') ?></h3>
    </div>
    <div class="box-body">
      <ul>
        <li><?= gettext('You should make a manual backup at least once a week unless you already have a regular backup procedule for your systems.') ?></li>
        <li><?= gettext('After you download the backup file, you should make two copies. Put one of them in a fire-proof safe on-site and the other in a safe location off-site.') ?></li>
        <li><?= gettext('If you are concerned about confidentiality of data stored in the EcclesiaCRM database, you should encrypt the backup data if it will be stored somewhere potentially accessible to others') ?></li>
        <li><?= gettext('For added backup security, you can e-mail the backup to yourself at an e-mail account hosted off-site or to a trusted friend.  Be sure to use encryption if you do this, however.') ?></li>
      </ul>
      <form method="post" action="<?= sRootPath ?>/api/database/backup" id="BackupDatabase">
        <div class="row">
          <div class="col-lg-12">
        <?= gettext('Select archive type') ?>:&nbsp;
        <?php 
          if ($hasGZIP) {
        ?>
            <input type="radio" name="archiveType" value="0"> <?= gettext('GZip') ?>
        <?php
          } 
        ?>
        <!--<?php if ($hasZIP) {
        ?><input type="radio" name="archiveType" value="1"><?= gettext('Zip') ?><?php
    } ?>-->
            &nbsp;&nbsp;&nbsp;<input type="radio" name="archiveType" value="2" checked> <?= gettext('Uncompressed') ?>
            &nbsp;&nbsp;&nbsp;<input type="radio" name="archiveType" value="3" checked> <?= gettext('tar.gz (Include Photos)') ?>
          </div>
        </div>
      
        <BR>
        
        <?php 
          if ($hasPGP) {
        ?>
        <div class="row">
          <div class="col-lg-12">
            <input type="checkbox" name="encryptBackup" value="1"><?= gettext('Encrypt backup file with a password?') ?>
          </div>
        </div>
        
        <br>
        
        <div class="row">
          <div class="col-lg-1">
            <?= gettext('Password') ?>:
          </div>
          <div class="col-lg-2">
            <input type="password" name="pw1" class="form-control input-sm">
          </div>
          <div class="col-lg-2">
            <?= gettext('Re-type Password') ?>:
          </div>
          <div class="col-lg-2">
            <input type="password" name="pw2" class="form-control input-sm">
          </div>
        </div>
        <BR>
        <div class="row">
          <div class="col-lg-12">
            <span id="passworderror" style="color: red"></span>
          </div>
        </div>
        <?php
          } 
        ?>
        <div class="row">
          <div class="col-lg-3">
            <input type="button" class="btn btn-primary" id="doBackup" <?= 'value="'.gettext('Generate and Download Backup').'"' ?>>
          </div>
          <div class="col-lg-5">
            <input type="button" class="btn btn-primary" id="doRemoteBackup" <?= 'value="'.gettext('Generate and Ship Backup to External Storage').'"' ?>>
          </div>
        </div>
      </form>
  </div>
</div>
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><?= gettext('Backup Status:') ?> </h3>&nbsp;<h3 class="box-title" id="backupstatus" style="color:red"> <?= gettext('No Backup Running') ?></h3>
    </div>
     <div class="box-body" id="resultFiles">
     </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">

function doBackup(isRemote)
{
  var endpointURL = "";
  if(isRemote)
  {
    endpointURL = window.CRM.root +'/api/database/backupRemote';
  }
  else
  {
    endpointURL = window.CRM.root +'/api/database/backup';
  }
  var errorflag =0;
  if ($("input[name=encryptBackup]").is(':checked'))
  {
    if ($('input[name=pw1]').val() =="")
    {
      $("#passworderror").html("You must enter a password");
      errorflag=1;
    }
    if ($('input[name=pw1]').val() != $('input[name=pw2]').val())
    {
      $("#passworderror").html("Passwords must match");
      errorflag=1;
    }
  }
  if (!errorflag)
  {
    $("#passworderror").html(" ");
    // get the form data
    // there are many ways to get this data using jQuery (you can use the class or id also)
    var formData = {
      'iArchiveType'              : $('input[name=archiveType]:checked').val(),
      'bEncryptBackup'            : $("input[name=encryptBackup]").is(':checked'),
      'password'                  : $('input[name=pw1]').val()
    };
    $("#backupstatus").css("color","orange");
    $("#backupstatus").html("Backup Running, Please wait.");
    console.log(formData);

   //process the form
   $.ajax({
      type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
      url         : endpointURL, // the url where we want to POST
      data        : JSON.stringify(formData), // our data object
      dataType    : 'json', // what type of data do we expect back from the server
      encode      : true,
      contentType: "application/json; charset=utf-8"
    })
    .done(function(data) {
      console.log(data);
      var downloadButton = "<button class=\"btn btn-primary\" id=\"downloadbutton\" role=\"button\" onclick=\"javascript:downloadbutton('"+data.filename+"')\"><i class='fa fa-download'></i>  "+data.filename+"</button>";
      $("#backupstatus").css("color","green");
      if(isRemote)
      {
        $("#backupstatus").html("Backup Generated and copied to remote server");
      }
      else
      {
        $("#backupstatus").html("Backup Complete, Ready for Download.");
        $("#resultFiles").html(downloadButton);
      }
    }).fail(function()  {
      $("#backupstatus").css("color","red");
      $("#backupstatus").html("Backup Error.");
    });
  }
}

$('#doBackup').click(function(event) {
  event.preventDefault();
  doBackup (0);
});

$('#doRemoteBackup').click(function(event) {
  event.preventDefault();
  doBackup(1);
});

function downloadbutton(filename) {
    window.location = window.CRM.root +"/api/database/download/"+filename;
    $("#backupstatus").css("color","green");
    $("#backupstatus").html("Backup Downloaded, Copy on server removed");
    $("#downloadbutton").attr("disabled","true");
}
</script>
<?php require 'Include/Footer.php' ?>
