$(function() {

    var fmt = window.CRM.datePickerformat.toUpperCase();

    window.CRM.fmt = window.CRM.datePickerformat.toUpperCase();

    $.fn.dataTable.moment( fmt );

    window.CRM.neverDate = moment('1900-01-01 00:00').format( window.CRM.fmt );

    function setBusyState($element, isBusy, busyHtml, defaultHtml) {
        if (!$element || $element.length === 0) {
            return;
        }

        if (isBusy) {
            if ($element.is('button')) {
                $element.data('default-html', $element.html());
            }
            $element.prop('disabled', true).addClass('disabled');
            if (busyHtml) {
                $element.html(busyHtml);
            }
            return;
        }

        $element.prop('disabled', false).removeClass('disabled');

        if ($element.is('button')) {
            $element.html(defaultHtml || $element.data('default-html') || $element.html());
        }
    }

    function showActionError(message) {
        bootbox.alert({
            title: '<i class="fas fa-exclamation-triangle text-warning mr-2"></i>' + i18next.t('Pastoral Care'),
            message: '<div class="alert alert-light border mb-0">' + message + '</div>'
        });
    }

    function enhanceTableRows(tableSelector) {
        var $table = $(tableSelector);
        $table.find('tbody tr').attr('title', i18next.t('Open the pastoral care record'));
    }

    function appendTabBadge(tabSelector, count) {
        var $tab = $(tabSelector);
        $tab.find('.pc-tab-count').remove();
        $tab.append(' <span class="badge badge-light pc-tab-count">' + count + '</span>');
    }

    function buildTableOptions(customOptions) {
        return $.extend(true, {
            bSort : true,
            pageLength: 5,
            dom: 'rtip',
            processing: true,
            language: {
                url: window.CRM.plugin.dataTable.language.url,
                emptyTable: i18next.t('No records found for this view.'),
                zeroRecords: i18next.t('No matching records found.'),
                processing: i18next.t('Loading...')
            },
            responsive: true,
            createdRow : function (row) {
                $(row).addClass('menuLinksRow');
            },
            drawCallback: function () {
                enhanceTableRows('#' + this.api().table().node().id);
            }
        }, customOptions);
    }

    function initNeverContactedTable(selector, ajaxUrl, dataSrc, columns, tabSelector) {
        var table = $(selector).DataTable(buildTableOptions({
            ajax: {
                url: window.CRM.root + ajaxUrl,
                type: 'POST',
                contentType: 'application/json',
                dataSrc: dataSrc,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' +  window.CRM.jwtToken);
                }
            },
            columns: columns,
            drawCallback: function () {
                var api = this.api();
                appendTabBadge(tabSelector, api.data().count());
                enhanceTableRows(selector);
            }
        }));

        return table;
    }

    function toPhoneLink(value) {
        if (value === null || value === undefined) {
            return '';
        }

        var display = String(value).trim();
        if (display === '') {
            return '';
        }

        var telValue = display.replace(/\s+/g, '');
        return `<a href="tel:${telValue}" class="d-inline-flex align-items-center">
            <span class="badge badge-light border mr-2"><i class="fas fa-phone-alt text-success"></i></span>
            <span>${display}</span>
            </a>`;
    }

    function buildIdentityLink(options) {
        var image = options.image;
        var href = options.href;
        var primary = options.primary || '';
        var secondary = options.secondary || '';
        var fallbackIcon = options.fallbackIcon || 'fa-user';

        return `<div class="d-flex align-items-center">${image}
            <div class="ml-2">
            <div class="font-weight-bold"><a href="${href}">${primary}</a></div>
                ${secondary !== '' ? `<div class="small text-muted"><i class="fas ${fallbackIcon} mr-1"></i>${secondary}</div>` : ''}
            </div>
            </div>`;
    }

    function buildThumbnail(full, id, type) {
        if (window.CRM.bThumbnailIconPresence) {
            return `<img src="${window.CRM.root}/api/${type}/${id}/thumbnail" alt="User Image" class="user-image initials-image-24">`;
        }

        var fallback = type === 'families' ? 'Family.png' : 'Person.png';
        return `<img src="${window.CRM.root}/Images/${fallback}" class="initials-image direct-chat-img-24">`;
    }

    function getBestPhone(full) {
        return full.CellPhone || full.cellPhone || full.HomePhone || full.homePhone || full.WorkPhone || full.workPhone || '';
    }

    function buildAddress(full) {
        var explicit = full.Address || full.address || '';
        if (explicit && String(explicit).trim() !== '') {
            return String(explicit).trim();
        }

        var parts = [
            full.Address1 || full.address1 || '',
            full.Address2 || full.address2 || '',
            full.City || full.city || '',
            full.State || full.state || '',
            full.Zip || full.zip || '',
            full.Country || full.country || ''
        ];

        return parts.join(' ').replace(/\s+/g, ' ').trim();
    }

    function toAddressLink(full) {
        var address = buildAddress(full);
        if (address === '') {
            return '';
        }
        return `<div class="d-inline-flex align-items-start">
            <span class="badge badge-light border mr-2"><i class="fas fa-map-marker-alt text-danger"></i></span>
            <span>${window.CRM.tools.getLinkMapFromAddress(address)}</span>
            </div>`;
    }

    function formatCareDate(data) {
        if (data === null || data === undefined) {
            return `<span class="text-muted"><i class="fas fa-ban mr-1"></i>${i18next.t("Never contacted")}</span>`;
        }
        var date = moment(data).format(fmt);
        if (date === window.CRM.neverDate) {
            return `<span class="badge badge-light border text-muted"><i class="fas fa-ban mr-1"></i>${i18next.t("Never contacted")}</span>`;
        }
        return `<span class="badge badge-light border text-dark"><i class="far fa-calendar-check mr-1 text-success"></i>${date}</span>`;
    }

    columnsPastoralCareMembers = [
        {
            width: 'auto',
            title:i18next.t("Name"),
            data:'LastName',
            render: function(data, type, full, meta) {
                    return buildIdentityLink({
                        image: buildThumbnail(full, full.PersonID, 'persons'),
                        href: window.CRM.root + "/v2/people/person/view/" + full.PersonID,
                        primary: data,
                        secondary: i18next.t('Pastoral care member'),
                        fallbackIcon: 'fa-user'
                    });
            }
        },
        {
            width: 'auto',
            title:i18next.t("First Name"),
            data:'FirstName',
            render: function(data, type, full, meta) {
                return `<a href="${window.CRM.root}/v2/people/person/view/${full.PersonID}">${data}</a>`;
            }
        }
    ];

    if (window.CRM.bPastoralcareStats) {
        columnsPastoralCareMembers.push(
            {
                width: 'auto',
                title:i18next.t("Visits/calls"),
                data:'Visits',
                render: function(data, type, full, meta) {
                    return `<a href="${window.CRM.root}/v2/pastoralcare/listforuser/${full.UserID}">${data}</a>`;
                }
            }
        )
    }

    window.CRM.dataPastoralcareMembers = $("#pastoralcareMembers").DataTable(buildTableOptions({
        ajax:{
            url: window.CRM.root + "/api/pastoralcare/members",
            type: 'POST',
            contentType: "application/json",
            dataSrc: "Pastors",
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Authorization', "Bearer " +  window.CRM.jwtToken);
            }
        },
        pageLength: 4,
        columns: columnsPastoralCareMembers
    }));

    window.CRM.personNeverBeenContacted = initNeverContactedTable(
        "#personNeverBeenContacted",
        "/api/pastoralcare/personNeverBeenContacted",
        "PersonNeverBeenContacted",
        [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'LastName',
                render: function(data, type, full, meta) {
                    return buildIdentityLink({
                        image: buildThumbnail(full, full.Id, 'persons'),
                        href: window.CRM.root + "/v2/pastoralcare/person/" + full.Id,
                        primary: data,
                        secondary: i18next.t('Person'),
                        fallbackIcon: 'fa-user'
                    });
                }
            },
            {
                width: 'auto',
                title:i18next.t("First Name"),
                data:'FirstName',
                render: function(data, type, full, meta) {
                    return `<a href="${window.CRM.root}/v2/pastoralcare/person/${full.Id}">${data}</a>`;
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        '#tab-persons'
    );

    window.CRM.familyNeverBeenContacted = initNeverContactedTable(
        "#familyNeverBeenContacted",
        "/api/pastoralcare/familyNeverBeenContacted",
        "FamilyNeverBeenContacted",
        [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'Name',
                render: function(data, type, full, meta) {
                    return buildIdentityLink({
                        image: buildThumbnail(full, full.Id, 'families'),
                        href: window.CRM.root + "/v2/pastoralcare/family/" + full.Id,
                        primary: data,
                        secondary: i18next.t('Family'),
                        fallbackIcon: 'fa-home'
                    });
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        '#tab-families'
    );

    window.CRM.singleNeverBeenContacted = initNeverContactedTable(
        "#singleNeverBeenContacted",
        "/api/pastoralcare/singleNeverBeenContacted",
        "SingleNeverBeenContacted",
        [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'Name',
                render: function(data, type, full, meta) {
                    let res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += '<img src="' + window.CRM.root + '/api/persons/' + full.PersonID + '/thumbnail" alt="User Image" class="user-image initials-image-24"> ';
                    } else {
                        res += '<img src="' + window.CRM.root + '/Images/Person.png" class="initials-image direct-chat-img-24"> ';
                    }
                    return `${res}<a href="${window.CRM.root}/v2/pastoralcare/person/${full.PersonID}">${data}</a>`;
                }
            },
            {
                width: 'auto',
                title:i18next.t("First Name"),
                data:'FirstName',
                render: function(data, type, full, meta) {
                    return `<a href="${window.CRM.root}/v2/pastoralcare/person/${full.PersonID}">${data}</a>`;
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        '#tab-singles'
    );

    window.CRM.retiredNeverBeenContacted = initNeverContactedTable(
        "#retiredNeverBeenContacted",
        "/api/pastoralcare/retiredNeverBeenContacted",
        "RetiredNeverBeenContacted",
        [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'LastName',
                render: function(data, type, full, meta) {
                    let res = '';
                    if (window.CRM.bThumbnailIconPresence) {
                        res += `<img src="${window.CRM.root}/api/persons/${full.Id}/thumbnail" alt="User Image" class="user-image initials-image-24"> `;
                    } else {
                        res += `<img src="${window.CRM.root}/Images/Person.png" class="initials-image direct-chat-img-24"> `;
                    }
                    return `${res}<a href="${window.CRM.root}/v2/pastoralcare/person/${full.Id}">${data}</a>`;
                }
            },
            {
                width: 'auto',
                title:i18next.t("First Name"),
                data:'FirstName',
                render: function(data, type, full, meta) {
                    return `<a href="${window.CRM.root}/v2/pastoralcare/person/${full.Id}">${data}</a>`;
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        '#tab-retired'
    );

    window.CRM.youngNeverBeenContacted = initNeverContactedTable(
        "#youngNeverBeenContacted",
        "/api/pastoralcare/youngNeverBeenContacted",
        "YoungNeverBeenContacted",
        [
            {
                width: 'auto',
                title:i18next.t("Name"),
                data:'LastName',
                render: function(data, type, full, meta) {
                    return buildIdentityLink({
                        image: buildThumbnail(full, full.Id, 'persons'),
                        href: window.CRM.root + "/v2/pastoralcare/person/" + full.Id,
                        primary: data,
                        secondary: i18next.t('Person'),
                        fallbackIcon: 'fa-user'
                    });
                }
            },
            {
                width: 'auto',
                title:i18next.t("First Name"),
                data:'FirstName',
                render: function(data, type, full, meta) {
                    return `<a href="${window.CRM.root}/v2/pastoralcare/person/${full.Id}">${data}</a>`;
                }
            },
            {
                width: 'auto',
                title:i18next.t("Phone"),
                data:null,
                render: function(data, type, full, meta) {
                    return toPhoneLink(getBestPhone(full));
                }
            },
            {
                width: 'auto',
                title:i18next.t("Address"),
                data:null,
                render: function(data, type, full, meta) {
                    return toAddressLink(full);
                }
            },
            {
                width: 'auto',
                title: i18next.t("Last visit/call"),
                data: 'PastoralCareLastDate',
                render: function (data, type, full, meta) {
                    return formatCareDate(data);
                }
            }
        ],
        '#tab-young'
    );

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
    });

    $('#add-event').on('click', function (e) {
        e.preventDefault();
        var fmt = 'YYYY-MM-DD HH:mm:ss';
        var $button = $(this);

        var dateStart = moment().format(fmt);
        var dateEnd = moment().format(fmt);

        setBusyState($button, true, '<i class="fas fa-spinner fa-spin mr-1"></i>' + i18next.t('Opening...'));
        addEvent(dateStart,dateEnd,i18next.t("Appointment"),sPageTitle);
        window.setTimeout(function () {
            setBusyState($button, false, null, '<i class="far fa-calendar-plus mr-1"></i>' + i18next.t('Appointment'));
        }, 600);
    });

    $( ".newPastorCare" ).on('click',function(e) {
        e.preventDefault();
        e.stopPropagation();

        var typeID   = $(this).data('typeid');
        var $trigger = $(this);
        var isButton = $trigger.is('button');
        var defaultHtml = isButton ? $trigger.html() : null;

        if ($trigger.data('busy') === true) {
            return;
        }

        $trigger.data('busy', true);
        if (isButton) {
            setBusyState($trigger, true, '<i class="fas fa-spinner fa-spin mr-1"></i>' + i18next.t('Selecting...'));
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'pastoralcare/createRandomly',
            data: JSON.stringify({"typeID":typeID})
        },function(data) {
            $trigger.data('busy', false);
            if (isButton) {
                setBusyState($trigger, false, null, defaultHtml);
            }

            if (data.status == "success") {
                switch (typeID) {
                    case 1:case 3:case 4:case 5:// person, old person or Young or single
                        location.href = window.CRM.root + '/v2/pastoralcare/person/' + data.personID;
                        break;
                    default:// a family
                        location.href = window.CRM.root + '/v2/pastoralcare/family/' + data.familyID;
                }
            } else {
                showActionError(i18next.t('No matching pastoral care record could be prepared right now. Please try another category.'));
            }
        }, function () {
            $trigger.data('busy', false);
            if (isButton) {
                setBusyState($trigger, false, null, defaultHtml);
            }
            showActionError(i18next.t('Unable to start a pastoral care action at the moment.'));
        });
    });
});
