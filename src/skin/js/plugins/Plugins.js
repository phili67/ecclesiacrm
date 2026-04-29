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
        var actionLabel = type === 'upgrade' ? i18next.t("Update plugin") : i18next.t("Add plugin");
        var helperText = type === 'upgrade'
            ? i18next.t("Choose the new zipped package to update this plugin.")
            : i18next.t("Choose a zipped plugin package to install it in your CRM.");

        var frm_str = '<div class="container-fluid px-0">\n' +
            '<form id="restoredatabase" action="' + window.CRM.root + '/api/plugins/' + type + '" method="POST" enctype="multipart/form-data">\n' +
            '<div class="alert alert-light border mb-3">\n' +
            '  <div class="d-flex align-items-start">\n' +
            '    <i class="fas fa-puzzle-piece text-primary mr-2 mt-1"></i>\n' +
            '    <div>\n' +
            '      <div class="font-weight-bold">' + actionLabel + '</div>\n' +
            '      <div class="small text-muted">' + helperText + '</div>\n' +
            '    </div>\n' +
            '  </div>\n' +
            '</div>\n' +
            '<div class="card card-outline card-primary mb-0">\n' +
            '  <div class="card-body">\n' +
            '    <div class="form-group mb-0">\n' +
            '      <label class="small text-uppercase text-muted mb-2" for="pluginFile">' + i18next.t("Plugin archive") + '</label>\n' +
            '      <input type="file" name="pluginFile" id="pluginFile" class="form-control-file" multiple="">\n' +
            '      <small class="form-text text-muted">' + i18next.t("Only zipped plugin packages should be selected here.") + '</small>\n';

        if (name !== null) {
            frm_str += '            <input type="hidden" name="name" value="' + name + '" />';
        }

        frm_str +=  '    </div>\n' +
            '  </div>\n' +
            '  <div class="card-footer d-flex justify-content-between align-items-center">\n' +
            '    <span class="small text-muted">' + i18next.t("The upload starts as soon as you submit this form.") + '</span>\n' +
            '    <button type="submit" class="btn btn-primary">' + actionLabel + '</button>\n' +
            '  </div>\n' +
            '</div>\n' +
            '</form>\n' +
            '</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    $('#add-plugin').on('click', function () {
        var modal = bootbox.dialog({
            title:i18next.t("Plugin manager"),
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
            title:i18next.t("Plugin manager") + " : " + name,
            message: BootboxContent('upgrade', name),
            size: 'large',
            show: true,
            onEscape: function () {
                modal.modal("hide");
            }
        });
    });
});

