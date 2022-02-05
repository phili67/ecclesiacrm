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

use EcclesiaCRM\dto\SystemURLs;

?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header  border-0">
                <h3 class="card-title"><i class="fas fa-male"></i><i class="fas fa-female"></i><i class="fas fa-child"></i> <?= _("Families") ?></h3>
            </div>
            <div class="card-body">
                <table id="families" class="table table-striped table-bordered data-table dataTable no-footer dtr-inline" style="width:100%"></table>
            </div>
        </div>
    </div>
</div>


<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(document).ready(function () {
        var familiesTableConfig = {
            ajax: {
                url: window.CRM.root + "/api/families/self-register",
                dataSrc: 'families'
            },
            columns: [
                {
                    title: i18next.t('Family Id'),
                    data: 'Id',
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return '<a href=' + window.CRM.root + '/FamilyView.php?FamilyID=' + data + '>' + data + '</a>';
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
                    render: function (data, type, full, meta) {
                        return moment(data).format(window.CRM.datePickerformat.toUpperCase());
                    }
                }
            ],
            order: [[2, "desc"]]
        };

        $.extend(familiesTableConfig,window.CRM.plugin.dataTable);

        $("#families").DataTable(familiesTableConfig);
    });
</script>
<?php
require '../Include/Footer.php';
?>
