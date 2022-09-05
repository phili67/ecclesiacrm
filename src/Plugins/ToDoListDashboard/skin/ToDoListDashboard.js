$(document).ready(function () {
    $('.todo-list').sortable({
        placeholder: 'sort-highlight',
        handle: '.handle',
        forcePlaceholderSize: true,
        zIndex: 999999
    });

    function CreateListBox() {
        var frm_str = '<b>' +
            '<form id="some-form">'
            + ' <div class="row">'
            + '     <div class="col-md-3">'
            + i18next.t('List name', {ns: 'ToDoListDashboard'}) + ' :'
            + '     </div>'
            + '     <div class="col-md-6">'
            + '         <input type="text" class="form-control" id="ListName" name="ListName" value="">'
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
                            // TO DO
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

            let cnt = data.items.length;
            var res = '';

            for (i = 0;i < cnt; i++) {
                res += '<li ' + (data.items[i].Checked == true?'class="done"':'') + '>' +
                    '  <span class="handle ui-sortable-handle">' +
                    '       <i class="fas fa-ellipsis-v"></i>' +
                    '       <i class="fas fa-ellipsis-v"></i>' +
                    '  </span>' +
                    '  <div class="icheck-primary d-inline ml-2">' +
                    '       <input type="checkbox" value="" name="todo1" class="todoListItemCheck" data-id="' + data.items[i].Id + '" ' + (data.items[i].Checked == true?'checked':'') + '>' +
                    '       <label for="todoCheck1"></label>' +
                    '  </div>' +
                    '  <span class="text">' + data.items[i].Name + '</span>' +
                    '  <small class="badge badge-danger"><i class="far fa-clock"></i> 2 mins</small>' +
                    '      <div class="tools">' +
                    '           <i class="fas fa-edit"></i>' +
                    '           <i class="fas fa-trash"></i>' +
                    '      </div>' +
                    '</li>'
            }

            ul.innerHTML = res;

            addListeners();
        });
    });

    function addToDoListDashboardItem(start) {
        var fmt = window.CRM.datePickerformat.toUpperCase();

        var EventWorkflowDate = moment(start).format(fmt);

        var frm_str = '<form id="some-form">'
            + ' <div class="row">'
            + '     <div class="col-md-3">'
            + i18next.t('Item name', {ns: 'ToDoListDashboard'}) + ' :'
            + '     </div>'
            + '     <div class="col-md-6">'
            + '         <input type="text" class="form-control" id="ToDoListItemDashboardName" name="ToDoListItemDashboardName" value="">'
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
            + '                 <input type="text" class="form-control timepicker form-control-sm" id="ToDoListItemDashboardTime" name="ToDoListItemDashboardTime" value="0:00">'
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
            message: addToDoListDashboardItem(),
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
                            // TO DO reload the datas
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

    function addListeners () {
        window.CRM.ElementListener('.todoListItemCheck', 'click', function (event) {
            var id = event.currentTarget.dataset.id;
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
            });
        });
    }

    addListeners();
});

