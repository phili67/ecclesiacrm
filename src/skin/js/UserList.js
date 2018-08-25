$(document).ready(function () {
    var click_checkbox = false;
    
    $(".checkbox_users").click(function() {
      click_checkbox = true;
    });
    
    $(".check_all").click(function() {
      var state = this.checked;
      $(".checkbox_users").each(function() {
        $(this)[0].checked=state;
      });
    });

    
    $('#user-listing-table').on('click', 'tr', function () {
      if (click_checkbox == true) {
        click_checkbox = false;
        return;        
      }
      
      var table = $('#user-listing-table').DataTable();
      var data = table.row( this ).data();
      
      if (data != undefined) {
        click_tr = true;
        var userID = $(data[0]).data("id");
        var state = $('.checkbox_user'+userID).prop('checked');
        $('.checkbox_user'+userID).prop('checked', !state);
        click_tr = false;
      }
    });
    
    $(".changeRole").click(function() {
      var roleID = $(this).data("id");
      var roleName = this.innerText;
      var userID = -1;
      
      $(".checkbox_users").each(function() {
          if (this.checked) {
            userID = $(this).data("id");
            _val = $(this).val();

            window.CRM.APIRequest({
               method: 'POST',
               path: 'users/applyrole',
               data: JSON.stringify({"userID": userID,"roleID" : roleID})
            }).done(function(data) {
              if (data.success == true) {
                 // à terminer !!!
                 $('.role'+data.userID).html(roleName);
              }
            });
          }
      });
      
      if (userID == -1) {
        window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("You've to check at least one user."));
      }
    });

    
    $(".webdavkey").click(function() {
      var userID = $(this).data("userid");
      
      window.CRM.APIRequest({
         method: 'POST',
         path: 'users/webdavKey',
         data: JSON.stringify({"userID": userID})
      }).done(function(data) {
        if (data.status == 'success') {
          if (data.token != null) {
            window.CRM.DisplayAlert(i18next.t("WebDav key"),i18next.t("The WebDav Key is")+" : "+data.token);
          } else {
            window.CRM.DisplayAlert(i18next.t("WebDav key"),i18next.t("The WebDav Key is")+" : "+i18next.t("None"));
          }
        }
      });
    });
    
    $(".lock-unlock").click(function() {
      var userID = $(this).data("userid");
      
      window.CRM.APIRequest({
         method: 'POST',
         path: 'users/lockunlock',
         data: JSON.stringify({"userID": userID})
      }).done(function(data) {
        if (data.success == true) {
           location.reload();
        }
      });
    });
    
    
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
            i18next.t("Be carefull, You are about to lose the home folder and the associated files, the Calendars, the Share calendars and all the events too, for")+':<b> ' + userName + '</b><br><br>'+
            i18next.t("This can't be undone")+'</p>',
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
