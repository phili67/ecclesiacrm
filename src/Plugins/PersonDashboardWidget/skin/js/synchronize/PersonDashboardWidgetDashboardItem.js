window.CRM.synchronize.renderers.PersonDashboardWidget = function (data) {
    const peopleStats = document.getElementById('peopleStatsDashboard');
    if (peopleStats !== null) {
        peopleStats.innerText = data.personCount;
    }
};
