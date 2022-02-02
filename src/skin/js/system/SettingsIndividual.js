$(document).ready(function () {
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
    ]

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

    $('.sStyleSideBar').change(function () {
        var color = $('.sStyleSideBarColor').val();
        mode = $(this).val();
        var sidebar = $('.main-sidebar');
        var sidebar_class = 'sidebar-' + mode + '-' + color;
        sidebar_skins.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)

        if (mode == 'dark') {
            $('.main-sidebar').css({
                'background': 'repeating-linear-gradient(to top, rgba(0, 0, 0, 0.95), rgba(114, 114, 114, 0.95)),url(/Images/sidebar.jpg)',
                'background-repeat': 'repeat-y'
            });
            $('.control-sidebar').removeClass('control-sidebar-light');
            $('.control-sidebar').addClass('control-sidebar-dark');
        } else {
            $('.main-sidebar').css({
                'background': 'repeating-linear-gradient(0deg,rgba(255,255,255,0.95),rgba(200,200,200,0.95)),url(/Images/sidebar.jpg)',
                'background-repeat': 'repeat-y'
            });
            $('.control-sidebar').removeClass('control-sidebar-dark');
            $('.control-sidebar').addClass('control-sidebar-light');
        }
    });

    $('.sStyleFontSize').change(function () {
        if ($(this).val() == "Small") {
            $('.sidebar-mini').addClass('text-sm')
        } else {
            $('.sidebar-mini').removeClass('text-sm')
        }
    });

    $(".bSidebarCollapse").change(function () {
        $('[data-widget="pushmenu"]').PushMenu('toggle');
    });


    $(".sStyleBrandLinkColor").change(function () {
        var color = $(this).val();
        var sidebar_class = 'navbar-' + color
        var sidebar = $('.brand-link')
        navbar_colors.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)
    });

    $(".sDarkMode").change(function () {
        if ($(this).val() == "dark") {
            $('.sidebar-mini').addClass('dark-mode');
            $('.table-dropdown-menu').addClass('dark-mode');

        } else if ($(this).val() == "light") {
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

    $(".sStyleNavBarColor").change(function () {
        var color = $(this).val();
        var sidebar_class = 'navbar-' + color
        var sidebar = $('.main-header')
        navbar_colors.map(function (skin) {
            sidebar.removeClass(skin)
        })

        sidebar.addClass(sidebar_class)
    });

    $(".sStyleSideBarColor").change(function () {
        var color = $(this).val();
        var sidebar_class = 'sidebar-' + ((mode == 'light') ? 'light' : 'dark') + '-' + color
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
            var res = '<div class="row">' +
                '           <div class="col-md-12">';
            res += '            <label>' + i18next.t("2 Factor Authentication Secret") + "</label>";
            res += '        </div>';
            res += '    </div>';
            res += '    <div class="row text-center">';
            res += '        <div class="col-md-6">';
            res += '            <img src="' + data.img + '"><br>';
            res += '        </div>';
            res += '        <div class="col-md-3">';
            //res += '            <br/><button class="btn btn-warning">' + i18next.t("Regenerate 2 Factor Authentication Secret") + '</button><br/><br/>';
            res += '            <br/><br/><button class="btn btn-danger remove-2fa">' + i18next.t("Remove 2 Factor Authentication Secret") + '</button>';
            res += '        </div>';
            res += '    </div>' +
                '<br/>' +
                '<br/>';

            res += '<div class="row">' +
            '   <div class="col-md-6">' +
            '       <label>' + i18next.t("Enter TOTP code to confirm enrollment") + ' : <input value="" id="inputCode"> <span id="verifyCode"></span> </label>' +
            '   </div>' +
            '</div>'

            res += '<br/><div class="row">' +
                '   <div class="col-md-12">' +
                '       <label id="rescuepasswords"></label>'
            '   </div>' +
            '</div>';

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
                $("#verifyCode").html('<i class="fas fa-check" style="font-size: 20px;color: green"></i>');

                message = '<div  class="row">';
                message += '<div class="col-md-12">';
                message += '<div class="card card-success">';
                message += '<div class="card-header">';
                message += '<h1 class="card-title">' + i18next.t("Keep these backup passwords in a safe place, in case you lose the OTP credentials.") + '</h1>';
                message += '</div>';
                message += '<div class="card-body">';
                message += '<p>' + data.rescue_passwords + '</p>';
                message += '</div>';
                message += '</div>';
                message += '</div>';
                message += '</div>';

                $("#two-factor-results").html(message);
            } else {
                $("#verifyCode").html('<i class="fas fa-ban" style="font-size: 20px;color: red"></i>');
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
