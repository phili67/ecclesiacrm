  var marker = null;
  
  function addMarkerWithInfowindow(map, marker_position, image, title, infowindow_content) {
      //Create marker
      var marker = new google.maps.Marker({
          position: marker_position,
          map: map,
          icon: image,
          title: title
      });

      google.maps.event.addListener(marker, 'click', function () {
          infowindow.setContent(infowindow_content);
          infowindow.open(map, marker);
          //set image/gravtar
          $('.profile-user-img').initial();
      });
      
      return marker;
  }
  
  function deleteMarker(mark)
  {
    if (mark != null) {
      mark.setMap(null);
    }
    mark = null;
  }


  function initMap(longitude,latitude,Salutation,Address,Name,Text) {
     var centerCard = window.CRM.churchloc;
     
     if ( longitude !== undefined && latitude !== undefined && longitude > 0 && latitude > 0 ) {
       var centerCard = {
          lat: Number(latitude),
          lng: Number(longitude)}; 
     }

      // Create a map object and specify the DOM element for display.
      window.CRM.map = new google.maps.Map(document.getElementById('MyMap'), {
          zoom: window.CRM.mapZoom,
          center: centerCard
      });
      
      google.maps.event.addListenerOnce(window.CRM.map, 'idle', function () {
        var currentCenter = window.CRM.map.getCenter();  // Get current center before resizing
        google.maps.event.trigger(window.CRM.map, "resize");
        window.CRM.map.setCenter(currentCenter); // Re-set previous center

        window.CRM.map.setZoom(15);
      });

    
      if ( longitude !== undefined && latitude !== undefined && longitude > 0 && latitude > 0 ) {
        var latlng = new google.maps.LatLng(Number(latitude), Number(longitude));

        var imghref = window.CRM.root+"/Calendar.php";
        var iconurl = window.CRM.root+"/skin/icons/event.png";
        
        var image = {
            url: iconurl,
            // This marker is 37 pixels wide by 34 pixels high.
            size: new google.maps.Size(37, 34),
            // The origin for this image is (0, 0).
            origin: new google.maps.Point(0, 0),
            // The anchor for this image is the base of the flagpole at (0, 32).
            anchor: new google.maps.Point(0, 32)
        };

        contentString = "<b><a href='" + imghref + "'>" + Salutation + "</a></b>";
        contentString += "<p>" + Address + "</p>";

        //Add marker and infowindow
        marker  = addMarkerWithInfowindow(window.CRM.map, latlng, image, Name, contentString);
      } else {
        //Churchmark
        marker = new google.maps.Marker({
            icon: window.CRM.root + "/skin/icons/church.png",
            position: new google.maps.LatLng(window.CRM.churchloc),
            map: window.CRM.map
        });
      }
  }