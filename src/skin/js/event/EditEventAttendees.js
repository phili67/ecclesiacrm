$(function() {

  $("#DeleleAllAttendees").on("click",function(e) {
    var eventID = $(this).data("eventid");

    bootbox.confirm({
     title: i18next.t("Attention"),
     message:i18next.t('Are you sure you want to DELETE all persons from Event ID:')+eventID,
     buttons: {
        'cancel': {
            label: i18next.t('Cancel'),
            className: 'btn-default'
        },
        'confirm': {
            label: i18next.t('OK'),
            className: 'btn-danger'
        }
    },
     callback:function(result) {
      if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/deleteAll',
            data: JSON.stringify({"eventID":eventID})
            },function(data) {
              window.location = window.location.href;
            });
        }
      }
    });
  });

  $(".DeleleAttendees").on("click",function(e) {
    var eventID = $(this).data("eventid");
    var personID = $(this).data("personid");
    var row = window.CRM.DataTableEventView.row( $(this).parents('tr') );
    var rowNode = row.node();

    bootbox.confirm({
     title: i18next.t("Attention"),
     message:i18next.t('Are you sure you want to DELETE this person from Event ID:')+eventID,
     buttons: {
        'cancel': {
            label: i18next.t('Cancel'),
            className: 'btn-default'
        },
        'confirm': {
            label: i18next.t('OK'),
            className: 'btn-danger'
        }
    },
    callback:function(result) {
      if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/delete',
            data: JSON.stringify({"eventID":eventID,"personID":personID})
            },function(data) {
                row.remove().draw();
            });
        }
      }
    });
  });

  $("#personGroupSearch").select2({
    minimumInputLength: 2,
    language: window.CRM.shortLocale,
    placeholder: " -- "+i18next.t("Person or Family or Group")+" -- ",
    allowClear: true, // This is for clear get the clear button if wanted
    ajax: {
      url: function (params) {
        return window.CRM.root + "/api/people/search/" + params.term;
      },
      dataType: 'json',
      delay: 250,
      headers: {
        "Authorization" : "Bearer "+window.CRM.jwtToken
      },
      data: function (params) {
        return {
          q: params.term, // search term
          page: params.page
        };
      },
      processResults: function (rdata, page) {
        return {results: rdata};
      },
      cache: true
    }
  });

  $("#personGroupSearch").on("select2:select", function (e) {

      if (e.params.data.personID !== undefined) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'events/person',
            data: JSON.stringify({"EventID":window.CRM.currentEvent,"PersonId":e.params.data.personID})
          },function(data) {
            $(".personSearch").val(null).trigger('change');
            //window.CRM.DataTableEventView.ajax.reload();
            window.location = window.location.href;
          });
      } else if (e.params.data.groupID !== undefined) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'events/group',
            data: JSON.stringify({"EventID":window.CRM.currentEvent,"GroupID":e.params.data.groupID})
          },function(data) {
            $(".personSearch").val(null).trigger('change');
            //window.CRM.DataTableEventView.ajax.reload();
            window.location = window.location.href;
          });
      } else if (e.params.data.familyID !== undefined) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'events/family',
            data: JSON.stringify({"EventID":window.CRM.currentEvent,"FamilyID":e.params.data.familyID})
          },function(data) {
            $(".personSearch").val(null).trigger('change');
            //window.CRM.DataTableEventView.ajax.reload();
            window.location = window.location.href;
          });
      }
  });
});
