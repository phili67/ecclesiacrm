<?php
/*******************************************************************************
 *
 *  filename    : volunteeropportunity.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019/2/6 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>


<div class="card">
  <div class="card-header border-1" style="display:flex; justify-content:space-between; align-items:center;">
    <h3 class="card-title mb-0"><i class="fas fa-hands-helping mr-2" aria-hidden="true"></i> <?= _('Volunteer Opportunities') ?></h3>
    <?php if ( $isVolunteerOpportunityEnabled ):// only an admin can modify the options?>
    <a href="#" class="btn btn-success btn-lg shadow-sm font-weight-bold py-2 px-4 ml-auto" id="add-new-volunteer-opportunity">
        <i class="fas fa-user-friends mr-2"></i> Ajouter Opportunité de Bénévolat
    </a>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>   <?= _('Only an admin can modify or delete this records.') ?></div>
    <table class="table table-striped table-bordered" id="VolunteerOpportunityTable" cellpadding="5" cellspacing="0"  width="100%"></table>
  </div>
</div>

<script src="<?= $sRootPath ?>/skin/js/volunteer/VolunteerOpportunityCommon.js"></script>

<script type="module" src="<?= $sRootPath ?>/skin/js/volunteer/VolunteerOpportunity.js" ></script>


<?php require $sRootDocument . '/Include/Footer.php';?>
