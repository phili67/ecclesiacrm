<?php
/*******************************************************************************
 *
 *  filename    : NoteDelete.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker, 2018 Philippe Logel
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\UserQuery;


//Set the page title
$sPageTitle = gettext('Note Delete Confirmation');

//Get the NoteID from the querystring
$iNoteID = InputUtils::LegacyFilterInput($_GET['NoteID'], 'int');

//Get the data on this note
$note = NoteQuery::create()->findPk($iNoteID);

$user = UserQuery::Create()->findPk($note->getPerId());

//If deleting a note for a person, set the PersonView page as the redirect
if ($note->getPerId() > 0) {
    $sReroute = 'PersonView.php?PersonID='.$note->getPerId();
}

//If deleting a note for a family, set the FamilyView page as the redirect
elseif ($note->getFamId() > 0) {
    $sReroute = 'FamilyView.php?FamilyID='.$note->getFamId();
}

$iCurrentFamID = $_SESSION['user']->getPerson()->getFamId();

// Security: User must have Notes permission
// Otherwise, re-direct them to the main menu.
if (!($_SESSION['user']->isNotesEnabled() || $note->getPerId() == $_SESSION['user']->getPersonId() || $note->getFamId() == $iCurrentFamID)) {
    Redirect('Menu.php');
    exit;
}


//Do we have confirmation?
if (isset($_GET['Confirmed'])) {
    $note = NoteQuery::create()->findPk($iNoteID);    
    $notes = NoteQuery::Create ()->filterByText ($note->getText())->findByPerId($note->getPerId());
    
    if ($note->getType () == 'file') {
    
      $target_delete_file = $user->getUserRootDir()."/".$note->getText();

      unlink($target_delete_file);
      
      $sReroute.= "&edrive=true";
    }
    
    if (!empty($notes) ) {
      $notes->delete();
    }

    //Send back to the page they came from
    Redirect($sReroute);
}

require 'Include/Header.php';

?>
<div class="box box-warning">
    <div class="box-header with-border">
      <h3 class="box-title">
        <label><?= gettext('Please confirm deletion of this note') ?> : <?= ($note->getType() == 'file')?$note->getText():$note->getTitle() ?></label> 
      </h3>
    </div>
  <div class="box-body">
    <?php 
      if ($note->getType() == 'file') {
    ?>
      <?= MiscUtils::embedFiles(SystemURLs::getRootPath()."/".$user->getUserRootDir()."/".$note->getText()) ?>
    <?php 
      } else {
    ?>
      <?= $note->getText() ?>
    <?php
     }
    ?>
  </div>
  <div class="box-footer">
    <a class="btn btn-primary" href="<?= $sReroute ?>"><?= gettext('Cancel') ?></a>
  	<a class="btn btn-danger" href="NoteDelete.php?Confirmed=Yes&NoteID=<?php echo $iNoteID ?>"><?= gettext('Yes, delete this record') ?></a> <?= gettext('(this action cannot be undone)') ?>
  </div>

<?php require 'Include/Footer.php' ?>
