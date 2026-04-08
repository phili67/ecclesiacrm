<?php

/*******************************************************************************
 *
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2017
 *
 ******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

//Set the page title
$sPageTitle = _('Families Self Registration');
require '../Include/Header.php';

?>


<div class="row">
    <div class="col-12">
        <div class="alert alert-light border mb-3">
            <i class="fas fa-info-circle mr-1 text-primary"></i>
            <?= _("Review newly registered families and open each profile to validate or complete information.") ?>
        </div>

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <h2 class="h4 mb-2 mb-md-0">
                        <i class="fas fa-users mr-2 text-primary"></i><?= _("Families Self Registration") ?>
                    </h2>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-info px-3 py-2 mr-2"><?= _("Recent requests") ?></span>
                        <a class="btn btn-sm btn-outline-secondary" href="#"
                           onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href='<?= $sRootPath ?>/v2/dashboard'; } return false;">
                            <i class="fas fa-arrow-left mr-1"></i><?= _("Back") ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="families" class="table table-sm table-hover table-bordered data-table dataTable no-footer dtr-inline mb-0" style="width:100%"></table>
                </div>
            </div>
        </div>
    </div>
</div>


<script nonce="<?= \EcclesiaCRM\dto\SystemURLs::getCSPNonce() ?>">
    $(function() {
        var familiesTableConfig = {
            ajax: {
                url: window.CRM.root + "/api/families/self-register",
                dataSrc: 'families'
            },
            columns: [{
                    title: i18next.t('Family Id'),
                    data: 'Id',
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return '<a href=' + window.CRM.root + '/v2/people/family/view/' + data + '>' + data + '</a>';
                    }
                },
                {
                    title: i18next.t('Family'),
                    data: 'FamilyString',
                    searchable: true
                },
                {
                    title: i18next.t('Date'),
                    data: 'DateEntered',
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return moment(data).format(window.CRM.datePickerformat.toUpperCase());
                    }
                }
            ],
            order: [
                [2, "desc"]
            ]
        };

        $.extend(familiesTableConfig, window.CRM.plugin.dataTable);

        $("#families").DataTable(familiesTableConfig);
    });
</script>
<?php
require '../Include/Footer.php';
?>