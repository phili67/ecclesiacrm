//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2018/06/27
//

  var marker = null;
  
  function updateMap()
  {
    document.getElementById('MyMap').style.width = '100%';
    document.getElementById('MyMap').style.height = '240px';      
    /*if (window.CRM.map) {
      window.CRM.map.setMapType(Microsoft.Maps.MapTypeId.mercator);
      setTimeout(function(){
        window.CRM.map.setMapType(Microsoft.Maps.MapTypeId.auto);
      }, 1)
    }*/
  }
  
  
  $(document).on('keydown','#EventLocation',function (val) {    
    if (val.which == 13) {
      deleteMarker(marker);
      
      var address       = $('form #EventLocation').val();  
      var EventTitle    = $('form #EventTitle').val();
      var EventDesc     = $('form #EventDesc').val();
      var Salutation = EventTitle + " ("+EventDesc+")";
      var Name = EventTitle;

      var imghref = window.CRM.root+"/Calendar.php";
      var iconurl = window.CRM.root+"/skin/icons/event.png";
  
      var icon = { 
        icon: iconurl,
      };
      
      contentString = "<p>" + address + "</p>";

      Microsoft.Maps.loadModule('Microsoft.Maps.Search', function () {
          var searchManager = new Microsoft.Maps.Search.SearchManager(window.CRM.map);
          var requestOptions = {
              bounds: window.CRM.map.getBounds(),
              where: address,
              callback: function (answer, userData) {
                  window.CRM.map.setView({ bounds: answer.results[0].bestView });
                  
                  var centerCard = {
                    lat: Number(answer.results[0].location.latitude),
                    lng: Number(answer.results[0].location.longitude)};
                    
                  marker  = addMarkerWithInfowindow(window.CRM.map, centerCard, icon, Name, contentString);
                  
                  window.CRM.map.setCenter(new Microsoft.Maps.Location(centerCard.lat, centerCard.lng), window.CRM.iLittleMapZoom );
              }
          };
          searchManager.geocode(requestOptions);
      });
    }
  });
  
  function addMarkerWithInfowindow(map, marker_position, image, title, infowindow_content) {         
      var pin = new Microsoft.Maps.Pushpin(new Microsoft.Maps.Location(marker_position.lat, marker_position.lng), image);
      
      map.entities.push(pin);

      var infobox = new Microsoft.Maps.Infobox(new Microsoft.Maps.Location(marker_position.lat, marker_position.lng), 
      { title: title,description: infowindow_content, visible: false });
        
      infobox.setMap(map);
        
      Microsoft.Maps.Events.addHandler(pin, 'click', function () {
          infobox.setOptions({ visible: true,offset: new Microsoft.Maps.Point(0, 32) });
      });
      
      return pin;
  }
  
  function deleteMarker(mark)
  {
    if (mark != null) {
      window.CRM.map.entities.remove(mark)
    }
    mark = null;
  }


  function initMap(longitude,latitude,Salutation,Address,Name,Text) {
          
      if ( longitude !== undefined && latitude !== undefined && longitude > 0 && latitude > 0 ) {
        // Create a map object and specify the DOM element for display.
        window.CRM.map = new Microsoft.Maps.Map('#MyMap', {center: new Microsoft.Maps.Location(latitude, longitude),zoom: window.CRM.iLittleMapZoom});

        var centerCard = {
          lat: Number(latitude),
          lng: Number(longitude)};

        var imghref = window.CRM.root+"/Calendar.php";
        var iconurl = window.CRM.root+"/skin/icons/event.png";
                
        var icon = { 
          icon: iconurl,
        };

        contentString = '<p><a href="http://maps.google.com/?q=1  ' + Address + '" target="_blank">' + Address + '</a></p>';

        //Add marker and infowindow
        marker  = addMarkerWithInfowindow(window.CRM.map, centerCard, icon, Name, contentString);
      } else {
        // Create a map object and specify the DOM element for display.
        window.CRM.map = new Microsoft.Maps.Map('#MyMap', {center: new Microsoft.Maps.Location(window.CRM.churchloc.lat, window.CRM.churchloc.lng),zoom: window.CRM.iLittleMapZoom});

        //Churchmark
        var icon = { 
          icon: window.CRM.root + "/skin/icons/church.png",
        };

        marker = addMarkerWithInfowindow(window.CRM.map,window.CRM.churchloc,icon,"titre",window.CRM.sChurchName);
      }
  }
  
  function GetMap() {
    initMap();
  }