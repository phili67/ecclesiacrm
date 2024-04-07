$(function() {
    if (window.CRM.timeEnglish == true) {
        window.CRM.fmt = 'hh:mm a';
    } else {
        window.CRM.fmt = 'HH:mm';
    }

    $('.todo-list').sortable({
        placeholder: 'sort-highlight',
        handle: '.handle',
        forcePlaceholderSize: true,
        zIndex: 999999,
        stop: function( ev ) {
            // we get all the element
            let LIs = document.getElementById('todo-list').getElementsByTagName('li');

            let res = [];

            let cnt = LIs.length;

            for (i=0;i<cnt;i++) {
                var liId = LIs[i].dataset.id;
                res.push(parseInt(liId));
            }

            window.CRM.APIRequest({
                method: 'POST',
                path: 'todolistdashboard/changeListItemsOrder',
                data: JSON.stringify({
                    "list": res
                })
            }, function (data) {
                // TO DO
            });
        }
    });

    function CreateListBox(name='') {
        var frm_str = '<b>' +
            '<form id="some-form">'
            + ' <div class="row">'
            + '     <div class="col-md-3">'
            + i18next.t('List name', {ns: 'ToDoListDashboard'}) + ' :'
            + '     </div>'
            + '     <div class="col-md-6">'
            + '         <input type="text" class="form-control" id="ListName" name="ListName" value="' + name + '">'
            + '     </div>'
            + ' </div>'
            ' </form>';

        return frm_str
    }

    window.CRM.ElementListener('#Add-To-Do-List-Dashboard', 'click', function(event) {
        var modal = bootbox.dialog({
            title: i18next.t("Create a list", {ns: 'ToDoListDashboard'}),
            message: CreateListBox(),
            size: "large",
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('OK'),
                    className: "btn btn-primary",
                    callback: function () {
                        let ListName = document.getElementById('ListName').value;

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'todolistdashboard/addList',
                            data: JSON.stringify({
                                "name": ListName
                            })
                        }, function (data) {
                            window.CRM.TodoListDashboardId = data.ListId;

                            var ul = document.getElementById('todo-list');

                            ul.innerHTML = '';

                            // add the new element to the list
                            var selectList = document.getElementById('select-to-do-list-dashboard');

                            var opt = document.createElement("option");

                            opt.value = data.ListId;
                            opt.text = ListName;

                            selectList.add(opt, null);

                            selectList.value = data.ListId;

                            // we show the select list
                            selectList.hidden = false;

                            document.querySelector('#add-to-do-list-item').disabled = false;
                        });
                    }
                }
            ],
            show: false,
            onEscape: function () {
                modal.modal("hide");
            }
        });

        modal.modal("show");
    });

    window.CRM.ElementListener('#edit-To-Do-List-Dashboard', 'click', function(event) {
        var listID = window.CRM.TodoListDashboardId;

        window.CRM.APIRequest({
            method: 'POST',
            path: 'todolistdashboard/listInfo',
            data: JSON.stringify({
                "listID": listID
            })
        },function (data) {
            if (data.status == "success") {
                var modal = bootbox.dialog({
                    title: i18next.t("Edit list", {ns: 'ToDoListDashboard'}),
                    message: CreateListBox(data.Name),
                    size: "large",
                    buttons: [
                        {
                            label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel"),
                            className: "btn btn-default",
                            callback: function () {
                                console.log("just do something on close");
                            }
                        },
                        {
                            label: '<i class="fas fa-check"></i> ' + i18next.t('OK'),
                            className: "btn btn-primary",
                            callback: function () {
                                let ListName = document.getElementById('ListName').value;

                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'todolistdashboard/modifyList',
                                    data: JSON.stringify({
                                        "ListID": window.CRM.TodoListDashboardId,
                                        "Name": ListName
                                    })
                                }, function (data) {
                                    // add the new element to the list
                                    var selectList = document.getElementById('select-to-do-list-dashboard');

                                    // we remove the old list from the selected menu
                                    for (var i = selectList.length - 1; i >= 0; i--){
                                        if (selectList[i].value == window.CRM.TodoListDashboardId) {
                                            selectList[i].text = ListName
                                        }
                                    }

                                });
                            }
                        }
                    ],
                    show: false,
                    onEscape: function () {
                        modal.modal("hide");
                    }
                });

                modal.modal("show");
            }
        });
    });

    window.CRM.ElementListener('#remove-To-Do-List-Dashboard', 'click', function(event) {
        var oldListID = window.CRM.TodoListDashboardId;
        bootbox.confirm({
            title: i18next.t("You're about to delete your list?", {ns: 'ToDoListDashboard'}),
            message: i18next.t("This can't be undone.", {ns: 'ToDoListDashboard'}),
            buttons: {
                cancel: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel")
                },
                confirm: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Confirm")
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'DELETE',
                        path: 'todolistdashboard/removeList',
                        data: JSON.stringify({
                            "ListID": oldListID
                        })
                    }, function (data) {
                        window.CRM.TodoListDashboardId = data.ListId;

                        var ul = document.getElementById('todo-list');

                        var res = '';

                        var selectList = document.getElementById('select-to-do-list-dashboard');

                        if (data.ListId != -1) {
                            res = addElementsToDoList(data);

                            // we select the right list
                            selectList.value = data.ListId;

                            // we remove the old list from the selected menu
                            for (var i = selectList.length - 1; i >= 0; i--){
                                if (selectList[i].value == oldListID) {
                                    selectList.remove(i);
                                }
                            }
                        } else {
                            selectList.hidden = true;
                            document.querySelector('#add-to-do-list-item').disabled = true;
                        }

                        ul.innerHTML = res;
                    });
                }
            }
        });
    });

    function addElementsToDoList(data) {
        let cnt = data.items.length;
        var res = '';

        for (i = 0;i < cnt; i++) {
            res += '<li ' + (data.items[i].Checked == true?'class="done"':'') + ' data-id="' + data.items[i].Id + '">' +
                '  <span class="handle ui-sortable-handle">' +
                '       <i class="fas fa-ellipsis-v"></i>' +
                '       <i class="fas fa-ellipsis-v"></i>' +
                '  </span>' +
                '  <div class="icheck-primary d-inline ml-2">' +
                '       <input type="checkbox" value="" name="todo' + data.items[i].Id + '" class="todoListItemCheck" data-id="' + data.items[i].Id + '" ' + (data.items[i].Checked == true?'checked':'') + ' id="todo-' + data.items[i].Id + '">' +
                '       <label for="todo-' + data.items[i].Id + '"></label>' +
                '  </div>' +
                '  <span class="text">' + data.items[i].Name + '</span>' +
                '  <small class="badge badge-' + data.items[i].color + '"><i class="far fa-clock"></i> ' + data.items[i].period + '</small>' +
                '      <div class="tools">' +
                '           <i class="fas fa-edit edit-todoitemlist" data-id="' + data.items[i].Id + '"></i>' +
                '           <i class="fas fa-trash remove-todoitemlist" data-id="' + data.items[i].Id + '"></i>' +
                '      </div>' +
                '</li>'
        }

        return res;
    }

    window.CRM.ElementListener('#select-to-do-list-dashboard', 'change', function(event) {
        var option = event.currentTarget.options[event.currentTarget.selectedIndex];
        var Id = option.value;

        window.CRM.APIRequest({
            method: 'POST',
            path: 'todolistdashboard/changeList',
            data: JSON.stringify({
                "id": Id
            })
        }, function (data) {
            // TO DO : reload the list Id
            window.CRM.TodoListDashboardId = Id;

            var ul = document.getElementById('todo-list');

            var res = addElementsToDoList(data)

            ul.innerHTML = res;

            addToDoListListeners();
        });
    });

    function addEditToDoListDashboardItem(start = '', name = '') {
        var fmt = window.CRM.datePickerformat.toUpperCase();

        var EventWorkflowDate = moment().format(fmt);
        var time = '00:00';

        if (start != '') {
            EventWorkflowDate = moment(start).format(fmt);
            time = moment(start).format(window.CRM.fmt);
        }

        var frm_str = '<form id="some-form">'
            + ' <div class="row">'
            + '     <div class="col-md-3">'
            + i18next.t('Item name', {ns: 'ToDoListDashboard'}) + ' :'
            + '     </div>'
            + '     <div class="col-md-6">'
            + '         <input type="text" class="form-control" id="ToDoListItemDashboardName" name="ToDoListItemDashboardName" value="' + name + '">'
            + '     </div>'
            + ' </div>'
            + ' <br/>'
            + '  <div class="row   div-title calendar-title">'
            + '     <div class="col-md-12">'
            + '         <div class="row">'
            + '             <div class="col-md-3"><span style="color: red">*</span>'
            + i18next.t('Date', {ns: 'ToDoListDashboard'}) + ' :'
            + '             </div>'
            + '             <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                      <span class="input-group-text"><i class="fas fa-calendar"></i></span>'
            + '                 </div>'
            + '                 <input class=" form-control  form-control-sm date-picker form-control-sm" type="text" id="ToDoListItemDashboardDate" name="ToDoListItemDashboardDate"  value="' + EventWorkflowDate + '" '
            + '                 maxlength="10" id="sel1" size="11"'
            + '                 placeholder="' + window.CRM.datePickerformat + '">'
            + '             </div>'
            + '             <div class="col-md-3"><span style="color: red">*</span>'
            + i18next.t('Time', {ns: 'EventWorkflow'}) + ' :'
            + '             </div>'
            + '             <div class="input-group col-md-3">'
            + '                 <div class="input-group-prepend">'
            + '                     <span class="input-group-text"><i class="fas fa-clock"></i></span>'
            + '                 </div>'
            + '                 <input type="text" class="form-control timepicker form-control-sm" id="ToDoListItemDashboardTime" name="ToDoListItemDashboardTime" value="' + time + '">'
            + '             </div>'
            + '         </div>'
            + '     </div>'
            + '</div>'
            ' </form>';

        return frm_str
    }

    window.CRM.ElementListener('#add-to-do-list-item', 'click', function(event) {
        var currentListId = window.CRM.TodoListDashboardId;

        var modal = bootbox.dialog({
            title: i18next.t("Create a list item", {ns: 'ToDoListDashboard'}),
            message: addEditToDoListDashboardItem(),
            size: "large",
            buttons: [
                {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel"),
                    className: "btn btn-default",
                    callback: function () {
                        console.log("just do something on close");
                    }
                },
                {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('OK'),
                    className: "btn btn-primary",
                    callback: function () {
                        let ListItemName = document.getElementById('ToDoListItemDashboardName').value;
                        let ToDoListItemDashboardDate = document.getElementById('ToDoListItemDashboardDate').value;
                        let ToDoListItemDashboardTime = document.getElementById('ToDoListItemDashboardTime').value;
                        let fmt = window.CRM.datePickerformat.toUpperCase();

                        if (window.CRM.timeEnglish == true) {
                            time_format = 'h:mm A';
                        } else {
                            time_format = 'H:mm';
                        }

                        fmt = fmt + ' ' + time_format;

                        let real_dateTime = moment(ToDoListItemDashboardDate + ' ' + ToDoListItemDashboardTime, fmt).format('YYYY-MM-DD H:mm');

                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'todolistdashboard/addListItem',
                            data: JSON.stringify({
                                "ListId": currentListId,
                                "name": ListItemName,
                                "DateTime":real_dateTime
                            })
                        }, function (data) {
                            var ul = document.getElementById('todo-list');

                            var res = addElementsToDoList(data)

                            ul.innerHTML = res;

                            addToDoListListeners();

                            document.querySelector('#add-to-do-list-item').disabled = (data.items.length == 8)?true:false;
                        });
                    }
                }
            ],
            show: false,
            onEscape: function () {
                modal.modal("hide");
            }
        });

        modal.modal("show");

        // dateP picker
        $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});

        //Timepicker
        $('.timepicker').datetimepicker({
            format: 'LT',
            locale: window.CRM.lang,
            icons:
                {
                    up: 'fas fa-angle-up',
                    down: 'fas fa-angle-down'
                }
        });
    });

    function addToDoListListeners () {
        window.CRM.ElementListener('.todoListItemCheck', 'click', function (event) {
            var id = parseInt(event.currentTarget.dataset.id);
            var checked = event.currentTarget.checked;

            window.CRM.APIRequest({
                method: 'POST',
                path: 'todolistdashboard/checkItem',
                data: JSON.stringify({
                    "id": id,
                    "checked": checked
                })
            }, function (data) {
                // TO DO reload the datas
                addToDoListListeners();
            });
        });

        window.CRM.ElementListener('.edit-todoitemlist', 'click', function (event) {
            var id = parseInt(event.currentTarget.dataset.id);
            var currentListId = window.CRM.TodoListDashboardId;

            window.CRM.APIRequest({
                method: 'POST',
                path: 'todolistdashboard/ListItemInfo',
                data: JSON.stringify({
                    "ItemID": id
                })
            },function (data) {
                var modal = bootbox.dialog({
                    title: i18next.t("Edit list item", {ns: 'ToDoListDashboard'}),
                    message: addEditToDoListDashboardItem(data.date, data.name),
                    size: "large",
                    buttons: [
                        {
                            label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel"),
                            className: "btn btn-default",
                            callback: function () {
                                console.log("just do something on close");
                            }
                        },
                        {
                            label: '<i class="fas fa-check"></i> ' + i18next.t('OK'),
                            className: "btn btn-primary",
                            callback: function () {
                                let ListItemName = document.getElementById('ToDoListItemDashboardName').value;
                                let ToDoListItemDashboardDate = document.getElementById('ToDoListItemDashboardDate').value;
                                let ToDoListItemDashboardTime = document.getElementById('ToDoListItemDashboardTime').value;
                                let fmt = window.CRM.datePickerformat.toUpperCase();

                                if (window.CRM.timeEnglish == true) {
                                    time_format = 'h:mm A';
                                } else {
                                    time_format = 'H:mm';
                                }

                                fmt = fmt + ' ' + time_format;

                                let real_dateTime = moment(ToDoListItemDashboardDate + ' ' + ToDoListItemDashboardTime, fmt).format('YYYY-MM-DD H:mm');

                                window.CRM.APIRequest({
                                    method: 'POST',
                                    path: 'todolistdashboard/modifyListItem',
                                    data: JSON.stringify({
                                        "ListId": currentListId,
                                        "ItemID": id,
                                        "Name": ListItemName,
                                        "DateTime":real_dateTime
                                    })
                                }, function (data) {
                                    if (data.status == "success") {
                                        var ul = document.getElementById('todo-list');

                                        var res = addElementsToDoList(data)

                                        ul.innerHTML = res;

                                        addToDoListListeners();
                                    }
                                });
                            }
                        }
                    ],
                    show: false,
                    onEscape: function () {
                        modal.modal("hide");
                    }
                });

                modal.modal("show");

                // dateP picker
                $('.date-picker').datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});

                //Timepicker
                $('.timepicker').datetimepicker({
                    format: 'LT',
                    locale: window.CRM.lang,
                    icons:
                        {
                            up: 'fas fa-angle-up',
                            down: 'fas fa-angle-down'
                        }
                });
            });

        });

        window.CRM.ElementListener('.remove-todoitemlist', 'click', function (event) {
            var currentListId = window.CRM.TodoListDashboardId;
            var id = event.currentTarget.dataset.id;

            bootbox.confirm({
                title: i18next.t("Delete item?", { ns: 'ToDoListDashboard' }),
                message: i18next.t("You're about to delete an item.", { ns: 'ToDoListDashboard' }),
                buttons: {
                    cancel: {
                        label: '<i class="fas fa-times"></i> '+ i18next.t("Cancel")
                    },
                    confirm: {
                        label: '<i class="fas fa-check"></i> '+ i18next.t("Confirm")
                    }
                },
                callback: function (result) {
                    if (result) {
                        window.CRM.APIRequest({
                            method: 'DELETE',
                            path: 'todolistdashboard/deleteListItem',
                            data: JSON.stringify({
                                "ListId": currentListId,
                                "ItemID": id
                            })
                        },function (data) {
                            // TO DO
                            var ul = document.getElementById('todo-list');

                            var res = addElementsToDoList(data)

                            ul.innerHTML = res;

                            addToDoListListeners();

                            document.querySelector('#add-to-do-list-item').disabled = false;
                        });
                    }
                }
            });
        });
    }

    addToDoListListeners();
});

