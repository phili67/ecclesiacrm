$(function() {
    var sidebar_colors = [
        'bg-blue',
        'bg-secondary',
        'bg-green',
        'bg-cyan',
        'bg-yellow',
        'bg-red',
        'bg-fuchsia',
        'bg-blue',
        'bg-yellow',
        'bg-indigo',
        'bg-navy',
        'bg-purple',
        'bg-pink',
        'bg-maroon',
        'bg-orange',
        'bg-lime',
        'bg-teal',
        'bg-olive',
        'bg-black',
        'bg-gray-dark',
        'bg-gray',
        'bg-light'
    ]

    var accent_colors = [
        'accent-blue',
        'accent-secondary',
        'accent-green',
        'accent-cyan',
        'accent-yellow',
        'accent-red',
        'accent-fuchsia',
        'accent-blue',
        'accent-yellow',
        'accent-indigo',
        'accent-navy',
        'accent-purple',
        'accent-pink',
        'accent-maroon',
        'accent-orange',
        'accent-lime',
        'accent-teal',
        'accent-olive',
        'accent-black',
        'accent-gray-dark',
        'accent-gray',
        'accent-light'
    ]

    var navbar_colors = [
        'navbar-blue',
        'navbar-secondary',
        'navbar-green',
        'navbar-cyan',
        'navbar-yellow',
        'navbar-red',
        'navbar-fuchsia',
        'navbar-blue',
        'navbar-yellow',
        'navbar-indigo',
        'navbar-navy',
        'navbar-purple',
        'navbar-pink',
        'navbar-maroon',
        'navbar-orange',
        'navbar-lime',
        'navbar-teal',
        'navbar-olive',
        'navbar-black',
        'navbar-gray-dark',
        'navbar-gray',
        'navbar-light'
    ]

    var sidebar_skins = [
        'sidebar-dark-blue',
        'sidebar-dark-secondary',
        'sidebar-dark-green',
        'sidebar-dark-cyan',
        'sidebar-dark-yellow',
        'sidebar-dark-red',
        'sidebar-dark-fuchsia',
        'sidebar-dark-blue',
        'sidebar-dark-yellow',
        'sidebar-dark-indigo',
        'sidebar-dark-navy',
        'sidebar-dark-purple',
        'sidebar-dark-pink',
        'sidebar-dark-maroon',
        'sidebar-dark-orange',
        'sidebar-dark-lime',
        'sidebar-dark-teal',
        'sidebar-dark-olive',
        'sidebar-dark-black',
        'sidebar-dark-gray-dark',
        'sidebar-dark-gray',
        'sidebar-dark-light',
        'sidebar-light-blue',
        'sidebar-light-secondary',
        'sidebar-light-green',
        'sidebar-light-cyan',
        'sidebar-light-yellow',
        'sidebar-light-red',
        'sidebar-light-fuchsia',
        'sidebar-light-blue',
        'sidebar-light-yellow',
        'sidebar-light-indigo',
        'sidebar-light-navy',
        'sidebar-light-purple',
        'sidebar-light-pink',
        'sidebar-light-maroon',
        'sidebar-light-orange',
        'sidebar-light-lime',
        'sidebar-light-teal',
        'sidebar-light-olive',
        'sidebar-light-black',
        'sidebar-light-gray-dark',
        'sidebar-light-gray',
        'sidebar-light-light'
    ];

    var $skinCol = $('#usersettings-skin-col');
    var $skinRow = $('#usersettings-skin-row');
    var validPanes = {
        '#usersettings-pane-profile': true,
        '#usersettings-pane-skin': true,
        '#usersettings-pane-specific': true,
        '#usersettings-pane-2fa': true
    };

    if ($skinCol.length && $skinRow.length) {
        $skinCol.removeClass('col-md-6').addClass('col-md-12');
        $skinRow.append($skinCol);
    }

    if (window.location.hash && validPanes[window.location.hash]) {
        $('#usersettings-tabs a[href="' + window.location.hash + '"]').tab('show');
    }

    $('#usersettings-tabs a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        var targetPane = $(e.target).attr('href');

        if (validPanes[targetPane] && window.history && window.history.replaceState) {
            window.history.replaceState(null, '', targetPane);
        }
    });

    $(".data-table").DataTable({
        "language": {
            "url": window.CRM.plugin.dataTable.language.url
        },
        pageLength: 100,
        info: false,
        bSort: false,
        searching: false, paging: false,
        responsive: true
    });

    $('.sStyleSideBar').on('change',function () {
        mode = $(this).val();   
        
        window.CRM.darkMode(mode);        
    });

    $('.sStyleFontSize').on('change',function () {
        if ($(this).val() == "Small") {
            $('.sidebar-mini').addClass('text-sm')
        } else {
            $('.sidebar-mini').removeClass('text-sm')
        }
    });

    $(".bSidebarCollapse").on('change',function () {
        $('[data-widget="pushmenu"]').PushMenu('toggle');
    });


    $(".sStyleBrandLinkColor").on('change',function () {
        var color = $(this).val();
        var sidebar_class = 'navbar-' + color
        var sidebar = $('.brand-link')
        navbar_colors.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)
    });

    $(".sDarkMode").on('change',function () {
        var color = $('.sStyleSideBarColor').val();
        var mode = $(this).val() == "dark" ? 'dark' : 'light';
        var sidebar = $('.main-sidebar');
        var sidebar_class = 'sidebar-' + mode + '-' + color;
        sidebar_skins.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)

        if (mode == "dark") {
            $('.sidebar-mini').addClass('dark-mode');
            $('.table-dropdown-menu').addClass('dark-mode');

        } else if (mode == "light") {
            $('.sidebar-mini').removeClass('dark-mode');
            $('.table-dropdown-menu').removeClass('dark-mode');
        } else {
            let matched = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if(matched) {
                $('.sidebar-mini').addClass('dark-mode');
                $('.table-dropdown-menu').addClass('dark-mode');
            } else {
                $('.sidebar-mini').removeClass('dark-mode');
                $('.table-dropdown-menu').removeClass('dark-mode');
            }
        }
    });

    $(".sStyleNavBarColor").on('change',function () {
        var color = $(this).val();
        var sidebar_class = 'navbar-' + color
        var sidebar = $('.main-header')
        navbar_colors.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)
    });

    $(".sStyleSideBarColor").on('change',function () {
        var color = $(this).val();
        var mode = window.CRM.bDarkMode?'dark':'light';
        var sidebar_class = 'sidebar-' + mode + '-' + color
        var sidebar = $('.main-sidebar')
        sidebar_skins.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)
    });


    $(".Twofa-activation").on("click",function(event) {
        $("#TwoFAEnrollmentSteps").html("");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'settingsindividual/get2FA'
        },function (data) {
        var res = `<div class="row mb-3">
                            <div class="col-md-12 d-flex align-items-center">
                                <span class="badge badge-primary mr-2 px-2 py-1">1</span>
                                <label class="mb-0 font-weight-bold">${i18next.t("2 Factor Authentication Secret")}</label>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-12">
                                <img src="${data.img}" class="img-thumbnail mb-3"><br>
                            </div>
                            <div class="col-md-12 mb-3">
                                <button class="btn btn-sm btn-outline-danger remove-2fa">
                                    <i class="fas fa-times mr-1"></i>${i18next.t("Remove 2 Factor Authentication Secret")}
                                </button>
                            </div>
                        </div>
                <hr/>
                <div class="row mb-3">
                    <div class="col-md-12 d-flex align-items-center">
                        <span class="badge badge-primary mr-2 px-2 py-1">2</span>
                        <label class="mb-0 font-weight-bold">${i18next.t("Enter TOTP code to confirm enrollment")} :
                        <input value="" id="inputCode" class="form-control form-control-sm d-inline-block ml-2" style="width:120px" placeholder="000000" />
                        <span id="verifyCode" class="ml-2"></span></label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label id="rescuepasswords"></label>
                    </div>
                </div>`;

            $("#TwoFAEnrollmentSteps").html(res);
        });
    });

    $(document).on("input","#inputCode",function(){
        var code = $(this).val();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'settingsindividual/verify2FA',
            data: JSON.stringify({"code": code})
        },function (data) {
            if (data.status == 'yes') {
                $("#verifyCode").html('<i class="fas fa-check text-success" style="font-size:18px"></i>');

                message = `<div class="row">
                                <div class="col-md-12">
                                    <div class="card card-outline card-success shadow-sm">
                                        <div class="card-header border-0">
                                            <h5 class="card-title"><i class="fas fa-key mr-2"></i>${i18next.t("Keep these backup passwords in a safe place, in case you lose the OTP credentials.")}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-monospace">${data.rescue_passwords}</p>
                                        </div>
                                    </div>
                                </div>
                        </div>`;
                
                $("#two-factor-results").html(message);
            } else {
                $("#verifyCode").html('<i class="fas fa-ban text-danger" style="font-size:18px"></i>');
                $("#two-factor-results").html("");
            }

        });
    });

    $(document).on("click",".remove-2fa",function(){
        window.CRM.APIRequest({
            method: 'POST',
            path: 'settingsindividual/remove2FA',
        },function (data) {
            if (data.status == 'yes') {
                location.reload();
            }
        });
    });

    if (window.CRM.twofa) {
        $("#TwoFaBox").focus();
    }

});
