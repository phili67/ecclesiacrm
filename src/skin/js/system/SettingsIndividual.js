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
});
