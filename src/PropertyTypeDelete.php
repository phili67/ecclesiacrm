<?php
/*******************************************************************************
 *
 *  filename    : PropertyTypeDelete.php
 *  last change : 2003-06-04
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2001-2003 Deane Barker, Chris Gebhardt
  *
 ******************************************************************************/

//Include the function library
require 'Include/Config.php';
require 'Include/Functions.php';

use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\PropertyTypeQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Record2propertyR2pQuery;

// Security: User must have property and classification editing permission
if (!$_SESSION['user']->isMenuOptionsEnabled()) {
    Redirect('Menu.php');
    exit;
}

//Set the page title
$sPageTitle = gettext('Property Type Delete Confirmation');

//Get the PersonID from the querystring
$iPropertyTypeID = InputUtils::LegacyFilterInput($_GET['PropertyTypeID'], 'int');

//Do we have deletion confirmation?
if (isset($_GET['Confirmed'])) {
    $propType = PropertyTypeQuery::Create()->findOneByPrtId($iPropertyTypeID);
    if (!is_null($propType)) {
      $propType->delete();
    }

    $properties = PropertyQuery::Create()->findByProPrtId ($iPropertyTypeID);
    
    foreach ($properties as $property) {
      $recProps = Record2propertyR2pQuery::Create()->findByR2pProId ($property->getProId());
      if(!is_null ($recProps)) {
        $recProps->delete();
      }
    }
    
    if (!is_null ($properties)) {
      $properties->delete();
    }

    Redirect('PropertyTypeList.php');
}

$propType = PropertyTypeQuery::Create()->findOneByPrtId($iPropertyTypeID);

require 'Include/Header.php';

if (isset($_GET['Warn'])) {
    ?>
  <p align="center" class="LargeError">
    <?= '<b>'.gettext('Warning').': </b>'.gettext('This property type is still being used by at least one property.').'<BR>'.gettext('If you delete this type, you will also remove all properties using').'<BR>'.gettext('it and lose any corresponding property assignments.'); ?>
  </p>
<?php
} ?>

<p align="center" class="MediumLargeText">
  <?= gettext('Please confirm deletion of this Property Type') ?>: <b><?= gettext($propType->getPrtName()) ?></b>
</p>

<p align="center">
  <a href="<?= SystemURLs::getRootPath() ?>/PropertyTypeDelete.php?Confirmed=Yes&PropertyTypeID=<?php echo $iPropertyTypeID ?>" class="btn btn-danger"><?= gettext('Yes, delete this record') ?></a>
  &nbsp;&nbsp;
  <a href="<?= SystemURLs::getRootPath() ?>/PropertyTypeList.php?Type=<?= $sType ?>" class="btn btn-primary"><?= gettext('No, cancel this deletion') ?></a>

</p>

<?php require 'Include/Footer.php' ?>
