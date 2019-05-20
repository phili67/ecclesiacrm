<?php
/*******************************************************************************
 *
 *  filename    : UpdateAllLatLon.php
 *  last change : 2013-02-02
 *  website     : http://www.ecclesiacrm.com
 *
 ******************************************************************************/

use EcclesiaCRM\FamilyQuery;

require 'Include/Config.php';
require 'Include/Functions.php';

$sPageTitle = _('Update Latitude & Longitude');
require 'Include/Header.php';

$families = FamilyQuery::create()->filterByLongitude(0)->_and()->filterByLatitude(0)->limit(100)->find();

if ($families->count() > 0) {
?>
<div class="box box-body box-info">

  <div class="box-header with-border">
    <h3 class="box-title"><?= _('Families without Geo Info') ?> : <?= $families->count()  ?></h3>
    <div class="box-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
    </div>
  </div>
  <div class="box-body ">

  <ul>
    <?php
    foreach ($families as $family) {
        $family->updateLanLng();
        $sNewLatitude = $family->getLatitude();
        $sNewLongitude = $family->getLongitude();
        if (!empty($sNewLatitude)) {
    ?>
          <li><?=  $family->getName() . ' ' . _('Latitude'). ' ' . $sNewLatitude . ' ' . _('Longitude ') . ' ' . $sNewLongitude ?></li>
    <?php
        }
    }
  ?>
  </ul>
  </div>
</div>
<?php 
}
  $families = FamilyQuery::create()->filterByLongitude(0)->_and()->filterByLatitude(0)->limit(100)->find();
  if ($families->count() > 0) {
?>
    <div class="box box-warning">
        <div class="box-header  with-border">
            <h3 class="box-title"><?= _('No coordinates found') ?></h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body ">
          <ul>
            <?php
              foreach ($families as $family) {
            ?>
                <li><a href="<?= $family->getViewURI() ?>"><?= $family->getName() ?></a> <?= $family->getAddress() ?></li>
            <?php
            } 
            ?>
          </ul>
        </div>
    </div>
<?php
}

require 'Include/Footer.php';
