$(function () {
    var $countBadge = $('#buyerCountBadge');

    /**
     * Update buyer count badge
     */
    function updateCountBadge() {
        if ($countBadge.length && window.CRM.paddleNumListTable) {
            $countBadge.text(window.CRM.paddleNumListTable.rows().count());
        }
    }

    /**
     * Handle buyer deletion
     */
    $('body').on('click', '.pnDelete', function (event) {
        event.preventDefault();
        var pnID = $(this).data('pnid');

        bootbox.confirm({
            message: i18next.t("You're about to delete the buyer !!!"),
            buttons: {
                confirm: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t('Yes'),
                    className: 'btn-danger'
                },
                cancel: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t('No'),
                    className: 'btn-primary'
                }
            },
            callback: function (result) {
                if (result) {
                    window.CRM.APIRequest({
                        method: 'DELETE',
                        path: 'fundraiser/paddlenum',
                        data: JSON.stringify({ fundraiserID: window.CRM.fundraiserID, pnID: pnID })
                    }, function (data) {
                        if (data.status === 'success') {
                            window.CRM.paddleNumListTable.ajax.reload(updateCountBadge);
                        }
                    });
                }
            }
        });
    });

    /**
     * Load persons into select dropdown
     */
    function addPersonsToSelectList(iPerID, iPaddleNum) {
        if (iPerID === undefined) {
            iPerID = false;
        }

        window.CRM.APIRequest({
            method: 'GET',
            path: 'fundraiser/paddlenum/persons/all/' + window.CRM.fundraiserID
        }, function (data) {
            var $elt = $('#Buyers');
            var persons = data.persons;
            var len = persons.length;

            if (iPaddleNum === -1) {
                $('#Number').val(data.Number);
            } else {
                $('#Number').val(iPaddleNum);
            }

            $elt.empty();

            var option = document.createElement('option');
            option.text = i18next.t('Unassigned');
            option.value = 0;
            $elt.append(option);

            for (var i = 0; i < len; ++i) {
                var opt = document.createElement('option');
                opt.text = persons[i].LastName + ', ' + persons[i].FirstName + ' - ' + persons[i].FamAddress1 + ' / ' + persons[i].FamCity + ((persons[i].FamState !== '') ? ' ' + persons[i].FamState : '');
                opt.value = persons[i].Id;

                if (iPerID && iPerID === persons[i].Id) {
                    opt.setAttribute('selected', 'selected');
                }

                $elt.append(opt);
            }

            $elt.select2();
            window.CRM.addbuyerModal.modal('show');
        });
    }

    /**
     * Generate the form content for buyer modal
     */
    function buildBuyerFormContent() {
        var frm = $('<div/>')
            .append(
                $('<div class="form-group mb-3"/>')
                    .append($('<label class="font-weight-semibold" for="Number">' + i18next.t('Buyer Number') + '</label>'))
                    .append($('<input type="text" id="Number" class="form-control form-control-sm" placeholder="' + i18next.t('Buyer Number') + '" required>')),
                $('<div class="form-group mb-0"/>')
                    .append($('<label class="font-weight-semibold" for="Buyers">' + i18next.t('Buyer') + '</label>'))
                    .append($('<select id="Buyers" class="form-control form-control-sm select2" style="width: 100%;"></select>'))
            );
        return frm;
    }

    /**
     * Create or edit buyer
     */
    function createOrEditBuyer(windowTitle, isEditMode, iPaddleNum, iPerID, iPaddleNumID) {
        if (iPaddleNumID === undefined) {
            iPaddleNumID = -1;
        }
        if (iPaddleNum === undefined) {
            iPaddleNum = -1;
        }

        var buttons = [
            {
                label: '<i class="fas fa-save"></i> ' + i18next.t('Save'),
                className: 'btn btn-primary',
                callback: function () {
                    saveBuyer(iPaddleNumID);
                }
            }
        ];

        if (!isEditMode) {
            buttons.push({
                label: '<i class="fas fa-plus"></i> ' + i18next.t('Save and Add'),
                className: 'btn btn-success',
                callback: function () {
                    saveBuyer(-1);
                    var newNum = parseInt($('#Number').val(), 10) + 1;
                    $('#Number').val(newNum);
                    return false;
                }
            });
        } else {
            buttons.push({
                label: '<i class="fas fa-file-export"></i> ' + i18next.t('Generate Statement'),
                className: 'btn btn-info',
                callback: function () {
                    generateStatement();
                    return false;
                }
            });
        }

        buttons.push({
            label: '<i class="fas fa-times"></i> ' + i18next.t('Close'),
            className: 'btn btn-secondary',
            callback: function () {
                // Close modal
            }
        });

        window.CRM.addbuyerModal = bootbox.dialog({
            title: i18next.t('Buyer Number Editor'),
            message: buildBuyerFormContent(),
            size: 'large',
            buttons: buttons,
            show: false,
            onEscape: function () {
                window.CRM.addbuyerModal.modal('hide');
            }
        });

        addPersonsToSelectList(iPerID, iPaddleNum);
    }

    /**
     * Save buyer data
     */
    function saveBuyer(iPaddleNumID) {
        var Num = $('#Number').val();
        var $buyersSelect = $('#Buyers');
        var PerID = $buyersSelect.length ? $buyersSelect.val() : 0;

        window.CRM.APIRequest({
            method: 'POST',
            path: 'fundraiser/paddlenum/add',
            data: JSON.stringify({
                fundraiserID: window.CRM.fundraiserID,
                Num: Num,
                PerID: PerID,
                PaddleNumID: iPaddleNumID
            })
        }, function (data) {
            window.CRM.paddleNumListTable.ajax.reload(updateCountBadge);
        });
    }

    /**
     * Generate statement for a buyer
     */
    function generateStatement() {
        var Num = $('#Number').val();
        var $buyersSelect = $('#Buyers');
        var PerID = $buyersSelect.length ? $buyersSelect.val() : 0;

        window.CRM.APIRequest({
            method: 'POST',
            path: 'fundraiser/paddlenum/info',
            data: JSON.stringify({
                fundraiserID: window.CRM.fundraiserID,
                Num: Num,
                PerID: PerID
            })
        }, function (data) {
            if (data.iPaddleNumID) {
                location.href = window.CRM.root + '/Reports/FundRaiserStatement.php?PaddleNumID=' + data.iPaddleNumID;
            }
        });
    }

    /**
     * Event: Select all checkbox
     */
    $('#SelectAll').on('click', function () {
        window.CRM.checkAll = $(this).is(':checked');
        window.CRM.paddleNumListTable.ajax.reload();
    });

    /**
     * Event: Add buyer button
     */
    $('#AddBuyer').on('click', function () {
        createOrEditBuyer(i18next.t('Buyer Number Editor'));
    });

    /**
     * Event: Add donors to buyer list
     */
    $('#AddDonnor').on('click', function () {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'fundraiser/paddlenum/add/donnors',
            data: JSON.stringify({ fundraiserID: window.CRM.fundraiserID })
        }, function (data) {
            if (data.status === 'success') {
                window.CRM.paddleNumListTable.ajax.reload(updateCountBadge);
            }
        });
    });

    /**
     * Event: Edit paddle number (row click)
     */
    $('body').on('click', '.edit-paddle-num', function (event) {
        event.preventDefault();
        var paddleID = $(this).data('id');
        var paddleNum = $(this).data('num');
        var personID = $(this).data('perid');

        createOrEditBuyer(i18next.t('Buyer Number Editor'), true, paddleNum, personID, paddleID);
    });

    /**
     * Event: Generate statements for selected
     */
    $('#GenerateStatements').on('click', function () {
        var selectedIDs = [];
        $('input[name^="Chk"]').each(function () {
            if ($(this).is(':checked')) {
                var paddleNumID = $(this).attr('name').substring(3);
                selectedIDs.push(paddleNumID);
            }
        });

        if (selectedIDs.length === 0) {
            bootbox.alert(i18next.t('Please select at least one buyer.'));
            return;
        }

        // Generate statements for all selected (simple approach: open first and let user handle rest)
        if (selectedIDs.length > 0) {
            // For simplicity, redirect to report with fundraiser ID and let backend filter
            location.href = window.CRM.root + '/Reports/FundRaiserStatement.php?CurrentFundraiser=' + window.CRM.fundraiserID + '&linkBack=v2/fundraiser/editor/' + window.CRM.fundraiserID;
        }
    });

    /**
     * Initialize DataTable
     */
    window.CRM.paddleNumListTable = $('#buyer-listing-table').DataTable({
        ajax: {
            url: window.CRM.root + '/api/fundraiser/paddlenum/list/' + window.CRM.fundraiserID,
            type: 'POST',
            contentType: 'application/json',
            dataSrc: 'PaddleNumItems',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + window.CRM.jwtToken);
            }
        },
        language: {
            url: window.CRM.plugin.dataTable.language.url
        },
        searching: false,
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        dom: 'rtip',
        columns: [
            {
                width: 'auto',
                title: '<input type="checkbox" id="HeaderCheckbox" class="form-check-input">',
                data: 'Id',
                orderable: false,
                className: 'align-middle text-center',
                render: function (data) {
                    return '<input type="checkbox" name="Chk' + data + '" class="form-check-input" ' + (window.CRM.checkAll ? 'checked="checked"' : '') + '>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Number'),
                data: 'Num',
                className: 'align-middle font-weight-semibold',
                render: function (data, type, full) {
                    return '<a href="#" class="edit-paddle-num" data-id="' + full.Id + '" data-num="' + full.Num + '" data-perid="' + full.PerId + '">' + full.Num + '</a>';
                }
            },
            {
                width: 'auto',
                title: i18next.t('Buyer'),
                data: 'BuyerFirstName',
                className: 'align-middle',
                render: function (data, type, full) {
                    return full.BuyerFirstName + ' ' + full.BuyerLastName;
                }
            },
            {
                width: 'auto',
                title: i18next.t('Delete'),
                data: 'Id',
                orderable: false,
                className: 'align-middle text-center',
                render: function (data) {
                    return '<a href="#" class="btn btn-xs btn-outline-danger pnDelete" data-pnid="' + data + '" title="' + i18next.t('Delete') + '"><i class="far fa-trash-alt" aria-hidden="true"></i></a>';
                }
            }
        ],
        responsive: true,
        createdRow: function (row) {
            $(row).addClass('paymentRow');
        },
        drawCallback: function () {
            updateCountBadge();
        }
    });

    updateCountBadge();
});
