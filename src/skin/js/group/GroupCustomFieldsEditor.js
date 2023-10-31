$(function() {
    $(document).on("click", ".delete-field", function () {
        var GroupID = $(this).data("groupid");
        var PropID = $(this).data("propid");
        var Field = $(this).data("field");

        bootbox.confirm({
            title: i18next.t("Attention"),
            message: i18next.t("Warning: By deleting this field, you will irrevocably lose all group data assigned for this field!"),
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
});
