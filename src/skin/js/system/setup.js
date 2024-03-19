window.CRM.dataBaseCheck = false;

function skipCheck() {
    $("#prerequisites-war").hide();
    window.CRM.prerequisitesStatus = true;
}

function dataBaseCheck() {
    var serverName = $('#DB_SERVER_NAME').val();
    var dbName = $('#DB_NAME').val();
    var dbPort = $('#DB_SERVER_PORT').val();
    var user = $('#DB_USER').val();
    var password = $('#DB_PASSWORD').val();

    var infos = {'serverName': serverName, 'dbName': dbName, 'dbPort': dbPort, 'user': user, 'password': password};

    $.ajax({
        url: window.CRM.root + "/setup/checkDatabaseConnection",
        method: "post",
        data: JSON.stringify(infos), // stringify the object we created earlier, and add it to the data payload
        contentType: "application/json",
        error: function (request, status, error) {
            window.CRM.dataBaseCheck = false;
            $('#databaseconnection-war').html('Connection to your database failed. Click the link <a href="#" onclick="dataBaseCheck()"><b>here</b></a> to re-check your connection.');
            $('.alert-db').removeClass('alert-warning');
            $('.alert-db').addClass('alert-danger');
        }
    }).done(function (data) {
        if (data.status !== undefined && data.status == "success") {
            $('#databaseconnection-war').html('Connection to your database successfully done. Click the "Next" button finish your installation.');
            $('.alert-db').removeClass('alert-warning');
            $('.alert-db').removeClass('alert-danger');
            $('.alert-db').addClass('alert-success');
            window.CRM.dataBaseCheck = true;
        } else {
            window.CRM.dataBaseCheck = false;
            $('#databaseconnection-war').html('Connection to your database failed. Click the link <a href="#" onclick="dataBaseCheck()"><b>here</b></a> to re-check your connection.');
            $('.alert-db').removeClass('alert-warning');
            $('.alert-db').addClass('alert-danger');
        }
    });
}

window.CRM.checkIntegrity = function () {
    window.CRM.renderPrerequisite("EcclesiaCRM File Integrity Check", "pending");
    $.ajax({
        url: window.CRM.root + "/setup/SystemIntegrityCheck",
        method: "GET"
    }).done(function (data) {
        if (data == "success") {
            window.CRM.renderPrerequisite("EcclesiaCRM File Integrity Check", "pass");
            $("#prerequisites-war").hide();
            window.CRM.prerequisitesStatus = true;
        } else {
            window.CRM.renderPrerequisite("EcclesiaCRM File Integrity Check", "fail");
        }

    }).fail(function () {
        window.CRM.renderPrerequisite("EcclesiaCRM File Integrity Check", "fail");
    });
};

window.CRM.checkPrerequisites = function () {
    $.ajax({
        url: window.CRM.root + "/setup/SystemPrerequisiteCheck",
        method: "GET",
        contentType: "application/json"
    }).done(function (data) {
        $.each(data, function (key, value) {
            if (value) {
                status = "pass";
            } else {
                status = "fail";
            }
            window.CRM.renderPrerequisite(key, status);
        });
    });
};

window.CRM.renderPrerequisite = function (name, status) {
    var td = {};
    if (status == "pass") {
        td = {
            class: 'text-blue',
            html: '&check;'
        };
    } else if (status == "pending") {
        td = {
            class: 'text-orange',
            html: '<i class="fas fa-spinner fa-spin"></i>'
        };
    } else if (status == "fail") {
        td = {
            class: 'text-red',
            html: '&#x2717;'
        };
    }
    var id = name.replace(/[^A-z0-9]/g, '');
    window.CRM.prerequisites[id] = status;
    var domElement = "#" + id;
    var prerequisite = $("<tr>", {id: id}).append(
        $("<td>", {text: name})).append(
        $("<td>", td));

    if ($(domElement).length != 0) {
        $(domElement).replaceWith(prerequisite);
    } else {
        $("#prerequisites").append(prerequisite);
    }

};


$(function() {
    var setupWizard = $("#setup-form");

    setupWizard.validate({
        rules: {
            DB_PASSWORD2: {
                equalTo: "#DB_PASSWORD"
            }
        }
    });

    setupWizard.children("div").steps({
        headerTag: "h2",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        stepsOrientation: "vertical",
        onStepChanging: function (event, currentIndex, newIndex) {
            if (currentIndex == 3) {
                if (window.CRM.dataBaseCheck == false) {
                    dataBaseCheck();
                    $("#setup-form").steps("previous", {});
                }
            }

            if (currentIndex > newIndex) {
                return true;
            }

            if (currentIndex == 0) {
                return window.CRM.prerequisitesStatus;
            }

            setupWizard.validate().settings.ignore = ":disabled,:hidden";
            return setupWizard.valid();
        },
        onFinishing: function (event, currentIndex) {
            setupWizard.validate().settings.ignore = ":disabled";
            return setupWizard.valid();
        },
        onFinished: function (event, currentIndex) {
            submitSetupData(setupWizard);
        }
    });

    window.CRM.checkIntegrity();
    window.CRM.checkPrerequisites();
});

function submitSetupData(form) {
    var formArray = form.serializeArray();
    var json = {};

    jQuery.each(formArray, function () {
        json[this.name] = this.value || '';
    });

    $.ajax({
        url: window.CRM.root + "/setup/",
        method: "POST",
        data: JSON.stringify(json),
        contentType: "application/json",
        success: function (data, status, xmlHttpReq) {
            location.replace(window.CRM.root + "/");
        }
    });

}

$(function() {
    $("#sLanguage").select2();
    $("#schurchcountry-input").select2();
    //$("#schurchstate-input").select2();
    $("#sTimeZone").select2();
    $("#schurchcountry-input").select2();
});
