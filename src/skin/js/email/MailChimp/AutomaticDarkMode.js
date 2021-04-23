$(document).ready(function () {
    window.CRM.AutomaticDarkModeFunction = function (darkMode)
    {
        if (darkMode) {
            $('.logo-mailchimp').attr('src',window.CRM.root + '/Images/Mailchimp_Logo-Horizontal_White.png');
        } else {
            $('.logo-mailchimp').attr('src',window.CRM.root + '/Images/Mailchimp_Logo-Horizontal_Black.png');
        }
    }

    <!-- for the theme before jquery load is finished -->
    if (window.CRM.sLightDarkMode == "automatic") {
        let matched = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if(matched) {// we're on dark mode
            $('.logo-mailchimp').attr('src',window.CRM.root + '/Images/Mailchimp_Logo-Horizontal_White.png');
        } else {// we're in light mode
            $('.logo-mailchimp').attr('src',window.CRM.root + '/Images/Mailchimp_Logo-Horizontal_Black.png');
        }
    }
});
