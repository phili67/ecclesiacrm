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
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\PersonVolunteerOpportunityQuery;
use EcclesiaCRM\Utils\MiscUtils;

$hier = VolunteerService::getHirearchicalView($volID, $volID);

?>

<?php
if (SessionUser::getUser()->isShowCartEnabled()) {
?>
    <a class="btn btn-app AddToGroupCart" id="AddToGroupCart" data-cartVolunterId="<?= $volID ?>"> <i class="fas fa-cart-plus"></i> <span class="cartActionDescription"><?= _("Add to Cart") ?></span></a>
<?php
}
?>

<a class="btn btn-app" id="modify-name" data-cartVolunterId="<?= $volID ?>" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?= _("To modify the name fo the volunteer opportunity") ?>"><i class="fas fa-pencil-alt"></i><?=  _("Modify Name") ?></a>

<button class="btn btn-app bg-maroon" id="deleteGroupButton"><i class="fas fa-trash-alt"></i><?=  _("Remove Volunteer Opportunities") ?></button>

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

<a class="btn btn-app bg-yellow-gradient  export-vcard-button <?= $thisVolOpp->isIncludeInEmailExport()?'':'disabled' ?> " data-toggle="tooltip" data-placement="bottom" title="" href="/api/volunteeropportunity/addressbook/extract/<?=  $volID ?>" data-original-title="Cliquer pour créer un carnet d'adresse du groupe"><i class="far fa-id-card">
        </i><?=  _("Contacts") ?></a>

<?php

$persons = PersonVolunteerOpportunityQuery::create()
            ->usePersonQuery()
                ->addAsColumn('FirstName', PersonTableMap::COL_PER_FIRSTNAME)
                ->addAsColumn('CellPhone', PersonTableMap::COL_PER_CELLPHONE)
                ->addAsColumn('Email', PersonTableMap::COL_PER_EMAIL)
                ->addAsColumn('LastName', PersonTableMap::COL_PER_LASTNAME)
                ->addAsColumn('PersonId', PersonTableMap::COL_PER_ID)
                ->useFamilyQuery()
                    ->addAsColumn('FamCellPhone', FamilyTableMap::COL_FAM_CELLPHONE)
                    ->addAsColumn('FamEmail', FamilyTableMap::COL_FAM_EMAIL)
                ->endUse()
            ->filterByDateDeactivated(NULL)
            ->endUse()
            ->addAscendingOrderByColumn('person_per.per_LastName')
            ->addAscendingOrderByColumn('person_per.per_FirstName')
            ->findByVolunteerOpportunityId($volID);

// Email Group link

$sEmailLink = '';
$sPhoneLink = '';
$sCommaDelimiter = ', ';

foreach ($persons as $person) {
    $sEmail = MiscUtils::SelectWhichInfo($person->getEmail(), $person->getFamEmail(), false);
    if ($sEmail) {
        $sEmailLink .= $sEmail . SessionUser::getUser()->MailtoDelimiter();
    }

    $sPhone = MiscUtils::SelectWhichInfo($person->getCellPhone(), $person->getFamCellPhone(), false);
    if ($sPhone) {
        if (!stristr($sPhoneLink, $sPhone)) {
            $sPhoneLink .= $sPhone .= $sCommaDelimiter;
        }
    }
}

if ($sEmailLink) {
    // Add default email if default email has been set and is not already in string
    if (SystemConfig::getValue('sToEmailAddress') != '' && !stristr($sEmailLink, SystemConfig::getValue('sToEmailAddress'))) {
        $sEmailLink .= SessionUser::getUser()->MailtoDelimiter() . SystemConfig::getValue('sToEmailAddress');
    }
    $sEmailLink = urlencode($sEmailLink);  // Mailto should comply with RFC 2368

    if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
        // Display link
?>
        <div class="btn-group">
            <a class="btn btn-app <?= $thisVolOpp->isIncludeInEmailExport()?'':'disabled' ?> email-button" href="mailto:<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank"><i class="far fa-paper-plane"></i><?= _("Email Group") ?></a>
        </div>

        <div class="btn-group">
            <a class="btn btn-app <?= $thisVolOpp->isIncludeInEmailExport()?'':'disabled' ?> email-cci-button" href="mailto:?bcc=<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank"><i class="fas fa-paper-plane"></i><?= _("Email (BCC)") ?></a>
        </div>

    <?php
    }
}


if ($sPhoneLink) {
    if (SessionUser::getUser()->isEmailEnabled()) { // Does user have permission to email groups
        // Display link
    ?>
        <a class="btn btn-app <?= $thisVolOpp->isIncludeInEmailExport()?'':'disabled' ?> sms-button" href="javascript:void(0)" onclick="allPhonesCommaD()"><i class="fas fa-mobile"></i><?= _('Text Group') ?></a>
        <script nonce="<?= $CSPNonce ?>">
            function allPhonesCommaD() {
                prompt("<?= _("Press CTRL + C to copy all group members\' phone numbers") ?>", "<?= mb_substr($sPhoneLink, 0, -2) ?>")
            };
        </script>
<?php
    }
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

    <a class="btn btn-app bg-yellow-gradient <?= $thisVolOpp->isIncludeInEmailExport()?'':'disabled' ?> export-vcard-button" data-toggle="tooltip" data-placement="bottom" title="" href="<?= $sRootPath ?>/api/groups/addressbook/extract/<?= $iGroupID ?>" data-original-title="<?= _("Click to create an addressbook of the Group") ?>"><i class="far fa-id-card">
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


<?php if ($isVolunteerOpportunityEnabled) { // only an admin can modify the options
?>
    <p align="center"><button class="btn btn-primary" id="add-new-volunteer-opportunity"><?= _("Add Volunteer Opportunity") ?></button></p>
<?php
} else {
?>
    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> <?= _('Only an admin can modify or delete this records.') ?></div>
<?php
}


?>


<div class="row">
<?php
    if ($_SESSION['bManageGroups'] or SessionUser::getUser()->isManageGroupsEnabled()) {
    ?>
        <div class="col group_Side_bar">
            <div class="sticky-top">
                <div id="accordion">
                <?php
            }
            if (SessionUser::getUser()->isManageGroupsEnabled()) {
                ?>
                    <div class="card group_accordion">
                        <div class="card-header border-1 group_header_accordion" id="headingQuickSettings">
                            <h3 class="card-title">
                                <i class="fas fa-sliders fa-fw"></i> <button class="btn btn-link" data-toggle="collapse" data-target="#collapseQuickSettings" aria-expanded="true" aria-controls="collapseQuickSettings">
                                    <?= _('Quick Settings') ?>
                                </button>
                            </h3>
                            <div class="card-tools pull-right">
                                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#collapseQuickSettings" aria-expanded="true" aria-controls="collapseQuickSettings"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div id="collapseQuickSettings" class="collapse show" aria-labelledby="headingQuickSettings" data-parent="#accordion" style="">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-5"><label><?= _("Group is") ?></label> : </div>
                                    <div class="col-md-7">
                                        <input data-width="100" class="btn btn-primary btn-sm" id="isVolunteersActive" type="checkbox" data-toggle="toggle" data-on="<?= _('Active') ?>" data-off="<?= _('Disabled') ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5"><label><?= _("The emails are") ?></label> : </div>
                                    <div class="col-md-7">
                                        <input data-width="100" class="btn btn-primary btn-sm" id="isVolunteersEmailExport" type="checkbox" data-toggle="toggle" data-on="<?= _('Include') ?>" data-off="<?= _('Exclude') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    

                </div>
            </div>
        </div>
    <?php
                }
    ?>

<div class="col" ?>
    <div class="card">
        <div class="card-header border-1">
            <h3 class="card-title"><i class="fas fa-users"></i> <?= _("Manage Group Members") ?>:</h3>
            <div class="card-tools pull-right">
                <button class="btn btn-primary" type="button">
                    <?= _('Total Members') ?> <span class="badge  bg-white" id="iTotalMembers"></span>
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php
            if (SessionUser::getUser()->isManageGroups() || $is_group_manager == true) {
            ?>

                <div class="row">
                    <div class="col-md-1">
                        <?= _("Add") ?>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control personSearch  select2" name="addGroupMember" style="width:100%"></select>
                    </div>
                </div>
                <br>
            <?php
            }
            ?>
            <!-- START GROUP MEMBERS LISTING  -->
            <table class="table table-striped table-bordered" id="VolunteerOpportunityTableMembers" cellpadding="5" cellspacing="0" width="100%"></table>
            <!-- END GROUP MEMBERS LISTING -->
        </div>
    </div>


</div>
</div>

<link href="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

<script src="<?= $sRootPath ?>/skin/external/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
<script src="<?= $sRootPath ?>/skin/external/bootstrap-colorpicker/bootstrap-colorpicker.min.js"
    type="text/javascript"></script>

<script src="<?= $sRootPath ?>/skin/external/ckeditor/ckeditor.js"></script>
<script src="<?= $sRootPath ?>/skin/js/ckeditor/ckeditorextension.js"></script>

<script nonce="<?= $CSPNonce ?>">
    window.CRM.volID = <?= $volID ?>;
    var isShowable = true;
    var sPageTitle = "<?= $sPageTitle ?>";
    
    window.CRM.isActive = <?= ($thisVolOpp->getActive() == 'true') ? 'true' : 'false' ?>;
    window.CRM.isIncludeInEmailExport = <?= $thisVolOpp->isIncludeInEmailExport() ? 'true' : 'false' ?>;    

    window.CRM.churchloc = {
        lat: parseFloat(<?= ChurchMetaData::getChurchLatitude() ?>),
        lng: parseFloat(<?= ChurchMetaData::getChurchLongitude() ?>)
    };
    window.CRM.mapZoom = <?= SystemConfig::getValue("iLittleMapZoom") ?>;
    window.CRM.address = '';
</script>

<script type="module" src="<?= $sRootPath ?>/skin/js/volunteer/VolunteerOpportunityView.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/calendar/EventEditor.js"></script>

<?php
if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/OpenStreetMapEvent.js"></script>
<?php
} else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps') {
?>
    <!--Google Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>"></script>

    <script src="<?= $sRootPath ?>/skin/js/calendar/GoogleMapEvent.js"></script>
<?php
} else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
?>
    <script src="<?= $sRootPath ?>/skin/js/calendar/BingMapEvent.js"></script>
<?php
}
?>

