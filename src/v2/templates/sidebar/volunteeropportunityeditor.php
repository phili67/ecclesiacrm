<?php
/*******************************************************************************
 *
 *  filename    : volunteeropportunityeditor.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019/2/6 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>

<?php if ( $isVolunteerOpportunityEnabled ) {// only an admin can modify the options
?>
    <p align="center"><button class="btn btn-primary" id="add-new-volunteer-opportunity"><?= _("Add Volunteer Opportunity") ?></button></p>
<?php
} else {
?>
    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>   <?= _('Only an admin can modify or delete this records.') ?></div>
<?php
}
?>
<div class="card card-body">
  <table class="table table-striped table-bordered" id="VolunteerOpportunityTable" cellpadding="5" cellspacing="0"  width="100%"></table>
</div>

<script src="<?= $sRootPath ?>/skin/js/sidebar/VolunteerOpportunity.js" ></script>


<?php
  require $sRootDocument . '/Include/Footer.php';
?>
