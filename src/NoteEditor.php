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

use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\UserQuery;


$iCurrentFamID = $_SESSION['user']->getPerson()->getFamId();

if (isset($_GET['PersonID'])) {
   $iFamily = PersonQuery::Create()->findOneById($_GET['PersonID'])->getFamId();
} else if (isset($_GET['FamilyID'])) {
   $iFamily = $_GET['FamilyID'];
} else {
  $iFamily = 0;
}

// Security: User must have Notes permission
// Otherwise, re-direct them to the main menu.
if (!($_SESSION['bNotes'] || $_GET['PersonID'] == $_SESSION['user']->getPersonId() || $iCurrentFamID == $iFamily)) {
    Redirect('Menu.php');
    exit;
}

//Set the page title
$sPageTitle = gettext('Document Editor');

if (isset($_GET['PersonID'])) {
    $iPersonID = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');
} else {
    $iPersonID = 0;
}

if (isset($_GET['FamilyID'])) {
    $iFamilyID = InputUtils::LegacyFilterInput($_GET['FamilyID'], 'int');
} else {
    $iFamilyID = 0;
}

//To which page do we send the user if they cancel?
if ($iPersonID > 0) {
    $sBackPage = 'PersonView.php?PersonID='.$iPersonID;
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
        $sNoteTextError = '<br><span style="color: red;">You must enter text for this note.</span>';
        $bErrorFlag = true;
    }

    //Were there any errors?
    if (!$bErrorFlag) {
      if (!empty($_FILES["noteInputFile"]["name"])) {
        $user = UserQuery::create()->findOneByPersonId($iPersonID);    
        
        $target_dir = $user->getHomedir();
        $target_file = $target_dir . "/".basename($_FILES["noteInputFile"]["name"]);
      
        if (move_uploaded_file($_FILES['noteInputFile']['tmp_name'], $target_file)) {
          echo "OK";
          //Are we adding or editing?
          if ($iNoteID <= 0) {
            $note = new Note();
            $note->setPerId($iPersonID);
            $note->setFamId($iFamilyID);
            $note->setPrivate($bPrivate);
            $note->setText(str_replace("private/userdir/","",$target_file));
            $note->setType('file');
            $note->setEntered($_SESSION['iUserID']);
            $note->setInfo(gettext('Create file'));
            
            $note->save();
          } else {
            $note = NoteQuery::create()->findPk($iNoteID);
            $target_delete_file = "private/userdir/".$note->getText();

            unlink($target_delete_file);
            
            $note->setPrivate($bPrivate);
            $note->setText(str_replace("private/userdir/","",$target_file));
            $note->setDateLastEdited(new DateTime());
            $note->setEditedBy($_SESSION['iUserID']);
            $note->setType('file');            
            $note->setInfo(gettext('Update file'));
            
            $note->save();
          }
        } else {
          echo "Pas OK";
        }    
        
        Redirect($sBackPage);
      }


      //Are we adding or editing?
      if ($iNoteID <= 0) {
          $note = new Note();
          $note->setPerId($iPersonID);
          $note->setFamId($iFamilyID);
          $note->setPrivate($bPrivate);
          $note->setText($sNoteText);
          $note->setType($_POST['noteType']);
          $note->setEntered($_SESSION['iUserID']);
          $note->save();
      } else {
          $note = NoteQuery::create()->findPk($iNoteID);
          $note->setPrivate($bPrivate);
          $note->setText($sNoteText);
          $note->setDateLastEdited(new DateTime());
          $note->setEditedBy($_SESSION['iUserID']);
          $note->setType($_POST['noteType']);
          $note->save();
      }

      //Send them back to whereever they came from
      Redirect($sBackPage);
    }
} else {
    //Are we adding or editing?
    if (isset($_GET['NoteID'])) {
        //Get the NoteID from the querystring
        $iNoteID = InputUtils::LegacyFilterInput($_GET['NoteID'], 'int');
        $dbNote = NoteQuery::create()->findPk($iNoteID);

        //Assign everything locally
        $sNoteText = $dbNote->getText();
        $bPrivate = $dbNote->getPrivate();
        $iPersonID = $dbNote->getPerId();
        $iFamilyID = $dbNote->getFamId();
        $sNoteType = $dbNote->getType();
    }
}
require 'Include/Header.php';

?>
<form method="post"<?= SystemURLs::getRootPath() ?>/NoteEditor.php" enctype="multipart/form-data">
  <div class="box box-primary">
    <div class="box-body">
      <div class="row">
          <div class="col-lg-3"></div>
          <div class="col-lg-3">
            <label><?= gettext("Choose your Document Type") ?> : </label>
          </div>
          <div class="col-lg-3">
            <select name="noteType" class="form-control input-sm" id="selectType">
              <option value="note" <?= ($sNoteType == "note")?'selected="selected"':"" ?>><?= gettext("Classic Document") ?></option>
              <option value="video" <?= ($sNoteType == "video")?'selected="selected"':"" ?>><?= gettext("Classic Video") ?></option>
              <option value="file" <?= ($sNoteType == "file")?'selected="selected"':"" ?>><?= gettext("Classic File") ?></option>
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
          <p align="center" id="blockFile"  <?= ($sNoteType == "file")?'':'style="display: none;' ?>>
            <label for="noteInputFile"><?= gettext("File input")." : ".$sNoteText ?></label>
            <input type="file" id="noteInputFile" name="noteInputFile">

            <?= gettext("Upload your file")?>.
          </p>
          <p align="center">
            <input type="checkbox" value="1" name="Private" <?php if ($bPrivate != 0) {
        echo 'checked';
    } ?>>&nbsp;<?= gettext('Private') ?>
          </p>
        </div>
      </div>
    </div>
  </div>
  <p align="center">
    <input type="submit" class="btn btn-success" name="Submit" value="<?= gettext('Save') ?>">
    &nbsp;
    <input type="button" class="btn btn-danger" name="Cancel" value="<?= gettext('Cancel') ?>" onclick="javascript:document.location='<?= $sBackPage ?>';">

  </p>
</form>

<?php require 'Include/Footer.php' ?>

<script src="<?= SystemURLs::getRootPath() ?>/skin/external/ckeditor/ckeditor.js"></script>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  CKEDITOR.replace('NoteText',{
    customConfig: '<?= SystemURLs::getRootPath() ?>/skin/js/ckeditor/note_editor_config.js',
    language : window.CRM.lang
  });
  
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
