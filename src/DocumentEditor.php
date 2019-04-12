<?php
/*******************************************************************************
 *
 *  filename    : DocumentEditor.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker, 2018 Philippe Logel
 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;


$iCurrentFamID = SessionUser::getUser()->getPerson()->getFamId();

$uploadEDrive = false;

if (isset($_GET['uploadEDrive'])) {
  $uploadEDrive = true;
  $sNoteType = 'file';
}

if (isset($_GET['PersonID'])) {
   $iFamily = PersonQuery::Create()->findOneById($_GET['PersonID'])->getFamId();
} else if (isset($_GET['FamilyID'])) {
   $iFamily = $_GET['FamilyID'];
} else {
  $iFamily = 0;
}

// Security: User must have Notes permission
// Otherwise, re-direct them to the main menu.
if (!(SessionUser::getUser()->isNotesEnabled() || $_GET['PersonID'] == SessionUser::getUser()->getPersonId() || $iCurrentFamID == $iFamily)) {
    RedirectUtils::Redirect('Menu.php');
    exit;
}

if (isset($_GET['PersonID'])) {
    $iPersonID = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');
} else {
    $iPersonID = 0;
}

//Set the page title
$user = UserQuery::create()->findOneByPersonId($iPersonID);

if ($sNoteType == 'file' && !is_null ($user) ) {
  $sPageTitle = _('Cloud Upload')." "._("in"). " : " . $user->getCurrentpath();
} else {
  $sPageTitle = _('Document Editor');
}

if (isset($_GET['FamilyID'])) {
    $iFamilyID = InputUtils::LegacyFilterInput($_GET['FamilyID'], 'int');
} else {
    $iFamilyID = 0;
}

//To which page do we send the user if they cancel?
if ($iPersonID > 0) {
    $sBackPage = 'PersonView.php?PersonID='.$iPersonID;
    if ($uploadEDrive) {
      $sBackPage.='&edrive=true';
    } else {
      $sBackPage.='&documents=true';
    }
} else {
    $sBackPage = 'FamilyView.php?FamilyID='.$iFamilyID;
}

//Has the form been submitted?
if (isset($_POST['Submit'])) {
    //Initialize the ErrorFlag
    $bErrorFlag = false;
        
    //Assign all variables locally
    $iNoteID = InputUtils::LegacyFilterInput($_POST['NoteID'], 'int');
    $sNoteText = InputUtils::FilterHTML($_POST['NoteText'], 'htmltext');
        
    $uploadOk = 1;

    //If they didn't check the private box, set the value to 0
    if (isset($_POST['Private'])) {
        $bPrivate = 1;
    } else {
        $bPrivate = 0;
    }
    
    
    //Did they enter text for the note?
    if ($sNoteText == '' && empty($_FILES["noteInputFile"]["name"])) {
        $sNoteTextError = '<br><span style="color: red;">'._("You must enter text for this document").'.</span>';
        $bErrorFlag = true;
    }

    //Were there any errors?
    if (!$bErrorFlag) {
      if (!empty($_FILES["noteInputFile"]["name"]) || isset($_POST['noteType']) && $_POST['noteType'] == 'file') {
        $user = UserQuery::create()->findOneByPersonId($iPersonID);
        
        $target_dir = $user->getHomedir();
        $target_file = $target_dir . $user->getCurrentpath() . basename($_FILES["noteInputFile"]["name"]);
      
        if (move_uploaded_file($_FILES['noteInputFile']['tmp_name'], $target_file)) {
          //Are we adding or editing?
          if ($iNoteID <= 0) {
            $note = new Note();
            $note->setPerId($iPersonID);
            $note->setFamId($iFamilyID);
            $note->setTitle($_POST['noteTitle']);
            $note->setPrivate($bPrivate);
            $note->setText($user->getUserName().str_replace($target_dir,"",$target_file));
            $note->setType('file');
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->setInfo(_('Create file'));
            
            $note->save();
          } else {
            
            $note = NoteQuery::create()->findPk($iNoteID);
            $notes = NoteQuery::Create ()->filterByText ($note->getText())->findByPerId($note->getPerId());
            
            $target_delete_file = $user->getUserRootDir() . $user->getCurrentpath().$note->getText();
   
            unlink($target_delete_file);
            
            $notes->delete();
                        
            $note = new Note();
            $note->setPerId($iPersonID);
            $note->setFamId($iFamilyID);
            $note->setTitle($_POST['noteTitle']);
            $note->setPrivate($bPrivate);
            $note->setText($user->getUserName().str_replace($target_dir,"",$target_file));
            $note->setType('file');
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->setInfo(_('Create file'));
            
            $note->save();          }
        } else {// now we simply store the document
          $note = NoteQuery::create()->findPk($iNoteID);
          
          $note->setTitle($_POST['noteTitle']);           
          $note->setDateLastEdited(new DateTime());
          $note->setCurrentEditedBy(0);
          $note->setCurrentEditedDate(NULL);          
          
          $note->save();
        }    
        
        RedirectUtils::Redirect($sBackPage.'&edrive=true');
      }


      //Are we adding or editing?
      if ($iNoteID <= 0) {
          $note = new Note();
          $note->setPerId($iPersonID);
          $note->setFamId($iFamilyID);
          $note->setPrivate($bPrivate);
          $note->setTitle($_POST['noteTitle']);
          $note->setText($sNoteText);
          $note->setType($_POST['noteType']);
          $note->setEntered(SessionUser::getUser()->getPersonId());

          $note->setCurrentEditedBy(0);
          $note->setCurrentEditedDate(NULL);          
          $note->save();
      } else {
          $note = NoteQuery::create()->findPk($iNoteID);
          $note->setPrivate($bPrivate);
          $note->setText($sNoteText);
          $note->setDateLastEdited(new DateTime());
          $note->setEditedBy(SessionUser::getUser()->getPersonId());
          $note->setType($_POST['noteType']);
          $note->setTitle($_POST['noteTitle']);
          
          $note->setCurrentEditedBy(0);
          $note->setCurrentEditedDate(NULL);          
          
          $note->save();
      }

      //Send them back to whereever they came from
      RedirectUtils::Redirect($sBackPage);
    }
} else if ( isset($_POST['Cancel']) ) {
  if (isset($_POST['NoteID'])) {
     $iNoteID = InputUtils::LegacyFilterInput($_POST['NoteID'], 'int');     
          
     $note = NoteQuery::create()->findPk($iNoteID);
     
     if (!empty($note)) {
       $note->setCurrentEditedBy(0);
       $note->setCurrentEditedDate(NULL);
          
       $note->save();
     }
  }
  
  RedirectUtils::Redirect($sBackPage);
} else {
    //Are we adding or editing?
    if (isset($_GET['NoteID'])) {
        //Get the NoteID from the querystring
        $iNoteID = InputUtils::LegacyFilterInput($_GET['NoteID'], 'int');
        $dbNote = NoteQuery::create()->findPk($iNoteID);

        //Assign everything locally
        $sTitleText = $dbNote->getTitle();
        $sNoteText  = $dbNote->getText();
        $bPrivate   = $dbNote->getPrivate();
        $iPersonID  = $dbNote->getPerId();
        $iFamilyID  = $dbNote->getFamId();
        $sNoteType  = $dbNote->getType();
        
        // now we now that the share document is used by someone
        if ($iPersonID > 0) {
          $dbNote->setCurrentEditedBy($iPersonID);
          $dbNote->setCurrentEditedDate(new DateTime());
          
          $dbNote->save();
        }
        
        if (empty($sTitleText) && $sNoteType == 'file') {
          $sTitleText = $sNoteText;
        }  
    }
}
require 'Include/Header.php';

?>
<form method="post" enctype="multipart/form-data">
  
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">
        <label><?= ($sNoteType == 'file')?_("File Info"):_("Document Title") ?></label> 
      </h3>
      <input type="text" name="noteTitle" id="noteTitle" value="<?= $sTitleText ?>" size="30" maxlength="100" class="form-control" width="100%" style="width: 100%" placeholder="<?= ($sNoteType == 'file')?_("Set your File Info"):_("Set your Document title") ?>"  required="">
    </div>
    <div class="box-body">
      <div class="row" <?= (!empty($sNoteType))?"":'style="display: none;"' ?>>
          <div class="col-lg-3"></div>
          <div class="col-lg-6">
            <center><label><?= _("Your Note type is") ?> : "<?= MiscUtils::noteType($sNoteType) ?>"</label></center>
          </div>
          <div class="col-lg-3"></div>
      </div>
      <div class="row" <?= (empty($sNoteType))?"":'style="display: none;"' ?>>
          <div class="col-lg-3"></div>
          <div class="col-lg-3">
            <label><?= _("Choose your Note Type") ?> : </label>
          </div>
          <div class="col-lg-3">
            <select name="noteType" class="form-control input-sm" id="selectType">
              <option value="note" <?= ($sNoteType == "document")?'selected="selected"':"" ?>><?= MiscUtils::noteType("document") ?></option>
              <option value="video" <?= ($sNoteType == "video")?'selected="selected"':"" ?>><?= MiscUtils::noteType("video") ?></option>
              <option value="audio" <?= ($sNoteType == "audio")?'selected="selected"':"" ?>><?= MiscUtils::noteType("audio") ?></option>
              <?php 
                if ($iFamilyID == 0 && $uploadEDrive == true) { 
              ?>
                <option value="file" <?= ($sNoteType == "file")?'selected="selected"':"" ?>><?= MiscUtils::noteType("file") ?></option>
              <?php 
                } 
              ?>
            </select>           
          </div>
          <div class="col-lg-3"></div>
      </div>
      <div class="row">
        <div class="col-lg-12">
          <br/>
            <input type="hidden" name="PersonID" value="<?= $iPersonID ?>">
            <input type="hidden" name="FamilyID" value="<?= $iFamilyID ?>">
            <input type="hidden" name="NoteID" value="<?= $iNoteID ?>">
          <p align="center" id="blockText" <?= ($sNoteType == "file")?'style="display: none;"':"" ?>>
            <textarea id="NoteText" name="NoteText" style="width: 100%;min-height: 300px;" rows="40"><?= $sNoteText ?></textarea>
            <?= $sNoteTextError ?>
          </p>            
          <div id="blockFile"  <?= ($sNoteType == "file")?'':'style="display: none;' ?>
            <p align="center" >
              <label for="noteInputFile"><?= _("File input")." : ".$sNoteText ?></label>
              <input type="file" id="noteInputFile" name="noteInputFile">

              <?= _("Upload your file")?>.
            </p>
          </div>
          <p align="center">
            <input type="checkbox" value="1" name="Private" <?php if ($bPrivate != 0) {
        echo 'checked';
    } ?>>&nbsp;<?= _('Private') ?>
          </p>
        </div>
      </div>
    </div>
  </div>
  <p align="center">
    <input type="submit" class="btn btn-success" name="Submit" value="<?= ($uploadEDrive == true)?_("Upload"):_('Save') ?>">
    &nbsp;
    <input type="submit" class="btn btn-danger" name="Cancel" value="<?= _('Cancel') ?>" formnovalidate>
  </p>
</form>

<?php require 'Include/Footer.php' ?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">  
  var editor = CKEDITOR.replace('NoteText',{
      customConfig: '<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/configs/note_editor_config.js',
      language : window.CRM.lang,
      extraPlugins : 'uploadfile,uploadimage,filebrowser',
      uploadUrl: '/uploader/upload.php?type=privateDocuments',
      imageUploadUrl: '/uploader/upload.php?type=privateImages',
      filebrowserUploadUrl: '/uploader/upload.php?type=privateDocuments',
      filebrowserBrowseUrl: '/browser/browse.php?type=privateDocuments'
  });
    
  add_ckeditor_buttons(editor);
  
  $( "#selectType" ).change(function() {
    switch ($(this).val()) {
      case 'file':
        $("#blockText").fadeOut(100, function () {
          $("#blockFile").fadeIn(300);
        });
        break;
      default:
        $("#blockFile").fadeOut(100, function () {
          $("#blockText").fadeIn(300);
        });
    }
  });
</script>
