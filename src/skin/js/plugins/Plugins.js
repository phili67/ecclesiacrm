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

    var pluginUpdateState = null;

    function showPluginUpdates() {
        var updates = pluginUpdateState.updates;
        var issues = pluginUpdateState.issues;
        var content = $('<div class="plugin-updates-dialog"></div>');
        var running = false;
        var attempted = false;
        var dialog;

        if (updates.length) {
            content.append($('<p class="text-muted mb-3"></p>').text(i18next.t('Select the plugins to update from the files already installed on the server.')));
            var list = $('<div class="list-group mb-3"></div>');
            updates.forEach(function (plugin) {
                var row = $('<label class="list-group-item d-flex align-items-center mb-0"></label>');
                var checkbox = $('<input type="checkbox" class="plugin-update-choice mr-3" checked>');
                checkbox.val(plugin.id);
                var details = $('<span class="flex-grow-1"></span>');
                details.append($('<span class="font-weight-bold d-block"></span>').text(plugin.name));
                details.append($('<small class="text-muted"></small>').text(
                    i18next.t('Database') + ': ' + plugin.installedVersion + ' → ' + i18next.t('Files') + ': ' + plugin.availableVersion
                ));
                row.append(checkbox, details, $('<span class="plugin-update-status ml-2"></span>'));
                list.append(row);
            });
            content.append(list);
            content.append($('<div class="alert alert-warning small"></div>').text(
                i18next.t('Back up your database before updating. If a migration fails, some changes may already have been applied.')
            ));
        }

        if (issues.length) {
            var warnings = $('<div class="alert alert-warning mb-3"></div>');
            warnings.append($('<div class="font-weight-bold mb-2"></div>').text(i18next.t('Plugins requiring attention')));
            var issueList = $('<ul class="mb-0 pl-3"></ul>');
            issues.forEach(function (plugin) {
                var text = plugin.reason === 'database_newer'
                    ? i18next.t('The database version is newer than the files.')
                    : i18next.t('The configuration or migration is invalid. Check the application logs.');
                var item = $('<li></li>').append($('<strong></strong>').text(plugin.name + ': '));
                item.append(document.createTextNode(text));
                if (plugin.availableVersion) {
                    item.append($('<small class="d-block text-muted"></small>').text(
                        i18next.t('Database') + ': ' + plugin.installedVersion + ' · ' + i18next.t('Files') + ': ' + plugin.availableVersion
                    ));
                }
                issueList.append(item);
            });
            warnings.append(issueList);
            content.append(warnings);
        }

        var feedback = $('<div role="status" aria-live="polite"></div>');
        content.append(feedback);

        function finish(message, failed) {
            running = false;
            feedback.empty().append($('<div></div>')
                .addClass('alert ' + (failed ? 'alert-danger' : 'alert-success') + ' mb-0')
                .text(message));
            dialog.find('[data-bb-handler="update"]').hide();
            dialog.find('[data-bb-handler="later"]').prop('disabled', false).text(i18next.t('Close and refresh'));
        }

        function updateNext(selected, index) {
            if (index === selected.length) {
                finish(i18next.t('The selected plugins have been updated.'), false);
                return;
            }

            var plugin = selected[index];
            var checkbox = content.find('.plugin-update-choice').filter(function () {
                return String($(this).val()) === String(plugin.id);
            });
            var status = checkbox.closest('label').find('.plugin-update-status');
            status.empty().append($('<i class="fas fa-spinner fa-spin text-primary" aria-hidden="true"></i>'));
            window.CRM.APIRequest({
                method: 'POST',
                path: 'plugins/upgrade-installed',
                data: JSON.stringify({
                    id: plugin.id,
                    installedVersion: plugin.installedVersion,
                    availableVersion: plugin.availableVersion
                })
            }, function () {
                status.empty().append($('<i class="fas fa-check-circle text-success" aria-hidden="true"></i>'));
                updateNext(selected, index + 1);
            }, function (error) {
                status.empty().append($('<i class="fas fa-exclamation-circle text-danger" aria-hidden="true"></i>'));
                finish(i18next.t('The update stopped at') + ' ' + plugin.name + '. ' + i18next.t(error.message), true);
            });
        }

        dialog = bootbox.dialog({
            title: i18next.t('Plugin updates'),
            message: content,
            size: 'large',
            closeButton: false,
            backdrop: true,
            onEscape: function () {
                if (running) return false;
                if (attempted) window.location.reload();
                return true;
            },
            buttons: {
                later: {
                    label: i18next.t('Later'),
                    className: 'btn-outline-secondary',
                    callback: function () {
                        if (attempted) window.location.reload();
                    }
                },
                update: {
                    label: i18next.t('Update selected plugins'),
                    className: 'btn-primary',
                    callback: function () {
                        var selectedIds = content.find('.plugin-update-choice:checked').map(function () {
                            return String($(this).val());
                        }).get();
                        var selected = updates.filter(function (plugin) {
                            return selectedIds.indexOf(String(plugin.id)) !== -1;
                        });
                        if (!selected.length) {
                            feedback.empty().append($('<div class="alert alert-warning mb-0"></div>').text(i18next.t('Select at least one plugin.')));
                            return false;
                        }
                        running = true;
                        attempted = true;
                        content.find('.plugin-update-choice').prop('disabled', true);
                        dialog.find('[data-bb-handler]').prop('disabled', true);
                        feedback.empty().append($('<p class="text-muted mb-0"></p>').text(i18next.t('Updating plugins. Please wait.')));
                        updateNext(selected, 0);
                        return false;
                    }
                }
            }
        });

        if (!updates.length) {
            dialog.find('[data-bb-handler="update"]').hide();
        }
    }

    function checkPluginUpdates() {
        window.CRM.APIRequest({path: 'plugins/updates'}, function (data) {
            if (!data || !Array.isArray(data.updates) || !Array.isArray(data.issues)) {
                window.CRM.openInfoDialog({title: i18next.t('Error'), lead: i18next.t('Unable to check plugin updates.')});
                return;
            }
            pluginUpdateState = data;
            if (data.updates.length || data.issues.length) {
                $('#plugin-updates-label').text(i18next.t('Plugin updates') + ' (' + data.updates.length + ')');
                $('#plugin-updates-button').removeClass('d-none');
                showPluginUpdates();
            }
        }, function (error) {
            window.CRM.openInfoDialog({
                title: i18next.t('Error'),
                lead: i18next.t('Unable to check plugin updates.'),
                details: $('<div></div>').text(i18next.t(error.message)).html()
            });
        });
    }

    $('#plugin-updates-button').on('click', showPluginUpdates);
    checkPluginUpdates();

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
