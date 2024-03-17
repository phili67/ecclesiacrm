var nss = ['translation'];

if ( window.CRM.all_plugins_i18keys !== false) {
    for (var ns in window.CRM.all_plugins_i18keys) {
        nss.push(ns);
    }
}

i18nextOpt = {
    lng: window.CRM.shortLocale,
    nsSeparator: false,
    keySeparator: false,
    pluralSeparator: false,
    contextSeparator: false,
    fallbackLng: false,
    ns: nss,
    defaultNS: 'translation',
    resources: {}
};

i18next.init(i18nextOpt);

i18next.addResourceBundle(window.CRM.shortLocale, 'translation', window.CRM.i18keys);

for (var ns in window.CRM.all_plugins_i18keys) {
    var new_ns_translation =  eval(window.CRM.all_plugins_i18keys[ns]);
    i18next.addResourceBundle(window.CRM.shortLocale, ns, new_ns_translation);
}

$(function() {
    // all bootbox are now localized
    bootbox.setDefaults({locale: window.CRM.lang});

    $(".multiSearch").select2({
        language: window.CRM.shortLocale,
        minimumInputLength: 2,
        ajax: {
            url: function (params) {
                return window.CRM.root + "/api/search/" + params.term;
            },
            dataType: 'json',
            delay: 250,
            data: "",
            headers: {
                "Authorization" : "Bearer "+window.CRM.jwtToken
            },
            processResults: function (data, params) {
                return {results: data};
            },
            cache: true
        }
    });
    $(".multiSearch").on("select2:select", function (e) {
        window.location.href = e.params.data.uri;
    });

    $(".date-picker").datepicker({format: window.CRM.datePickerformat, language: window.CRM.lang});

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
    window.CRM.DashboardRefreshTimer = setInterval(window.CRM.synchronize.refresh, window.CRM.iDashboardPageServiceIntervalTime * 1000);
    window.CRM.synchronize.refresh();

    // refresh the cart icon
    window.CRM.cart.refresh();

    // run all the jobs
    window.CRM.system.runTimerJobs();

    // when the window if focused
    window.onfocus = function () {
        window.CRM.synchronize.refresh();
    }

    $(document).on("click", "#deleteCart", function (e) {
        window.CRM.cart.delete(function (data) {
            var global_path = window.location.pathname;
            pathPersonView = global_path.includes("v2/people/person/view/");                                 

            if (data.status == "failure") {
                var box = window.CRM.DisplayAlert(i18next.t("Error text"), data.message);

                setTimeout(function () {
                    // be careful not to call box.hide() here, which will invoke jQuery's hide method
                    box.modal('hide');

                    if ((pathPersonView || global_path == "/v2/cart/view") && data != 'nothing was done') {
                        location.reload();
                    }
                }, 7000);
            } else if ((pathPersonView || global_path == "/v2/cart/view") && data != 'nothing was done') {
                location.reload();
            }
        });
    });

    $(document).on("click", "#deactivateCart", function (e) {
        window.CRM.cart.deactivate(function (data) {
            var global_path = window.location.pathname;
            pathPersonView = global_path.includes("v2/people/person/view/");
            path = global_path.substring(global_path.lastIndexOf("/") + 1);            
            path = path.split("?")[0].split("#")[0];            

            if (data.status == "failure") {
                var box = window.CRM.DisplayAlert(i18next.t("Error text"), data.message);

                setTimeout(function () {
                    // be careful not to call box.hide() here, which will invoke jQuery's hide method
                    box.modal('hide');

                    if ((pathPersonView || global_path == "/v2/cart/view") && data != 'nothing was done') {
                        location.reload();
                    }
                }, 7000);
            } else if ((pathPersonView || global_path == "/v2/cart/view") && data != 'nothing was done') {
                location.reload();
            }
        });
    });

    window.matchMedia('(prefers-color-scheme: dark)').addListener(function (e) {
        if (window.CRM.sLightDarkMode == "automatic") {
            if (e.matches) {// we're on dark mode
                $('.sidebar-mini').addClass('dark-mode');
                $('.table-dropdown-menu').addClass('dark-mode');

                window.CRM.bDarkMode = true;
            } else {// we're in light mode
                $('.sidebar-mini').removeClass('dark-mode');
                $('.table-dropdown-menu').removeClass('dark-mode');
                window.CRM.bDarkMode = false;
            }

            if (typeof window.CRM.AutomaticDarkModeFunction === 'function') {
                window.CRM.AutomaticDarkModeFunction(e.matches);
            }
        }
    });

    // for the profile menu
    if (window.CRM.sLightDarkMode == "automatic") {
        let matched = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if(matched) {// we're on dark mode
            $('.table-dropdown-menu').addClass('dark-mode');
            window.CRM.bDarkMode = true;
        } else {// we're in light mode
            $('.table-dropdown-menu').removeClass('dark-mode');
            window.CRM.bDarkMode = false;
        }
    }

    /*
      * Hacky fix for a bug in select2 with jQuery 3.6.0's new nested-focus "protection"
      * see: https://github.com/select2/select2/issues/5993
      * see: https://github.com/jquery/jquery/issues/4382
      *
      * TODO: Recheck with the select2 GH issue and remove once this is fixed on their side
      */

    $(document).on('select2:open', () => {
        let allFound = document.querySelectorAll('.select2-container--open .select2-search__field');
        allFound[allFound.length - 1].focus();
    });

    $('.exit-control-account').on('click', function () {
        var userId = $(this).data("userid");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'users/exitControlAccount',
            data: JSON.stringify({"userID": userId})
        },function (data) {
            if (data.success) {
                window.location = window.CRM.root + "/v2/users/";
            }
        });
    });
});
