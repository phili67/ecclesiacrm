$(document).ready(function () {
    function renderKioskAssignment(data) {

        if(data.Accepted && window.CRM.events.futureEventsLoaded == true){
            var options ='<option value="None">' + i18next.t("None") + '</option>';
            var currentAssignment = data.KioskAssignments[0];
            for (var i=0; i < window.CRM.events.futureEvents.length; i++)
            {
                var event = window.CRM.events.futureEvents[i];
                if (currentAssignment !== undefined && currentAssignment.EventId === event.Id)
                {
                    options += '<option selected value="1-'+event.Id+'">' + i18next.t("Event") + ' - '+event.Title+'</option>';
                }
                else
                {
                    options += '<option value="1-'+event.Id+'">' + i18next.t("Event") + ' - '+event.Title+'</option>';
                }

            }

            return '<select class="assignmentMenu form-control form-control-sm" data-kioskid="'+data.Id+'">'+ options +'</select>';
        }
        else
        {
            return "Kiosk must be accepted";
        }
    }

    $('#isNewKioskRegistrationActive').change(function() {
        if ($("#isNewKioskRegistrationActive").prop('checked')){
            window.CRM.kiosks.enableRegistration().done(function(data) {
                window.CRM.secondsLeft = moment(data.visibleUntil.date).unix() - moment().unix();
                window.CRM.discoverInterval = setInterval(function(){
                    window.CRM.secondsLeft-=1;
                    if (window.CRM.secondsLeft > 0)
                    {
                        $("#isNewKioskRegistrationActive").next(".toggle-group").children(".toggle-on").html("Active for "+window.CRM.secondsLeft+" seconds");
                    }
                    else
                    {
                        clearInterval(window.CRM.discoverInterval);
                        $('#isNewKioskRegistrationActive').bootstrapToggle('off');
                    }

                },1000)
            });
        }

    })

    window.CRM.events.getFutureEventes();

    $(document).on("change",".assignmentMenu",function(event){
        var kioskId = $(event.currentTarget).data("kioskid");
        var selected = $(event.currentTarget).val();
        window.CRM.kiosks.setAssignment(kioskId,selected);
    })

    $(document).ready(function(){
        var kioskTableConfig = {
            ajax: {
                url: window.CRM.root + "/api/kiosks/",
                dataSrc: "KioskDevices"
            },
            columns: [
                {
                    width: 'auto',
                    title: 'Id',
                    data: 'Id',
                    searchable: false
                },
                {
                    width: 'auto',
                    title: i18next.t('Kiosk Name'),
                    data: 'Name',
                },
                {
                    width: 'auto',
                    title: i18next.t('Assignment'),
                    data: function (row,type,set,meta){
                        if (row.KioskAssignments.length > 0)
                        {
                            return row.KioskAssignments[0];
                        }
                        else
                        {
                            return "None";
                        }

                    },
                    render: function (data,type,full,meta)
                    {
                        return renderKioskAssignment(full);
                    }

                },
                {
                    width: 'auto',
                    title: i18next.t('Last Heartbeat'),
                    data: 'LastHeartbeat',
                    render: function (data, type, full, meta) {
                        return moment(full.LastHeartbeat).fromNow();
                    }
                },
                {
                    width: 'auto',
                    title: i18next.t('Accepted'),
                    data: 'Accepted',
                    render: function (data, type, full, meta) {
                        if (full.Accepted)
                        {
                            return i18next.t("True");
                        }
                        else {
                            return i18next.t("False");
                        }

                    }
                },
                {
                    width: 'auto',
                    title: i18next.t('Actions'),
                    data: 'Id',
                    render: function (data, type, full, meta) {
                        buttons = "<button class='btn btn-secondary btn-xs reload reloadKiosk' data-id='" + full.Id + "' >"+ i18next.t("Reload") +"</button>" +
                            " <button class='btn btn-secondary btn-xs identify identifyKiosk' data-id='" + full.Id + "' >" + i18next.t("Identify") + "</button>";
                        if(!full.Accepted){
                            buttons += " <button class='btn btn-primary btn-xs accept acceptKiosk' data-id='" + full.Id + "' >" + i18next.t("Accept") + "</button>";
                        }
                        buttons += " <button class='btn btn-danger accept btn-xs deleteKiosk' data-id='" + full.Id + "' >" + i18next.t("Delete") + "</button>";
                        return buttons;
                    }
                }
            ]
        };

        $.extend(kioskTableConfig,window.CRM.plugin.dataTable);

        window.CRM.kioskDataTable = $("#KioskTable").DataTable(kioskTableConfig);

        $('body').on('click','.reloadKiosk', function(){
            var id = $(this).data('id');
            window.CRM.kiosks.reload(id);
        });

        $('body').on('click','.identifyKiosk', function(){
            var id = $(this).data('id');
            window.CRM.kiosks.identify(id);
        });

        $('body').on('click','.acceptKiosk', function(){
            var id = $(this).data('id');
            window.CRM.kiosks.accept(id);
        });

        $('body').on('click','.deleteKiosk', function(){
            var id = $(this).data('id');
            window.CRM.kiosks.delete(id);
        });

        setInterval(function(){window.CRM.kioskDataTable.ajax.reload()},5000);
    })
});
