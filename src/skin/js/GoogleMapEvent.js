//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2018/06/27
//

 
  var marker = null;
  var infowindow = null;
    
  function updateMap()
  {
      // Safari Google Map bug correction for an inclusion in a bootbox
      document.getElementById('MyMap').style.position = 'relative';
      document.getElementById('MyMap').style.background = 'none';
      document.getElementById('MyMap').style.width = '100%';
      document.getElementById('MyMap').style.height = '210px';      
  }
  
  $(document).on('keydown','#EventLocation',function (val) {    
    if (val.which == 13) {
      deleteMarker(marker);
      
      var address       = $('form #EventLocation').val();
  
      $.ajax({
        url:"https://maps.googleapis.com/maps/api/geocode/json?address="+address+"&sensor=false&key="+window.CRM.iGoogleMapKey,
        type: "POST",
        success:function(res){
          var latitude  = res.results[0].geometry.location.lat;
          var longitude = res.results[0].geometry.location.lng;
          var EventTitle =  $('form #EventTitle').val();
          var EventDesc =  $('form #EventDesc').val();
      
          if ( latitude > 0 && longitude > 0 ) {
            var Salutation = EventTitle + " ("+EventDesc+")";
            var Name = EventTitle;
            var latlng = new google.maps.LatLng(latitude, longitude);

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
            contentString += "<p>" + address + "</p>";
      
            //Add marker and infowindow
            marker  = addMarkerWithInfowindow(window.CRM.map, latlng, image, Name, contentString);
        
            window.CRM.map.setCenter(latlng);
          }
        }
      });
    }
  });
  
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
          infowindow.open(window.CRM.map, marker);
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
          zoom: window.CRM.iLittleMapZoom,
          center: centerCard
      });
      
      infowindow = new google.maps.InfoWindow({
        maxWidth: 200
      });
      
      google.maps.event.addListenerOnce(window.CRM.map, 'idle', function () {
        var currentCenter = window.CRM.map.getCenter();  // Get current center before resizing
        google.maps.event.trigger(window.CRM.map, "resize");
        window.CRM.map.setCenter(currentCenter); // Re-set previous center

        window.CRM.map.setZoom(window.CRM.iLittleMapZoom);
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