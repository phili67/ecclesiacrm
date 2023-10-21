$(function() {

    $('body').on('click', ".pnDelete", function () {
        var pnID = $(this).data("pnid");

        bootbox.confirm({
            message: i18next.t ("You're about to delete the buyer !!!"),
            buttons: {
                confirm: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t ('Yes'),
                    className: 'btn-danger'
                },
                cancel: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t ('No'),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: "DELETE",
                        path: "fundraiser/paddlenum",
                        data: JSON.stringify({"fundraiserID": window.CRM.fundraiserID, "pnID": pnID})
                    },function (data) {
                        if (data.status == "success") {
                            window.CRM.paddleNumListTable.ajax.reload();
                        }
                    });
                }
            }
        });
    });

    function addPersonsToSelectList(iPerID, iPaddleNum) {

        if (iPerID === undefined) {
            iPerID = false;
        }

        window.CRM.APIRequest({
            method: 'GET',
            path: 'fundraiser/paddlenum/persons/all/' + window.CRM.fundraiserID,
        },function (data) {
            var elt = document.getElementById("Buyers");
            var persons = data.persons;
            var len = persons.length;

            if (iPaddleNum == -1) {
                $("#Number").val(data.Number);
            } else {
                $("#Number").val(iPaddleNum);
            }

            var option = document.createElement("option");
            option.text = i18next.t ('Unassigned');
            option.value = 0;
            elt.appendChild(option);

            for (i = 0; i < len; ++i) {
                var option = document.createElement("option");
                option.text = persons[i].LastName + ", " + persons[i].FirstName + " - " + persons[i].FamAddress1 + " / " + persons[i].FamCity + ((persons[i].FamState != "")?" " + persons[i].FamState : "");
                option.value = persons[i].Id;

                if (iPerID && iPerID === persons[i].Id) {
                    option.setAttribute('selected', 'selected');
                }

                elt.appendChild(option);
            }
            $("#Buyers").select2();

            window.CRM.addbuyerModal.modal("show");
        });
    }

    function BootboxContent( windowtitle, number ) {
        var frm_str = '<h3 style="margin-top:-5px">' + i18next.t ('Buyer Number Editor') + '</h3><div id="some-form">'
            + '<div>'
            + '  <div class="row  div-title BuyerTitle">'
            + '      <div class="col-md-3"><label>' + i18next.t('Add buyer') + "</label></div>"
            + '  </div>'
            + '  <div class="row NumberTitle">'
            + '      <div class="col-md-3">' + i18next.t('Number') + ":</div>"
            + '      <div class="col-md-9">'
            + "         <input type='text' id='Number' placeholder='" + i18next.t("Buyer Number") + "' size='30' maxlength='100' class='form-control form-control-sm'  width='100%' style='width: 100%' required " + ((windowtitle != undefined) ? ("value='" + number + "'") : "") + ">"
            + '      </div>'
            + '  </div><br>'
            + '  <div class="row  buyer-list-title">'
            + '      <div class="col-md-3">' + i18next.t('Buyer') + ":</div>"
            + '      <div class="col-md-9">'
            + '          <select name="PerID" class="form-control select2  form-control-sm" id="Buyers" style="width: 100%">></select>'
            + '      </div>'
            + '  </div>'
            + '</div>';

        var object = $('<div/>').html(frm_str).contents();

        return object
    }

    function createBuyerEditorWindow(windowtitle, editionMode, iPaddleNum, iPerdId, iPaddleNumID) // dialogType : createEvent or modifyEvent, eventID is when you modify and event
    {
        windowtitle = i18next.t ('Buyer Number Editor');

        if (iPaddleNumID === undefined) {
            iPaddleNumID = -1;
        }

        if (iPaddleNum === undefined) {
            iPaddleNum = -1;
        }

        buttons = [
            {
                label: '<i class="fas fa-check"></i> ' + i18next.t("Save"),
                className: "btn btn-primary",
                callback: function () {
                    var Num = $("#Number").val();
                    var e = document.getElementById("Buyers");
                    var PerID = e.options[e.selectedIndex].value;


                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'fundraiser/paddlenum/add',
                        data: JSON.stringify({"fundraiserID": window.CRM.fundraiserID, "Num": Num, "PerID": PerID,
                            "PaddleNumID": iPaddleNumID})
                    },function (data) {
                        window.CRM.paddleNumListTable.ajax.reload();
                    });
                }
            }
        ];

        if (editionMode === undefined) {
            buttons = buttons.concat([
                {
                    label: i18next.t("Save and Add"),
                    className: "btn btn-success",
                    callback: function () {
                        var Num = $("#Number").val();
                        var e = document.getElementById("Buyers");
                        var PerID = e.options[e.selectedIndex].value;


                        window.CRM.APIRequest({
                            method: 'POST',
                            path: 'fundraiser/paddlenum/add',
                            data: JSON.stringify({"fundraiserID": window.CRM.fundraiserID, "Num": Num, "PerID": PerID,
                                "PaddleNumID": -1})
                        },function (data) {
                            $("#Number").val(++Num);
                            window.CRM.paddleNumListTable.ajax.reload();
                        });

                        return false;
                    }
                }
            ]);
        } else {
            buttons = buttons.concat([{
                label: i18next.t("Generate Statement"),
                className: "btn btn-info",
                callback: function () {
                    var Num = $("#Number").val();
                    var e = document.getElementById("Buyers");
                    var PerID = e.options[e.selectedIndex].value;

                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'fundraiser/paddlenum/info',
                        data: JSON.stringify({"fundraiserID": window.CRM.fundraiserID, "Num": Num, "PerID": PerID})
                    },function (data) {
                        location.href = window.CRM.root + "/Reports/FundRaiserStatement.php?PaddleNumID="+data.iPaddleNumID;
                    });

                    return false;
                }
            }])
        }

        buttons = buttons.concat([
            {
                label: '<i class="fas fa-times"></i> ' + i18next.t("Close"),
                className: "btn btn-default",
                callback: function () {
                    console.log("just do something on close");
                }
            }
        ]);

        window.CRM.addbuyerModal = bootbox.dialog({
            message: BootboxContent(windowtitle, iPaddleNum),
            size: 'large',
            buttons: buttons,
            show: false,
            onEscape: function() {
                window.CRM.addbuyerModal.modal("hide");
            }
        });

        addPersonsToSelectList(iPerdId, iPaddleNum);
    }

    $("#SelectAll").on('click', function () {
        var isChecked  = $(this).is(':checked');

        if (isChecked) {
            window.CRM.checkAll = true;
        } else {
            window.CRM.checkAll = false;
        }

        window.CRM.paddleNumListTable.ajax.reload();
    });

    $("#AddBuyer").on('click', function () {
        createBuyerEditorWindow(i18next.t('Buyer Number Editor'));
    });

    $("#AddDonnor").on('click', function () {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'fundraiser/paddlenum/add/donnors',
            data: JSON.stringify({"fundraiserID": window.CRM.fundraiserID})
        },function(data) {
            if (data.status == "success") {
                window.CRM.paddleNumListTable.ajax.reload();
            }
        });
    });

    $('body').on('click', ".edit-paddle-num", function () {
        var paddleID = $(this).data("id");
        var paddleNum = $(this).data("num");
        var personID = $(this).data("perid");

        createBuyerEditorWindow( i18next.t('Buyer Number Editor'), true, paddleNum, personID, paddleID);
    });

    window.CRM.paddleNumListTable = $("#buyer-listing-table").DataTable({
        ajax: {
            url: window.CRM.root + "/api/fundraiser/paddlenum/list/" + window.CRM.fundraiserID,
            type: 'POST',
            contentType: "application/json",
            dataSrc: "PaddleNumItems"
        },
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        "searching": false,
        columns: [
            {
                width: 'auto',
                title: i18next.t('Select'),
                data: 'Id',
                render: function (data, type, full, meta) {
                    return '<input type="checkbox"' +
                        'name="Chk' + data + '" ' + ((window.CRM.checkAll) ? 'checked="yes"' : '') + ' ></input>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Number'),
                data: 'Num',
                render: function (data, type, full, meta) {
                    return '<a href="#" class="edit-paddle-num" data-id="' + full.Id + '" data-num="' + full.Num + '" data-perid="' + full.PerId +'"> ' + full.Num + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Buyer'),
                data: 'BuyerFirstName',
                render: function (data, type, full, meta) {
                    return full.BuyerFirstName + ' ' + full.BuyerLastName;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Delete'),
                data: 'Id',
                render: function (data, type, full, meta) {
                    return '<a href="#" data-pnid="' + data + '" class="pnDelete">\n' +
                        '<i class="far fa-trash-alt" aria-hidden="true" style="color:#ff0000"></i></a>'
                }
            }
        ],
        responsive: true,
        createdRow: function (row, data, index) {
            $(row).addClass("paymentRow");
        }
    });

});
