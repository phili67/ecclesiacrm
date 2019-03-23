<?php
/*******************************************************************************
 *
 *  filename    : PastoralCare.php
 *  last change : 2018-07-12
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without any authorization
 *
 ******************************************************************************/
use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';
?>


<?php
  if ($ormPastoralCares->count() == 0) {
?>
<div class="callout callout-info"><?= _("Please add some records with the button below.") ?></div>

<?php
  }
?>

<div class="box box-primary box-body">
  <div class="btn-group">
<?php
  foreach ($ormPastoralTypeCares as $ormPastoralTypeCare) {
    $type_and_desc = $ormPastoralTypeCare->getTitle().((!empty($ormPastoralTypeCare->getDesc()))?" (".$ormPastoralTypeCare->getDesc().")":"");
?>
    <a class="btn btn-app newPastorCare" data-typeid="<?= $ormPastoralTypeCare->getId() ?>" data-visible="<?= ($ormPastoralTypeCare->getVisible())?1:0 ?>" data-typeDesc="<?= $type_and_desc ?>"><i class="fa fa-sticky-note"></i><?= _("Add Pastoral Care Notes") ?></a>
<?php
    break;
  }
?>
    <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
       <span class="caret"></span>
       <span class="sr-only">Menu déroulant</span>
    </button>
    <ul class="dropdown-menu" role="menu">
      <?php
         foreach ($ormPastoralTypeCares as $ormPastoralTypeCare) {
           $type_and_desc = $ormPastoralTypeCare->getTitle().((!empty($ormPastoralTypeCare->getDesc()))?" (".$ormPastoralTypeCare->getDesc().")":"");
      ?>
        <li> <a class="newPastorCare" data-typeid="<?= $ormPastoralTypeCare->getId() ?>" data-visible="<?= ($ormPastoralTypeCare->getVisible())?1:0 ?>" data-typeDesc="<?= $type_and_desc ?>"><?= $type_and_desc ?></a></li>
      <?php
         }
      ?>
    </ul>
  </div>
  <a class="btn btn-app" href="<?= $sRootPath ?>/PrintPastoralCare.php?PersonID=<?= $currentPersonID ?>"><i class="fa fa-print"></i> <?= _("Printable Page") ?></a>
  
  <div class="btn-group pull-right">
    <a class="btn btn-app filterByPastor" data-personid="<?= SessionUser::getUser()->getPerson()->getId() ?>"><i class="fa fa-sticky-note"></i><?= SessionUser::getUser()->getPerson()->getFullName()  ?></a>
    <button type="button" class="btn btn-app dropdown-toggle" data-toggle="dropdown">
       <span class="caret"></span>
       <span class="sr-only">Menu déroulant</span>
    </button>
    <ul class="dropdown-menu" role="menu">
      <li> <a class="filterByPastorAll"><?= _("Everyone") ?></a></li>
      <?php
         foreach ($ormPastors as $ormPastor) {
      ?>
        <li> <a class="filterByPastor" data-personid="<?= $ormPastor->getPastorId() ?>"><?= $ormPastor->getPastorName() ?></a></li>
      <?php
         }
      ?>
    </ul>
  </div>
  <div class="pull-right" style="margin-right:15px;margin-top:10px">
    <h4><?= _("Filters") ?></h4>
  </div>
</div>

<?php
  if ($ormPastoralCares->count() > 0) {
?>
<ul class="timeline">
  <!-- timeline time label -->
  <li class="time-label">
        <span class="bg-red">
          <?= (new DateTime(''))->format($sDateFormatLong) ?>
        </span>
  </li>
  <!-- /.timeline-label -->
  <!-- timeline item -->
<?php
  foreach ($ormPastoralCares as $ormPastoralCare) {
?>
  <li class="item-<?= $ormPastoralCare->getPastorId()?> all-items">
    <i class="fa fa-clock-o bg-blue"></i>
    <div class="timeline-item">
      <span class="time"><i class="fa fa-clock-o"></i> <?= $ormPastoralCare->getDate()->format($sDateFormatLong.' H:i:s') ?></span>

      <h3 class="timeline-header"><b><?= $ormPastoralCare->getPastoralCareType()->getTitle()."</b>  : " ?><a href="<?= $sRootPath."/PersonView.php?PersonID=".$ormPastoralCare->getPastorId() ?>"><?= $ormPastoralCare->getPastorName() ?></a></h3>
      <div class="timeline-body">
      <?php if ($ormPastoralCare->getVisible() || $ormPastoralCare->getPastorId() == $currentPastorId) {
         echo $ormPastoralCare->getText();
      ?>
      </div>
      <div class="timeline-footer">
      <?php 
        if (SessionUser::getUser()->isAdmin() || $ormPastoralCare->getPastorId() == $currentPastorId) { 
      ?>
        <a class="btn btn-primary btn-xs modify-pastoral" data-id="<?= $ormPastoralCare->getId() ?>"><?= _("Modify") ?></a>
        <a class="btn btn-danger btn-xs delete-pastoral" data-id="<?= $ormPastoralCare->getId() ?>"><?= _("Delete") ?></a>
      <?php 
        } 
      ?>
      </div>
      <?php
        } else {
      ?>
      <div class="timeline-footer">
        <a class="btn btn-danger btn-xs delete-pastoral" data-id="<?= $ormPastoralCare->getId() ?>"><?= _("Delete") ?></a>
      </div>
      <?php
        }
      ?>
    </div>
  </li>
<?php
  }
?>
  <!-- END timeline item -->
  <li>
    <i class="fa fa-clock-o bg-gray"></i>
  </li>
</ul>

<?php
  }
?>

<center>
<input type="button" class="btn btn-success" value="<?= _('Return') ?>" name="Cancel" onclick="javascript:document.location='<?= $sRootPath.'/PersonView.php?PersonID='.$currentPersonID ?>';">
</center>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= $sCSPNonce ?>">  
  var currentPersonID = <?= $currentPersonID ?>;
  var currentPastorId = <?= $currentPastorId ?>;  
</script>

<script src="<?= $sRootPath ?>/skin/js/people/PastoralCare.js"></script>

