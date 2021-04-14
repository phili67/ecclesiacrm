$(document).ready(function () {
    var groupID = window.CRM.currentGroup;

    $("#remove_all_members").click(function () {
        bootbox.confirm({
            title: i18next.t("You're about to delete all the group members ?"),
            message: i18next.t("Are you sure ? This can't be undone."),
            buttons: {
                cancel: {
                    label: i18next.t('No'),
                    className: 'btn-success'
                },
                confirm: {
                    label: i18next.t('Yes'),
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'groups/emptygroup',
                        data: JSON.stringify({"groupID": groupID})
                    }).done(function (data) {
                        window.CRM.DataTableGroupView.ajax.reload();/* we reload the data no need to add the person inside the dataTable */
                    });
                }
            }
        });
    });
});
