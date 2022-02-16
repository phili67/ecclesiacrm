$(document).ready(function () {
    $('#SaveSettings').click('focus', function (e) {
        var domain = $('#domain').val();
        var domainscriptpath = $('#domainscriptpath').val();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'meeting/changeSettings',
            data: JSON.stringify({"domain": domain, "domainscriptpath":domainscriptpath})
        },function (data) {
            window.CRM.DisplayAlert(i18next.t("Settings", { ns: 'MeetingJitsi' }), i18next.t("Saved", { ns: 'MeetingJitsi' }), function () {
                location.reload();
            })
        });
    });
});
