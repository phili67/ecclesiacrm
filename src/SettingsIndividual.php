<?php
/*******************************************************************************
*
*  filename    : SettingsIndividual.php
*  website     : http://www.ecclesiacrm.com
*  description : Page where users can modify their own settings
*                   File copied from SettingsUser.php with minor edits.
*
*  Contributors:
*  2006 Ed Davis

******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;

$iPersonID = $_SESSION['user']->getPersonId();

// Save Settings
if (isset($_POST['save'])) {
    $new_value = $_POST['new_value'];
    $type = $_POST['type'];
    ksort($type);
    reset($type);
    while ($current_type = current($type)) {
        $id = key($type);
        // Filter Input
        if ($current_type == 'text' || $current_type == 'textarea') {
            $value = InputUtils::LegacyFilterInput($new_value[$id]);
        } elseif ($current_type == 'number') {
            $value = InputUtils::LegacyFilterInput($new_value[$id], 'float');
        } elseif ($current_type == 'date') {
            $value = InputUtils::LegacyFilterInput($new_value[$id], 'date');
        } elseif ($current_type == 'boolean') {
            if ($new_value[$id] != '1') {
                $value = '';
            } else {
                $value = '1';
            }
        }
        // We can't update unless values already exist.
        $sSQL = 'SELECT * FROM userconfig_ucfg '
        ."WHERE ucfg_id=$id AND ucfg_per_id=$iPersonID ";
        $bRowExists = true;
        $iNumRows = mysqli_num_rows(RunQuery($sSQL));
        if ($iNumRows == 0) {
            $bRowExists = false;
        }

        if (!$bRowExists) { // If Row does not exist then insert default values.
            // Defaults will be replaced in the following Update
            $sSQL = 'SELECT * FROM userconfig_ucfg '
            ."WHERE ucfg_id=$id AND ucfg_per_id=0 ";
            $rsDefault = RunQuery($sSQL);
            $aDefaultRow = mysqli_fetch_row($rsDefault);
            if ($aDefaultRow) {
                list($ucfg_per_id, $ucfg_id, $ucfg_name, $ucfg_value, $ucfg_type,
                    $ucfg_tooltip, $ucfg_permission) = $aDefaultRow;

                $sSQL = "INSERT INTO userconfig_ucfg VALUES ($iPersonID, $id, "
                ."'$ucfg_name', '$ucfg_value', '$ucfg_type', '$ucfg_tooltip', "
                ."$ucfg_permission, ' ')";
                $rsResult = RunQuery($sSQL);
            } else {
                echo '<BR> Error: Software BUG 3216';
                exit;
            }
        }

        // Save new setting
        $sSQL = 'UPDATE userconfig_ucfg '
        ."SET ucfg_value='$value' "
        ."WHERE ucfg_id=$id AND ucfg_per_id=$iPersonID ";
        $rsUpdate = RunQuery($sSQL);
        next($type);
    }
    
    Redirect('SettingsIndividual.php');// to reflect the tooltip change, we have to refresh the page
}

// Set the page title and include HTML header
$sPageTitle = gettext('My User Settings');
require 'Include/Header.php';

// Get settings
$sSQL = 'SELECT * FROM userconfig_ucfg WHERE ucfg_per_id='.$iPersonID
.' ORDER BY ucfg_id';
$rsConfigs = RunQuery($sSQL);
?>
<div class="box box-body">
<form method=post action=SettingsIndividual.php>
<div class="row">
  <div class="col-md-12">
<table class="table table-hover data-person data-table no-footer dtr-inline dataTable" id="user-listing-table" style="width:100%;">
<thead>
<tr>
  <th><?= gettext('Variable name') ?></th>
  <th><?= gettext('Current Value')?></th>
  <th><?= gettext('Notes')?></h3></th>
</tr>
</thead>
<tbody>
<?php
$r = 1;
// List Individual Settings
while (list($ucfg_per_id, $ucfg_id, $ucfg_name, $ucfg_value, $ucfg_type, $ucfg_tooltip, $ucfg_permission) = mysqli_fetch_row($rsConfigs)) {
    if (!(($ucfg_permission == 'TRUE') || $_SESSION['user']->isAdmin())) {
        continue;
    } // Don't show rows that can't be changed : BUG, you must continue the loop, and not break it PL

    // Cancel, Save Buttons every 20 rows
    if ($r == 20) {
        echo "<tr><td>&nbsp;</td>
      <input type=submit class='btn btn-default' name=cancel value='".gettext('Cancel')."'>
      <td><input type=submit class='btn btn-primary' name=save value='".gettext('Save Settings')."'>
      </td></tr>";
        $r = 1;
    }

    // Variable Name & Type
    echo '<tr><td class=LabelColumn>'.$ucfg_name;
    echo '<input type=hidden name="type['.$ucfg_id.']" value="'.$ucfg_type.'"></td>';

    // Current Value
    if ($ucfg_type == 'text') {
        echo "<td class=TextColumnWithBottomBorder>
      <input class=\"form-control input-md\" type=text size=30 maxlength=255 name='new_value[$ucfg_id]'
      value='".htmlspecialchars($ucfg_value, ENT_QUOTES)."'></td>";
    } elseif ($ucfg_type == 'textarea') {
        echo "<td class=TextColumnWithBottomBorder>
      <textarea rows=4 cols=30 name='new_value[$ucfg_id]'>"
            .htmlspecialchars($ucfg_value, ENT_QUOTES).'</textarea></td>';
    } elseif ($ucfg_type == 'number' || $ucfg_type == 'date') {
        echo '<td class=TextColumnWithBottomBorder><input type=text size=15 maxlength=15 name='
            ."'new_value[$ucfg_id]' value='$ucfg_value'></td>";
    } elseif ($ucfg_type == 'boolean') {
        if ($ucfg_value) {
            $sel2 = 'SELECTED';
            $sel1 = '';
        } else {
            $sel1 = 'SELECTED';
            $sel2 = '';
        }
        echo "<td class=TextColumnWithBottomBorder><select class=\"form-control input-sm \" name=\"new_value[$ucfg_id]\">";
        echo "<option value='' $sel1>".gettext('False');
        echo "<option value='1' $sel2>".gettext('True');
        echo '</select></td>';
    }

    // Notes
    echo '<td>'.gettext($ucfg_tooltip).'</td>  </tr>';
    $r++;
}
?>
</tbody>
</table>
</div>
</div>
<div class="row">
  <div class="col-md-2">
  </div>
  <div class="col-md-6">
      <input type=submit class='btn btn-default' name=cancel value="<?= gettext('Cancel') ?>">
      <input type=submit class='btn btn-primary'  name=save value="<?= gettext('Save Settings') ?>">
  </div>
</div>
</form>


</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>" >
    $(document).ready(function () {
        $(".data-table").DataTable({
          "language": {
            "url": window.CRM.plugin.dataTable.language.url
          },
          pageLength: 100,
          info: false,
          bSort : false,
          searching: false, paging: false,
          responsive: true
        });
    });
</script>
<?php
require 'Include/Footer.php';
?>
