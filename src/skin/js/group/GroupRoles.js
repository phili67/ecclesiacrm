function UpdateRoles() {
    var group_ID = $('#GroupID option:selected').val();  // get the selected group ID
    window.CRM.APIRequest({
        method: "GET",
        path: "groups/" + group_ID + "/roles"
    }, function (data) {
        var html = "";
        $.each(data.ListOptions, function (index, value) {
            html += "<option value=\"" + value.OptionId + "\"";
            html += ">" + i18next.t(value.OptionName) + "</option>";
        });
        $("#GroupRole").html(html);
    });
}

$(document).ready(function (e, confirmed) {
    $("#addToGroup").on('click', function () {
        window.CRM.groups.addGroup(function (data) {
            location.href = 'CartToGroup.php?groupeCreationID=' + data.Id;
        });
    });
});
