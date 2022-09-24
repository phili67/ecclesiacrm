document.addEventListener("DOMContentLoaded", function() {
    window.CRM.ElementListener('#saveDashboardNote', 'click', function(event) {
        let note = document.querySelector('#NoteDashboardContent').value;

        window.CRM.APIRequest({
            method: 'POST',
            path: 'notedashboardplugin/modify',
            data: JSON.stringify({
                "note": note
            })
        }, function (data) {
        });
    });
});
