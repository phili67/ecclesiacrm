<?php
/*******************************************************************************
 *
 *  filename    : GroupPropsEditor.php
 *  last change : 2013-02-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2003 Chris Gebhardt (http://www.openserve.org)
 *                Copyright 2013 Michael Wilt
 *
 *  function    : Editor for the special properties of a group member
  *
 ******************************************************************************/

require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemURLs;

// Security: user must be allowed to edit records to use this page.
if (!SessionUser::getUser()->isEditRecordsEnabled()) {
    RedirectUtils::Redirect('Menu.php');
    exit;
}

$sPageTitle = _('Group Member Properties Editor');

// Get the Group and Person IDs from the querystring
$iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');
$iPersonID = InputUtils::LegacyFilterInput($_GET['PersonID'], 'int');

// Get some info about this person.  per_Country is needed in case there are phone numbers.
$sSQL = 'SELECT per_FirstName, per_LastName, per_Country, per_fam_ID FROM person_per WHERE per_ID = '.$iPersonID;
$rsPersonInfo = RunQuery($sSQL);
extract(mysqli_fetch_array($rsPersonInfo));

$fam_Country = '';

if ($per_fam_ID > 0) {
    $sSQL = 'SELECT fam_Country FROM family_fam WHERE fam_ID = '.$per_fam_ID;
    $rsFam = RunQuery($sSQL);
    extract(mysqli_fetch_array($rsFam));
}

$sPhoneCountry = SelectWhichInfo($per_Country, $fam_Country, false);

// Get the name of this group.
$sSQL = 'SELECT grp_Name FROM group_grp WHERE grp_ID = '.$iGroupID;
$rsGroupInfo = RunQuery($sSQL);
extract(mysqli_fetch_array($rsGroupInfo));

// We assume that the group selected has a special properties table and that it is populated
//  with values for each group member.

// Get the properties list for this group: names, descriptions, types and prop_ID for ordering;  will process later..

$sSQL = 'SELECT groupprop_master.* FROM groupprop_master
			WHERE grp_ID = '.$iGroupID.' ORDER BY prop_ID';
$rsPropList = RunQuery($sSQL);

$aPropErrors = [];

// Is this the second pass?
if (isset($_POST['GroupPropSubmit'])) {
    // Process all HTTP post data based upon the list of properties data we are expecting
    // If there is an error message, it gets assigned to an array of strings, $aPropErrors, for use in the form.

    $bErrorFlag = false;

    while ($rowPropList = mysqli_fetch_array($rsPropList, MYSQLI_BOTH)) {
        extract($rowPropList);

        $currentFieldData = InputUtils::LegacyFilterInput($_POST[$prop_Field]);

        $bErrorFlag |= !validateCustomField($type_ID, $currentFieldData, $prop_Field, $aPropErrors);

        // assign processed value locally to $aPersonProps so we can use it to generate the form later
        $aPersonProps[$prop_Field] = $currentFieldData;
    }

    // If no errors, then update.
    if (!$bErrorFlag) {
        mysqli_data_seek($rsPropList, 0);

        $sSQL = 'UPDATE groupprop_'.$iGroupID.' SET ';

        while ($rowPropList = mysqli_fetch_array($rsPropList, MYSQLI_BOTH)) {
            extract($rowPropList);
            $currentFieldData = trim($aPersonProps[$prop_Field]);

            sqlCustomField($sSQL, $type_ID, $currentFieldData, $prop_Field, $sPhoneCountry);
        }

        // chop off the last 2 characters (comma and space) added in the last while loop iteration.
        $sSQL = mb_substr($sSQL, 0, -2);

        $sSQL .= ' WHERE per_ID = '.$iPersonID;

        //Execute the SQL
        RunQuery($sSQL);

        // Return to the Person View
        RedirectUtils::Redirect('PersonView.php?PersonID='.$iPersonID.'&group=true');
    }
} else {
    // First Pass
    // we are always editing, because the record for a group member was created when they were added to the group

    // Get the existing data for this group member
    $sSQL = 'SELECT * FROM groupprop_'.$iGroupID.' WHERE per_ID = '.$iPersonID;
    $rsPersonProps = RunQuery($sSQL);
    $aPersonProps = mysqli_fetch_array($rsPersonProps, MYSQLI_BOTH);
}

require 'Include/Header.php';

if (mysqli_num_rows($rsPropList) == 0) {
    ?>
  <form>
    <h3><?= _('This group currently has no properties!  You can add them in the Group Editor.') ?></h3>
    <BR>
    <input type="button" class="btn" value="<?= _('Return to Person Record') ?>" Name="Cancel" onclick="javascript:document.location='<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= $iPersonID ?>';">
  </form>
  <?php
} else {
  ?>
  <p class="alert alert-warning"><span class="fa fa-exclamation-triangle"> <?= _("Warning: Field changes will be lost if you do not 'Save Changes' before using an up, down, delete, or 'add new' button!") ?></span></p>

  <div class="box ">
    <div class="box-header  with-border">
      <h3 class="box-title"><?= _('Editing') ?> : <i> <?= $grp_Name ?> </i> <?= _('data for member') ?> <i> <?= $per_FirstName.' '.$per_LastName ?> </i></h3>
    </div>
    <div class="box-body">
      <form method="post" action="<?= SystemURLs::getRootPath() ?>/GroupPropsEditor.php?<?= 'PersonID='.$iPersonID.'&GroupID='.$iGroupID ?>" name="GroupPropEditor">

        <table class="table">
          <?php

          // Make sure we're at the beginning of the properties list resource (2nd pass code used it)
          mysqli_data_seek($rsPropList, 0);

        while ($rowPropList = mysqli_fetch_array($rsPropList, MYSQLI_BOTH)) {
            extract($rowPropList); 
            if ($prop_PersonDisplay == 'false') continue;
            ?>
            <tr>
              <td><?= $prop_Name ?>: </td>
              <td>
                <?php
                $currentFieldData = trim($aPersonProps[$prop_Field]);

            if ($type_ID == 11) {
                $prop_Special = $sPhoneCountry;
            }  // ugh.. an argument with special cases!

            OutputUtils::formCustomField($type_ID, $prop_Field, $currentFieldData, $prop_Special, !isset($_POST['GroupPropSubmit']));

            if (array_key_exists($prop_Field, $aPropErrors)) {
                echo '<span style="color: red; ">'.$aPropErrors[$prop_Field].'</span>';
            } ?>
              </td>
              <td><?= OutputUtils::displayCustomField($type_ID, $prop_Description, $prop_Special) ?></td>
            </tr>
          <?php
        } ?>
          <tr>
            <td align="center" colspan="3">
              <br><br>
              <input type="submit" class="btn btn-primary" value="<?= _('Save') ?>" Name="GroupPropSubmit">
              &nbsp;
              <input type="button" class="btn" value="<?= _('Cancel') ?>" Name="Cancel" onclick="javascript:document.location='PersonView.php?PersonID=<?= $iPersonID ?>&group=true';">
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
  <?php
    } ?>

<?php 
require 'Include/Footer.php';
?>
