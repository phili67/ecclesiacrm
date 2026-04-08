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

$currentUser = SessionUser::getUser();
$canShowCart = $currentUser->isShowCartEnabled();
$canManageVolunteers = $currentUser->isManageVolunteersEnabled($volID);
$canManageAppointments = $currentUser->isDeleteRecordsEnabled() || $currentUser->isAddRecordsEnabled() || $currentUser->isMenuOptionsEnabled();
$canEmail = $currentUser->isEmailEnabled();
$isAdmin = $currentUser->isAdmin();
$isEmailExportEnabled = $thisVolOpp->isIncludeInEmailExport();
$emailExportStateClass = $isEmailExportEnabled ? '' : 'disabled';
?>

<div class="card card-outline card-warning mb-3">
    <div class="card-header border-0">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i><?= _("Actions") ?></h3>
    </div>
    
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 align-items-center" style="gap: 0.5rem;">
            <?php if ($canShowCart) { ?>
                    <a class="btn btn-sm btn-outline-primary AddToGroupCart" id="AddToGroupCart" data-cartVolunterId="<?= $volID ?>">
                        <i class="fas fa-cart-plus mr-1"></i>
                        <span class="cartActionDescription"><?= _("Add to Cart") ?></span>
                    </a>
                
            <?php } ?>

            <?php if ($canManageVolunteers) { ?>
                    <a class="btn btn-sm btn-outline-secondary" id="modify-name" data-id="<?= $volID ?>" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?= _("To modify the name of the volunteer opportunity") ?>">
                        <i class="fas fa-pencil-alt mr-1"></i><?= _("Modify Name") ?>
                    </a>
                    <?php if ($isAdmin) { ?>
                        <button class="btn btn-sm btn-outline-danger" id="deleteVolunteerOpportunityButton">
                            <i class="fas fa-trash-alt mr-1"></i><?= _("Remove Volunteer Opportunities") ?>
                        </button>
                    <?php } ?>
                
            <?php } ?>

            <?php if ($canManageAppointments) { ?>
                    <a class="btn btn-sm btn-outline-warning" id="add-event"><i class="far fa-calendar-plus mr-1"></i><?= _("Appointment") ?></a>
                
            <?php } ?>

            <?php if ($canEmail) { ?>
                    <a class="btn btn-sm btn-outline-info export-vcard-button <?= $emailExportStateClass ?>" data-toggle="tooltip" data-placement="bottom" title="" href="/api/volunteeropportunity/addressbook/extract/<?= $volID ?>" data-original-title="<?= _('Click to create a group address book') ?>">
                        <i class="far fa-id-card mr-1"></i><?= _("Contacts") ?>
                    </a>                
            <?php } ?>


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

                if ($canEmail) { // Does user have permission to email groups
                    // Display link
            ?>
                        <a class="btn btn-sm btn-outline-primary <?= $emailExportStateClass ?> email-button" href="mailto:<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank"><i class="far fa-paper-plane mr-1"></i><?= _("Email Group") ?></a>
                        <a class="btn btn-sm btn-outline-secondary <?= $emailExportStateClass ?> email-cci-button" href="mailto:?bcc=<?= mb_substr($sEmailLink, 0, -3) ?>" target="_blank"><i class="fas fa-paper-plane mr-1"></i><?= _("Email (BCC)") ?></a>                    

                <?php
                }
            }


            if ($sPhoneLink) {
                if ($canEmail) { // Does user have permission to email groups
                    // Display link
                ?>
                        <a class="btn btn-sm btn-outline-success <?= $emailExportStateClass ?> sms-button" href="javascript:void(0)" onclick="allPhonesCommaD()"><i class="fas fa-mobile-alt mr-1"></i><?= _('Text Group') ?></a>
                    <script nonce="<?= $CSPNonce ?>">
                        function allPhonesCommaD() {
                            prompt("<?= _("Press CTRL + C to copy all group members\' phone numbers") ?>", "<?= mb_substr($sPhoneLink, 0, -2) ?>")
                        };
                    </script>
            <?php
                }
            }
            ?>

            <?php if ($isVolunteerOpportunityEnabled) { // only an admin can modify the options
            ?>
                <button class="btn btn-sm btn-primary btn-sm" id="add-new-volunteer-opportunity">
                    <i class="fas fa-plus-circle mr-1"></i><?= _("Add Volunteer Opportunity") ?>
                </button>
            <?php
            }
            ?>
        </div>
    </div>
</div>

<?php if (!$isVolunteerOpportunityEnabled) { ?>
    <div class="alert alert-info">
        <?= _("Volunteer opportunities are currently disabled. Please contact your administrator.") ?>
    </div>
<?php } ?>

<div class="group_Side_bar_container">
    <div class="group_Side_bar">
        <div class="sticky-top">
            <div id="accordion">
                <div class="card group_accordion">
                    <div class="card-header border-1 group_header_accordion" id="headingHierarchy">
                        <h3 class="card-title">
                            <i class="fas fa-users"></i> <button class="btn btn-sm btn-link" data-toggle="collapse" data-target="#collapseHierachy" aria-expanded="true" aria-controls="collapseHierachy">
                                <?= _("Hierarchy") ?>
                            </button>
                        </h3>
                        <div class="card-tools pull-right">
                            <button type="button" class="btn btn-sm btn-tool" data-toggle="collapse" data-target="#collapseHierachy" aria-expanded="true" aria-controls="collapseHierachy"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div id="collapseHierachy" class="collapse show" aria-labelledby="headingHierarchy" data-parent="#accordion" style="">
                        <div class="clt">
                            <ul>
                                <li>
                                    <?= $hier ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
                if (SessionUser::getUser()->isManageVolunteersEnabled($volID)) {
                ?>
                    <div class="card group_accordion">
                        <div class="card-header border-1 group_header_accordion" id="headingQuickSettings">
                            <h3 class="card-title">
                                <i class="fas fa-sliders fa-fw"></i> <button class="btn btn-sm btn-link" data-toggle="collapse" data-target="#collapseQuickSettings" aria-expanded="true" aria-controls="collapseQuickSettings">
                                    <?= _('Quick Settings') ?>
                                </button>
                            </h3>
                            <div class="card-tools pull-right">
                                <button type="button" class="btn btn-sm btn-tool" data-toggle="collapse" data-target="#collapseQuickSettings" aria-expanded="true" aria-controls="collapseQuickSettings"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div id="collapseQuickSettings" class="collapse" aria-labelledby="headingQuickSettings" data-parent="#accordion" style="">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-7"><label><?= _("Volunteers opportunity is") ?></label> : </div>
                                    <div class="col-md-5">
                                        <input data-width="100" class="btn btn-sm btn-primary btn-sm" id="isVolunteersActive" type="checkbox" data-toggle="toggle" data-on="<?= _('Active') ?>" data-off="<?= _('Disabled') ?>" <?= ($thisVolOpp->getActive() == "true") ? 'checked' : '' ?>>
                                    </div>
                                </div>
                                <?php if (SessionUser::getUser()->isEmailEnabled()) { ?>
                                    <div class="row">
                                        <div class="col-md-7"><label><?= _("The emails are") ?></label> : </div>
                                        <div class="col-md-5">
                                            <input data-width="100" class="btn btn-sm btn-primary btn-sm" id="isVolunteersEmailExport" type="checkbox" data-toggle="toggle" data-on="<?= _('Include') ?>" data-off="<?= _('Exclude') ?>" <?= $thisVolOpp->isIncludeInEmailExport() ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php if (SessionUser::getUser()->isAdmin()) { ?>
                        <div class="card group_accordion">
                            <div class="card-header border-1 group_header_accordion" id="headingGroupManager">
                                <h3 class="card-title">
                                    <i class="fas fa-users"></i> <button class="btn btn-sm btn-link" data-toggle="collapse" data-target="#collapseGroupManager" aria-expanded="true" aria-controls="collapseGroupManager">
                                        <?= _("Volunteers Managers") ?>
                                    </button>
                                </h3>
                                <div class="card-tools pull-right">
                                    <button type="button" class="btn btn-sm btn-tool" data-toggle="collapse" data-target="#collapseGroupManager" aria-expanded="true" aria-controls="collapseGroupManager"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <div id="collapseGroupManager" class="collapse" aria-labelledby="headingGroupManager" data-parent="#accordion" style="">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4"></div>
                                        <div class="col-md-4">
                                            <input data-width="100" class="btn btn-sm btn-primary btn-sm" id="isManagersActive" type="checkbox" data-toggle="toggle" data-on="<?= _('Yes') ?>" data-off="<?= _('No') ?>" <?= ($thisVolOpp->isManagers() == "true") ? 'checked' : '' ?>>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php
                }
                ?>


            </div>
        </div>
    </div>


    <div class="group_Side_bar_right">
        <div class="card card-warning card-outline">
            <div class="card-header border-1">
                <h3 class="card-title"><i class="fas fa-users"></i> <?= _("Manage Group Members") ?>:</h3>
                <div class="card-tools pull-right">
                    <button class="btn btn-sm btn-primary" type="button">
                        <?= _('Total Members') ?> <span class="badge  bg-white" id="iTotalMembers"></span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php
                if (SessionUser::getUser()->isManageVolunteersEnabled($volID)) {
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
    window.CRM.volName = "<?= $thisVolOpp->getName() ?>";
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

<script src="<?= $sRootPath ?>/skin/js/volunteer/VolunteerOpportunityCommon.js"></script>

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