<?php
/*******************************************************************************
 *
 *  filename    : CartToFamily.php
 *  last change : 2003-10-09
 *  description : Add cart records to a family
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2003 Chris Gebhardt
 *            2018 Philippe Logel
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';
require 'Include/CountryDropDown.php';
require 'Include/StateDropDown.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;

// Security: User must have add records permission
if (!$_SESSION['bAddRecords']) {
    Redirect('Menu.php');
    exit;
}

// Was the form submitted?
if (isset($_POST['Submit']) && count($_SESSION['aPeopleCart']) > 0) {

    // Get the FamilyID
    $iFamilyID = InputUtils::LegacyFilterInput($_POST['FamilyID'], 'int');
    
    // Are we creating a new family
    if ($iFamilyID == 0) {
        $sFamilyName = InputUtils::LegacyFilterInput($_POST['FamilyName']);

        $dWeddingDate = InputUtils::LegacyFilterInput($_POST['WeddingDate']);
        if (strlen($dWeddingDate) > 0) {
            $dWeddingDate = '"'.$dWeddingDate.'"';
        } else {
            $dWeddingDate = 'NULL';
        }

        $iPersonAddress = InputUtils::LegacyFilterInput($_POST['PersonAddress']);

        if ($iPersonAddress != 0) {
            $sSQL = 'SELECT * FROM person_per WHERE per_ID = '.$iPersonAddress;
            $rsPerson = RunQuery($sSQL);
            extract(mysqli_fetch_array($rsPerson));
        }

        SelectWhichAddress($sAddress1, $sAddress2, InputUtils::LegacyFilterInput($_POST['Address1']), InputUtils::LegacyFilterInput($_POST['Address2']), $per_Address1, $per_Address2, false);
        $sCity = SelectWhichInfo(InputUtils::LegacyFilterInput($_POST['City']), $per_City);
        $sZip = SelectWhichInfo(InputUtils::LegacyFilterInput($_POST['Zip']), $per_Zip);
        $sCountry = SelectWhichInfo(InputUtils::LegacyFilterInput($_POST['Country']), $per_Country);

        if ($sCountry == 'United States' || $sCountry == 'Canada') {
            $sState = InputUtils::LegacyFilterInput($_POST['State']);
        } else {
            $sState = InputUtils::LegacyFilterInput($_POST['StateTextbox']);
        }
        $sState = SelectWhichInfo($sState, $per_State);

        // Get and format any phone data from the form.
        $sHomePhone = InputUtils::LegacyFilterInput($_POST['HomePhone']);
        $sWorkPhone = InputUtils::LegacyFilterInput($_POST['WorkPhone']);
        $sCellPhone = InputUtils::LegacyFilterInput($_POST['CellPhone']);
        if (!isset($_POST['NoFormat_HomePhone'])) {
            $sHomePhone = CollapsePhoneNumber($sHomePhone, $sCountry);
        }
        if (!isset($_POST['NoFormat_WorkPhone'])) {
            $sWorkPhone = CollapsePhoneNumber($sWorkPhone, $sCountry);
        }
        if (!isset($_POST['NoFormat_CellPhone'])) {
            $sCellPhone = CollapsePhoneNumber($sCellPhone, $sCountry);
        }

        $sHomePhone = SelectWhichInfo($sHomePhone, $per_HomePhone);
        $sWorkPhone = SelectWhichInfo($sWorkPhone, $per_WorkPhone);
        $sCellPhone = SelectWhichInfo($sCellPhone, $per_CellPhone);
        $sEmail = SelectWhichInfo(InputUtils::LegacyFilterInput($_POST['Email']), $per_Email);

        if (strlen($sFamilyName) == 0) {
            $sError = '<p class="callout callout-warning" align="center" style="color:red;">'.gettext('No family name entered!').'</p>';
            $bError = true;
        } else {
            $sSQL = "INSERT INTO family_fam (fam_Name, fam_Address1, fam_Address2, fam_City, fam_State, fam_Zip, fam_Country, fam_HomePhone, fam_WorkPhone, fam_CellPhone, fam_Email, fam_WeddingDate, fam_DateEntered, fam_EnteredBy) VALUES ('".$sFamilyName."','".$sAddress1."','".$sAddress2."','".$sCity."','".$sState."','".$sZip."','".$sCountry."','".$sHomePhone."','".$sWorkPhone."','".$sCellPhone."','".$sEmail."',".$dWeddingDate.",'".date('YmdHis')."',".$_SESSION['iUserID'].')';
            RunQuery($sSQL);

            //Get the key back
            $sSQL = 'SELECT MAX(fam_ID) AS iFamilyID FROM family_fam';
            $rsLastEntry = RunQuery($sSQL);
            extract(mysqli_fetch_array($rsLastEntry));
        }
    }

    if (!$bError) {
        // Loop through the cart array
        $iCount = 0;
        while ($element = each($_SESSION['aPeopleCart'])) {
            $iPersonID = $_SESSION['aPeopleCart'][$element[key]];
            $ormPerson = PersonQuery::Create()
                         ->findOneById($iPersonID);

            // Make sure they are not already in a family
            if ($ormPerson->getFamId() == 0) {
                $iFamilyRoleID = 0;

                if (isset($_POST['role'.$iPersonID])) {
                    $iFamilyRoleID = InputUtils::LegacyFilterInput($_POST['role'.$iPersonID], 'int');
                }
                
                $ormPerson->setFamId($iFamilyID);
                $ormPerson->setFmrId($iFamilyRoleID);
                $ormPerson->save();

                $iCount++;
            }
        }

        $sGlobalMessage = $iCount.' records(s) successfully added to selected Family.';

        Redirect('FamilyView.php?FamilyID='.$iFamilyID.'&Action=EmptyCart');
    }
}

// Set the page title and include HTML header
$sPageTitle = gettext('Add Cart to Family');
require 'Include/Header.php';

echo $sError;
?>
<form method="post">
<div class="box">
<?php
if (count($_SESSION['aPeopleCart']) > 0) {

    // Get all the families
    $ormFamilies = FamilyQuery::Create()
                    ->orderByName()
                    ->find();
                    
    // Get the family roles
    $ormFamilyRoles = ListOptionQuery::Create()
          ->filterById(2)
          ->orderByOptionSequence()
          ->find();


    $sRoleOptionsHTML = '';
    foreach ($ormFamilyRoles as $ormFamilyRole) {
        $sRoleOptionsHTML .= '<option value="'.$ormFamilyRole->getOptionId().'">'.$ormFamilyRole->getOptionName().'</option>';
    }

    $ormCartItems = PersonQuery::Create()
                ->Where('per_ID IN ('.ConvertCartToString($_SESSION['aPeopleCart']).')')
                ->orderByLastName()
                ->find();
    ?>
  <table class='table table-hover dt-responsive'>
    <tr>
    <td>&nbsp;</td>
    <td><b><?= gettext('Name') ?></b></td>
    <td align="center"><b><?= gettext('Assign Role') ?></b></td>

    <?php
    $count = 1;    
    foreach ($ormCartItems as $ormCartItem) {
        $sRowClass = AlternateRowStyle($sRowClass);
        ?>
        <tr class="<?= $sRowClass ?>">
          <td align="center"><?= $count++ ?></td>
          <td><img src="<?= SystemURLs::getRootPath()?>/api/persons/<?= $ormCartItem->getId() ?>/thumbnail" class="direct-chat-img"> &nbsp <a href="PersonView.php?PersonID=<?= $ormCartItem->getId() ?>"><?= FormatFullName($ormCartItem->getTitle(), $ormCartItem->getFirstName(), $ormCartItem->getMiddleName(), $ormCartItem->getLastName(), $ormCartItem->getSuffix(), 1) ?></a></td>
          <td align="center">
          <?php
            if ($ormCartItem->getFamId() == 0) {
              ?>
                  <select name="role<?= $ormCartItem->getId() ?>" class="form-control"><?= $sRoleOptionsHTML ?></select>
              <?php
            } else {
              ?>
                  <?= gettext('Already in a family') ?>
              <?php
            }
          ?>
          </td>
        </tr>
    <?php
    }
    ?>

       </table>
</div>
<div class="box">
<div class="table-responsive">
<table align="center" class="table table-hover" id="cart-family-table" width="100%">
  <thead>
		<tr>
				<th></th>
				<th></th>
		</tr>
	</thead>
	<tbody>
    <tr>
    <td class="LabelColumn"><?= gettext('Add to Family') ?>:</td>
    <td class="TextColumn">
        <select name="FamilyID"  class="form-control">
              <option value="0"><?= gettext('Create new family') ?></option>
      <?php            
        // Create the family select drop-down
        foreach ($ormFamilies as $ormFamily) {
        ?>
          <option value="<?= $ormFamily->getId()?>"><?= $ormFamily->getName() ?></option>
        <?php
        }
        ?>
       </select>
    </td>
  </tr>

  <tr>
    <td></td>
    <td><p class="MediumLargeText"><?= gettext('If adding a new family, enter data below.') ?></p></td>
  </tr>


  <tr>
    <td class="LabelColumn"><?= gettext('Family Name') ?>:</td>
    <td class="TextColumnWithBottomBorder"><input type="text" Name="FamilyName" value="<?= $sName ?>" maxlength="48"><font color="red"><?= $sNameError ?></font></td>
  </tr>

  <tr>
        <td class="LabelColumn"><?= gettext('Wedding Date') ?>:</td>
    <td class="TextColumnWithBottomBorder"><input type="text" Name="WeddingDate" value="<?= $dWeddingDate ?>" maxlength="10" id="sel1" size="15"  class="form-control active date-picker"><font color="red"><?php echo '<BR>'.$sWeddingDateError ?></font></td>
  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('Use address/contact data from') ?>:</td>
    <td class="TextColumn">
      <select name="PersonAddress"  class="form-control">
         <option value="0"><?= gettext('Only the new data below') ?></option>

      <?php 
      foreach ($ormCartItems as $ormCartItem) {
        if ($ormCartItem->getFamId() == 0) {
        ?>
           <option value="<?= $ormCartItem->getId() ?>"><?= $ormCartItem->getFirstName()?> <?= $ormCartItem->getLastName() ?></option>
        <?php      
        }
      }
      ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('Address') ?> 1:</td>
    <td class="TextColumn"><input type="text" Name="Address1" value="<?= $sAddress1 ?>" size="50" maxlength="250"></td>
  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('Address') ?> 2:</td>
    <td class="TextColumn"><input type="text" Name="Address2" value="<?= $sAddress2 ?>" size="50" maxlength="250"></td>
  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('City') ?>:</td>
    <td class="TextColumn"><input type="text" Name="City" value="<?= $sCity ?>" maxlength="50"></td>
  </tr>

  <tr <?= (SystemConfig::getValue('sStateUnuseful'))?"style=\"display: none;\"":""?>>
    <td class="LabelColumn"><?= gettext('State') ?>:</td>
    <td class="TextColumn">
      <?php                          
          $statesDD = new StateDropDown();     
          echo $statesDD->getDropDown($sState);
      ?>
      OR
      <input type="text" name="StateTextbox" value="<?php if ($sCountry != 'United States' && $sCountry != 'Canada') {
            echo $sState;
        } ?>" size="20" maxlength="30">
      <BR><?= gettext('(Use the textbox for countries other than US and Canada)') ?>
    </td>
  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('Zip')?>:</td>
    <td class="TextColumn">
      <input type="text" Name="Zip" value="<?= $sZip ?>" maxlength="10" size="8">
    </td>

  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('Country') ?>:</td>
    <td class="TextColumnWithBottomBorder">
      <?= CountryDropDown::getDropDown($sCountry); ?>
    </td>
  </tr>

  <tr>
    <td>&nbsp;</td><td></td>
  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('Home Phone') ?>:</td>
    <td class="TextColumn">
      <input type="text" Name="HomePhone" value="<?= $sHomePhone ?>" size="30" maxlength="30">
      <input type="checkbox" name="NoFormat_HomePhone" value="1" <?php if ($bNoFormat_HomePhone) {
            echo ' checked';
        } ?>><?= gettext('Do not auto-format') ?>
    </td>
  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('Work Phone') ?>:</td>
    <td class="TextColumn">
      <input type="text" name="WorkPhone" value="<?php echo $sWorkPhone ?>" size="30" maxlength="30">
      <input type="checkbox" name="NoFormat_WorkPhone" value="1" <?php if ($bNoFormat_WorkPhone) {
            echo ' checked';
        } ?>><?= gettext('Do not auto-format') ?>
    </td>
  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('Mobile Phone') ?>:</td>
    <td class="TextColumn">
      <input type="text" name="CellPhone" value="<?php echo $sCellPhone ?>" size="30" maxlength="30">
      <input type="checkbox" name="NoFormat_CellPhone" value="1" <?php if ($bNoFormat_CellPhone) {
            echo ' checked';
        } ?>><?= gettext('Do not auto-format') ?>
    </td>
  </tr>

  <tr>
    <td class="LabelColumn"><?= gettext('Email') ?>:</td>
    <td class="TextColumnWithBottomBorder"><input type="text" Name="Email" value="<?= $sEmail ?>" size="30" maxlength="50"></td>
  </tr>
</tbody>
</table>
</div>
<p align="center">
<BR>
<input type="submit" class="btn btn-primary" name="Submit" value="<?= gettext('Add to Family') ?>">
<BR><BR>
</p>
<?php
} else {
            echo "<p align=\"center\" class='callout callout-warning'>".gettext('Your cart is empty!').'</p>';
        }
?>
</div>
</form>


<script  nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(document).ready(function() {
        $("#country-input").select2();
        $("#state-input").select2();
        
        $("#cart-family-table").DataTable({
            responsive:true,
            paging: false,
            searching: false,
            ordering: false,
            info:     false,
            //dom: window.CRM.plugin.dataTable.dom,
            fnDrawCallback: function( settings ) {
              $("#selector thead").remove(); 
            }
        });
    });
</script>
<?php require 'Include/Footer.php'; ?>
