/* copyright 2018 Philippe Logel */

$(function() {
    // Helper function to get parameters from the query string.
    // use to search the ckeditor function to put the right param in the ckeditor image tool
    function getUrlParam(paramName) {
        var reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');
        var match = window.location.search.match(reParam);

        return (match && match.length > 1) ? match[1] : null;
    }

    // DOM callback for all the project
    window.CRM.reloadEDriveTable = function (callback) {
        window.CRM.dataEDriveTable.ajax.reload(function (json) {
            installDragAndDrop();
            if (callback) {
                callback();
            }
        });
    }

    // EDrive
    var selected = [];// the selected rows
    var uploadWindow = null;
    var oldTextField = null;

    window.CRM.dataEDriveTable = $("#edrive-table").DataTable({
        ajax: {
            url: window.CRM.root + "/api/filemanager/" + window.CRM.currentPersonID,
            type: 'POST',
            contentType: "application/json",
            dataSrc: "files",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " +  window.CRM.jwtToken
                );
            }
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        searching: true,
        select: true,
        columns: [
            {
                width: '5%',
                title: i18next.t('Icon'),
                data: 'icon',
                render: function (data, type, full, meta) {
                    if (!full.dir) {
                        return '<span class="drag drag-file" id="' + full.name + '" type="file" data-path="' + full.path + '" data-perid="' + full.perID + '">' + data + '</span>';
                    } else {
                        return '<a class="change-folder" data-personid="' + window.CRM.currentPersonID + '" data-folder="' + full.name + '"><span class="drag drop" id="' + full.name + '" type="folder">' + data + '</span>';
                    }
                }
            },
            {
                width: '50%',
                title: i18next.t('Name'),
                data: 'name',
                type: 'column-name',
                render: function (data, type, full, meta) {
                    if (full.dir) {
                        var fileName = data.substring(1);

                        return '<input type="text" value="' + fileName + '" class="fileName" data-name="' + data + '" data-type="folder" readonly>';
                    } else {
                        var fileName = data;
                        fileName = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;

                        return '<input type="text" value="' + fileName + '" class="fileName" data-name="' + data + '" data-type="file" readonly>';
                    }
                }
            },
            {
                width: '5%',
                title: i18next.t('Actions'),
                data: 'id',
                render: function (data, type, full, meta) {
                    if (!full.dir) {
                        var ret = '<div class="btn-group">' +
                            '   <a href="' + window.CRM.root + '/api/filemanager/getFile/' + full.perID + '/' + full.path + '" type="button" id="uploadFile" class="btn btn-primary btn-sm" data-personid="1" data-toggle="tooltip" data-placement="top" title="" data-original-title="Télécharger fichier dans EDrive"><i class="fas fa-download"></i></a>' +
                            '   <button type="button" class="btn btn-' + (full.isShared?'success':'default') + ' btn-sm shareFile" data-personid="1"  data-id="' + data + '" data-shared="' + full.isShared + 'data-toggle="tooltip" data-placement="top" title="" data-original-title="Créer un dossier"><i class="fas fa-share-square"></i></button>' +
                            '</div>';
                        return ret;
                    }

                    return '';
                }
            },
            {
                width: '15%',
                title: i18next.t('Modification Date'),
                data: 'date',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: '10%',
                title: i18next.t('Type'),
                data: 'type',
                render: function (data, type, full, meta) {
                    return data;
                }
            },
            {
                width: '10%',
                title: i18next.t('Size'),
                data: 'size',
                type: 'file-size',
                render: function (data, type, full, meta) {
                    return data
                }
            }
        ],
        responsive: true,
        createdRow: function (row, data, index) {
            $(row).addClass("edriveRow");
            //$(row).attr('id', data.id)
            $(row).attr('id', data.name);
        },
        "rowCallback": function (row, data) {
            if ($.inArray(data.DT_RowId, selected) !== -1) {
                $(row).addClass('selected');
            }
        },
        "initComplete": function (settings, json) {
            installDragAndDrop();
        }
    });


    $("body").on('click', '.filemanager-download', function (e) {
        var selectedRows = window.CRM.dataEDriveTable.rows('.selected').data()
        $.each(selectedRows, function (index, value) {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'filemanager/getRealLink',
                data: JSON.stringify({"personID": window.CRM.currentPersonID, "pathFile": value.path})
            },function (data) {
                if (data && data.success) {
                    var fileUrl = data.address;
                    if (window.CRM.donatedItemID) {
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'fundraiser/donatedItem/submit/picture',
                            data: JSON.stringify({"DonatedItemID": window.CRM.donatedItemID, "pathFile": fileUrl})
                        },function (data) {
                            window.close();
                        });
                    } else {
                        var funcNum = getUrlParam('CKEditorFuncNum');
                        window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);
                        window.close();
                    }
                }
            });
        });
    });


    $('#edrive-table tbody').on('click', 'td', function (e) {

        var id = $(this).parent().attr('id');
        var col = window.CRM.dataEDriveTable.cell(this).index().column;

        if (!(col == 2)) {
            if (!e.shiftKey) {
                selected.length = 0;// no lines
                $('#edrive-table tbody tr').removeClass('selected');
            }

            var index = $.inArray(id, selected);

            if (index === -1) {
                selected.push(id);
            } else {
                selected.splice(index, 1);
            }

            $(this).parent().toggleClass('selected');

            var selectedRows = window.CRM.dataEDriveTable.rows('.selected').data().length;

            if (selectedRows == 0) {
                selected.length = 0;// no lines
            }

            if (window.CRM.browserImage == true) {
                if (selectedRows) {
                    $(".filemanager-download").css("display", "block");
                } else {
                    $(".filemanager-download").css("display", "none");
                }
            }

            if (!(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
                (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform))) && selectedRows == 1) {

                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'filemanager/getPreview',
                    data: JSON.stringify({"personID": window.CRM.currentPersonID, "name": id})
                },function (data) {
                    if (data && data.success) {
                        $('.filmanager-left').removeClass("col-md-12").addClass("col-md-9");
                        $('.filmanager-right').show();
                        $('.preview').html(data.path);
                    }
                });
            } else {
                $('.preview').html('');
            }
        }

    });


    $("body").on('click', '.fileName', function (e) {
        if ((/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
            (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)))) {
            // we're on a SmartPhone
            var oldName = $(this).data("name");
            var fileName = '';

            if (oldName[0] == '/') {
                fileName = oldName.substring(1);
            } else {
                fileName = oldName.substring(0, oldName.lastIndexOf('.')) || oldName;
            }

            var type = $(this).data("type");

            bootbox.prompt({
                title: i18next.t("Set a File/Folder name"),
                value: fileName,
                callback: function (result) {
                    if (result != '') {
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'filemanager/rename',
                            data: JSON.stringify({
                                "personID": window.CRM.currentPersonID,
                                "oldName": oldName,
                                "newName": result,
                                "type": type
                            })
                        },function (data) {
                            if (data && data.success) {
                                window.CRM.reloadEDriveTable();
                            }
                        });
                    }
                }
            });
        }
    });

    $("body").on('dblclick', '.drag-file', function (e) {
        if (!(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
            (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)))) {
            var perID = $(this).data("perid");
            var path = $(this).data("path");

            window.location.href = window.CRM.root + '/api/filemanager/getFile/' + perID + '/' + path;
        }
    });

    $("body").on('dblclick', '.fileName', function (e) {
        if (!(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
            (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)))) {
            // we're on a computer
            if (oldTextField != null) {
                $(oldTextField).css("background", "transparent");
                $(oldTextField).attr('readonly');
            }

            $(this).css("background", "white");
            $(this).removeAttr('readonly');

            oldTextField = this;
        }
    });

    $("body").on('click', '.close-file-preview', function (e) {
        $('.filmanager-left').removeClass("col-md-9").addClass("col-md-12");
        $('.filmanager-right').hide();
    });


    $("body").on('keypress', '.fileName', function (e) {
        var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
        var newName = $(this).val();
        var oldName = $(this).data("name");
        var type = $(this).data("type");

        switch (key) {
            case 13:// return
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'filemanager/rename',
                    data: JSON.stringify({
                        "personID": window.CRM.currentPersonID,
                        "oldName": oldName,
                        "newName": newName,
                        "type": type
                    })
                },function (data) {
                    if (data && data.success) {
                        window.CRM.reloadEDriveTable();
                    }
                });
                break;
            case 27:// ESC
                var fileName = oldName;

                if (type == 'file') {
                    fileName = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;
                } else {
                    fileName = fileName.substring(1);
                }

                $(this).attr('readonly');
                $(this).css("background", "transparent");
                $(this).val(fileName);
                oldTextField = null;
                break;
        }
    });

    $('.trash-drop').on('click', function () {
        var selected = $.map(window.CRM.dataEDriveTable.rows('.selected').data(), function (item) {
            return item['name']
        });

        var title = "";

        if (selected.length > 1) {
            title = i18next.t("You are about to delete several items together")
        } else if (selected.length == 1) {
            title = i18next.t("You're about to remove an item");
        }

        if (selected.length) {
            bootbox.confirm({
                title: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + title + '</span>',
                message: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + i18next.t("This can't be undone !!!!") + '</span>',
                buttons: {
                    cancel: {
                        label: '<i class="fas fa-times"></i> ' + i18next.t('Cancel'),
                        className: 'btn-primary'
                    },
                    confirm: {
                        label: '<i class="fas fa-trash-alt"></i> ' + i18next.t('Delete'),
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    if (result) {
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'filemanager/deleteFiles',
                            data: JSON.stringify({"personID": window.CRM.currentPersonID, "files": selected})
                        },function (data) {
                            if (data && data.success) {
                                if (data.error.length) {
                                    alert(data.error[0]);
                                }
                                window.CRM.reloadEDriveTable(function () {
                                    selected.length = 0;
                                });
                            }
                        });
                    }
                }
            });
        } else {
            window.CRM.DisplayAlert(i18next.t("Error"), i18next.t("You've to select at least one line !!!"));
        }
    });

    $('.trash-drop').droppable({
        drop: function (event, ui) {
            var selected = $.map(window.CRM.dataEDriveTable.rows('.selected').data(), function (item) {
                return item['name']
            });

            var len = selected.length;

            if (len > 1) {
                bootbox.confirm({
                    title: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + i18next.t("You are about to delete several items together") + '</span>',
                    message: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + i18next.t("This can't be undone !!!!") + '</span>',
                    buttons: {
                        cancel: {
                            label: '<i class="fas fa-times"></i> ' + i18next.t('Cancel'),
                            className: 'btn-primary'
                        },
                        confirm: {
                            label: '<i class="fas fa-trash-alt"></i> ' + i18next.t('Delete'),
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'filemanager/deleteFiles',
                                data: JSON.stringify({"personID": window.CRM.currentPersonID, "files": selected})
                            },function (data) {
                                if (data && data.success) {
                                    window.CRM.reloadEDriveTable(function () {
                                        selected.length = 0;
                                    });
                                }
                            });
                        }
                    }
                });

                return;
            }

            var name = $(ui.draggable).attr('id');
            var type = $(ui.draggable).attr('type');

            if (type == 'folder') {
                bootbox.confirm({
                    title: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + i18next.t("You're about to remove a folder and it's content") + '</span>',
                    message: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + i18next.t("This can't be undone !!!!") + '</span>',
                    buttons: {
                        cancel: {
                            label: '<i class="fas fa-times"></i> ' + i18next.t('Cancel'),
                            className: 'btn-primary'
                        },
                        confirm: {
                            label: '<i class="fas fa-trash-alt"></i> ' + i18next.t('Delete'),
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'filemanager/deleteFiles',
                                data: JSON.stringify({"personID": window.CRM.currentPersonID, "files": [name]})
                            },function (data) {
                                if (data && data.success) {
                                    window.CRM.reloadEDriveTable(function () {
                                        selected.length = 0;
                                    });
                                }
                            });
                        }
                    }
                });
            } else {// in the case of a file
                bootbox.confirm({
                    title: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + i18next.t("You're about to remove an item") + "</span>",
                    message: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + i18next.t("This can't be undone !!!!") + "</span>",
                    buttons: {
                        cancel: {
                            label: '<i class="fas fa-times"></i> ' + i18next.t('Cancel'),
                            className: 'btn-primary'
                        },
                        confirm: {
                            label: '<i class="fas fa-trash-alt"></i> ' + i18next.t('Delete'),
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            window.CRM.APIRequest({
                                method: 'POST',
                                path: 'filemanager/deleteFiles',
                                data: JSON.stringify({"personID": window.CRM.currentPersonID, "files": [name]})
                            },function (data) {
                                if (data && data.success) {
                                    window.CRM.reloadEDriveTable(function () {
                                        selected.length = 0;
                                    });
                                }
                            });
                        }
                    }
                });
            }
        }
    });

    $('.folder-back-drop').droppable({

        drop: function (event, ui) {
            var name = $(ui.draggable).attr('id');
            var folderName = '/..';

            var selected = $.map(window.CRM.dataEDriveTable.rows('.selected').data(), function (item) {
                return item['name']
            });

            if (selected.length > 0) {// Drag in a folder
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'filemanager/movefiles',
                    data: JSON.stringify({
                        "personID": window.CRM.currentPersonID,
                        "folder": folderName,
                        "files": selected
                    })
                },function (data) {
                    if (data && !data.success) {
                        window.CRM.DisplayAlert(i18next.t("Error"), data.message);
                    }

                    window.CRM.reloadEDriveTable(function () {
                        selected.length = 0;
                    });
                });
            } else {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'filemanager/movefiles',
                    data: JSON.stringify({
                        "personID": window.CRM.currentPersonID,
                        "folder": folderName,
                        "files": [name]
                    })
                },function (data) {
                    if (data && !data.success) {
                        window.CRM.DisplayAlert(i18next.t("Error"), data.message);
                    }

                    window.CRM.reloadEDriveTable(function () {
                        selected.length = 0;
                    });
                });
            }
        }

    });

    function installDragAndDrop() {
        $('.drag').draggable({
            helper: 'clone',
            appendTo: 'body',
            zIndex: 1100
            //revert : true
        });

        $('.drop').droppable({
            drop: function (event, ui) {
                var name = $(ui.draggable).attr('id');
                var folderName = $(event.target).attr('id');

                var selected = $.map(window.CRM.dataEDriveTable.rows('.selected').data(), function (item) {
                    return item['name']
                });

                if (selected.length > 0) {// Drag in a folder
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'filemanager/movefiles',
                        data: JSON.stringify({
                            "personID": window.CRM.currentPersonID,
                            "folder": folderName,
                            "files": selected
                        })
                    },function (data) {
                        if (data && !data.success) {
                            window.CRM.DisplayAlert(i18next.t("Error"), data.message);
                        }

                        window.CRM.reloadEDriveTable(function () {
                            selected.length = 0;
                        });
                    });
                } else {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'filemanager/movefiles',
                        data: JSON.stringify({
                            "personID": window.CRM.currentPersonID,
                            "folder": folderName,
                            "files": [name]
                        })
                    },function (data) {
                        if (data && !data.success) {
                            window.CRM.DisplayAlert(i18next.t("Error"), data.message);
                        }

                        window.CRM.reloadEDriveTable(function () {
                            selected.length = 0;
                        });
                    });
                }
            }
        });
    }

    function openFolder(personID, folder) {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'filemanager/changeFolder',
            data: JSON.stringify({"personID": personID, "folder": folder})
        },function (data) {
            if (data && data.success) {
                window.CRM.reloadEDriveTable(function () {
                    $(".folder-back-drop").show();
                    $("#currentPath").html(data.currentPath);
                    selected.length = 0;// no more selected files
                });
            }
        });
    }

    $(document).on('click', '.filemanager-refresh', function () {
        window.CRM.reloadEDriveTable(function () {
        });
    });

    $(document).on('click', '.change-folder', function () {
        if ((/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
            (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)))) {
            var personID = $(this).data("personid");
            var folder = $(this).data("folder");

            openFolder(personID, folder);
        }
    });

    $(document).on('dblclick', '.change-folder', function () {
        //$(".change-folder").on('click', function () {
        var personID = $(this).data("personid");
        var folder = $(this).data("folder");

        if (!(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ||
            (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.platform)))) {
            var personID = $(this).data("personid");
            var folder = $(this).data("folder");

            openFolder(personID, folder);
        }
    });

    $(".new-folder").on('click', function () {
        var personID = $(this).data("personid");

        bootbox.prompt(i18next.t("Set your Folder name"), function (result) {
            if (result != '') {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'filemanager/newFolder',
                    data: JSON.stringify({"personID": personID, "folder": result})
                },function (data) {
                    if (data && !data.success) {
                        window.CRM.DisplayAlert(i18next.t("Error"), data.message);
                    }

                    window.CRM.reloadEDriveTable(function () {
                        selected.length = 0;// no more selected files
                    });
                });
            }
        });
    });

    $(".folder-back-drop").on('click', function () {
        var personID = $(this).data("personid");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'filemanager/folderBack',
            data: JSON.stringify({"personID": personID})
        },function (data) {
            if (data && data.success) {
                window.CRM.reloadEDriveTable(function () {
                    if (data.isHomeFolder) {
                        $(".folder-back-drop").hide();
                    } else {
                        $(".folder-back-drop").show();
                    }

                    $("#currentPath").html(data.currentPath);
                });
            }
        });
    });


    function BootboxContentUploadFile() {
        var frm_str = '  <form action="api/" method="post" id="formId" enctype="multipart/form-data">'
            + '  <div class="card">'
            + '     <div class="card-body">'
            + '       <label for="noteInputFile">' + i18next.t("Files input") + " : " + '</label>'
            + '       <input type="file" id="noteInputFile" name="noteInputFile[]" multiple>'
            + '       ' + i18next.t('Upload your files')
            + '     </div>'
            + '     <div class="card-footer">'
            + '       <input type="submit" class="btn btn-success" name="Submit" value="' + i18next.t("Upload") + '">'
            + '     </div>'
            + '  </div>'
            + '  </form>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    function CreateUploadFileWindow() {
        var modal = bootbox.dialog({
            title: i18next.t("Upload your Files"),
            message: BootboxContentUploadFile(),
            size: "large",
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel"),
                    className: "btn btn-default",
                    callback: function () {
                        modal.modal("hide");
                        return true;
                    }
                },
            ],
            show: false,
            onEscape: function () {
                modal.modal("hide");
            }
        });

        return modal;
    }

    $(document).on('submit', '#formId', function (e) {
        $.ajax({
            url: window.CRM.root + "/api/filemanager/uploadFile/" + window.CRM.currentPersonID,
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false
        }).done(function (data) {
            window.CRM.reloadEDriveTable(function () {
                uploadWindow.modal("hide");
            });
        });
        e.preventDefault();
    });

    $("#uploadFile").on('click', function () {
        uploadWindow = CreateUploadFileWindow();

        uploadWindow.modal("show");
    });


    // the share files
    window.CRM.BootboxContentShareFiles = function () {
        var frm_str = '<h3 style="margin-top:-5px">' + i18next.t("Share your File") + '</h3>'
            + '<div>'
            + '<div class="row div-title">'
            + '<div class="col-md-4">'
            + '<span style="color: red">*</span>' + i18next.t("With") + ":"
            + '</div>'
            + '<div class="col-md-8">'
            + '<select size="6" style="width:100%" id="select-share-persons" multiple>'
            + '</select>'
            + '</div>'
            + '</div>'
            + '<div class="row div-title">'
            + '<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Set Rights") + ":</div>"
            + '<div class="col-md-8">'
            + '<select name="person-group-Id" id="person-group-rights" class="form-control form-control-sm"'
            + 'style="width:100%" data-placeholder="text to place">'
            + '<option value="0">' + i18next.t("Select your rights") + " [👀  ]" + i18next.t("or") + "[👀 ✐]" + ' -- </option>'
            + '<option value="1">' + i18next.t("[👀  ]") + ' -- ' + i18next.t("[R ]") + '</option>'
            + '<option value="2">' + i18next.t("[👀 ✐]") + ' -- ' + i18next.t("[RW]") + '</option>'
            + '</select>'
            + '</div>'
            + '</div>'
            + '<div class="row div-title">'
            + '<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Send email notification") + ":</div>"
            + '<div class="col-md-8">'
            + '<input id="sendEmail" type="checkbox">'
            + '</div>'
            + '</div>'
            + '<div class="row div-title">'
            + '<div class="col-md-4"><span style="color: red">*</span>' + i18next.t("Add persons/Family/groups") + ":</div>"
            + '<div class="col-md-8">'
            + '<select name="person-group-Id" id="person-group-Id" class="form-control select2"'
            + 'style="width:100%">'
            + '</select>'
            + '</div>'
            + '</div>'
            + '</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

// Share Files management
    function addPersonsFromNotes(noteId) {
        $('#select-share-persons').find('option').remove();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'sharedocument/getallperson',
            data: JSON.stringify({"noteId": noteId})
        },function (data) {
            var elt = document.getElementById("select-share-persons");
            var len = data.length;

            for (i = 0; i < len; ++i) {
                var option = document.createElement("option");
                // there is a groups.type in function of the new plan of schema
                option.text = data[i].name;
                //option.title = data[i].type;
                option.value = data[i].id;

                elt.appendChild(option);
            }
        });

        //addProfilesToMainDropdown();
    }

    window.CRM.addSharedButtonsActions = function (noteId, isShared, button, state, modal) {
        $("#person-group-Id").select2({
            language: window.CRM.shortLocale,
            minimumInputLength: 2,
            placeholder: " -- " + i18next.t("Person or Family or Group") + " -- ",
            allowClear: true, // This is for clear get the clear button if wanted
            ajax: {
                url: function (params) {
                    return window.CRM.root + "/api/people/search/" + params.term;
                },
                headers: {
                    "Authorization" : "Bearer "+window.CRM.jwtToken
                },
                dataType: 'json',
                delay: 250,
                data: "",
                processResults: function (data, params) {
                    return {results: data};
                },
                cache: true
            }
        });

        $("#person-group-rights").change(function () {
            var rightAccess = $(this).val();
            var deferreds = [];
            var i = 0;

            $('#select-share-persons :selected').each(function (i, sel) {
                var personID = $(sel).val();
                var str = $(sel).text();

                deferreds.push(
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'sharedocument/setrights',
                        data: JSON.stringify({"noteId": noteId, "personID": personID, "rightAccess": rightAccess})
                    },function (data) {
                        if (rightAccess == 1) {
                            res = str.replace(i18next.t("[👀 ✐]"), i18next.t("[👀  ]"));
                        } else {
                            res = str.replace(i18next.t("[👀  ]"), i18next.t("[👀 ✐]"));
                        }

                        var elt = [personID, res];
                        deferreds[i++] = elt;
                    })
                );

            });

            $.when.apply($, deferreds).done(function (data) {
                // all images are now prefetched
                //addPersonsFromNotes(noteId);

                deferreds.forEach(function (element) {
                    $('#select-share-persons option[value="' + element[0] + '"]').text(element[1]);
                });

                $("#person-group-rights option:first").attr('selected', 'selected');
            });
        });

        $("#select-share-persons").change(function () {
            $("#person-group-rights").val(0);
        });


        $("#person-group-Id").on("select2:select", function (e) {
            var notification = ($("#sendEmail").is(':checked')) ? 1 : 0;

            if (e.params.data.personID !== undefined) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'sharedocument/addperson',
                    data: JSON.stringify({
                        "noteId": noteId,
                        "currentPersonID": window.CRM.currentPersonID,
                        "personID": e.params.data.personID,
                        "notification": notification
                    })
                },function (data) {
                    addPersonsFromNotes(noteId);
                    $(state).css('color', 'green');
                    $(button).data('shared', 1);
                });
            } else if (e.params.data.groupID !== undefined) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'sharedocument/addgroup',
                    data: JSON.stringify({
                        "noteId": noteId,
                        "currentPersonID": window.CRM.currentPersonID,
                        "groupID": e.params.data.groupID,
                        "notification": notification
                    })
                },function (data) {
                    addPersonsFromNotes(noteId);
                    $(state).css('color', 'green');
                    $(button).data('shared', 1);
                });
            } else if (e.params.data.familyID !== undefined) {
                window.CRM.APIRequest({
                    method: 'POST',
                    path: 'sharedocument/addfamily',
                    data: JSON.stringify({
                        "noteId": noteId,
                        "currentPersonID": window.CRM.currentPersonID,
                        "familyID": e.params.data.familyID,
                        "notification": notification
                    })
                },function (data) {
                    addPersonsFromNotes(noteId);
                    $(state).css('color', 'green');
                    $(button).data('shared', 1);
                });
            }
        });

        addPersonsFromNotes(noteId);
        modal.modal('show');

        // this will ensure that image and table can be focused
        $(document).on('focusin', function (e) {
            e.stopImmediatePropagation();
        });
    }

    function openShareFilesWindow(event, button, state) {
        var noteId = event.currentTarget.dataset.id;
        var isShared = event.currentTarget.dataset.shared;

        var modal = bootbox.dialog({
            message: window.CRM.BootboxContentShareFiles(),
            size: "large",
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Delete"),
                    className: "btn btn-warning",
                    callback: function () {
                        bootbox.confirm(i18next.t("Are you sure ? You're about to delete this Person ?"), function (result) {
                            if (result) {
                                $('#select-share-persons :selected').each(function (i, sel) {
                                    var personID = $(sel).val();

                                    window.CRM.APIRequest({
                                        method: 'POST',
                                        path: 'sharedocument/deleteperson',
                                        data: JSON.stringify({"noteId": noteId, "personID": personID})
                                    },function (data) {
                                        $("#select-share-persons option[value='" + personID + "']").remove();

                                        if (data.count == 0) {
                                            $(state).css('color', '#777');
                                            $(button).data('shared', 0);
                                        }

                                        $("#person-group-Id").val("").trigger("change");
                                    });
                                });
                            }
                        });
                        return false;
                    }
                },
                {
                    label: '<i class="far fa-stop-circle"></i> ' + i18next.t("Stop sharing"),
                    className: "btn btn-danger",
                    callback: function () {
                        bootbox.confirm(i18next.t("Are you sure ? You are about to stop sharing your document ?"), function (result) {
                            if (result) {
                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'sharedocument/cleardocument',
                                    data: JSON.stringify({"noteId": noteId})
                                },function (data) {
                                    addPersonsFromNotes(noteId);
                                    $(state).css('color', '#777');
                                    $(button).data('shared', 0);
                                    modal.modal("hide");
                                    window.CRM.reloadEDriveTable();
                                });
                            }
                        });
                        return false;
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Ok"),
                    className: "btn btn-primary",
                    callback: function () {
                        window.CRM.reloadEDriveTable(function () {
                            uploadWindow.modal("hide");
                        });
                        return true;
                    }
                },
            ],
            show: false,
            onEscape: function () {
                window.CRM.dataEDriveTable.ajax.reload(function (json) {
                    modal.modal("hide");
                    installDragAndDrop();
                });
            }
        });

        window.CRM.addSharedButtonsActions(noteId, isShared, button, state, modal);
    }

    var isOpened = false;

    $(document).on('click', '.shareFile', function (event) {
        var button = $(this); //Assuming first tab is selected by default
        var state = button.find('.share-color');

        if (!isOpened) {
            openShareFilesWindow(event, button, state);
            isOpened = true;
        } else {
            isOpened = false;
        }
    });

    $.fn.dataTable.moment = function (format, locale) {
        var types = $.fn.dataTable.ext.type;

        // Add type detection
        types.detect.unshift(function (d) {
            // Removed true as the last parameter of the following moment
            return moment(d, format, locale).isValid() ?
                'moment-' + format :
                null;
        });

        // Add sorting method - use an integer for the sorting
        types.order['moment-' + format + '-pre'] = function (d) {
            console.log("d");
            return moment(d, format, locale, true).unix();
        };
    };

    $.fn.dataTable.ext.type.order['column-name-pre'] = function (data) {
        var val = $(data).data("name");

        return val;
    }

    $.fn.dataTable.ext.type.order['file-size-pre'] = function (data) {
        var units = data.replace(/[\d\.\,\ ]/g, '').toLowerCase();
        var multiplier = 1;

        if (units === 'kb') {
            multiplier = 1000;
        } else if (units === 'mb') {
            multiplier = 1000000;
        } else if (units === 'gb') {
            multiplier = 1000000000;
        }

        return parseFloat(data) * multiplier;
    };
    // end of EDrive management

    $('#edrive-table').on('draw.dt', function () {
        installDragAndDrop();
    });

});



