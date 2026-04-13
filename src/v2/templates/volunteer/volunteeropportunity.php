<?php
/*******************************************************************************
 *
 *  filename    : volunteeropportunity.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019/2/6 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';


// Récupération des stats par type d'opportunité bénévole
$opportunityTypeCounts = [];

$opportunityTypes = [];
$opportunities = EcclesiaCRM\VolunteerOpportunityQuery::create()
  ->filterByParentId(null)
  ->orderByName()
  ->find();

$opportunityTypeCounts = array_fill_keys($opportunityTypes, 0);

$allColors = [
  'bg-gradient-orange', 'bg-gradient-info', 'bg-gradient-light', 'bg-gradient-pink',
  'bg-gradient-lime', 'bg-gradient-lightblue', 'bg-gradient-primary', 'bg-gradient-secondary',
  'bg-gradient-success', 'bg-gradient-danger', 'bg-gradient-warning', 'bg-gradient-dark',
  'bg-gradient-indigo', 'bg-gradient-purple', 'bg-gradient-teal', 'bg-gradient-cyan',
  'bg-gradient-fuchsia', 'bg-gradient-blue', 'bg-gradient-olive', 'bg-gradient-maroon',
  'bg-gradient-navy', 'bg-gradient-gray', 'bg-gradient-gray-dark'
];

// Compte le nombre d'opportunités par type
foreach ($opportunities as $opportunity) {
  $childs = EcclesiaCRM\VolunteerOpportunityQuery::create()->findByParentId($opportunity->getId());
  $cnt = $childs->count() == 0 ? _('No sub opportunities') : $childs->count();  
  $opportunityTypeCounts[$opportunity->getName()] = $cnt;
}

$sortedTypes = array_keys($opportunityTypeCounts);
sort($sortedTypes);
$typeColors = [];
foreach ($sortedTypes as $i => $type) {
  $typeColors[$type] = $allColors[$i % count($allColors)];
}
uksort($opportunityTypeCounts, function($a, $b) {
  return strcasecmp($a, $b);
});

?>


<?php
$totalSubOpportunities = 0;
foreach ($opportunityTypeCounts as $cnt) {
    if (is_numeric($cnt)) {
        $totalSubOpportunities += $cnt;
    }
}
?>
<div class="mb-2 mt-3 text-center">
  <h4 class="mb-1">
    <i class="fas fa-hands-helping text-primary mr-2"></i>
    <?= _("Sub-Opportunities Overview") ?>
  </h4>
  <div class="text-muted small mb-2">
    <?= sprintf(_("There are <b>%d</b> different parent opportunity types."), count($opportunityTypeCounts)) ?><br />
    <?php if ($totalSubOpportunities > 0): ?>
      <?= sprintf(_("In total, there are <b>%d</b> sub-opportunities."), $totalSubOpportunities) ?>
    <?php else: ?>
      <?= _("No sub-opportunities found.") ?>
    <?php endif; ?>
  </div>
</div>

<div class="row mb-4">
  <?php foreach ($opportunityTypeCounts as $name => $count): ?>
    <?php $colorClass = isset($typeColors[$name]) ? $typeColors[$name] : 'bg-gradient-info'; ?>
    <div class="col-md-2 col-sm-6 col-xs-12">
      <div class="info-box <?= $colorClass ?> shadow-sm">
        <span class="info-box-icon">
          <i class="fas fa-hands-helping"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text"><?= htmlspecialchars($name) ?></span>
          <span class="info-box-number"><?= $count ?></span>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="alert alert-warning d-flex align-items-center gap-2" style="font-size:1.1em;">
  <i class="fas fa-user-lock fa-lg mr-2 text-black" aria-hidden="true"></i>
  <div>
    <strong><?= _('Restricted Access') ?> :</strong> <?= _('Only administrators can add, modify or delete volunteer opportunities. If you need to make changes, please contact your site administrator.') ?>
  </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
  <div class="card-header border-0" style="display:flex; justify-content:space-between; align-items:center;">
    <h3 class="card-title mb-0"><i class="fas fa-hands-helping mr-2" aria-hidden="true"></i> <?= _('Volunteer Opportunities') ?></h3>
    <?php if ( $isVolunteerOpportunityEnabled ):// only an admin can modify the options?>
    <a href="#" class="btn btn-success btn-lg shadow-sm font-weight-bold py-2 px-4 ml-auto" id="add-new-volunteer-opportunity">
        <i class="fas fa-user-friends mr-2"></i> Ajouter Opportunité de Bénévolat
    </a>
    <?php endif; ?>
  </div>
  <div class="card-body p-0">    
    <table class="table table-striped table-bordered" id="VolunteerOpportunityTable" cellpadding="5" cellspacing="0"  width="100%"></table>
  </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/volunteer/VolunteerOpportunityCommon.js"></script>

<script type="module" src="<?= $sRootPath ?>/skin/js/volunteer/VolunteerOpportunity.js" ></script>


<?php require $sRootDocument . '/Include/Footer.php';?>
