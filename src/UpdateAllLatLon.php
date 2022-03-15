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
<div class="card card-body box-info">

  <div class="card-header ">
    <h3 class="card-title"><?= _('Families without Geo Info') ?> : <?= $families->count()  ?></h3>
    <div class="card-tools pull-right">
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fas fa-minus"></i></button>
      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fas fa-times"></i></button>
    </div>
  </div>
  <div class="card-body ">

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
    <div class=card card-warning">
        <div class="card-header  ">
            <h3 class="card-title"><?= _('No coordinates found') ?></h3>
            <div class="card-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fas fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div class="card-body ">
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
