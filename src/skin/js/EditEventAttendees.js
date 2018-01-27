$(document).ready(function () {

  $("#DeleleAllAttendees").on("click",function(e) {
    var eventID = $(this).data("eventid");
    
    bootbox.confirm({
     title: i18next.t("Attention"),
     message:i18next.t('Are you sure you want to DELETE all persons from Event ID:')+eventID, 
     callback:function(result) {
      if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/deleteAll',
            data: JSON.stringify({"eventID":eventID})
            }).done(function(data) {
              window.location = window.location.href;
            });
        }
      }
    });
  });
  
  $(".DeleleAttendees").on("click",function(e) {
    var eventID = $(this).data("eventid");
    var personID = $(this).data("personid");
    
    bootbox.confirm({
     title: i18next.t("Attention"),
     message:i18next.t('Are you sure you want to DELETE this person from Event ID:')+eventID, 
     callback:function(result) {
      if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'attendees/delete',
            data: JSON.stringify({"eventID":eventID,"personID":personID})
            }).done(function(data) {
              window.location = window.location.href;
            });
        }
      }
    });
  });

  $(".personSearch").select2({
    minimumInputLength: 2,
    language: window.CRM.shortLocale,
    ajax: {
      url: function (params) {
        return window.CRM.root + "/api/persons/search/" + params.term;
      },
      dataType: 'json',
      delay: 250,
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

  $(".personSearch").on("select2:select", function (e) {
      
          window.CRM.APIRequest({
            method: 'POST',
            path: 'events/person',
            data: JSON.stringify({"EventID":window.CRM.currentEvent,"PersonId":e.params.data.objid})            
          }).done(function(data) {          
            $(".personSearch").val(null).trigger('change');
            //window.CRM.DataTableEventView.ajax.reload();
            window.location = window.location.href;
          });
  });
  
  
  $(".groupSearch").select2({
    minimumInputLength: 2,
    language: window.CRM.shortLocale,
    ajax: {
      url: function (params) {
        return window.CRM.root + "/api/groups/search/" + params.term;
      },
      dataType: 'json',
      delay: 250,
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

  $(".groupSearch").on("select2:select", function (e) {      
      window.CRM.APIRequest({
        method: 'POST',
        path: 'events/group',
        data: JSON.stringify({"EventID":window.CRM.currentEvent,"GroupID":e.params.data.objid})            
      }).done(function(data) {          
        $(".personSearch").val(null).trigger('change');
        //window.CRM.DataTableEventView.ajax.reload();
        window.location = window.location.href;
      });
  });
  
});