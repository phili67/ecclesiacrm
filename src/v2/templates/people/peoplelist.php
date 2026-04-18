<?php
/*******************************************************************************
 *
 *  filename    : peoplelist.php
 *  last change : 2020-03-08
 *  description : Philippe Logel All right reserved
 *
 ******************************************************************************/

use EcclesiaCRM\SessionUser;

// Security
require $sRootDocument . '/Include/Header.php';

$mode = "";

if ($sMode == 'person') {
    $mode = "*";
} else if ($sMode == 'family') {
    $mode = _("Families");
} else if ($sMode == 'single' or $sMode == 'singles') {
    $mode = _("Singles");
}

$hidePersonFilters = in_array($sMode, ['family', 'single', 'singles'], true);
?>
<div class="card card-warning card-outline">
    <div class="card-header border-1">
        <h3 class="card-title mb-0"><i class="fas fa-sliders-h text-warning mr-2"></i><?= _('Filters') ?></h3>
        <div class="small text-muted mt-1"><?= _('Choose a search term, optionally narrow the scope, then refresh the results table.') ?></div>
    </div>
    <div class="card-body">
        <div class="alert alert-light border d-flex align-items-start mb-4">
            <i class="fas fa-lightbulb text-warning mt-1 mr-2"></i>
            <div>
                <strong><?= _("How to search") ?></strong><br>
                <span class="text-muted"><?= _("Use one or more filters, then run the search to update the results table.") ?></span>
            </div>
        </div>

        <div class="border rounded p-3 mb-3">
        <div class="row align-items-center">
            <div class="col-md-3">
                <label class="mb-1 font-weight-bold" for="SearchTerm"><i class="fas fa-search text-primary mr-1"></i><?= _("Enter the search term") ?></label>
                <div class="small text-muted"><?= _('Search by name, phone, address, group, payment, city, zip code, and more.') ?></div>
            </div>
            <div class="col-md-8 mt-2 mt-md-0">
                <select id="SearchTerm"
                        placeholder="<?= _("Search terms like : name, first name, phone number, property, group name, etc ...") ?>"
                        size="1" maxlength="100"
                        class="SearchTerm form-control form-control-sm" style="width: 100%"></select>
            </div>
            <div class="col-md-1 text-md-center mt-2 mt-md-0">
                <a data-toggle="popover" title="" data-content="<?= "*"."<br>"._("Singles")."<br>"._("Volunteers")."<br>"._("Families")."<br>"._("Groups")."<br>"._("Sunday Groups")."<br>"._("groupmasters")."<br>" ?>
                 <?= _("phone number")."<br>"._("first name")."<br>"._("name")."<br>"._("group name")."<br>"._("check number")."<br>"._("city")."<br>"._("street")."<br>"._("zip code")." "._("or what else")." .... " ?>" target="_blank" class="text-primary" data-original-title="<?= _("Filter Hints") ?>">
                    <i class="fas fa-magic fa-lg"></i>
                </a>
            </div>
        </div>
        </div>

        <div class="border rounded p-3 mb-3<?= $hidePersonFilters ? ' d-none' : '' ?>" id="search_type_filters">
        <div class="row align-items-center">
            <div class="col-md-3">
                <label class="mb-1 font-weight-bold" for="searchTypeCombo"><i class="fas fa-layer-group text-info mr-1"></i><?= _("Limit search to") ?></label>
                <div class="small text-muted"><?= _('Focus the search on specific result categories such as persons, families, groups, deposits, or payments.') ?></div>
            </div>
            <div class="col-md-9 mt-2 mt-md-0">
                <select name="searchTypes[]" multiple="" id="searchTypeCombo" style="width:100%" size="1"
                        data-select2-id="searchTypeCombo" tabindex="-1" aria-hidden="true"></select>
            </div>
        </div>
        </div>

        <div class="person-filters<?= $hidePersonFilters ? ' d-none' : '' ?>">
            <div class="border rounded p-3 mb-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="mb-1 font-weight-bold" for="searchCombo"><i class="fas fa-user-check text-success mr-1"></i><?= _("Choose your person filters") ?></label>
                    <div class="small text-muted"><?= _('Refine people results with gender, classification, family role, property, or group type.') ?></div>
                </div>
                <div class="col-md-9 mt-2 mt-md-0">
                    <select name="search[]" multiple="" id="searchCombo" style="width:100%" size="1"
                            data-select2-id="searchList" tabindex="-1" aria-hidden="true"></select>
                </div>
            </div>
            </div>
        </div>
        <div id="group_search_filters">
            <div class="border rounded p-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="mb-1 font-weight-bold" for="searchComboGroup"><i class="fas fa-users text-purple mr-1"></i><?= _("Group filters") ?></label>
                    <div class="small text-muted"><?= _('Choose a group and optionally a role to narrow the matching members.') ?></div>
                </div>
                <div class="col-md-4 mt-2 mt-md-0">
                    <select name="searchGroup[]" id="searchComboGroup" style="width:100%" size="1"
                            data-select2-id="searchListGroups" tabindex="-1" aria-hidden="true"></select>
                </div>
                <div class="col-md-5 mt-2 mt-md-0">
                    <select name="searchGroupRole[]" id="searchComboGroupRole" style="width:100%" size="1"
                            data-select2-id="searchComboGroupRole" tabindex="-1" aria-hidden="true"></select>
                </div>
            </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" id="search_OK"><i class="fas fa-search-plus mr-1"></i><?= _("Search") ?></button>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header border-1">
        <h3 class="card-title mb-0"><i class="fas fa-stream text-primary mr-2"></i><?= _('Search Results') ?></h3>
        &nbsp;<div class="small text-muted mt-1"><?= _('Browse the matching records and use cart actions directly from the list.') ?></div>
        <div class="card-tools">
            <span class="badge badge-success border px-3 py-2">
                <i class="fas fa-chart-bar text-yellow mr-3"></i>
                <?= _("Results count:") ?>
                <span class="ml-1 font-weight-bold" id="numberOfPersons"></span>
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table width="100%" cellpadding="2"
               class="table table-striped table-bordered data-table dataTable no-footer dtr-inline"
               id="DataSearchTable"></table>
        </div>
    </div>
</div>

<script nonce="<?= $sCSPNonce ?>">
    window.CRM.searchMode = "<?= $sMode ?>";
    window.CRM.mode = "<?= $mode ?>",
    window.CRM.listPeople = [];
    window.CRM.gender = <?= $iGender ?>;
    window.CRM.familyRole = <?= $iFamilyRole ?>;
    window.CRM.classification = <?= $iClassification ?>;
    window.CRM.isShowCartEnabled = <?= SessionUser::getUser()->isShowCartEnabled()?"true":"false" ?>;
</script>

<script src="<?= $sRootPath ?>/skin/js/Search/Search.js"></script>
<script src="<?= $sRootPath ?>/skin/js/people/AddRemoveCart.js"></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>
