$(function() {

    $(".check_all").on('click', function () {
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

    $('#action-selector').on('change',function (e) {
        switch ($(this).val()) {
            case "activate-selected":
                $(".checkbox_plugins").each(function () {
                    if (this.checked) {
                        var Id = $(this).data("id");
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'plugins/activate',
                            data: JSON.stringify({"Id": Id})
                        }, function (data) {
                            location.reload(); // this shouldn't be necessary
                        });
                    }
                });
                break;
            case "deactivate-selected":
                $(".checkbox_plugins").each(function () {
                    if (this.checked) {
                        var Id = $(this).data("id");
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'plugins/deactivate',
                            data: JSON.stringify({"Id": Id})
                        }, function (data) {
                            location.reload(); // this shouldn't be necessary
                        });
                    }
                });
                break;
            case "delete-selected":
                bootbox.confirm({
                    title: i18next.t("Confirmation of plugin removal"),
                    message: '<p style="color: red">' +
                        i18next.t("You are about to delete the selected plugins. This action cannot be undone!") + '</p>',
                    callback: function (result) {
                        if (result) {
                            $(".checkbox_plugins").each(function () {
                                if (this.checked) {
                                    var Id = $(this).data("id");
                                    window.CRM.APIRequest({
                                        method: 'DELETE',
                                        path: 'plugins/',
                                        data: JSON.stringify({"Id": Id})
                                    }, function (data) {
                                        location.reload(); // this shouldn't be necessary
                                    });
                                }
                            });
                        }
                    }
                });
                break;
        }
    });

    $('.Deactivate-plugin').on('click',function (e) {
        var Id = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'plugins/deactivate',
            data: JSON.stringify({"Id": Id})
        }, function (data) {
            location.reload(); // this shouldn't be necessary
        });
    });

    $('.Activate-plugin').on('click',function (e) {
        var Id = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'plugins/activate',
            data: JSON.stringify({"Id": Id})
        }, function (data) {
            location.reload(); // this shouldn't be necessary
        });
    });

    $('.Activate-plugin').on('click',function (e) {
        var Id = $(this).data("id");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'plugins/activate',
            data: JSON.stringify({"Id": Id})
        }, function (data) {
            location.reload(); // this shouldn't be necessary
        });
    });

    function BootboxContent(type, name = null) {
        var frm_str = '<section class="content">\n' +
            '<form id="restoredatabase" action="' + window.CRM.root + '/api/plugins/' + type + '" method="POST" enctype="multipart/form-data">\n' +
            '<div class="card card-gray">\n' +
            '    <div class="card-header">\n' +
            '        <h3 class="card-title">' + i18next.t("Select your zipped plugin file") + '</h3>\n' +
            '    </div>\n' +
            '    <div class="card-body">\n' +
            '            <input type="file" name="pluginFile" id="pluginFile" multiple="">\n';

        if (name !== null) {
            frm_str += '            <input type="hidden" name="name" value="' + name + '" />';
        }

        frm_str +=  '    </div>\n' +
            '    <div class="card-footer"">' +
            '            <button type="submit" class="btn btn-primary btn-small">' + i18next.t("Download the zipped file of the plugin") + '</button>\n' +
            '    </div>'
            '</div>\n' +
            '</form>\n' +
            '</section>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    $('#add-plugin').on('click', function () {
        var modal = bootbox.dialog({
            title:i18next.t("Plugin download manager"),
            message: BootboxContent('add'),
            size: 'large',
            show: true,
            onEscape: function () {
                modal.modal("hide");
            }
        });
    });


    $('.update-plugin').on('click', function () {
        var name = $(this).data("name");

        var modal = bootbox.dialog({
            title:i18next.t("Plugin download manager") + " : " + name,
            message: BootboxContent('upgrade', name),
            size: 'large',
            show: true,
            onEscape: function () {
                modal.modal("hide");
            }
        });
    });
});

