$(document).ready(function () {
      $.fn.dataTable.moment = function ( format, locale ) {
        var types = $.fn.dataTable.ext.type;

        // Add type detection
        types.detect.unshift( function ( d ) {
            // Removed true as the last parameter of the following moment
            return moment( d, format, locale ).isValid() ?
                'moment-'+format :
            null;
        } );

        // Add sorting method - use an integer for the sorting
        types.order[ 'moment-'+format+'-pre' ] = function ( d ) {
           console.log("d");
            return moment ( d, format, locale, true ).unix();
        };
      };
      
  
      $.fn.dataTable.moment(window.CRM.datePickerformat.toUpperCase(),window.CRM.shortLocale);
    
      $("#user-listing-table").DataTable({
       "language": {
         "url": window.CRM.plugin.dataTable.language.url
       },
       responsive: true
      });
    });

    function deleteUser(userId, userName) {
        bootbox.confirm({
            title: i18next.t("User Delete Confirmation"),
            message: '<p style="color: red">' +
            i18next.t("Please confirm removal of user status from:")+'<b>' + userName + '</b><br><br>'+
            i18next.t("Be carefull !!! You will lose the home folder and the files, the Calendars, the Share calendars and all the events too, for")+':<b> ' + userName + '</b><br><br>'+
            i18next.t("This can be undone")+'</p>',
            callback: function (result) {
                if (result) {
                    $.ajax({
                        method: "POST",
                        url: window.CRM.root + "/api/users/" + userId,
                        dataType: "json",
                        encode: true,
                        data: {"_METHOD": "DELETE"}
                    }).done(function (data) {
                        if (data.status == "success")
                            window.location.href = window.CRM.root + "/UserList.php";
                    });
                }
            }
        });
    }

    function restUserLoginCount(userId, userName) {
        bootbox.confirm({
            title: i18next.t("Action Confirmation"),
            message: '<p style="color: red">' +
            i18next.t("Please confirm reset failed login count")+": <b>" + userName + "</b></p>",
            callback: function (result) {
                if (result) {
                    $.ajax({
                        method: "POST",
                        url: window.CRM.root + "/api/users/" + userId + "/login/reset",
                        dataType: "json",
                        encode: true,
                    }).done(function (data) {
                        if (data.status == "success")
                            window.location.href = window.CRM.root + "/UserList.php";
                    });
                }
            }
        });
    }

    function resetUserPassword(userId, userName) {
        bootbox.confirm({
            title: i18next.t("Action Confirmation"),
            message: '<p style="color: red">' +
            i18next.t("Please confirm the password reset of this user")+": <b>" + userName + "</b></p>",
            callback: function (result) {
                if (result) {
                    $.ajax({
                        method: "POST",
                        url: window.CRM.root + "/api/users/" + userId + "/password/reset",
                        dataType: "json",
                        encode: true,
                    }).done(function (data) {
                        if (data.status == "success")
                            showGlobalMessage(i18next.t("Password reset for") + userName, i18next.t("success"));
                    });
                }
            }
        });
    }