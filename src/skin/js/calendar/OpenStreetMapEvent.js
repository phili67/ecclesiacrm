//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2024/03/31
//
  var marker = null;
  
  const  updateMap = () => {
      document.getElementById('MyMap').style.width = '100%';
      document.getElementById('MyMap').style.height = '210px';      

      setTimeout(function() {
        window.CRM.map.invalidateSize(true);
      }, 1);
  }
  
  $(document).on('keydown','#EventLocation',function (val) {    
    if (val.which == 13) {
      deleteMarker(marker);
      
      var address       = $('form #EventLocation').val();
      
      var address_nomatim = address.replace(/ /g,'+');

      fetch(window.CRM.sNominatimLink+"/search?q="+address_nomatim+"&format=json", {            
          method: "GET",
          headers: {
            "Content-Type": "application/json",            
          }
      })
        .then(resj => resj.json())
        .then(res => {          
          if (res === undefined || res.length == 0) {
            alert(i18next.t('Wrong address format.'));
            return;
          }
          
          let latitude  = res[0].lat;
          let longitude = res[0].lon;
          let EventTitle =  $('form #EventTitle').val();
          let EventDesc =  $('form #EventDesc').val();
      
          if ( latitude > 0 && longitude > 0 ) {
            let Salutation = EventTitle + " ("+EventDesc+")";
            let Name = EventTitle;

            let imghref = window.CRM.root+"/v2/calendar";
            let iconurl = window.CRM.root+"/skin/icons/event.png";
      
            let icon = L.icon({
                iconUrl: iconurl,
                iconSize:     [32, 32], // size of the icon
                iconAnchor:   [16, 32], // point of the icon which will correspond to marker's location
                popupAnchor:  [0, -32] // point from which the popup should open relative to the iconAnchor
            });

            contentString = "<b><a href='" + imghref + "'>" + Salutation + "</a></b>";
            contentString += "<p>" + address + "</p>";
            
            let centerCard = {
              lat: Number(latitude),
              lng: Number(longitude)};

            //Add marker and infowindow
            marker  = addMarkerWithInfowindow(window.CRM.map, centerCard, icon, Name, contentString);
        
            window.CRM.map.setView([centerCard.lat, centerCard.lng], window.CRM.mapZoom);
          }        
      });
    }
  });
  
  const addMarkerWithInfowindow = (map, marker_position, image, title, infowindow_content) => {
      if (marker != null) {
        deleteMarker(marker);
      }
      
      marker = L.marker([marker_position.lat, marker_position.lng], {icon: image})
         .bindPopup(infowindow_content)
         .addTo(map);

      window.CRM.map.panTo(marker_position);
         
      return marker;
  }
    
  const deleteMarker = (mark) => {
    if (mark != null) {
      window.CRM.map.removeLayer(mark);
    }
    marker = null;
  }


  const initMap = (longitude,latitude,Salutation,Address,Name,Text) => {
      // Create a map object and specify the DOM element for display.
      if ( longitude !== undefined && latitude !== undefined && longitude > 0 && latitude > 0 ) {
         var centerCard = {
            lat: Number(latitude),
            lng: Number(longitude)};
         window.CRM.map = L.map('MyMap').setView([centerCard.lat, centerCard.lng], window.CRM.iLittleMapZoom);
      } else {
         window.CRM.map = L.map('MyMap').setView([window.CRM.churchloc.lat, window.CRM.churchloc.lng], window.CRM.iLittleMapZoom);
      }

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.ecclesiacrm.com">EcclesiaCRM</a>'
      }).addTo(window.CRM.map);

      if ( longitude !== undefined && latitude !== undefined && longitude > 0 && latitude > 0 ) {
        var imghref = window.CRM.root+"/v2/calendar";

        var iconurl = window.CRM.root+"/skin/icons/event.png";
        
        var icon = L.icon({
            iconUrl: iconurl,
            iconSize:     [32, 32], // size of the icon
            iconAnchor:   [16, 32], // point of the icon which will correspond to marker's location
            popupAnchor:  [0, -32] // point from which the popup should open relative to the iconAnchor
        });

        contentString = "<b><a href='" + imghref + "'>" + Salutation + "</a></b>";
        contentString += "<p>" + Address + "</p>";

        //Add marker and infowindow
        marker  = addMarkerWithInfowindow(window.CRM.map, centerCard, icon, Name, contentString);
      } else {
        //Churchmark
        var icon = L.icon({
            iconUrl: window.CRM.root + "/skin/icons/church.png",
            iconSize:     [32, 37], // size of the icon
            iconAnchor:   [16, 37], // point of the icon which will correspond to marker's location
            popupAnchor:  [0, -37] // point from which the popup should open relative to the iconAnchor
        });

        addMarkerWithInfowindow(window.CRM.map,window.CRM.churchloc,icon,"titre",window.CRM.sEntityName);
      }
  }