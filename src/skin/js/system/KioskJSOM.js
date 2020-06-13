window.CRM.kiosk = {

  APIRequest: function(options) {
    if (!options.method)
    {
      options.method="GET"
    }
    options.url=window.CRM.root+"/kiosk/"+options.path;
    options.dataType = 'json';
    options.contentType =  "application/json";
    return $.ajax(options);
  },

  renderClassMember: function (classMember) {
      existingDiv = $("#personId-"+classMember.personId);
      if (existingDiv.length > 0)
      {

      }
      else
      {
        var globaldiv= $("<div>").addClass("row");
        var outerDiv = $("<div>",{id:"personId-"+classMember.personId}).addClass("col-sm-12");
        var innerDiv = $("<div>").addClass("card card-widget widget-user-2");
        var userHeaderDiv = $("<div>",{class :"widget-user-header bg-yellow"}).attr("data-personid",classMember.personId);
        var imageDiv = $("<div>", {class:"widget-user-image"})
                .append($("<img>",{
                            class:"initials-image profile-user-img img-responsive img-circle no-border",
                            src:window.CRM.root+"/kiosk/activeClassMember/"+classMember.personId+"/photo",
                            name:classMember.displayName
                        })
                );
        userHeaderDiv.append(imageDiv);
        userHeaderDiv.append($("<h3>",{class:"widget-user-username", text:classMember.displayName})).append($("<h3>",{class:"widget-user-desc", style:"clear:both", text: i18next.t(classMember.classRole)}));
        innerDiv.append(userHeaderDiv);
        innerDiv.append($("<div>", { class : "card-footer no-padding"})
                  .append($("<div>", { class : "row"})
                  .append($("<div>", {class:"col-md-6"})
                        .append($("<button>",{class: "btn btn-danger parentAlertButton", style:"width:100%", text : i18next.t("Trigger Parent Alert"), "data-personid": classMember.personId})
                            .prepend($("<i>",{class:"fa fa-exclamation-triangle",'aria-hidden':"true"}))
                        )
                  )
                  .append($("<div>", {class:"col-md-6"})
                    .append($("<button>",{class: "btn btn-primary checkinButton", style:"width:100%", text : i18next.t("Checkin"), "data-personid": classMember.personId}))
                  )
                ));
        outerDiv.append(innerDiv);
          globaldiv.append(outerDiv)
        $("#classMemberContainer").append(globaldiv);
      }

      if (classMember.status == 1)
      {
        window.CRM.kiosk.setCheckedIn(classMember.personId);
      }
      else
      {
        window.CRM.kiosk.setCheckedOut(classMember.personId);

      }

    },

  updateActiveClassMembers: function()  {
     window.CRM.kiosk.APIRequest({
       path:"activeClassMembers"
     })
     .done(function(data){
          $(data.EventAttends).each(function(i,d){
            window.CRM.kiosk.renderClassMember({displayName:d.Person.FirstName+" "+d.Person.LastName, classRole:d.RoleName,personId:d.Person.Id,status:d.status})
          });
      })
  },

  heartbeat: function(){
    window.CRM.kiosk.APIRequest({
       path:"heartbeat"
     }).
        done(function(data){
          thisAssignment = data.Assignment;
          if( window.CRM.kioskAssignmentId === undefined)
          {
            window.CRM.kioskAssignmentId = thisAssignment;
          }
          else if (thisAssignment && window.CRM.kioskAssignmentId != null && (thisAssignment.EventId !== window.CRM.kioskAssignmentId.EventId || thisAssignment.Event.GroupId !== window.CRM.kioskAssignmentId.Event.GroupId)){
            location.reload();
          }

          if (data.Commands === "Reload")
          {
            location.reload();
          }

          if (data.Commands === "Identify")
          {
            clearInterval(window.CRM.kioskEventLoop);
            $("#event").hide();
            $("#noEvent").show();
            $("#noEvent").html("Kiosk Name: " + data.Name);
            setTimeout(function(){location.reload()},2000);
            return;
          }

          if (data.Accepted)
          {
            Assignment=data.Assignment;
            if (Assignment && Assignment.AssignmentType == 1)
            {
              window.CRM.kiosk.updateActiveClassMembers();
              $("#noEvent").hide();
              $("#event").show();

              $("#eventKiosk").text("( " + i18next.t("Kiosk") + " : " + data.Name + ')');
              $("#eventTitle").text(Assignment.Event.Title);
              $("#startTime").text(moment(Assignment.Event.Start.date).format('MMMM Do YYYY, h:mm:ss a'));
              /* TO DO : date: "2020-06-04 22:00:00.000000"
                timezone: "Europe/Paris"
                timezone_type: 3*/

              $("#endTime").text(moment(Assignment.Event.End.date).format('MMMM Do YYYY, h:mm:ss a'));
            }
            else
            {
               $("#noEvent").show();
               $("#noEvent").html('No assignments for kiosk : ' + data.Name);
               $("#event").hide();
            }
          }
          else
          {
            $("#noEvent").show();
            $("#noEvent").html("This kiosk has not been accepted.<br/>Kiosk Name: " + data.Name);
            $("#event").hide();
          }

      })
  },

  checkInPerson: function(personId) {
    window.CRM.kiosk.APIRequest({
      path:"checkin",
      method:"POST",
      data:JSON.stringify({"PersonId":personId})
    }).
    done(function(data){
      window.CRM.kiosk.setCheckedIn(personId);
    });

  },

  checkOutPerson: function(personId)  {
    window.CRM.kiosk.APIRequest({
      path:"checkout",
      method:"POST",
      data:JSON.stringify({"PersonId":personId})
    }).
    done(function(data){
      window.CRM.kiosk.setCheckedOut(personId);
    });
  },

  setCheckedOut: function (personId)  {
    $personDiv = $("#personId-"+personId)
    $personDivButton = $("#personId-"+personId+" .checkoutButton")
    $personDivButton.addClass("checkinButton");
    $personDivButton.removeClass("checkoutButton");
    $personDivButton.text("Checkin");
    $personDiv.find(".widget-user-header").addClass("bg-yellow");
    $personDiv.find(".widget-user-header").removeClass("bg-green");
  },

  setCheckedIn: function (personId)  {
    $personDiv = $("#personId-"+personId)

    $personDivButton = $("#personId-"+personId+" .checkinButton")
    $personDivButton.removeClass("checkinButton");
    $personDivButton.addClass("checkoutButton");
    $personDivButton.text(i18next.t("Checkout"));

    $personDiv.find(".widget-user-header").removeClass("bg-yellow");
    $personDiv.find(".widget-user-header").addClass("bg-green");

  },

  triggerNotification:  function(personId)  {
    //window.CRM.kiosk.stopEventLoop();
    window.CRM.kiosk.APIRequest({
     path:"triggerNotification",
     method:"POST",
     data:JSON.stringify({"PersonId":personId})
   }).
   done(function(data){
     //window.CRM.kiosk.startEventLoop();
       //TODO:  Signal to the kiosk user that the notification was sent
   });

  },

  enterFullScreen: function() {
    if(document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen();
    } else if(document.documentElement.mozRequestFullScreen) {
      document.documentElement.mozRequestFullScreen();
    } else if(document.documentElement.webkitRequestFullscreen) {
      document.documentElement.webkitRequestFullscreen();
    } else if(document.documentElement.msRequestFullscreen) {
      document.documentElement.msRequestFullscreen();
    }
  },

  exitFullScreen: function() {
    if(document.exitFullscreen) {
     document.exitFullscreen();
   } else if(document.mozCancelFullScreen) {
     document.mozCancelFullScreen();
   } else if(document.webkitExitFullscreen) {
     document.webkitExitFullscreen();
   }
  },

  displayPersonInfo: function (personId)
  {
    //TODO: Display information (allergies, etc) about the person selected.
  },

  startEventLoop: function() {
    window.CRM.kiosk.kioskEventLoop = setInterval(window.CRM.kiosk.heartbeat,2000);
  },

  stopEventLoop: function() {
    clearInterval(window.CRM.kiosk.kioskEventLoop);
  }

}
