/* copyright 2018 Philippe Logel */

$(function () {
    // Helper function to get parameters from the query string.
    // use to search the ckeditor function to put the right param in the ckeditor image tool
    const getUrlParam = (paramName) => {
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

    var DataTableOpts = {
        ajax: {
            url: window.CRM.root + "/api/filemanager/" + window.CRM.currentPersonID,
            type: 'POST',
            contentType: "application/json",
            dataSrc: "files",
            "beforeSend": function (xhr) {
                xhr.setRequestHeader('Authorization',
                    "Bearer " + window.CRM.jwtToken
                );
            }
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        order: [[1, 'asc']],
        searching: true,
        select: true,
        paging: false,
        columns: [            
            {
                width: '5%',
                title: i18next.t('Icon'),
                data: 'icon',
                render: function (data, type, full, meta) {
                    let locked_icon = '';
                    if (full.locked) {
                        locked_icon = ' <i class="fa-solid fa-lock"></i>';
                    }
                    if (!full.dir) {
                        return '<span class="drag drag-file" id="' + full.name + '" type="file" data-path="' + full.path + '" data-perid="' + full.perID + '">' + data + locked_icon+ '</span>';
                    } else {
                        return '<a class="change-folder" data-personid="' + window.CRM.currentPersonID + '" data-folder="' + full.name + '"><span class="drag drop" id="' + full.name + '" type="folder">' + data + locked_icon + '</span>';
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

                        return '<input type="text" value="' + fileName + '" class="fileName form-control form-control-sm" data-name="' + data + '" data-type="folder" readonly>';
                    } else {
                        var fileName = data;
                        fileName = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;

                        return '<input type="text" value="' + fileName + '" class="fileName form-control form-control-sm" data-name="' + data + '" data-type="file" readonly>';
                    }
                }
            },
            {
                width: '5%',
                title: i18next.t('Actions'),
                data: 'id',
                render: function (data, type, full, meta) {
                    if (!full.dir && !full.link) {
                        var ret = '<div class="btn-group">' +
                            '   <a href="' + window.CRM.root + '/api/filemanager/getFile/' + full.perID + '/' + full.path + '" type="button" id="uploadFile" class="btn btn-secondary btn-sm" data-personid="' + window.CRM.currentPersonID + '" data-toggle="tooltip" data-placement="top" title="" data-original-title="Télécharger fichier dans EDrive"><i class="fas fa-download"></i></a>' +
                            '   <button type="button" class="btn btn-' + (full.isShared ? 'success' : 'default') + ' btn-sm shareFile" data-personid="' + window.CRM.currentPersonID + '"  data-id="' + data + '" data-shared="' + full.isShared + '" data-toggle="tooltip" data-placement="top" title="" data-original-title="Créer un dossier"><i class="fas fa-share-square"></i></button>' +
                            '</div>';
                        return ret;
                    } else if (!full.link) {
                        var ret = '<div class="btn-group">' +
                            '   <button type="button" class="btn btn-' + (full.isShared ? 'success' : 'default') + ' btn-sm shareFile" data-personid="' + window.CRM.currentPersonID + '"  data-id="' + data + '" data-shared="' + full.isShared + '" data-toggle="tooltip" data-placement="top" title="" data-original-title="Créer un dossier"><i class="fas fa-share-square"></i></button>' +
                            '</div>';
                        return ret;
                    } else if (full.link) {
                        var ret = '<div class="btn-group">' +
                            '   <a href="' + window.CRM.root + '/api/filemanager/getFile/' + full.perID + '/' + full.path + '" type="button" id="uploadFile" class="btn btn-secondary btn-sm" data-personid="' + window.CRM.currentPersonID + '" data-toggle="tooltip" data-placement="top" title="" data-original-title="Télécharger fichier dans EDrive"><i class="fas fa-download"></i></a>' +
                            '   <button type="button" class="btn btn-' + (full.isShared ? 'success' : 'default') + ' btn-sm shareFile" data-personid="' + window.CRM.currentPersonID + '"  data-id="' + data + '" data-shared="' + full.isShared + '" data-toggle="tooltip" data-placement="top" title="" data-original-title="Créer un dossier"><i class="fas fa-link"></i></button>' +
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
            if (window.CRM.currentpath !== "/") {
                $(".flex-wrap").addClass('shift-flex-wrapper-right');
            }
        }
    };


    $.extend(DataTableOpts, window.CRM.plugin.dataTable);

    window.CRM.plugin.dataTable.buttons.push({
        text: '<i class="fas fa-trash-alt"></i> ' + i18next.t("Delete"),
        attr: {
            id: 'trash-drop',
            'data-personid':window.CRM.currentPersonID,
            'data-toggle':"tooltip",
            'data-placement': "top",
            'title': i18next.t("Delete"),            
        },
        enabled: false,
        className: 'btn btn-danger btn-sm drag-elements trash-drop ui-droppable',
        action: function (e, dt, node, config) {
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
                                data: JSON.stringify({ "personID": window.CRM.currentPersonID, "files": selected })
                            }, function (data) {
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
        }
    });

    window.CRM.plugin.dataTable.buttons.push({
        text: '<i class="fas fa-folder"></i> ' + i18next.t("Create a Folder"),
        className: 'btn btn-secondary btn-sm new-folder',
        attr: {
            id: 'new-folder',
            'data-personid':window.CRM.currentPersonID,
            'title': i18next.t("Create a Folder"),
            'data-toggle': 'tooltip',
            'data-placement':"top"
        },
        enabled: true,        
        action: function (e, dt, node, config) {
            var personID = $('#new-folder').data("personid");

            bootbox.prompt(i18next.t("Set your Folder name"), function (result) {
                if (result != '' && result != null) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'filemanager/newFolder',
                        data: JSON.stringify({ "personID": personID, "folder": result })
                    }, function (data) {
                        if (data && !data.success) {
                            window.CRM.DisplayAlert(i18next.t("Error"), data.message);
                        }

                        window.CRM.reloadEDriveTable(function () {
                            selected.length = 0;// no more selected files
                        });
                    });
                }
            });
        }
    });

    window.CRM.plugin.dataTable.buttons.push({
        text: '<i class="fas fa-sync-alt"></i> ' + i18next.t("Actualize files"),
        className: 'btn btn-secondary btn-sm drag-elements filemanager-refresh',
        attr: {
            id: 'filemanager-refresh',
            'data-personid':window.CRM.currentPersonID,
            'title': i18next.t("Actualize files"),
            'data-toggle': 'tooltip',
            'data-placement':"top"
        },
        enabled: true,        
        action: function (e, dt, node, config) {
            var realRows = window.CRM.dataEDriveTable.rows({ selected: true });
            window.CRM.reloadEDriveTable(function () {
                realRows.select();        
            });
        }
    });

    window.CRM.dataEDriveTable = $("#edrive-table").DataTable(DataTableOpts);

    $("body").on('click', '.filemanager-download', function (e) {
        var selectedRows = window.CRM.dataEDriveTable.rows('.selected').data()
        $.each(selectedRows, function (index, value) {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'filemanager/getRealLink',
                data: JSON.stringify({ "personID": window.CRM.currentPersonID, "pathFile": value.path })
            }, function (data) {
                if (data && data.success) {
                    var fileUrl = data.address;
                    if (window.CRM.donatedItemID) {
                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'fundraiser/donatedItem/submit/picture',
                            data: JSON.stringify({ "DonatedItemID": window.CRM.donatedItemID, "pathFile": fileUrl })
                        }, function (data) {
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

    // click in the table
    $('#edrive-table tbody').on('click', 'tr', function(e) {
        let column = window.CRM.dataEDriveTable.column( this ).index();//unusefull at this moment
        var data = window.CRM.dataEDriveTable.row(this).data();
        let id = data['name'];

        $(this).toggleClass('selected');

        var selectedRows = window.CRM.dataEDriveTable.rows('.selected').data().length;
        
        if (window.CRM.browserImage == true) {
            if (selectedRows) {
                $(".filemanager-download").css("display", "block");
            } else {
                $(".filemanager-download").css("display", "none");
            }
        }        

        if (selectedRows) {
            $("#trash-drop").removeClass('disabled');            
        } else {
            $("#trash-drop").addClass('disabled');            
        }

        
        window.CRM.APIRequest({
            method: 'POST',
            path: 'filemanager/getPreview',
            data: JSON.stringify({ "personID": window.CRM.currentPersonID, "name": id })
        }, function (data) {
            if (data && data.success) {
                $('.filmanager-right').show();
                $('.preview-title').html(data.name);
                $('.preview').html(data.path);
                if (data.link) {
                    $('.share-part').hide();
                    $('.share-part-another-user').show();
                    sharedByPersonsSabre();
                } else {
                    $('.share-part').show();             
                    $('.share-part-another-user').hide();       

                    addSharedPersonsSabre();
                }
                
            }
        });        
    });

    /*$('#edrive-table tbody').on('click', 'td', function (e) {
        var data = window.CRM.dataEDriveTable.row($(this).parent()).data();
        let id = data['name'];

        var col = window.CRM.dataEDriveTable.cell(this).index().column;

        if (!(col == 2)) {
            $(this).parent().toggleClass('selected');

            var selectedRows = window.CRM.dataEDriveTable.rows('.selected').data().length;

            if (selectedRows) {
                $("#trash-drop").removeClass('disabled');
            } else {
                $("#trash-drop").addClass('disabled');
            }

            if (window.CRM.browserImage == true) {
                if (selectedRows) {
                    $(".filemanager-download").css("display", "block");
                } else {
                    $(".filemanager-download").css("display", "none");
                }
            }

            window.CRM.APIRequest({
                method: 'POST',
                path: 'filemanager/getPreview',
                data: JSON.stringify({ "personID": window.CRM.currentPersonID, "name": id })
            }, function (data) {
                if (data && data.success) {
                    $('.filmanager-right').show();
                        $('.preview-title').html(data.name);
                        $('.preview').html(data.path);
                    if (data.link) {
                        $('.share-part').hide();
                    } else {
                        $('.share-part').show();                    

                        addSharedPersonsSabre();
                    }
                    
                }
            });               
        }
    });*/


    $("body").on('click', '.fileName', function (e) {
        if (navigator.userAgent.match(/iPad|iPhone|Android|BlackBerry|Windows Phone|webOS/i)) {
            // we're on a SmartPhone
            var oldName = $(this).data("name");
            var fileName = '';
            var realRows = window.CRM.dataEDriveTable.rows({ selected: true });

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
                        }, function (data) {
                            if (data && data.success) {
                                window.CRM.reloadEDriveTable(function () {
                                    realRows.select();
                                });
                            } else {
                                window.CRM.DisplayAlert(i18next.t("Error"), data.message);
                                window.CRM.reloadEDriveTable(function () {
                                   realRows.select();
                                });
                            }
                        });
                    }
                }
            });
        }
    });

    $("body").on('dblclick', '.drag-file', function (e) {
        if (!navigator.userAgent.match(/iPad|iPhone|Android|BlackBerry|Windows Phone|webOS/i)) {
            var perID = $(this).data("perid");
            var path = $(this).data("path");

            window.location.href = window.CRM.root + '/api/filemanager/getFile/' + perID + '/' + path;
        }
    });

    $("body").on('dblclick', '.fileName', function (e) {
        if (!navigator.userAgent.match(/iPad|iPhone|Android|BlackBerry|Windows Phone|webOS/i)) {
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
        $('.filmanager-right').hide();
    });


    $("body").on('keypress', '.fileName', function (e) {
        var realRows = window.CRM.dataEDriveTable.rows({ selected: true });
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
                }, function (data) {
                    if (data && data.success) {
                        window.CRM.reloadEDriveTable(function () {
                           realRows.select();
                        });
                    } else  {
                        window.CRM.DisplayAlert(i18next.t("Error"), data.message);
                        window.CRM.reloadEDriveTable(function () {
                            realRows.select();
                        });
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
                }, function (data) {
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
                }, function (data) {
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
                    }, function (data) {
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
                    }, function (data) {
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

    const openFolder = (personID, folder) => {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'filemanager/changeFolder',
            data: JSON.stringify({ "personID": personID, "folder": folder })
        }, function (data) {
            if (data && data.success) {
                $('.filmanager-right').hide();
                window.CRM.reloadEDriveTable(function () {
                    $(".folder-back-drop").show();
                    $(".flex-wrap").addClass('shift-flex-wrapper-right');
                    $("#currentPath").html(data.currentPath);
                    selected.length = 0;// no more selected files
                });
            }
        });
    }

    $(document).on('click', '.change-folder', function () {
        if (navigator.userAgent.match(/iPad|iPhone|Android|BlackBerry|Windows Phone|webOS/i)) {
            var personID = $(this).data("personid");
            var folder = $(this).data("folder");

            openFolder(personID, folder);
        }
    });

    $(document).on('dblclick', '.change-folder', function () {
        //$(".change-folder").on('click', function () {
        var personID = $(this).data("personid");
        var folder = $(this).data("folder");

        if (!navigator.userAgent.match(/iPad|iPhone|Android|BlackBerry|Windows Phone|webOS/i)) {
            var personID = $(this).data("personid");
            var folder = $(this).data("folder");

            openFolder(personID, folder);
        }
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
                        $(".flex-wrap").removeClass('shift-flex-wrapper-right');
                        $(".folder-back-drop").hide();
                    } else {
                        $(".flex-wrap").addClass('shift-flex-wrapper-right');
                        $(".folder-back-drop").show();
                    }

                    $("#currentPath").html(data.currentPath);
                });
            }
        });
    });

    const uploadEvent = () => {
        window.CRM.ElementListener('#formId', 'submit', function (event) {
            event.preventDefault();

            const fileInput = document.getElementById('noteInputFile');

            let totalFilesToUpload = fileInput.files.length;

            //nothing was selected 
            if (totalFilesToUpload === 0) {
                alert('Please select one or more files.');
                return;
            }

            for (let i = 0; i < totalFilesToUpload; i++) {
                const file = fileInput.files[i];
                const formData = new FormData();
                formData.append('noteInputFile', file);

                const request = new Request(window.CRM.root + "/api/filemanager/uploadFile/" + window.CRM.currentPersonID, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Authorization': 'Bearer ' + window.CRM.jwtToken,
                    }
                });

                fetch(request)
                    .then(response => response.json())
                    .then(data => {
                        window.CRM.reloadEDriveTable(function () {
                            uploadWindow.modal("hide");
                        });
                    });
            }
        });
    }

    // Share Files management
    $("#preview-person-group-sabre-Id").select2({
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        placeholder: " -- " + i18next.t("User") + " -- ",
        allowClear: true, // This is for clear get the clear button if wanted
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/people/searchonlyuserwithedrive/" + params.term;
            },
            headers: {
                "Authorization": "Bearer " + window.CRM.jwtToken
            },
            dataType: 'json',
            delay: 250,
            data: "",
            processResults: function (data, params) {
                return { results: data };
            },
            cache: true
        }
    });

    $("#preview-person-group-sabre-Id").on("select2:select", function (e) {
        let access = $("#person-group-rights").val();
        var realRows = window.CRM.dataEDriveTable.rows({ selected: true });
        let data = realRows.data();
        let rows = [];
        for (let i = 0; i < data.length; i++) {
            rows.push(data[i]);
        }

        let notification = document.getElementById("sendEmail-sabre").checked

        if (e.params.data.personID !== undefined) {
            window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/addpersonsabre',
                data: JSON.stringify({
                    "currentPersonID": window.CRM.currentPersonID,
                    "personToShareID": e.params.data.personID,
                    "rows": rows,
                    "access": access, // by default read and write
                    "notification": notification
                })
            }, function (data) {
                window.CRM.reloadEDriveTable(function () {
                    realRows.select();
                });
                addSharedPersonsSabre();
            });
        }
    });

    
    const sharedByPersonsSabre = () => {
        let data = window.CRM.dataEDriveTable.rows({ selected: true }).data();
        let rows = [];
        for (let i = 0; i < data.length; i++) {
            rows.push(data[i]);
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'sharedocument/getShareInfosSabre',
            data: JSON.stringify({
                "currentPersonID": window.CRM.currentPersonID,
                "rows": rows
            })}, function (data) {
                let res = '<ul>';
                for (const element of data) {
                    res += '<li>' + element['fullName'] + '</li>';
                }
                res += '</ul>';

                $(".share-part-another-user-content").html(res);
            }
        );
    }

    const addSharedPersonsSabre = () => {
        $("#dropdownMenuButtonRights").prop('disabled', true);
        $("#delete-share").prop('disabled', true);    

        let data = window.CRM.dataEDriveTable.rows({ selected: true }).data();
        let rows = [];
        for (let i = 0; i < data.length; i++) {
            rows.push(data[i]);
        }

        $('#select-share-persons-sabre').find('option').remove();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'sharedocument/getallpersonsabre',
            data: JSON.stringify({
                "currentPersonID": window.CRM.currentPersonID,
                "rows": rows
            })
        }, function (data) {
            var elt = document.getElementById("select-share-persons-sabre");
            var len = data.length;

            $("#delete-all-share").prop('disabled', (len>0)?false:true);
            
            for (i = 0; i < len; ++i) {
                var option = document.createElement("option");
                // there is a groups.type in function of the new plan of schema
                option.text = data[i].name;
                //option.title = data[i].type;
                option.value = data[i].id;

                //option.classList.add("fontawesome");

                elt.appendChild(option);
            }
        });
    }

    $("#select-share-persons-sabre").on('change', function () {
        let values = $('#select-share-persons-sabre').val();

        let activated = false;
        if (values.length > 0) {
            activated = true;
        }

        $("#dropdownMenuButtonRights").prop('disabled', !activated);
        $("#delete-share").prop('disabled', !activated);
        //$("#delete-all-share").prop('disabled', !activated);
    });

    $("#set-right-read").on('click', function () {
        var rightAccess = 2;
        let data = window.CRM.dataEDriveTable.rows({ selected: true }).data();
        var rows = [];
        for (let i = 0; i < data.length; i++) {
            rows.push(data[i]);
        }

        $('#select-share-persons-sabre :selected').each(function (i, sel) {
            var selection = sel;
            let personID = $(sel).val();
            let str = $(sel).text();

            window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/setrightssabre',
                data: JSON.stringify({
                    "rows": rows,
                    "currentPersonID": window.CRM.currentPersonID,
                    "personToShareID": personID,
                    "rightAccess": rightAccess
                })
            }, function (data) {
                if (rightAccess == 2) {
                    res = str.replace(i18next.t("[👀 ✐]"), i18next.t("[👀  ]"));
                } else {
                    res = str.replace(i18next.t("[👀  ]"), i18next.t("[👀 ✐]"));
                }
                $(selection).text(res);
            })
        });
    });

    $("#set-right-read-write").on('click', function () {
        var rightAccess = 3;
        let data = window.CRM.dataEDriveTable.rows({ selected: true }).data();
        var rows = [];
        for (let i = 0; i < data.length; i++) {
            rows.push(data[i]);
        }

        $('#select-share-persons-sabre :selected').each(function (i, sel) {
            var selection = sel;
            let personID = $(sel).val();
            let str = $(sel).text();

            window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/setrightssabre',
                data: JSON.stringify({
                    "rows": rows,
                    "currentPersonID": window.CRM.currentPersonID,
                    "personToShareID": personID,
                    "rightAccess": rightAccess
                })
            }, function (data) {
                if (rightAccess == 2) {
                    res = str.replace(i18next.t("[👀 ✐]"), i18next.t("[👀  ]"));
                } else {
                    res = str.replace(i18next.t("[👀  ]"), i18next.t("[👀 ✐]"));
                }
                $(selection).text(res);
            })
        });
    });


    $("#person-group-rights-sabre").on('change', function () {
        var rightAccess = parseInt($(this).val());
        let data = window.CRM.dataEDriveTable.rows({ selected: true }).data();
        var rows = [];
        for (let i = 0; i < data.length; i++) {
            rows.push(data[i]);
        }

        $('#select-share-persons-sabre :selected').each(function (i, sel) {
            var selection = sel;
            let personID = $(sel).val();
            let str = $(sel).text();

            window.CRM.APIRequest({
                method: 'POST',
                path: 'sharedocument/setrightssabre',
                data: JSON.stringify({
                    "rows": rows,
                    "currentPersonID": window.CRM.currentPersonID,
                    "personToShareID": personID,
                    "rightAccess": rightAccess
                })
            }, function (data) {
                if (rightAccess == 2) {
                    res = str.replace(i18next.t("[👀 ✐]"), i18next.t("[👀  ]"));
                } else {
                    res = str.replace(i18next.t("[👀  ]"), i18next.t("[👀 ✐]"));
                }
                $(selection).text(res);
            })
        });
    });

    $("#delete-share").on('click', function () {
        var realRows = window.CRM.dataEDriveTable.rows({ selected: true });
        var data = realRows.data();
        var rows = [];
        for (let i = 0; i < data.length; i++) {
            rows.push(data[i]);
        }

        bootbox.confirm({title: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + i18next.t('Are you sure you want to stop sharing for selected users?') + '</span>',
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
                    $('#select-share-persons-sabre').each(function (i, sel) {
                        var personPrincipal = $(sel).val();

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'sharedocument/deletepersonsabre',
                            data: JSON.stringify({
                                "rows": rows,
                                "personPrincipal": personPrincipal,
                                "currentPersonID": window.CRM.currentPersonID
                            })
                        }, function (data) {
                            window.CRM.reloadEDriveTable(function () {
                                realRows.select();
                            });
                            $("#select-share-persons-sabre option[value='" + personPrincipal + "']").remove();
                            addSharedPersonsSabre();
                        });
                    });
                }
            }
        });
    });


    $("#delete-all-share").on('click', function () {
        var realRows = window.CRM.dataEDriveTable.rows({ selected: true });
        var data = realRows.data();
        var rows = [];
        for (let i = 0; i < data.length; i++) {
            rows.push(data[i]);
        }

        bootbox.confirm({title: '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + i18next.t('Are you sure, you want to stop all sharing ?') + '</span>',
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
            callback:  function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'sharedocument/cleardocumentsabre',
                        data: JSON.stringify({ 
                            "rows": rows,
                            "currentPersonID": window.CRM.currentPersonID
                        })
                    }, function (data) {
                        window.CRM.reloadEDriveTable(function () {
                            realRows.select();
                        });
                        addSharedPersonsSabre();
                    });
                }
            }
        });
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

    uploadEvent();
});