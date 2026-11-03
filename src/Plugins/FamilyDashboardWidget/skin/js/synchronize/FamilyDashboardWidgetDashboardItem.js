window.CRM.synchronize.renderers.FamilyDashboardWidget = function (data) {
    const familyCount = document.getElementById('realFamilyCNT');
    if (familyCount !== null) {
        familyCount.innerText = data.familyCount;
    }
};
