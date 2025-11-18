<?php
/*******************************************************************************
 *
 *  filename    : volunteeropportunity.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019/2/6 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';

use EcclesiaCRM\Service\VolunteerService;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\Cart;

$hier = VolunteerService::getHirearchicalView($volID, $volID);

?>

<?php
if (SessionUser::getUser()->isShowCartEnabled()) {
?>
    <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartVolunterId="<?= $volID ?>"> <i class="fas fa-cart-plus"></i> <span class="cartActionDescription"><?= _("Add to Cart") ?></span></a>
<?php
}
?>

<?php
if (
    SessionUser::getUser()->isDeleteRecordsEnabled() || SessionUser::getUser()->isAddRecordsEnabled()
    || SessionUser::getUser()->isMenuOptionsEnabled()
) {
?>
    <a class="btn btn-app bg-orange" id="add-event"><i class="far fa-calendar-plus"></i><?= _("Appointment") ?></a>
<?php
}
?>

<?php
/*if (SessionUser::getUser()->isManageGroupsEnabled() || $_SESSION['bManageGroups']) { // use session variable for an current group manager
?>
    <form method="POST" action="<?= $sRootPath ?>/v2/group/reports" style="display:inline">
        <input type="hidden" id="GroupID" name="GroupID" value="<?= $iGroupID ?>">
        <button type="submit" class="btn btn-app bg-green exportCheckOutCSV"><i class="fas fa-file-pdf"></i><?= _("Group reports") ?></button>
    </form>

    <a class="btn btn-app bg-purple" id="groupbadge" data-groupid="<?= $iGroupID ?>" data-toggle="tooltip"
        data-placement="bottom" title="<?= _("Create here your badges or QR-Code to call the register with them") ?>"> <i
            class="fas fa-id-badge"></i> <span
            class="cartActionDescription"><?= _("Group Badges") ?></span></a>

    <a class="btn btn-app bg-yellow-gradient <?= $thisGroup->isIncludeInEmailExport()?'':'disabled' ?> export-vcard-button" data-toggle="tooltip" data-placement="bottom" title="" href="<?= $sRootPath ?>/api/groups/addressbook/extract/<?= $iGroupID ?>" data-original-title="<?= _("Click to create an addressbook of the Group") ?>"><i class="far fa-id-card">
        </i> <?= _('Address Book') ?></a>
<?php
}*/
?>

<div class="clt">
    <ul>
        <li>
            <?= $hier ?>
        </li>
    </ul>
</div>


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
  <table class="table table-striped table-bordered" id="VolunteerOpportunityTableMembers" cellpadding="5" cellspacing="0"  width="100%"></table>
</div>

<script nonce="<?= $CSPNonce ?>">
    window.CRM.volID = <?= $volID ?>;
    var isShowable = true;
</script>

<script type="module" src="<?= $sRootPath ?>/skin/js/volunteer/VolunteerOpportunityView.js" ></script>


<?php require $sRootDocument . '/Include/Footer.php';?>
