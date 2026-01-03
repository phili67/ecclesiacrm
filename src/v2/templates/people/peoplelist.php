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
?>
<div
    class="card card-warning">
    <div class="card-header border-1">
        <h3 class="card-title"><i class="fas fa-filter"></i> <?= _('Filters') ?></h3>
    </div>
    <div class="card-body clearfix">
        <div class="row">
            <div class="col-sm-3"><?= _("Enter the search term") ?> :</div>
            <div class="col-sm-8">
                <select type="text" id="SearchTerm"
                       placeholder="<?= _("Search terms like : name, first name, phone number, property, group name, etc ...") ?>"
                       size="1" maxlength="100"
                        class="SearchTerm form-control form-control-sm" width="100%" style="width: 100%"></select>
            </div>
            <div class="col-sm-1">
            <a data-toggle="popover" title="" data-content="<?= "*"."<br>"._("Singles")."<br>"._("Volunteers")."<br>"._("Families")."<br>"._("Groups")."<br>"._("Sunday Groups")."<br>"._("groupmasters")."<br>" ?>
                 <?= _("phone number")."<br>"._("first name")."<br>"._("name")."<br>"._("group name")."<br>"._("check number")."<br>"._("city")."<br>"._("street")."<br>"._("zip code")." "._("or what else")." .... " ?>" target="_blank" class="blue" data-original-title="<?= _("Filter Hints") ?>"><i class="far  fa-question-circle"></i></a>
            </div>
        </div>
        <div class="person-filters">
            <hr/>
            <div class="row ">
                <div class="col-sm-3"><?= _("Choose your person filters") ?> :</div>
                <div class="col-sm-9">
                    <select name="search[]" multiple="" id="searchCombo" style="width:100%" size="1"
                            data-select2-id="searchList" tabindex="-1" aria-hidden="true"></select>
                </div>
            </div>
        </div>
        <div id="group_search_filters">
            <br/>
            <div class="row">
                <div class="col-sm-3"><?= _("Group filters") ?> :</div>
                <div class="col-md-4">
                    <select name="searchGroup[]" id="searchComboGroup" style="width:100%" size="1"
                            data-select2-id="searchListGroups" tabindex="-1" aria-hidden="true"></select>
                </div>
                <div class="col-md-4">
                    <select name="searchGroupRole[]" id="searchComboGroupRole" style="width:100%" size="1"
                            data-select2-id="searchComboGroupRole" tabindex="-1" aria-hidden="true"></select>
                </div>
            </div>
        </div>        
    </div>
    <div class="card-footer">
            <div class="pull-right">
                <button type="button" class="btn btn-primary" id="search_OK" class="right"><i class="fas fa-search"></i>  <?= _("Search") ?></button>
            </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header border-1">
        <h3 class="card-title"><i class="fas fa-search"></i> <?= _('Search Results') ?></h3>
        <div class="card-tools">
            <div style="text-align: center;">
                    <label>
                        <?= _("Results count:") ?>
                    </label>
                    <span id="numberOfPersons"></span>
                </div>
        </div>
    </div>
    <div class="card-body">        
        <table width="100%" cellpadding="2"
               class="table table-striped table-bordered data-table dataTable no-footer dtr-inline"
               id="DataSearchTable"></table>
    </div>
</div>

<script nonce="<?= $sCSPNonce ?>">
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
