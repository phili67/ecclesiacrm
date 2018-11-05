<?php
/*******************************************************************************
 *
 *  filename    : PropertyEditor.php
 *  last change : 2003-01-07
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001, 2002 Deane Barker
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\PropertyTypeQuery;
use EcclesiaCRM\PropertyType;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Property;
use Propel\Runtime\ActiveQuery\Criteria;

// Security: User must have property and classification editing permission
if (!$_SESSION['user']->isMenuOptionsEnabled()) {
    Redirect('Menu.php');
    exit;
}

$sClassError = '';
$sNameError = '';

//Get the PropertyID
$iPropertyID = 0;
if (array_key_exists('PropertyID', $_GET)) {
    $iPropertyID = InputUtils::LegacyFilterInput($_GET['PropertyID'], 'int');
}

//Get the Type
$sType = InputUtils::LegacyFilterInput($_GET['Type'], 'char', 1);

//Based on the type, set the TypeName
switch ($sType) {
    case 'p':
        $sTypeName = gettext('Person');
        break;
    case 'f':
        $sTypeName = gettext('Family');
        break;
    case 'g':
        $sTypeName = gettext('Group');
        break;
    case 'm':
        $sTypeName = gettext('Menu');
        break;
    default:
        Redirect('Menu.php');
        exit;
        break;
}

//Set the page title
$sPageTitle = $sTypeName.' : '.gettext('Property Editor');

$bError = false;
$iType = 0;

//Was the form submitted?
if (isset($_POST['Submit'])) {
    $sName        = InputUtils::FilterString($_POST['Name']);
    $sDescription = InputUtils::FilterString($_POST['Description']);
    $iClass       = InputUtils::FilterInt($_POST['Class'], 'int');
    $sPrompt      = InputUtils::FilterString($_POST['Prompt']);

    //Did they enter a name?
    if (strlen($sName) < 1) {
        $sNameError = '<br><font color="red">'.gettext('You must enter a name').'</font>';
        $bError = true;
    }

    //Did they select a Type
    if (strlen($iClass) < 1) {
        $sClassError = '<br><font color="red">'.gettext('You must select a type').'</font>';
        $bError = true;
    }

    //If no errors, let's update
    if (!$bError) {
        $propertyType = PropertyTypeQuery::Create()
                        ->findOneByPrtId($iClass);
                        
        $prt_class = $propertyType->getPrtClass();

        //Vary the SQL depending on if we're adding or editing
        if ($iPropertyID == 0) {
            $property = new Property();
            
            $property->setProPrtId ($iClass);
            $property->setProName ($sName);
            $property->setProClass ($prt_class);
            $property->setProDescription ($sDescription);
            $property->setProPrompt ($sPrompt);
            $property->setProPrompt ($sPrompt);
            
            $property->save();
        } else {
            $property = PropertyQuery::Create()
                      ->findOneByProId ($iPropertyID);
                      
            $property->setProPrtId ($iClass);
            $property->setProName ($sName);
            $property->setProClass ($prt_class);
            $property->setProDescription ($sDescription);
            $property->setProPrompt ($sPrompt);
            $property->setProPrompt ($sPrompt);
            
            $property->save();
        }

        //Route back to the list
        Redirect('PropertyList.php?Type='.$sType);
    }
} else {
    if ($iPropertyID != 0) {
        //Get the data on this property
        $property = PropertyQuery::Create()
                    ->findOneByProId ($iPropertyID);
        
        //Assign values locally
        $sName = $property->getProName();
        $sDescription = $property->getProDescription();
        $iType = $property->getProPrtId();
        $sPrompt = $property->getProPrompt();
    } else {
        $sName = '';
        $sDescription = '';
        $iType = 0;
        $sPrompt = '';
    }
}

//Get the Property Types
$ormPropertyTypes = PropertyTypeQuery::Create()
                      ->filterByPrtClass($sType)
                      ->find();

require 'Include/Header.php';

?>
<div class="box box-body">
  <form method="post" action="PropertyEditor.php?PropertyID=<?= $iPropertyID ?>&Type=<?= $sType ?>">
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <label for="Class"><?= gettext('Type') ?>:</label>
                <select  class="form-control input-small" name="Class">
                    <option value=""><?= gettext('Select Property Type') ?></option>
                  <?php                    
                    foreach ($ormPropertyTypes as $ormPropertyType) {
                  ?>
                      <option value="<?= $ormPropertyType->getPrtId()?>" <?= ($iType == $ormPropertyType->getPrtId() || gettext($ormPropertyType->getPrtName()) == $sTypeName)?'selected':'' ?>><?= gettext($ormPropertyType->getPrtName()) ?></option>
                  <?php
                    }
                  ?>
                </select>
                <?= $sClassError ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="Name"><?= gettext('Name') ?>:</label>
                <input class="form-control input-small" type="text" name="Name" value="<?= htmlentities(stripslashes($sName), ENT_NOQUOTES, 'UTF-8') ?>" size="50">
                <?= $sNameError ?>
           </div>
       </div>
       <div class="row">
            <div class="col-md-6">
                <label for="Description">"<?= gettext('A') ?> <?= $sTypeName ?><BR><?= gettext('with this property..') ?>":</label>
                <textarea class="form-control input-small" name="Description" cols="60" rows="3"><?= htmlentities(stripslashes($sDescription), ENT_NOQUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="Prompt"><?= gettext('Prompt') ?>:</label>
                <input class="form-control input-small" type="text" name="Prompt" value="<?= htmlentities(stripslashes($sPrompt), ENT_NOQUOTES, 'UTF-8') ?>" size="50">
                <span class="SmallText"><?= gettext('Entering a Prompt value will allow the association of a free-form value.') ?></span>
            </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <input type="submit" class="btn btn-primary" name="Submit" value="<?= gettext('Save') ?>">&nbsp;<input type="button" class="btn btn-default" name="Cancel" value="<?= gettext('Cancel') ?>" onclick="document.location='PropertyList.php?Type=<?= $sType ?>';">
        </div>
        </div>
    </div>
</form>
</div>

<?php require 'Include/Footer.php' ?>
