<?php
/*******************************************************************************
 *
 *  filename    : CSVImport.php
 *  last change : 2003-10-02
 *  description : Tool for importing CSV person data into InfoCentral
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2003 Chris Gebhardt
 *  Copyright 2018 Philippe Logel
 *
 ******************************************************************************/

// Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';


use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Note;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\CountryDropDown;


if (!$_SESSION['user']->isAdmin()) {
    Redirect('Menu.php');
    exit;
}
/**
 A monogamous society is assumed, however  it can be patriarchal or matriarchal
 **/
class Family
{
    public $Members;       // array for member data
    public $MemberCount;   // obious
    public $WeddingDate;   // one per family
    public $Phone;         // one per family
    public $Envelope;      // one per family
    public $_nAdultMale;   // if one adult male
    public $_nAdultFemale; // and 1 adult female we assume spouses
    public $_type;         // 0=patriarch, 1=martriarch
    
    public function __construct()
    {
    }

    // constructor, initialize variables
    public function Family($famtype)
    {
        $this->_type = $famtype;
        $this->MemberCount = 0;
        $this->Envelope = 0;
        $this->_nAdultMale = 0;
        $this->_nAdultFemale = 0;
        $this->Members = [];
        $this->WeddingDate = '';
        $this->Phone = '';
    }

    /** Add what we need to know about members for role assignment later **/
    public function AddMember($PersonID, $Gender, $Age, $Wedding, $Phone, $Envelope)
    {
        // add member with un-assigned role
        $this->Members[] = ['personid'     => $PersonID,
                                 'age'     => $Age,
                                 'gender'  => $Gender,
                                 'role'    => 0,
                                 'phone'   => $Phone,
                                 'envelope'=> $Envelope, ];
                                 
        if ($Wedding != '') {
            $this->WeddingDate = $Wedding;
        }
        if ($Envelope != 0) {
            $this->Envelope = $Envelope;
        }
        $this->MemberCount++;
        if ($Age > 18) {
            $Gender == 1 ? $this->_nAdultMale++ : $this->_nAdultFemale++;
        }
    }

    /** Assigning of roles to be called after all members added **/
    public function AssignRoles()
    {
        // only one meber, must be "head"
        if ($this->MemberCount == 1) {
            $this->Members[0]['role'] = 1;
            $this->Phone = $this->Members[0]['phone'];
        } else {
            for ($m = 0; $m < $this->MemberCount; $m++) {
                if ($this->Members[$m]['age'] >= 0) { // -1 if unknown age
                    // child
                    if ($this->Members[$m]['age'] <= 18) {
                        $this->Members[$m]['role'] = 3;
                    } else {
                        // if one adult male and 1 adult female we assume spouses
                        if ($this->_nAdultMale == 1 && $this->_nAdultFemale == 1) {
                            // find head / spouse
                            if (($this->Members[$m]['gender'] == 1 && $this->_type == 0) || ($this->Members[$m]['gender'] == 2 && $this->_type == 1)) {
                                $this->Members[$m]['role'] = 1;
                                if ($this->Members[$m]['phone'] != '') {
                                    $this->Phone = $this->Members[$m]['phone'];
                                }
                            } else {
                                $this->Members[$m]['role'] = 2;
                            }
                        }
                    }
                }
            }
        }
    }
}

// Set the page title and include HTML header
$sPageTitle = gettext('CSV Import');
require 'Include/Header.php'; ?>

<div class="box import-users" style="display: box;">
<div class="box-header with-border">
   <h3 class="box-title"><?= gettext('Import Data')?></h3>
</div>
<div class="box-body">

<?php

$iStage = 1;
$csvError = '';

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
        $csvError = gettext('No file selected for upload.');
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
        <form method="post" action="CSVImport.php">
          <input type="hidden" name="sSeperator" value="<?= $generalCSVSeparator ?>">
          <input type="hidden" name="iSelectedValues" value="0" id="selectedValues">

        <label><?= gettext('Total number of rows in the CSV file:') ?></label> <b><?= $iNumRows ?></b>
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

        $sSQL = 'SELECT * FROM person_custom_master ORDER BY custom_Order';
        $rsCustomFields = RunQuery($sSQL);

        $sPerCustomFieldList = '';
        while ($aRow = mysqli_fetch_array($rsCustomFields)) {
            extract($aRow);
            // No easy way to import person-from-group or custom-list types
            if ($type_ID != 9 && $type_ID != 12) {
                $sPerCustomFieldList .= '<option value="'.$custom_Field.'">'.$custom_Name."</option>\n";
            }
        }

        $sSQL = 'SELECT * FROM family_custom_master ORDER BY fam_custom_Order';
        $rsfamCustomFields = RunQuery($sSQL);

        $sFamCustomFieldList = '';
        while ($aRow = mysqli_fetch_array($rsfamCustomFields)) {
            extract($aRow);
            if ($type_ID != 9 && $type_ID != 12) {
                $sFamCustomFieldList .= '<option value="f'.$fam_custom_Field.'">'.$fam_custom_Name."</option>\n";
            }
        }
        
        // add select boxes for import destination mapping
        for ($col = 0; $col < $numCol; $col++) {
            ?>
            <td>
            <select name="<?= 'col'.$col ?>" class="columns" class="form-control" id="col<?= $col ?>"  data-col="<?= $col ?>" data-numcol="<?= $numCol ?>">
                <option value="0"><?= gettext('Ignore this Field') ?></option>
                <option value="1"><?= gettext('Title') ?></option>
                <option value="2"><?= gettext('First Name') ?></option>
                <option value="3"><?= gettext('Middle Name') ?></option>
                <option value="4"><?= gettext('Last Name') ?></option>
                <option value="5"><?= gettext('Suffix') ?></option>
                <option value="6"><?= gettext('Gender') ?></option>
                <option value="7"><?= gettext('Donation Envelope') ?></option>
                <option value="8"><?= gettext('Address') ?> 1</option>
                <option value="9"><?= gettext('Address') ?> 2</option>
                <option value="10"><?= gettext('City') ?></option>
                <option value="11"><?= gettext('State') ?></option>
                <option value="12"><?= gettext('Zip') ?></option>
                <option value="13"><?= gettext('Country') ?></option>
                <option value="14"><?= gettext('Home Phone') ?></option>
                <option value="15"><?= gettext('Work Phone') ?></option>
                <option value="16"><?= gettext('Mobile Phone') ?></option>
                <option value="17"><?= gettext('Email') ?></option>
                <option value="18"><?= gettext('Work / Other Email') ?></option>
                <option value="19"><?= gettext('Birth Date') ?></option>
                <option value="20"><?= gettext('Membership Date') ?></option>
                <option value="21"><?= gettext('Wedding Date') ?></option>
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
            <div class="callout callout-danger">
              <?= gettext("An error occurs when you import the CSV file. You've to select values above in the select fields.") ?>
            </div>
            <br>
             
        <?php
          }
        ?>
        <div class="row" style="margin-top:-10px">
          <div class="col-lg-12">
            <span style="color:blue;float:right"><?= gettext("Scroll right to see the other columns") ?></span>
            <span style="color:red;float:left">• <?= gettext("Check the right <b>Date format</b> and to chose it below !!!!!") ?></span><br>
            <span style="color:red;float:left">• <?= gettext("<b>IMPORTANT !</b> Associate the <b>gender</b> to a column.") ?></span>
            </ul>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-10">
            <h3  class="box-title"><?= gettext("Important Options") ?></h3>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-10" style="color:green">
            <input type="checkbox" value="1" name="IgnoreFirstRow"> &nbsp;&nbsp;&nbsp;&nbsp; <?= gettext('Ignore first CSV row (to exclude a header)') ?>
          </div>
        </div>

        <BR>

        <div class="row">
          <div class="col-lg-1" style="width:10px">
             <input type="checkbox" value="1" name="MakeFamilyRecords" checked="true">
          </div>
          <div class="col-lg-3">
            <select name="MakeFamilyRecordsMode" class="form-control input-sm">
                <option value="0"><?= gettext('Make Family records based on last name and address') ?></option>
                <?= $sPerCustomFieldList.$sFamCustomFieldList ?>
            </select>
          </div>
        </div>

        <BR>

        <div class="row">
          <div class="col-lg-1" style="width:10px">
          </div>
          <div class="col-lg-3">
              <select name="DateMode"  class="form-control input-sm" style="color:red">
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
          <div class="col-lg-5">
            <span style="color:red"><b><?= gettext("Date Format") ?></b></span>&nbsp;&nbsp;&nbsp;&nbsp;(<?= gettext('NOTE: Separators (dashes, etc.) or lack thereof do not matter') ?>)
          </div>
        </div>
        
        <br>
        
        <input type="hidden" name="selectedValues" value="0">

        <div class="row">
          <div class="col-lg-10" style="color:green">
            <input type="checkbox" value="1" name="PutInCart" checked> &nbsp;&nbsp;&nbsp;&nbsp; <?= gettext('Put all the persons in the cart, to import them in a group, sundayschool group, etc....') ?>
          </div>
        </div>


        <div class="row">
          <div class="col-lg-10">
            <h3  class="box-title"><?= gettext("Not usefull options") ?></h3>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-1" style="width:10px">
          </div>
          <div class="col-lg-3">
            <select name="FamilyMode"  class="form-control input-sm">
                <option value="0"><?= gettext('Patriarch') ?></option>
                <option value="1"><?= gettext('Matriarch') ?></option>
            </select>
          </div>
          <div class="col-lg-5">
             <?= gettext('Family Type: used with Make Family records... option above') ?>
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
            <?= gettext('Default country if none specified otherwise') ?>
          </div>
        </div>

      <?php
        $sSQL = 'SELECT * FROM list_lst WHERE lst_ID = 1 ORDER BY lst_OptionSequence';
        $rsClassifications = RunQuery($sSQL); 
      ?>      
        <BR>
        
        <div class="row">
          <div class="col-lg-1" style="width:10px">
          </div>
          <div class="col-lg-3">
            <select name="Classification" class="form-control input-sm">
               <option value="0"><?= gettext('Unassigned') ?></option>
               <option value="0">-----------------------</option>

            <?php
              while ($aRow = mysqli_fetch_array($rsClassifications)) {
                extract($aRow);
            ?>
                <option value="<?= $lst_OptionID ?>"><?= $lst_OptionName ?>&nbsp;
            <?php
              } 
            ?>
            </select>
          </div>
          <div class="col-lg-5">
             <?= gettext('Classification') ?>
          </div>
        </div>
        <BR><BR>
        <input type="submit" class="btn btn-primary" value="<?= gettext('Perform Import') ?>" name="DoImport">
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
            $sSQL = 'SELECT * FROM person_custom_master';
            $rsCustomFields = RunQuery($sSQL);

            while ($aRow = mysqli_fetch_array($rsCustomFields)) {
                extract($aRow);
                $aCustomTypes[$custom_Field] = $type_ID;
            }

            $sSQL = 'SELECT * FROM family_custom_master';
            $rsfamCustomFields = RunQuery($sSQL);

            while ($aRow = mysqli_fetch_array($rsfamCustomFields)) {
                extract($aRow);
                $afamCustomTypes[$fam_custom_Field] = $type_ID;
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
                                $sSQL = "SELECT '' FROM person_per WHERE per_Envelope = ".$iEnv;
                                $rsTemp = RunQuery($sSQL);
                                if (mysqli_num_rows($rsTemp) == 0) {
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
                            $sSQLpersonData .= "'".addslashes(CollapsePhoneNumber($aData[$col], $sCountry))."',";
                            $sSQLpersonFields .= $aPersonTableFields[$currentType].', ';
                            break;
                        default:
                            break;
                    }
                }
            }

            // Finish up the person_per SQL..
            $sSQLpersonData .= $iClassID.",'".addslashes($sCountry)."',";
            $sSQLpersonData .= "'".date('YmdHis')."',".$_SESSION['user']->getPersonId();
            $sSQLpersonData .= ')';

            $sSQLpersonFields .= 'per_cls_ID, per_Country, per_DateEntered, per_EnteredBy';
            $sSQLpersonFields .= ')';
            $sSQLperson = $sSQLpersonFields.$sSQLpersonData;
            
            RunQuery($sSQLperson);

            // Make a one-person family if requested
            if (isset($_POST['MakeFamilyRecords'])) {
                $sSQL = 'SELECT MAX(per_ID) AS iPersonID FROM person_per';
                $rsPersonID = RunQuery($sSQL);
                extract(mysqli_fetch_array($rsPersonID));
                $sSQL = 'SELECT * FROM person_per WHERE per_ID = '.$iPersonID;
                $rsNewPerson = RunQuery($sSQL);
                extract(mysqli_fetch_array($rsNewPerson));

                // see if there is a family...
                if (!isset($_POST['MakeFamilyRecordsMode']) || $_POST['MakeFamilyRecordsMode'] == '0') {
                    // ...with same last name and address
                    $sSQL = "SELECT fam_ID
                             FROM family_fam where fam_Name = '".addslashes($per_LastName)."'
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
                $rsExistingFamily = RunQuery($sSQL);
                $famid = 0;
                if (mysqli_num_rows($rsExistingFamily) > 0) {
                    extract(mysqli_fetch_array($rsExistingFamily));
                    $famid = $fam_ID;
                    if (array_key_exists($famid, $Families)) {
                        $Families[$famid]->AddMember($per_ID,
                                                     $iGender,
                                                     GetAge($iBirthMonth, $iBirthDay, $iBirthYear),
                                                     $dWedding,
                                                     $per_HomePhone,
                                                     $iEnvelope);
                    }
                } else {
                    $sSQL = 'INSERT INTO family_fam (fam_ID,
                                                     fam_Name,
                                                     fam_Address1,
                                                     fam_Address2,
                                                     fam_City,
                                                     fam_State,
                                                     fam_Zip,
                                                     fam_Country,
                                                     fam_HomePhone,
                                                     fam_WorkPhone,
                                                     fam_CellPhone,
                                                     fam_Email,
                                                     fam_DateEntered,
                                                     fam_EnteredBy,
                                                     fam_Latitude,
                                                     fam_Longitude)
                             VALUES (NULL, '.
                                     '"'.$per_LastName.'", '.
                                     '"'.$sAddress1.'", '.
                                     '"'.$sAddress2.'", '.
                                     '"'.$sCity.'", '.
                                     '"'.$sState.'", '.
                                     '"'.$sZip.'", '.
                                     '"'.$per_Country.'", '.
                                     '"'.$per_HomePhone.'", '.
                                     '"'.$per_WorkPhone.'", '.
                                     '"'.$per_CellPhone.'", '.
                                     '"'.$per_Email.'",'.
                                     '"'.date('YmdHis').'",'.
                                     '"'.$_SESSION['user']->getPersonId().'", '.
                                     '"0", '.
                                     '"0");';
                    RunQuery($sSQL);

                    $sSQL = 'SELECT LAST_INSERT_ID()';
                    $rsFid = RunQuery($sSQL);
                    $aFid = mysqli_fetch_array($rsFid);
                    $famid = $aFid[0];
                    $note = new Note();
                    $note->setFamId($famid);
                    $note->setText(gettext('Imported'));
                    $note->setType('create');
                    $note->setEntered($_SESSION['user']->getPersonId());
                    $note->save();
                    $sSQL = "INSERT INTO `family_custom` (`fam_ID`) VALUES ('".$famid."')";
                    RunQuery($sSQL);

                    $fFamily = new Family(InputUtils::LegacyFilterInput($_POST['FamilyMode'], 'int'));
                    $fFamily->AddMember($per_ID,
                                        $iGender,
                                        GetAge($iBirthMonth, $iBirthDay, $iBirthYear),
                                        $dWedding,
                                        $per_HomePhone,
                                        $iEnvelope);
                    $Families[$famid] = $fFamily;
                }
                $sSQL = 'UPDATE person_per SET per_fam_ID = '.$famid.' WHERE per_ID = '.$per_ID;
                RunQuery($sSQL);

                if ($bHasFamCustom) {
                    // Check if family_custom record exists
                    $sSQL = "SELECT fam_id FROM family_custom WHERE fam_id = $famid";
                    $rsFamCustomID = RunQuery($sSQL);
                    if (mysqli_num_rows($rsFamCustomID) == 0) {
                        $sSQL = "INSERT INTO `family_custom` (`fam_ID`) VALUES ('".$famid."')";
                        RunQuery($sSQL);
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
                                    $currentFieldData = ConvertToBoolean($currentFieldData);
                                }
                            } else {
                                $currentFieldData = addslashes($currentFieldData);
                            }

                            // aColumnID is the custom table column name
                            sqlCustomField($sSQLFamCustom, $currentType, $currentFieldData, $colID, $sCountry);
                        }
                    }

                    // Finalize and run the update for the person_custom table.
                    $sSQLFamCustom = mb_substr($sSQLFamCustom, 0, -2);
                    $sSQLFamCustom .= ' WHERE fam_ID = '.$famid;
                    RunQuery($sSQLFamCustom);
                }
            }

            // Get the last inserted person ID and insert a dummy row in the person_custom table
            $sSQL = 'SELECT MAX(per_ID) AS iPersonID FROM person_per';
            $rsPersonID = RunQuery($sSQL);
            extract(mysqli_fetch_array($rsPersonID));
            
            if ($iPutInCart == 1) {
              Cart::AddPerson($iPersonID);
            }
            
            $note = new Note();
            $note->setPerId($iPersonID);
            $note->setText(gettext('Imported'));
            $note->setType('create');
            $note->setEntered($_SESSION['user']->getPersonId());
            $note->save();
            if ($bHasCustom) {
                $sSQL = "INSERT INTO `person_custom` (`per_ID`) VALUES ('".$iPersonID."')";
                RunQuery($sSQL);

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
                                $currentFieldData = ConvertToBoolean($currentFieldData);
                            }
                        } else {
                            $currentFieldData = addslashes($currentFieldData);
                        }

                        // aColumnID is the custom table column name
                        sqlCustomField($sSQLcustom, $currentType, $currentFieldData, $aColumnID[$col], $sCountry);
                    }
                }

                // Finalize and run the update for the person_custom table.
                $sSQLcustom = mb_substr($sSQLcustom, 0, -2);
                $sSQLcustom .= ' WHERE per_ID = '.$iPersonID;
                RunQuery($sSQLcustom);
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
                $sSQL = 'UPDATE person_per SET per_fmr_ID = '.$iRole.' WHERE per_ID = '.$member['personid'];
                RunQuery($sSQL);
            }

            $sSQL = 'UPDATE family_fam SET fam_WeddingDate = '."'".$family->WeddingDate."'";

            if ($family->Phone != '') {
                $sSQL .= ', fam_HomePhone ='."'".$family->Phone."'";
            }

            if ($family->Envelope != 0) {
                $sSQL .= ', fam_Envelope  = '.$family->Envelope;
            }

            $sSQL .= ' WHERE fam_ID = '.$fid;
            RunQuery($sSQL);
        }

        $iStage = 3;
    } else {
  ?>
        <?=  gettext('ERROR: the uploaded CSV file no longer exists!') ?>
  <?php
    }
}

if ($iStage == 1) {
    // Display the select file form?>
      <form method="post" action="CSVImport.php" enctype="multipart/form-data">
        <div class="row">
          <div class="col-lg-12">      
            <h2><?= gettext("Steps to import users") ?></h2>
            <ul>
              <li>
                 <?= gettext("Your CSV file should be prepared like") ?> : <br>             
                 - <b>Title;Name;First Name;Gender;Suffix;Middle Name;Address 1;Address 2;City;State ....</b><br>
                 - or : <b>Title,Name,First Name,Gender,Suffix,Middle Name,Address 1,Address 2,City,State ....</b><br>             
                 <?= gettext("So the CSV delimiter are <b>';'</b> in the first example and <b>','</b> in the second one.") ?><br>
              </li>
              <li>
                <?= gettext("Don't forget the <b>gender</b> and the <b>title</b>") ?> : <br>
                <?= gettext("You can format your columns in Excel LibreOffice Calc and <b>duplicate the Title column and rename the label header to gender</b>") ?>.<br>
                <?= gettext("The gender column must be set to <b>1 for a man, boy, male</b> and <b>2 for a women, girl, female</b> ...") ?>
              </li>
              <li>
                 <?= gettext("Prepare your CRM and add enough custom Person Fields, to do this click") ?> : <b><a href="<?= SystemURLs::getRootPath() ?>/PersonCustomFieldsEditor.php"><?= gettext("here") ?></a></b><br>
              </li>
              <li>
                 <p style="color: red"><?= gettext("All dates should be formated like : 2018-7-1 or 1/7/2018 or 7-1-2018 or 7/1/2018") ?></p>
              </li>
            </ul>        
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <h3><?= gettext("The next step should be, if not select the other CSV seperator") ?></h3>
            <img src="<?= SystemURLs::getRootPath() ?>/Images/csvimport.png" width=50%>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <h3><?= gettext("Upload CSV File") ?></h3>
            <p style="color: red"> <?= $csvError ?></p>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="callout callout-info"><?= gettext("<b>DON'T FORGET</b> 
              <br>• your CSV file must have a <b>Title</b> header name column (with M. Mr. Mlle. etc ...),
              <br>• and a <b>gender</b> header name column (with 1 for a men, 2 for a women)") ?>
              <div class="row">
                <div class="col-lg-3">
                  • <?= gettext("Select <b>NOW</b> your <b>CSV separator</b>") ?>
                </div>
                <div class="col-lg-2">        
                  <select name="sSeperator" class="form-control input-sm">
                      <option value=",">,</option>
                      <option value=";">;</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-5">        
                  <?= gettext("• Last set correctly all the dates like : <b>YYYY-MM-DD or DD/MM/YYYY</b> ......... ") ?>
                </div>
                <div class="col-lg-4">                        
                  <span style="color: red"><?= gettext("This is proposed in the next step, read carefully your table.") ?></span>
                </div>
             </div>
          </div>
        </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <input class="icTinyButton" type="file" name="CSVfile"><br/>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-3">
            <input type="submit" class="btn btn-primary" value=" <?= gettext('Upload CSV File') ?> " name="UploadCSV">
          </div>
        </div>
      </form>
   </div>
</div>
<div class="box">
  <div class="box-header">
    <h3 class="box-title"><?= gettext('Clear Data')?></h3>
  </div>
  <div class="box-body">
    <button type="button" class="btn btn-danger" id="clear-people"><?= gettext('Clear Persons and Families') ?></button>
    <span id="import-success" style="color:green"></label>
<?php
}

if ($iStage == 3) {
?>
    <p class="MediumLargeText"><?= gettext('Data import successful.').' '.$importCount.' '.gettext('persons were imported') ?></p>
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

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
  $(document).ready(function(){
    $(".columns").select2();
  });
</script>

<script src="<?= SystemURLs::getRootPath() ?>/skin/js/CSVImport.js" ></script>
<?php
require 'Include/Footer.php';
?>