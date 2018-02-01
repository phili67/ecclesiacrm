<?php
/*******************************************************************************
 *
 *  filename    : SelectDelete
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001-2003 Deane Barker, Lewis Franklin
 *




 *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\OutputUtils;

// Security: User must have Delete records permission
// Otherwise, re-direct them to the main menu.
if (!$_SESSION['bDeleteRecords']) {
    Redirect('Menu.php');
    exit;
}

// default values to make the newer versions of php happy
$iFamilyID = 0;
$iDonationFamilyID = 0;
$sMode = 'family';

if (!empty($_GET['FamilyID'])) {
    $iFamilyID = InputUtils::LegacyFilterInput($_GET['FamilyID'], 'int');
}
if (!empty($_GET['DonationFamilyID'])) {
    $iDonationFamilyID = InputUtils::LegacyFilterInput($_GET['DonationFamilyID'], 'int');
}
if (!empty($_GET['mode'])) {
    $sMode = $_GET['mode'];
}

if (isset($_GET['CancelFamily'])) {
    Redirect("FamilyView.php?FamilyID=$iFamilyID");
    exit;
}

$DonationMessage = '';

// Move Donations from 1 family to another
if ($_SESSION['bFinance'] && isset($_GET['MoveDonations']) && $iFamilyID && $iDonationFamilyID && $iFamilyID != $iDonationFamilyID) {
    $today = date('Y-m-d');
    $sSQL = "UPDATE pledge_plg SET plg_FamID='$iDonationFamilyID',
		plg_DateLastEdited ='$today', plg_EditedBy='" . $_SESSION['iUserID']
        . "' WHERE plg_FamID='$iFamilyID'";
    RunQuery($sSQL);

    $sSQL = "UPDATE egive_egv SET egv_famID='$iDonationFamilyID',
		egv_DateLastEdited ='$today', egv_EditedBy='" . $_SESSION['iUserID']
        . "' WHERE egv_famID='$iFamilyID'";
    RunQuery($sSQL);

    $DonationMessage = '<p><b><font color=red>' . gettext('All donations from this family have been moved to another family.') . '</font></b></p>';
}

//Set the Page Title
$sPageTitle = gettext('Family Delete Confirmation');

//Do we have deletion confirmation?
if (isset($_GET['Confirmed'])) {
    // Delete Family
    // Delete all associated Notes associated with this Family record
    $sSQL = 'DELETE FROM note_nte WHERE nte_fam_ID = ' . $iFamilyID;
    RunQuery($sSQL);

    // Delete Family pledges
    $sSQL = "DELETE FROM pledge_plg WHERE plg_PledgeOrPayment = 'Pledge' AND plg_FamID = " . $iFamilyID;
    RunQuery($sSQL);

    // Remove family property data
    $sSQL = "SELECT pro_ID FROM property_pro WHERE pro_Class='f'";
    $rsProps = RunQuery($sSQL);

    while ($aRow = mysqli_fetch_row($rsProps)) {
        $sSQL = 'DELETE FROM record2property_r2p WHERE r2p_pro_ID = ' . $aRow[0] . ' AND r2p_record_ID = ' . $iFamilyID;
        RunQuery($sSQL);
    }

    if (isset($_GET['Members'])) {
        // Delete all persons that were in this family
        PersonQuery::create()->filterByFamId($iFamilyID)->find()->delete();
    } else {
        // Reset previous members' family ID to 0 (undefined)
        $sSQL = 'UPDATE person_per SET per_fam_ID = 0 WHERE per_fam_ID = ' . $iFamilyID;
        RunQuery($sSQL);
    }

    // Delete the specified Family record
    $sSQL = 'DELETE FROM family_fam WHERE fam_ID = ' . $iFamilyID;
    RunQuery($sSQL);

    // Remove custom field data
    $sSQL = 'DELETE FROM family_custom WHERE fam_ID = ' . $iFamilyID;
    RunQuery($sSQL);

    // Delete the photo files, if they exist
    $photoThumbnail = 'Images/Family/thumbnails/' . $iFamilyID . '.jpg';
    if (file_exists($photoThumbnail)) {
        unlink($photoThumbnail);
    }
    $photoFile = 'Images/Family/' . $iFamilyID . '.jpg';
    if (file_exists($photoFile)) {
        unlink($photoFile);
    }

    // Redirect back to the family listing
    Redirect('FamilyList.php');
}


//Get the family record in question
$sSQL = 'SELECT * FROM family_fam WHERE fam_ID = ' . $iFamilyID;
$rsFamily = RunQuery($sSQL);
extract(mysqli_fetch_array($rsFamily));

require 'Include/Header.php';

?>
<div class="box">
    <div class="box-body">
        <?php
        // Delete Family Confirmation
        // See if this family has any donations OR an Egive association
        $sSQL = "SELECT plg_plgID FROM pledge_plg WHERE plg_PledgeOrPayment = 'Payment' AND plg_FamID = " . $iFamilyID;
        $rsDonations = RunQuery($sSQL);
        $bIsDonor = (mysqli_num_rows($rsDonations) > 0);

        if ($bIsDonor && !$_SESSION['bFinance']) {
            // Donations from Family. Current user not authorized for Finance
            echo '<p class="LargeText">' . gettext('Sorry, there are records of donations from this family. This family may not be deleted.') . '<br><br>';
            echo '<a href="FamilyView.php?FamilyID=' . $iFamilyID . '">' . gettext('Return to Family View') . '</a></p>';
        } elseif ($bIsDonor && $_SESSION['bFinance']) {
            // Donations from Family. Current user authorized for Finance.
            // Select another family to move donations to.
            echo '<p class="LargeText">' . gettext('WARNING: This family has records of donations and may NOT be deleted until these donations are associated with another family.') . '</p>';
            echo '<form name=SelectFamily method=get action=SelectDelete.php>';
            echo '<div class="ShadedBox">';
            echo '<div class="LightShadedBox"><strong>' . gettext('Family Name') . ':' . " $fam_Name</strong></div>";
            echo '<p>' . gettext('Please select another family with whom to associate these donations:');
            echo '<br><b>' . gettext('WARNING: This action can not be undone and may have legal implications!') . '</b></p>';
            echo "<input name=FamilyID value=$iFamilyID type=hidden>";
            echo '<select name="DonationFamilyID" class="form-control input-sm"><option value=0 selected>' . gettext('Unassigned') . '</option>';

            //Get Families for the drop-down
            $sSQL = 'SELECT fam_ID, fam_Name, fam_Address1, fam_City, fam_State FROM family_fam ORDER BY fam_Name';
            $rsFamilies = RunQuery($sSQL);
            // Build Criteria for Head of Household

            $head_criteria = ' per_fmr_ID = ' . SystemConfig::getValue('sDirRoleHead') ? SystemConfig::getValue('sDirRoleHead') : '1';
            // If more than one role assigned to Head of Household, add OR
            $head_criteria = str_replace(',', ' OR per_fmr_ID = ', $head_criteria);
            // Add Spouse to criteria
            if (intval(SystemConfig::getValue('sDirRoleSpouse')) > 0) {
                $head_criteria .= ' OR per_fmr_ID = ' . SystemConfig::getValue('sDirRoleSpouse');
            }
            // Build array of Head of Households and Spouses with fam_ID as the key
            $sSQL = 'SELECT per_FirstName, per_fam_ID FROM person_per WHERE per_fam_ID > 0 AND (' . $head_criteria . ') ORDER BY per_fam_ID';
            $rs_head = RunQuery($sSQL);
            $aHead = '';
            while (list($head_firstname, $head_famid) = mysqli_fetch_row($rs_head)) {
                if ($head_firstname && $aHead[$head_famid]) {
                    $aHead[$head_famid] .= ' & ' . $head_firstname;
                } elseif ($head_firstname) {
                    $aHead[$head_famid] = $head_firstname;
                }
            }
            while ($aRow = mysqli_fetch_array($rsFamilies)) {
                extract($aRow);
                echo '<option value="' . $fam_ID . '"';
                if ($fam_ID == $iFamilyID) {
                    echo ' selected';
                }
                echo '>' . $fam_Name;
                if ($aHead[$fam_ID]) {
                    echo ', ' . $aHead[$fam_ID];
                }
                if ($fam_ID == $iFamilyID) {
                    echo ' -- ' . gettext('CURRENT FAMILY WITH DONATIONS');
                } else {
                    echo ' ' . FormatAddressLine($fam_Address1, $fam_City, $fam_State);
                }
            }
            echo '</select><br><br>';
            echo '<input type="submit" class="btn btn-primary" name="CancelFamily" value="'.gettext("Cancel and Return to Family View").'"> &nbsp; &nbsp; ';
            echo '<input type="submit" class="btn btn-danger" name="MoveDonations" value="'.gettext("Move Donations to Selected Family").'">';
            echo '</div></form>';

            // Show payments connected with family
            // -----------------------------------
            echo '<br><br>';
            //Get the pledges for this family
            $sSQL = 'SELECT plg_plgID, plg_FYID, plg_date, plg_amount, plg_schedule, plg_method, 
		         plg_comment, plg_DateLastEdited, plg_PledgeOrPayment, a.per_FirstName AS EnteredFirstName, a.Per_LastName AS EnteredLastName, b.fun_Name AS fundName
				 FROM pledge_plg 
				 LEFT JOIN person_per a ON plg_EditedBy = a.per_ID
				 LEFT JOIN donationfund_fun b ON plg_fundID = b.fun_ID
				 WHERE plg_famID = ' . $iFamilyID . ' ORDER BY pledge_plg.plg_date';
            $rsPledges = RunQuery($sSQL); ?>
        <table cellspacing="0" width="100%" class="table table-striped table-bordered data-table">
          <theader>
            <tr>
                <th><?= gettext('Type') ?></th>
                <th><?= gettext('Fund') ?></th>
                <th><?= gettext('Fiscal Year') ?></th>
                <th><?= gettext('Date') ?></th>
                <th><?= gettext('Amount') ?></th>
                <th><?= gettext('Schedule') ?></th>
                <th><?= gettext('Method') ?></th>
                <th><?= gettext('Comment') ?></th>
                <th><?= gettext('Date Updated') ?></th>
                <th><?= gettext('Updated By') ?></th>
            </tr>
          </theader>
          <tbody>
            <?php
            $tog = 0;
            //Loop through all pledges
            while ($aRow = mysqli_fetch_array($rsPledges)) {
                $tog = (!$tog);
                $plg_FYID = '';
                $plg_date = '';
                $plg_amount = '';
                $plg_schedule = '';
                $plg_method = '';
                $plg_comment = '';
                $plg_plgID = 0;
                $plg_DateLastEdited = '';
                $plg_EditedBy = '';
                extract($aRow);

                //Alternate the row style
                if ($tog) {
                    $sRowClass = 'RowColorA';
                } else {
                    $sRowClass = 'RowColorB';
                }

                if ($plg_PledgeOrPayment == 'Payment') {
                    if ($tog) {
                        $sRowClass = 'PaymentRowColorA';
                    } else {
                        $sRowClass = 'PaymentRowColorB';
                    }
                } ?>
                <tr>
                    <td><?= gettext($plg_PledgeOrPayment) ?>&nbsp;</td>
                    <td><?= gettext($fundName) ?>&nbsp;</td>
                    <td><?= MakeFYString($plg_FYID) ?>&nbsp;</td>
                    <td><?= OutputUtils::change_date_for_place_holder($plg_date) ?>&nbsp;</td>
                    <td><?= $plg_amount ?>&nbsp;</td>
                    <td><?= gettext($plg_schedule) ?>&nbsp;</td>
                    <td><?= gettext($plg_method) ?>&nbsp;</td>
                    <td><?= $plg_comment ?>&nbsp;</td>
                    <td><?= OutputUtils::change_date_for_place_holder($plg_DateLastEdited) ?>&nbsp;</td>
                    <td><?= $EnteredFirstName . ' ' . $EnteredLastName ?>&nbsp;</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
          </table>
        <?php
        } else {
            // No Donations from family.  Normal delete confirmation
            echo $DonationMessage;
            echo "<p class='callout callout-warning'><b>" . gettext('Please confirm deletion of this family record:') . '</b><br/>';
            echo gettext('Note: This will also delete all Notes associated with this Family record.');
            echo gettext('(this action cannot be undone)') . '</p>';
            echo '<div>';
            echo '<strong>' . gettext('Family Name') . ':</strong>';
            echo '&nbsp;' . $fam_Name;
            echo '</div><br/>';
            echo '<div><strong>' . gettext('Family Members:') . '</strong><ul>';
            //List Family Members
            $sSQL = 'SELECT * FROM person_per WHERE per_fam_ID = ' . $iFamilyID;
            $rsPerson = RunQuery($sSQL);
            while ($aRow = mysqli_fetch_array($rsPerson)) {
                extract($aRow);
                echo '<li>' . $per_FirstName . ' ' . $per_LastName . '</li>';
                RunQuery($sSQL);
            }
            echo '</ul></div>';
            echo "<p class=\"text-center\"><a class='btn btn-danger' href=\"SelectDelete.php?Confirmed=Yes&FamilyID=" . $iFamilyID . '">' . gettext('Delete Family Record ONLY') . '</a> ';
            echo "<a class='btn btn-danger' href=\"SelectDelete.php?Confirmed=Yes&Members=Yes&FamilyID=" . $iFamilyID . '">' . gettext('Delete Family Record AND Family Members') . '</a> ';
            echo "<a class='btn btn-info' href=\"FamilyView.php?FamilyID=" . $iFamilyID . '">' . gettext('No, cancel this deletion') . '</a></p>';
        }
            ?>
    </div>
</div>

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
$(document).ready(function () {
  $(".data-table").DataTable({
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    responsive: true});
});
</script>

<?php require 'Include/Footer.php' ?>


