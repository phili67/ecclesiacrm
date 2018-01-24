$(document).ready(function () {

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
  
});