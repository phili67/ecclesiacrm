$(document).ready(function () {

    $(".check_all").click(function () {
        var state = this.checked;
        $(".checkbox_plugins").each(function () {
            $(this)[0].checked = state;
            var tr = $(this).closest("tr");
            if (state) {
                $(tr).addClass('selected');
            } else {
                $(tr).removeClass('selected');
            }
        });
    });


    $('#plugins-listing-table').on('click', 'tr', function () {
        $(this).toggleClass('selected');

        var table = $('#plugins-listing-table').DataTable();
        var data = table.row(this).data();

        if (data != undefined) {
            click_tr = true;
            var userID = $(data[0]).data("id");
            var state = $(this).hasClass("selected");
            $('.checkbox_plugin' + userID).prop('checked', state);
            click_tr = false;
        }
    });

    window.CRM.fmt = "";

    if (window.CRM.timeEnglish == true) {
        window.CRM.fmt = window.CRM.datePickerformat.toUpperCase() + ' hh:mm a';
    } else {
        window.CRM.fmt = window.CRM.datePickerformat.toUpperCase() + ' HH:mm';
    }

    $.fn.dataTable.moment(window.CRM.fmt);

    $("#plugins-listing-table").DataTable({
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        responsive: true
    });


    $('.Deactivate-plugin').click(function(e) {
        var Id = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'plugins/deactivate',
            data: JSON.stringify({"Id": Id})
        }, function (data) {
            location.reload(); // this shouldn't be necessary
        });
    });

    $('.Activate-plugin').click(function(e) {
        var Id = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'plugins/activate',
            data: JSON.stringify({"Id": Id})
        }, function (data) {
            location.reload(); // this shouldn't be necessary
        });
    });
});

