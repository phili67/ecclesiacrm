<?php
/*******************************************************************************
 *
 *  filename    : UpdateAllLatLon.php
 *  last change : 2023-05-15
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2019 Philippe Logel all right reserved not MIT licence                
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\FamilyQuery;

require $sRootDocument . '/Include/Header.php';

$families = FamilyQuery::create()->filterByDateDeactivated(NULL)->filterByLongitude(0)->_and()->filterByLatitude(0)->limit(100)->find();

if ($families->count() > 0) {
?>
<div class="card card-body box-info">

  <div class="card-header border-1">
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
  if ($families->count() > 0) {
?>
    <div class=card card-warning">
        <div class="card-header  border-1">
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
?>

<?php require $sRootDocument . '/Include/Footer.php'; ?>