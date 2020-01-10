i18nextOpt = {
  lng:window.CRM.shortLocale,
  nsSeparator: false,
  keySeparator: false,
  pluralSeparator:false,
  contextSeparator:false,
  fallbackLng: false,
  resources: { }
};

i18nextOpt.resources[window.CRM.shortLocale] = {
  translation: window.CRM.i18keys
};

i18next.init(i18nextOpt);

$("document").ready(function(){
    // all bootbox are now localized
    bootbox.setDefaults({locale: window.CRM.lang});

    $(".multiSearch").select2({
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        ajax: {
            url: function (params){
              return window.CRM.root + "/api/search/" + params.term;
            },
            dataType: 'json',
            delay: 250,
            data: "",
            processResults: function (data, params) {
              return {results: data};
            },
            cache: true
        }
    });
    $(".multiSearch").on("select2:select",function (e) { window.location.href= e.params.data.uri;});

    $(".date-picker").datepicker({format:window.CRM.datePickerformat, language: window.CRM.lang});

    $(".maxUploadSize").text(window.CRM.maxUploadSize);

    /* IMPORTANT : be careful
       You have to be careful with this part of code !!!!!
       this part of code will work in two different js code : PersonView.js and GroupList.js */
    $(document).on("click", ".emptyCart", function (e) {
        window.CRM.cart.emptyCart();
    });

    $(document).on("click", "#emptyCartToEvent", function (e) {
      window.CRM.cart.emptyCartToEvent();
    });

    $(document).on("click", "#emptyCartToGroup", function (e) {
        window.CRM.cart.emptyCartToGroup();
    });

    $(document).on("click", "#registerSoftware", function (e) {
        window.CRM.register()
    });

    // we manage now the dashboard and synchronize system
    window.CRM.DashboardRefreshTimer=setInterval(window.CRM.synchronize.refresh, window.CRM.iDashboardPageServiceIntervalTime * 1000);
    window.CRM.synchronize.refresh();

    // refresh the cart icon
    window.CRM.cart.refresh();

    // run all the jobs
    window.CRM.system.runTimerJobs();
});
