$(function() {
    $("[data-mask]").inputmask();
    
    $(document).on("click", ".delete-field", function (e) {
        e.preventDefault();
        var GroupID = $(this).data("groupid");
        var PropID = $(this).data("propid");
        var Field = $(this).data("field");

        bootbox.confirm({
            title: '<i class="fas fa-exclamation-triangle text-danger mr-2"></i>' + i18next.t("Attention"),
            message: '<div class="alert alert-danger mb-0">'
                + i18next.t("Warning: By deleting this field, you will irrevocably lose all group data assigned for this field!")
                + '</div>',
            buttons: {
                cancel: {
                    label: i18next.t('Cancel'),
                    className: 'btn-outline-secondary'
                },
                confirm: {
                    label: '<i class="fas fa-trash-alt mr-1"></i>' + i18next.t('Delete'),
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'groups/deletefield',
                        data: JSON.stringify({"GroupID": GroupID, "PropID": PropID, "Field": Field})
                    },function (data) {
                        //window.CRM.dataFundTable.ajax.reload();
                        window.location = window.location.href;
                    });
                }
            }
        });
    });

    $(document).on("click", ".up-action", function () {
        var GroupID = $(this).data("groupid");
        var PropID = $(this).data("propid");
        var Field = $(this).data("field");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'groups/upactionfield',
            data: JSON.stringify({"GroupID": GroupID, "PropID": PropID, "Field": Field})
        },function (data) {
            //window.CRM.dataFundTable.ajax.reload();
            window.location = window.location.href;
        });
    });

    $(document).on("click", ".down-action", function () {
        var GroupID = $(this).data("groupid");
        var PropID = $(this).data("propid");
        var Field = $(this).data("field");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'groups/downactionfield',
            data: JSON.stringify({"GroupID": GroupID, "PropID": PropID, "Field": Field})
        },function (data) {
            //window.CRM.dataFundTable.ajax.reload();
            window.location = window.location.href;
        });
    });

    $("#custom-fields-table").DataTable({
        responsive: true,
        paging: false,
        searching: false,
        ordering: false,
        info: false,
        //dom: window.CRM.plugin.dataTable.dom,
        fnDrawCallback: function (settings) {
            $("#selector thead").remove();
        }
    });
});
