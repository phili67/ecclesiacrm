<?php
/*******************************************************************************
 *
 *  filename    : CSVImport.php
 *  last change : 2003-10-02
 *  description : Tool for importing CSV person data into InfoCentral
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2003 Chris Gebhardt
 *  Copyright 2025 Philippe Logel
 *
 ******************************************************************************/

// Include the function library

use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\FamilyCustomQuery;
use EcclesiaCRM\FamilyCustom;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Family;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PersonCustom;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\ListOptionQuery;

use EcclesiaCRM\Note;


use EcclesiaCRM\Utils\CSVImport\FamilyImportUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\MiscUtils;


use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\CountryDropDown;
use EcclesiaCRM\SessionUser;
use Propel\Runtime\Propel;

// Set the page title and include HTML header

require $sRootDocument . '/Include/Header.php';
?>

<div class="card import-users" style="display:block;">
<div class="card-header border-1">
   <h3 class="card-title"><?= _('Import Data')?></h3>
</div>
<div class="card-body">

<?php

$iStage = 1;
$csvError = '';

$connection = Propel::getConnection();

if (isset($_POST['iSelectedValues'])) {
  $iSelectedValues = $_POST['iSelectedValues'];
}

// Is the CSV file being uploaded?
if (isset($_POST['UploadCSV']) || isset($_POST['iSelectedValues']) && $iSelectedValues < 3) {
    $generalCSVSeparator = ',';

    if (isset($_POST['sSeperator'])) {
      $generalCSVSeparator = $_POST['sSeperator'];
    }

    // Check if a valid CSV file was actually uploaded
    if ($_FILES['CSVfile']['name'] == '' && !isset($_POST['iSelectedValues'])) {
        $csvError = _('No file selected for upload.');
    }

    // Valid file, so save it and display the import mapping form.
    else {
        $csvTempFile = 'import.csv';
        $system_temp = ini_get('session.save_path');
        if (strlen($system_temp) > 0) {
            $csvTempFile = $system_temp.'/'.$csvTempFile;
        }
        move_uploaded_file($_FILES['CSVfile']['tmp_name'], $csvTempFile);

        // create the file pointer
        $pFile = fopen($csvTempFile, 'r');

        // count # lines in the file
        $iNumRows = 0;
        while ($tmp = fgets($pFile, 2048)) {
            $iNumRows++;
        }
        rewind($pFile);

        // create the form?>
        <form method="post" action="<?= $sRootPath ?>/v2/system/csv/import">
          <input type="hidden" name="sSeperator" value="<?= $generalCSVSeparator ?>">
          <input type="hidden" name="iSelectedValues" value="0" id="selectedValues">

        <label><?= _('Total number of rows in the CSV file:') ?></label> <b><?= $iNumRows ?></b>
        <BR>
        <table class="table horizontal-scroll" id="importTable" border=1 rules="all">
      <?php
        // grab and display up to the first 8 lines of data in the CSV in a table
        $iRow = 0;
        $numCol = -10;

        while (($aData = fgetcsv($pFile, 2048, $generalCSVSeparator)) && $iRow++ < 9) {
            $tempNumCol = count($aData);

            if ($numCol < $tempNumCol) {
              $numCol = $tempNumCol;
            }
      ?>
          <tr>
      <?php
            for ($col = 0; $col < $numCol; $col++) {
      ?>
            <td><?= (($iRow == 1)?'<b>':'').$aData[$col].(($iRow == 1)?'</b>':'') ?>&nbsp;</td>
      <?php
            }
      ?>
          </tr>
      <?php
        }

        fclose($pFile);

        $ormPersonCustomMasterFields = PersonCustomMasterQuery::create()
          ->orderByCustomOrder()
          ->find();

        $sPerCustomFieldList = '';
        foreach ($ormPersonCustomMasterFields as $customField) {
          if ($customField->getTypeId() != 9 and $customField->getTypeId() != 12) {
              $sPerCustomFieldList .= '<option value="'.$customField->getCustomField().'">'.$customField->getCustomName()."</option>\n";
          }
        }

        $ormFamilyCustomMasterFields = FamilyCustomMasterQuery::create()
          ->orderByCustomOrder()
          ->find();

        $sFamCustomFieldList = '';
        foreach ($ormFamilyCustomMasterFields as $customField) {
          if ($customField->getTypeId() != 9 and $customField->getTypeId() != 12) {
              $sPerCustomFieldList .= '<option value="'.$customField->getCustomField().'">'.$customField->getCustomName()."</option>\n";
          }
        }



        // add select boxes for import destination mapping
        for ($col = 0; $col < $numCol; $col++) {
            ?>
            <td>
            <select name="<?= 'col'.$col ?>" class="columns" class= "form-control form-control-sm" id="col<?= $col ?>"  data-col="<?= $col ?>" data-numcol="<?= $numCol ?>">
                <option value="0"><?= _('Ignore this Field') ?></option>
                <option value="1"><?= _('Title') ?></option>
                <option value="2"><?= _('First Name') ?></option>
                <option value="3"><?= _('Middle Name') ?></option>
                <option value="4"><?= _('Last Name') ?></option>
                <option value="5"><?= _('Suffix') ?></option>
                <option value="6"><?= _('Gender') ?></option>
                <option value="7"><?= _('Donation Envelope') ?></option>
                <option value="8"><?= _('Address') ?> 1</option>
                <option value="9"><?= _('Address') ?> 2</option>
                <option value="10"><?= _('City') ?></option>
                <option value="11"><?= _('State') ?></option>
                <option value="12"><?= _('Zip') ?></option>
                <option value="13"><?= _('Country') ?></option>
                <option value="14"><?= _('Home Phone') ?></option>
                <option value="15"><?= _('Work Phone') ?></option>
                <option value="16"><?= _('Cell Phone') ?></option>
                <option value="17"><?= _('Email') ?></option>
                <option value="18"><?= _('Work / Other Email') ?></option>
                <option value="19"><?= _('Birth Date') ?></option>
                <option value="20"><?= _('Membership Date') ?></option>
                <option value="21"><?= _('Wedding Date') ?></option>
                <?= $sPerCustomFieldList.$sFamCustomFieldList ?>
            </select>
            </td>
          <?php
            }
          ?>
        </table>
        <?php
          if (isset($_POST['iSelectedValues']) && $iSelectedValues < 3) {
        ?>
            <div class="alert alert-danger">
              <?= _("An error occurs when you import the CSV file. You've to select values above in the select fields.") ?>
            </div>
            <br>

        <?php
          }
        ?>
        <div class="row" style="margin-top:-10px">
          <div class="col-lg-12">
            <span style="color:blue;float:right"><?= _("Scroll right to see the other columns") ?></span>
            <span style="color:red;float:left">• <?= _("Check the right <b>Date format</b> and to chose it below !!!!!") ?></span><br>
            <span style="color:red;float:left">• <?= _("<b>IMPORTANT !</b> Associate the <b>gender</b> to a column.") ?></span>
          </div>
        </div>
        
        <hr/>
        <div class="row">
          <div class="col-lg-10">
            <label><?= _("Important Options") ?></label>
          </div>
        </div>
        <br>
        <div class="row">
          <div class="col-lg-10" style="color:green">
            <input type="checkbox" value="1" name="IgnoreFirstRow" checked> &nbsp;&nbsp;&nbsp;&nbsp; <?= _('Ignore first CSV row (to exclude a header)') ?>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-10">
            <input type="checkbox" value="1" name="PutInCart" checked> &nbsp;&nbsp;&nbsp;&nbsp; <?= _('Put all the persons in the cart, to import them in a group, sundayschool group, etc....') ?>
          </div>
        </div>

        <hr>

        <div class="row">
          <div class="col-lg-1" style="width:10px">
             <input type="checkbox" value="1" name="MakeFamilyRecords" checked=true>
          </div>
          <div class="col-lg-3">
            <select name="MakeFamilyRecordsMode" class="form-control form-control-sm">
                <option value="0"><?= _('Make Family records based on last name and address') ?></option>
                <?= $sPerCustomFieldList.$sFamCustomFieldList ?>
            </select>
          </div>
        </div>

        <BR>

        <div class="row">
          <div class="col-lg-1" style="width:10px">
          </div>
          <div class="col-lg-3">
              <select name="DateMode"  class="form-control form-control-sm" style="color:red">
                  <option value="1">YYYY-MM-DD</option>
                  <option value="2">MM-DD-YYYY</option>
                  <option value="3">DD-MM-YYYY</option>
                  <option value="4">YYYY/MM/DD</option>
                  <option value="5">MM/DD/YYYY</option>
                  <option value="6">DD/MM/YYYY</option>
                  <option value="7">YYYY MM DD</option>
                  <option value="8">MM DD YYYY</option>
                  <option value="9">DD MM YYYY</option>
              </select>
          </div>
          <div class="col-lg-8">
            <span style="color:red"><b><?= _("Date Format") ?></b></span>&nbsp;&nbsp;&nbsp;&nbsp;(<?= _('NOTE: Separators (dashes, etc.) or lack thereof do not matter') ?>)
          </div>
        </div>

        <input type="hidden" name="selectedValues" value="0">

        <hr/>

        <div class="row">
          <div class="col-lg-10">
            <h3  class="card-title"><?= _("Not usefull options") ?></h3>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-1" style="width:10px">
          </div>
          <div class="col-lg-3">
            <select name="FamilyMode"  class="form-control form-control-sm">
                <option value="0"><?= _('Patriarch') ?></option>
                <option value="1"><?= _('Matriarch') ?></option>
            </select>
          </div>
          <div class="col-lg-5">
             <?= _('Family Type: used with Make Family records... option above') ?>
          </div>
        </div>

        <BR>

        <div class="row">
          <div class="col-lg-1" style="width:10px">
          </div>
          <div class="col-lg-3">
            <?php
                $sCountry = SystemConfig::getValue('sDefaultCountry');
                echo CountryDropDown::getDropDown($sCountry);
            ?>
          </div>
          <div class="col-lg-5">
            <?= _('Default country if none specified otherwise') ?>
          </div>
        </div>

      <?php
        $ormClassifications = ListOptionQuery::create()
          ->filterById(1)
          ->orderByOptionSequence()
          ->find();
      ?>
        <BR>
        <div class="row">
          <div class="col-lg-1" style="width:10px">
          </div>
          <div class="col-lg-3">
            <select name="Classification" class="form-control form-control-sm">
               <option value="0"><?= _('Unassigned') ?></option>
               <option value="0">-----------------------</option>

            <?php
              foreach ($ormClassifications as $classification) {
              ?>
                <option value="<?= $classification->getOptionId() ?>"><?= $classification->getOptionName() ?></option>
              <?php
              }
            ?>
            </select>
          </div>
          <div class="col-lg-5">
             <?= _('Classification') ?>
          </div>
        </div>
        <BR><BR>
        <input type="submit" class="btn btn-primary" value="<?= _('Perform Import') ?>" name="DoImport">
      </form>

  <?php
        $iStage = 2;
    }
}


// Has the import form been submitted yet?
if (isset($_POST['DoImport']) && $iSelectedValues >= 3) {
    $aColumnCustom = [];
    $aFamColumnCustom = [];
    $bHasCustom = false;
    $bHasFamCustom = false;

    $generalCSVSeparator = ',';

    if (isset($_POST['sSeperator'])) {
      $generalCSVSeparator = $_POST['sSeperator'];
    }

    $csvTempFile = 'import.csv';
    $system_temp = ini_get('session.save_path');
    if (strlen($system_temp) > 0) {
        $csvTempFile = $system_temp.'/'.$csvTempFile;
    }

    $Families = [];

    // make sure the file still exists
    if (file_exists($csvTempFile)) {
        // create the file pointer
        $pFile = fopen($csvTempFile, 'r');

        $bHasCustom = false;
        $sDefaultCountry = InputUtils::LegacyFilterInput($_POST['Country']);
        $iClassID = InputUtils::LegacyFilterInput($_POST['Classification'], 'int');
        $iDateMode = InputUtils::LegacyFilterInput($_POST['DateMode'], 'int');
        $iPutInCart = InputUtils::LegacyFilterInput($_POST['PutInCart'], 'int');

        if ($iPutInCart) {
          Cart::CleanCart();
        }

        // Get the number of CSV columns for future reference
        $aData = fgetcsv($pFile, 2048, $generalCSVSeparator);
        $numCol = count($aData);
        if (!isset($_POST['IgnoreFirstRow'])) {
            rewind($pFile);
        }

        // Put the column types from the mapping form into an array
        for ($col = 0; $col < $numCol; $col++) {
            if (mb_substr($_POST['col'.$col], 0, 1) == 'c') {
                $aColumnCustom[$col] = 1;
                $aFamColumnCustom[$col] = 0;
                $bHasCustom = true;
            } else {
                $aColumnCustom[$col] = 0;
                if (mb_substr($_POST['col'.$col], 0, 2) == 'fc') {
                    $aFamColumnCustom[$col] = 1;
                    $bHasFamCustom = true;
                } else {
                    $aFamColumnCustom[$col] = 0;
                }
            }
            $aColumnID[$col] = $_POST['col'.$col];
        }

        if ($bHasCustom) {            
            $ormPersonCustomMasterFields = PersonCustomMasterQuery::create()
                ->find();
            
            foreach ($ormPersonCustomMasterFields as $customField) {
                $aCustomTypes[$customField->getCustomField()] = $customField->getTypeId();
            }            

            $ormFamilyCustomMasterFields = FamilyCustomMasterQuery::create()
                ->find();
            
            foreach ($ormFamilyCustomMasterFields as $customField) {
                $afamCustomTypes[$customField->getCustomField()] = $customField->getTypeId();
            }
        }

        //
        // Need to lock the person_custom and person_per tables!!
        //

        $aPersonTableFields = [
                1 => 'per_Title', 2=>'per_FirstName', 3=>'per_MiddleName', 4=>'per_LastName',
                5 => 'per_Suffix', 6=>'per_Gender', 7=>'per_Envelope', 8=>'per_Address1', 9=>'per_Address2',
                10=> 'per_City', 11=>'per_State', 12=>'per_Zip', 13=>'per_Country', 14=>'per_HomePhone',
                15=> 'per_WorkPhone', 16=>'per_CellPhone', 17=>'per_Email', 18=>'per_WorkEmail',
                19=> 'per_BirthYear, per_BirthMonth, per_BirthDay', 20=>'per_MembershipDate',
                21=> 'fam_WeddingDate',
        ];

        $importCount = 0;

        while ($aData = fgetcsv($pFile, 2048, $generalCSVSeparator)) {
            $iBirthYear = 0;
            $iBirthMonth = 0;
            $iBirthDay = 0;
            $iGender = 0;
            $dWedding = '';
            $sAddress1 = '';
            $sAddress2 = '';
            $sCity = '';
            $sState = '';
            $sZip = '';
            // Use the default country from the mapping form in case we don't find one otherwise
            $sCountry = SystemConfig::getValue('sDefaultCountry');
            $iEnvelope = 0;

            $sSQLpersonFields = 'INSERT INTO person_per (';
            $sSQLpersonData = ' VALUES (';
            $sSQLcustom = 'UPDATE person_custom SET ';

            $aData[$col]." ".$col."<br>";

            // Build the person_per SQL first.
            // We do this in case we can get a country, which will allow phone number parsing later
            for ($col = 0; $col < $numCol; $col++) {
                // Is it not a custom field?
                if (!$aColumnCustom[$col] && !$aFamColumnCustom[$col]) {
                    $currentType = $aColumnID[$col];

                    // handler for each of the 20 person_per table column possibilities
                    switch ($currentType) {
                        // Address goes with family record if creating families
                        case 8: case 9: case 10: case 11: case 12:
                            // if not making family records, add to person
                            if (!isset($_POST['MakeFamilyRecords'])) {
                                $sSQLpersonData .= "'".addslashes($aData[$col])."',";
                            } else {
                                switch ($currentType) {
                                    case 8:
                                        $sAddress1 = addslashes($aData[$col]);
                                        break;
                                    case 9:
                                        $sAddress2 = addslashes($aData[$col]);
                                        break;
                                    case 10:
                                        $sCity = addslashes($aData[$col]);
                                        break;
                                    case 11:
                                        $sState = addslashes($aData[$col]);
                                        break;
                                    case 12:
                                        $sZip = addslashes($aData[$col]);
                                }
                            }
                            break;

                        // Simple strings.. no special processing
                        case 1: case 2: case 3: case 4: case 5:
                        case 17: case 18:
                            $sSQLpersonData .= "'".addslashes($aData[$col])."',";
                            break;

                        // Country.. also set $sCountry for use later!
                        case 13:
                            $sCountry = $aData[$col];
                            break;

                        // Gender.. check for multiple possible designations from input
                        case 6:
                            switch (strtolower($aData[$col])) {
                                case '1':case 'male': case 'm': case 'boy': case 'man':case 'm.':case 'm':case 'mr.':case 'mr':
                                    $sSQLpersonData .= '1, ';
                                      $iGender = 1;
                                    break;
                                case '2':case 'female': case 'f': case 'girl': case 'woman':case 'mme.':case 'mlle.':case 'mme':case 'mlle':
                                    $sSQLpersonData .= '2, ';
                                      $iGender = 2;
                                    break;
                                default:
                                    $sSQLpersonData .= '0, ';
                                    break;
                            }
                            break;

                        // Donation envelope.. make sure it's available!
                        case 7:
                            $iEnv = InputUtils::LegacyFilterInput($aData[$col], 'int');
                            if ($iEnv == '') {
                                $iEnvelope = 0;
                            } else {                                
                                $persons = PersonQuery::create()->filterByEnvelope($iEnv)->find();
                                if ($persons->count() == 0) {
                                  $iEnvelope = $iEnv;
                                } else {
                                    $iEnvelope = 0;
                                }                                
                            }
                            break;

                        // Birth date.. parse multiple date standards.. then split into day,month,year
                        case 19:
                            $sDate = $aData[$col];
                            $aDate = ParseDate($sDate, $iDateMode);
                            $sSQLpersonData .= $aDate[0].','.$aDate[1].','.$aDate[2].',';
                            // Save these for role calculation
                            $iBirthYear = $aDate[0];
                            $iBirthMonth = $aDate[1];
                            $iBirthDay = $aDate[2];

                            break;

                        // Membership date.. parse multiple date standards
                        case 20:
                            $sDate = $aData[$col];
                            $aDate = ParseDate($sDate, $iDateMode);
                            if ($aDate[0] == 'NULL' || $aDate[1] == 'NULL' || $aDate[2] == 'NULL') {
                                $sSQLpersonData .= 'NULL,';
                            } else {
                                $sSQLpersonData .= '"'.$aDate[0].'-'.$aDate[1].'-'.$aDate[2].'",';
                            }
                            break;

                        // Wedding date.. parse multiple date standards
                        case 21:
                            $sDate = $aData[$col];
                            $aDate = ParseDate($sDate, $iDateMode);
                            if ($aDate[0] == 'NULL' || $aDate[1] == 'NULL' || $aDate[2] == 'NULL') {
                                $dWedding = 'NULL';
                            } else {
                                $dWedding = $aDate[0].'-'.$aDate[1].'-'.$aDate[2];
                            }
                            break;

                        // Ignore field option
                        case 0:

                        // Phone numbers.. uh oh.. don't know country yet.. wait to do a second pass!
                        case 14: case 15: case 16:
                        default:
                            break;

                    }

                    switch ($currentType) {
                        case 0: case 7: case 13: case 14: case 15: case 16: case 21:
                            break;
                        case 8: case 9: case 10: case 11: case 12:
                            // if not making family records, add to person
                            if (!isset($_POST['MakeFamilyRecords'])) {
                                $sSQLpersonFields .= $aPersonTableFields[$currentType].', ';
                            }
                            break;
                        default:
                            $sSQLpersonFields .= $aPersonTableFields[$currentType].', ';
                            break;
                    }
                }
            }

            // Second pass at the person_per SQL.. this time we know the Country
            for ($col = 0; $col < $numCol; $col++) {
                // Is it not a custom field?
                if (!$aColumnCustom[$col] && !$aFamColumnCustom[$col]) {
                    $currentType = $aColumnID[$col];
                    switch ($currentType) {
                        // Phone numbers..
                        case 14: case 15: case 16:
                            $sSQLpersonData .= "'".addslashes(MiscUtils::CollapsePhoneNumber($aData[$col], $sCountry))."',";
                            $sSQLpersonFields .= $aPersonTableFields[$currentType].', ';
                            break;
                        default:
                            break;
                    }
                }
            }

            // Finish up the person_per SQL..
            $sSQLpersonData .= $iClassID.",'".addslashes($sCountry)."',";
            $sSQLpersonData .= "'".date('YmdHis')."',".SessionUser::getUser()->getPersonId();
            $sSQLpersonData .= ')';

            $sSQLpersonFields .= 'per_cls_ID, per_Country, per_DateEntered, per_EnteredBy';
            $sSQLpersonFields .= ')';
            $sSQLperson = $sSQLpersonFields.$sSQLpersonData;            

            $statement = $connection->prepare($sSQLperson);
            $statement->execute();

            // Make a one-person family if requested
            if (isset($_POST['MakeFamilyRecords'])) {
                $personMax = PersonQuery::Create()
                        ->addAsColumn('MaxPersonID', 'MAX(' . PersonTableMap::COL_PER_ID . ')')
                        ->findOne();

                $iPersonID = $personMax->getMaxPersonID();
                
                $person = PersonQuery::create()
                  ->findOneById($iPersonID);
              
                // see if there is a family...
                if (!isset($_POST['MakeFamilyRecordsMode']) || $_POST['MakeFamilyRecordsMode'] == '0') {
                    // ...with same last name and address
                    $sSQL = "SELECT fam_ID
                             FROM family_fam where fam_Name = '".addslashes($person->getLastName())."'
                             AND fam_Address1 = '".$sAddress1."'"; // slashes added already
                } else {
                    // ...with the same custom field values
                    $field = $_POST['MakeFamilyRecordsMode'];
                    $field_value = '';
                    for ($col = 0; $col < $numCol; $col++) {
                        if ($aFamColumnCustom[$col] && $field == $aColumnID[$col]) {
                            $field_value = trim($aData[$col]);
                            break;
                        }
                    }
                    $sSQL = 'SELECT f.fam_ID FROM family_fam f, family_custom c
                             WHERE f.fam_ID = c.fam_ID AND c.'.addslashes(mb_substr($field, 1))." = '".addslashes($field_value)."'";
                }

                $statement = $connection->prepare($sSQL);
                $statement->execute();

                $pdoExistingFamily = $statement->fetchAll(PDO::FETCH_ASSOC);

                /*
                tester le code ici
                */
                $famid = 0;
                if (count($pdoExistingFamily) > 0) {
                    $row0 = $pdoExistingFamily[0];
                    $famid = $row0['fam_ID'];
                    if (array_key_exists($famid, $Families)) {
                        $Families[$famid]->AddMember($person->getId(),
                            $iGender,
                            GetAge($iBirthMonth, $iBirthDay, $iBirthYear),
                            $dWedding,
                            $person->getHomePhone(),
                            $iEnvelope);
                    }
                } else {
                    $family = new Family();

                    $family->setName($person->getLastName());
                    $family->setAddress1($sAddress1);
                    $family->setAddress2($sAddress2);
                    $family->setCity($sCity);
                    $family->setState($sState);
                    $family->setZip($sZip);
                    $family->setCountry($person->getCountry());
                    $family->setHomePhone($person->getHomePhone());
                    $family->setWorkPhone($person->getHomePhone());
                    $family->setCellPhone($person->getCellPhone());
                    $family->setEmail($person->getEmail());
                    $family->setDateEntered(date('YmdHis'));
                    $family->setEnteredBy(SessionUser::getUser()->getPersonId());
                    $family->getLongitude(0);
                    $family->setLatitude(0);

                    $family->save();

                    $famid = $family->getId(); 

                    $note = new Note();
                    $note->setFamId($famid);
                    $note->setText(_('Imported'));
                    $note->setType('create');
                    $note->setEntered(SessionUser::getUser()->getPersonId());
                    $note->save();

                    $famCustom = new FamilyCustom();
                    $famCustom->setFamId($famid);
                    $famCustom->save();
                
                    $fFamily = new FamilyImportUtils(InputUtils::FilterInt($_POST['FamilyMode']));
                    $fFamily->AddMember($person->getId(),
                        $iGender,
                        GetAge($iBirthMonth, $iBirthDay, $iBirthYear),
                        $dWedding,
                        $per_HomePhone,
                        $iEnvelope);
                    $Families[$famid] = $fFamily;
                }

                $person->setFamId($famid);
                $person->save();

                if ($bHasFamCustom) {
                    // Check if family_custom record exists    
                    $famCustom = FamilyCustomQuery::create()
                            ->findOneByFamId($famid);

                    if (is_null($famCustom)) {
                      $famCustom = new FamilyCustom();
                      $famCustom->setFamId($famid);
                      $famCustom->save();
                    }                  

                    // Build the family_custom SQL
                    $sSQLFamCustom = 'UPDATE family_custom SET ';
                    for ($col = 0; $col < $numCol; $col++) {
                        // Is it a custom field?
                        if ($aFamColumnCustom[$col]) {
                            $colID = mb_substr($aColumnID[$col], 1);
                            $currentType = $afamCustomTypes[$colID];
                            $currentFieldData = trim($aData[$col]);

                            // If date, first parse it to the standard format..
                            if ($currentType == 2) {
                                $aDate = ParseDate($currentFieldData, $iDateMode);
                                if ($aDate[0] == 'NULL' || $aDate[1] == 'NULL' || $aDate[2] == 'NULL') {
                                    $currentFieldData = '';
                                } else {
                                    $currentFieldData = implode('-', $aDate);
                                }
                            }
                            // If boolean, convert to the expected values for custom field
                            elseif ($currentType == 1) {
                                if (strlen($currentFieldData)) {
                                    $currentFieldData = MiscUtils::ConvertToStringBoolean($currentFieldData);
                                }
                            } else {
                                $currentFieldData = addslashes($currentFieldData);
                            }

                            // aColumnID is the custom table column name
                            MiscUtils::sqlCustomField($sSQLFamCustom, $currentType, $currentFieldData, $colID, $sCountry);
                        }
                    }

                    // Finalize and run the update for the person_custom table.
                    $sSQLFamCustom = mb_substr($sSQLFamCustom, 0, -2);
                    $sSQLFamCustom .= ' WHERE fam_ID = '.$famid;
                    
                    $statement = $connection->prepare($sSQLFamCustom);
                    $statement->execute();
                }
            }

            // Get the last inserted person ID and insert a dummy row in the person_custom table
            $personMax = PersonQuery::Create()
                    ->addAsColumn('MaxPersonID', 'MAX(' . PersonTableMap::COL_PER_ID . ')')
                    ->findOne();

            $iPersonID = $personMax->getMaxPersonID();
            
            $person = PersonQuery::create()
              ->findOneById($iPersonID);

            if ($iPutInCart == 1) {              
              Cart::AddPerson($iPersonID);
            }

            $note = new Note();
            $note->setPerId($iPersonID);
            $note->setText(_('Imported'));
            $note->setType('create');
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->save();

            if ($bHasCustom) {
                $personCustom = new PersonCustom();
                $personCustom->setPerId($iPersonID);
                $personCustom->save();

                // Build the person_custom SQL
                for ($col = 0; $col < $numCol; $col++) {
                    // Is it a custom field?
                    if ($aColumnCustom[$col]) {
                        $currentType = $aCustomTypes[$aColumnID[$col]];
                        $currentFieldData = trim($aData[$col]);

                        // If date, first parse it to the standard format..
                        if ($currentType == 2) {
                            $aDate = ParseDate($currentFieldData, $iDateMode);
                            if ($aDate[0] == 'NULL' || $aDate[1] == 'NULL' || $aDate[2] == 'NULL') {
                                $currentFieldData = '';
                            } else {
                                $currentFieldData = implode('-', $aDate);
                            }
                        }
                        // If boolean, convert to the expected values for custom field
                        elseif ($currentType == 1) {
                            if (strlen($currentFieldData)) {
                                $currentFieldData = MiscUtils::ConvertToStringBoolean($currentFieldData);
                            }
                        } else {
                            $currentFieldData = addslashes($currentFieldData);
                        }

                        // aColumnID is the custom table column name
                        MiscUtils::sqlCustomField($sSQLcustom, $currentType, $currentFieldData, $aColumnID[$col], $sCountry);
                    }
                }

                // Finalize and run the update for the person_custom table.
                $sSQLcustom = mb_substr($sSQLcustom, 0, -2);
                $sSQLcustom .= ' WHERE per_ID = '.$iPersonID;
                
                $statement = $connection->prepare($sSQLcustom);
                $statement->execute();
            }

            $importCount++;
        }

        fclose($pFile);

        // delete the temp file
        unlink($csvTempFile);

        // role assignments from config
        $aDirRoleHead = explode(',', SystemConfig::getValue('sDirRoleHead'));
        $aDirRoleSpouse = explode(',', SystemConfig::getValue('sDirRoleSpouse'));
        $aDirRoleChild = explode(',', SystemConfig::getValue('sDirRoleChild'));

        // update roles now that we have complete family data.
        foreach ($Families as $fid=>$family) {
            $family->AssignRoles();
            foreach ($family->Members as $member) {
                switch ($member['role']) {
                    case 1:
                        $iRole = $aDirRoleHead[0];
                        break;
                    case 2:
                        $iRole = $aDirRoleSpouse[0];
                        break;
                    case 3:
                        $iRole = $aDirRoleChild[0];
                        break;
                    default:
                        $iRole = 0;
                }

                $per = PersonQuery::create()
                  ->findOneById($member['personid']);

                if (!is_null($per)) {
                  $per->setFmrId($iRole);
                  $per->save();
                }
            }

            $fam = FamilyQuery::create()->findOneById($fid);

            if ( !is_null($fam) ) {
              if ($family->WeddingDate != '') {
                $fam->setWeddingdate($family->WeddingDate);
              }
              if ($family->Phone != '') {
                $fam->setHomePhone($family->Phone);
              }
              
              if ($family->Envelope != '') {
                $fam->setEnvelope($family->Envelope);
              }

              $fam->save();
            }            
        }

        $iStage = 3;
    } else {
  ?>
        <?=  _('ERROR: the uploaded CSV file no longer exists!') ?>
  <?php
    }
}

if ($iStage == 1) {
    // Display the select file form?>
      <form method="post" action="<?= $sRootPath ?>/v2/system/csv/import" enctype="multipart/form-data">
        <div class="row">
          <div class="col-lg-12">
            <h2><?= _("Steps to import users") ?></h2>
            <ul>
              <li>
                 <?= _("Your CSV file must have a header row, as follows") ?> : <br>
                 - <?= _("avec le séparateur csv \" ; \"") ?> 
                    <b><?= _("Title;Name;First Name;Gender;Suffix;Middle Name;Address 1;Address 2;City;zip code;State;Country;phone;cell phone;work phone;email;work email") ?> ....</b> <br>
                 - <?= _("avec le séparateur csv \" , \"") ?>  : <b><?= _("Title,Name,First Name,Gender,Suffix,Middle Name,Address 1,Address 2,City,zip code,State,phone,cell phone,work phone,email,work email") ?> ....</b><br>                 
              </li>
              <li>
                <?= _("Don't forget the <b>gender</b> and the <b>title</b>") ?> : <br>
                <?= _("You can format your columns in Excel LibreOffice Calc and <b>duplicate the Title column and rename the label header to gender</b>") ?>.<br>
                <?= _("The gender column must be set to <b>1 for a man, boy, male</b> and <b>2 for a women, girl, female</b> ...") ?>
              </li>
              <li>
                 <?= _("Prepare your CRM and add enough custom Person Fields, to do this click") ?> : <b><a href="<?= $sRootPath ?>/v2/people/person/customfield/editor"><?= _("here") ?></a></b><br>
              </li>
              <li>
                 <p style="color: red"><?= _("All dates should be formated like : 2018-7-1 or 1/7/2018 or 7-1-2018 or 7/1/2018") ?></p>
              </li>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <h3><?= _("The next step should be, if not select the other CSV seperator") ?></h3>
            <img src="<?= $sRootPath ?>/Images/csvimport.png" class="image-max-width" width=100%>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <h3><?= _("Upload CSV File") ?></h3>
            <p style="color: red"> <?= $csvError ?></p>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="alert alert-info"><?= _("<b>DON'T FORGET</b>
              <br>• Your csv table must contain a <b>header</b> row with columns named (For example, for the label title: Mr., Mrs., Miss, etc ...),
              <br>• and the <b>gender</b> column must be in the form (with 1 for male gender, 2 for female gender)") ?>
              <div class="row">
                <div class="col-lg-3">
                  • <?= _("Select <b>NOW</b> your <b>CSV separator</b>") ?>
                </div>
                <div class="col-lg-2">
                  <select name="sSeperator" class="form-control form-control-sm">
                      <option value=",">,</option>
                      <option value=";">;</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-5">
                  <?= _("• Last set correctly all the dates like : <b>YYYY-MM-DD or DD/MM/YYYY</b> ......... ") ?>
                </div>
                <div class="col-lg-4">
                  <span style="color: red"><?= _("This is proposed in the next step, read carefully your table.") ?></span>
                </div>
             </div>
          </div>
        </div>
        </div>
        <div class="row">
          <div class="col-lg-3">
            <input class="icTinyButton btn" type="file" name="CSVfile"><br/>
          </div>
          <div class="col-lg-3">
            <input type="submit" class="btn btn-primary" value="<?= _('Upload CSV File') ?> " name="UploadCSV">
          </div>
        </div>
      </form>
   </div>
</div>
<div class="card">
  <div class="card-header  border-1">
    <h3 class="card-title"><?= _('Clear Data')?></h3>
  </div>
  <div class="card-body">
    <button type="button" class="btn btn-danger" id="clear-people"><i class="fa fa-trash-can"></i> <?= _('Clear Persons and Families') ?></button>
    <label id="import-success" style="color:green"></label>
<?php
}

if ($iStage == 3) {
?>
    <p class="MediumLargeText"><?= _('Data import successful.').' '.$importCount.' '._('persons were imported') ?></p>
<?php
}

// Returns a date array [year,month,day]
function ParseDate($sDate, $iDateMode)
{
    $cSeparator = '';
    $sDate = trim($sDate);
    for ($i = 0; $i < strlen($sDate); $i++) {
        if (is_numeric(mb_substr($sDate, $i, 1))) {
            continue;
        }
        $cSeparator = mb_substr($sDate, $i, 1);
        break;
    }
    $aDate[0] = '0000';
    $aDate[1] = '00';
    $aDate[2] = '00';

    switch ($iDateMode) {
        // International standard: YYYY-MM-DD
        case 1:
            $date = DateTime::createFromFormat('Y-m-d', $sDate);
            break;

        // MM-DD-YYYY
        case 2:
            $date = DateTime::createFromFormat('m-d-Y', $sDate);
            break;

        // DD-MM-YYYY
        case 3:
            $date = DateTime::createFromFormat('d-m-Y', $sDate);
            break;

        // International standard: YYYY/MM/DD
        case 4:
            $date = DateTime::createFromFormat('Y/m/d', $sDate);
            break;

        // MM/DD/YYYY
        case 5:
            $date = DateTime::createFromFormat('m/d/Y', $sDate);
            break;

        // DD/MM/YYYY
        case 6:
            $date = DateTime::createFromFormat('d/m/Y', $sDate);
            break;

        // International standard: YYYY MM DD
        case 7:
            $date = DateTime::createFromFormat('Y m d', $sDate);
            break;

        // MM/DD/YYYY
        case 8:
            $date = DateTime::createFromFormat('m d Y', $sDate);
            break;

        // DD/MM/YYYY
        case 9:
            $date = DateTime::createFromFormat('d m Y', $sDate);
            break;
    }

    if ($date != FALSE) {
      $aDate[0] = $date->format('Y');
      $aDate[1] = $date->format('m');
      $aDate[2] = $date->format('d');
    }

    if ((0 + $aDate[0]) < 1901 || (0 + $aDate[0]) > 2155) {
        $aDate[0] = 'NULL';
    }
    if ((0 + $aDate[1]) < 0 || (0 + $aDate[1]) > 12) {
        $aDate[1] = 'NULL';
    }
    if ((0 + $aDate[2]) < 0 || (0 + $aDate[2]) > 31) {
        $aDate[2] = 'NULL';
    }

    return $aDate;
}

function GetAge($Month, $Day, $Year)
{
    if ($Year > 0) {
        if ($Year == date('Y')) {
            return 0;
        } elseif ($Year == date('Y') - 1) {
            $monthCount = 12 - $Month + date('m');
            if ($Day > date('d')) {
                $monthCount--;
            }
            if ($monthCount >= 12) {
                return 1;
            } else {
                return 0;
            }
        } elseif ($Month > date('m') || ($Month == date('m') && $Day > date('d'))) {
            return  date('Y') - 1 - $Year;
        } else {
            return  date('Y') - $Year;
        }
    } else {
        return -1;
    }
}
?>
  </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/CSVImport.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
