<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2018/06/27
//

require 'Include/Config.php';
require 'Include/Functions.php';
require 'Include/ReportFunctions.php';

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Base\FamilyQuery;
use EcclesiaCRM\Base\ListOptionQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\ChurchMetaData;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Utils\InputUtils;

use EcclesiaCRM\EventQuery;

use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Sharing;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\VObject;
use EcclesiaCRM\MyVCalendar;
use Sabre\DAV\PropPatch;
use Sabre\DAVACL;
use EcclesiaCRM\MyPDO\CalDavPDO;
use EcclesiaCRM\MyPDO\PrincipalPDO;
use Propel\Runtime\Propel;


//Set the page title
$sPageTitle = gettext('View on Map');

require 'Include/Header.php';

$iGroupID = InputUtils::LegacyFilterInput($_GET['GroupID'], 'int');
?>

<div class="callout callout-info">
    <a href="<?= SystemURLs::getRootPath() ?>/UpdateAllLatLon.php" class="btn bg-green-active"><i class="fa fa-map-marker"></i> </a>
    <?= gettext('Missing Families? Update Family Latitude or Longitude now.') ?>
</div>

<?php if (ChurchMetaData::getChurchLatitude() == '') {
    ?>
    <div class="callout callout-danger">
        <?= gettext('Unable to display map due to missing Church Latitude or Longitude. Please update the church Address in the settings menu.') ?>
    </div>
    <?php
} else {
    // new way to manage events
    // we get the PDO for the Sabre connection from the Propel connection
    $pdo = Propel::getConnection();         
    
    // We set the BackEnd for sabre Backends
    $calendarBackend = new CalDavPDO($pdo->getWrappedConnection());
    $principalBackend = new PrincipalPDO($pdo->getWrappedConnection());
    // get all the calendars for the current user
    
    $calendars = $calendarBackend->getCalendarsForUser('principals/'.strtolower($_SESSION['user']->getUserName()),"displayname",false);
    
    $eventsArr = [];
    
    foreach ($calendars as $calendar) {
      // we get all the events for the Cal
      $eventsForCal = $calendarBackend->getCalendarObjects($calendar['id']);
      
      if ($calendar['present'] == 0 || $calendar['visible'] == 0) {// this ensure the events are present or not
        continue;
      }
      
      foreach ($eventsForCal as $eventForCal) {
        $evnt = EventQuery::Create()->filterByInActive('false')->findOneById($eventForCal['id']);
        
        if ($evnt != null && $evnt->getLocation() != '') {
          $eventsArr[] = $evnt->getID();
        }            
      }
    }
    
    
    // we can work with the normal locations
    $plotFamily = false;
    //Get the details from DB
    $dirRoleHead = SystemConfig::getValue('sDirRoleHead');

    if ($iGroupID > 0) {
       // security test
       $currentUserBelongToGroup = $_SESSION['user']->belongsToGroup($iGroupID);
            
       if ($currentUserBelongToGroup == 0) {
          Redirect('Menu.php');
       }

        //Get all the members of this group
        $persons = PersonQuery::create()
          ->usePerson2group2roleP2g2rQuery()
          ->filterByGroupId($iGroupID)
          ->endUse()
          ->find();
    } elseif ($iGroupID == 0) {
        // group zero means map the cart
        if (!empty($_SESSION['aPeopleCart'])) {
            $persons = PersonQuery::create()
            ->filterById($_SESSION['aPeopleCart'])
            ->find();
        }
    } else {
      if ( !($_SESSION['user']->isShowMapEnabled()) ) {
          Redirect('Menu.php');
      }
       
      //Map all the families
      $families = FamilyQuery::create()
        ->filterByDateDeactivated(null)
        ->filterByLatitude(0, Criteria::NOT_EQUAL)
        ->filterByLongitude(0, Criteria::NOT_EQUAL)
        ->usePersonQuery('per')
        ->filterByFmrId($dirRoleHead)
        ->endUse()
        ->find();
        
      $plotFamily = true;
    }

    //Markericons list
    $icons = ListOptionQuery::create()
    ->filterById(1)
    ->orderByOptionSequence()
    ->find();

    $markerIcons = explode(',', SystemConfig::getValue('sGMapIcons'));
    array_unshift($markerIcons, 'red-pushpin'); //red-pushpin for unassigned classification
?>


    <div class="box">
        <!-- Google map div -->
        <div id="mapid" class="map-div"></div>

        <!-- map Desktop legend-->
        <div id="maplegend" style="z-index: 1000; position: absolute; bottom: 127px; right: 0px;"><h4><?= gettext('Legend') ?></h4>
            <div class="row legendbox">
                <div class="legenditem">
                    <img
                        src='https://www.google.com/intl/en_us/mapfiles/ms/micons/<?= $markerIcons[0] ?>.png'/>
                    <?= gettext('Unassigned') ?>
                </div>
                <div class="legenditem">
                        <img
                            src='<?= SystemURLs::getRootPath() ?>/skin/icons/event.png'/>
                        <?= gettext("Calendar") ?>
                </div>
                <?php
                foreach ($icons as $icon) {
                    ?>
                    <div class="legenditem">
                        <img
                            src='https://www.google.com/intl/en_us/mapfiles/ms/micons/<?= $markerIcons[$icon->getOptionId()] ?>.png'/>
                        <?= $icon->getOptionName() ?>
                    </div>
                    <?php
                } ?>                
            </div>
        </div>

        <!-- map Mobile legend-->
        <div id="maplegend-mobile" class="box visible-xs-block">
            <div class="row legendbox">
                <div class="btn bg-primary col-xs-12"><?= gettext('Legend') ?></div>
            </div>
            <div class="row legendbox">
                <div class="col-xs-6 legenditem">
                    <img
                        class="legendicon" src='https://www.google.com/intl/en_us/mapfiles/ms/micons/<?= $markerIcons[0] ?>.png'/>
                    <div class="legenditemtext"><?= gettext('Unassigned') ?></div>
                </div>
                <div class="legenditem">
                        <img
                            src='<?= SystemURLs::getRootPath() ?>/skin/icons/event.png'/>
                        <?= gettext("Calendar") ?>
                </div>
                <?php
                foreach ($icons as $icon) {
                    ?>
                    <div class="col-xs-6 legenditem">
                        <img
                            class="legendicon" src='https://www.google.com/intl/en_us/mapfiles/ms/micons/<?= $markerIcons[$icon->getOptionId()] ?>.png'/>
                        <div class="legenditemtext"><?= $icon->getOptionName() ?></div>
                    </div>
                    <?php
                } ?>
            </div>
        </div>
    </div> <!--Box-->


<?php
  }
  require 'Include/Footer.php' 
?>


 <script nonce="<?= SystemURLs::getCSPNonce() ?>">
  var churchloc = {
      lat: <?= ChurchMetaData::getChurchLatitude() ?>,
      lng: <?= ChurchMetaData::getChurchLongitude() ?>};


  var markerIcons = <?= json_encode($markerIcons) ?>;
  var iconsJSON = <?= $icons->toJSON() ?>;
  var icons = iconsJSON.ListOptions;
  var iconBase = 'https://www.google.com/intl/en_us/mapfiles/ms/micons/';
  
  
  function addMarkerWithInfowindow(map, marker_position, image, title, infowindow_content) {
      L.marker([marker_position.lat, marker_position.lng], {icon: image})
         .bindPopup(infowindow_content)
         .addTo(map);

  }
  
  
  function initialize() {
      // init map
      var map = L.map('mapid').setView([churchloc.lat, churchloc.lng], <?= SystemConfig::getValue("iMapZoom")?>);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.ecclesiacrm.com">EcclesiaCRM</a>'
      }).addTo(map);

      //Churchmark
      var icon = L.icon({
          iconUrl: window.CRM.root + "/skin/icons/church.png",
          iconSize:     [32, 37], // size of the icon
          iconAnchor:   [16, 37], // point of the icon which will correspond to marker's location
          popupAnchor:  [0, -37] // point from which the popup should open relative to the iconAnchor
      });

      addMarkerWithInfowindow(map,churchloc,icon,"titre","<?= SystemConfig::getValue('sChurchName') ?>");


    <?php
      $arr = array();
        $arrPlotItems = array();
        if ($plotFamily) {
            foreach ($families as $family) {
                if ($family->hasLatitudeAndLongitude()) {
                    //this helps to add head people persons details: otherwise doesn't seems to populate
                    $class = $family->getHeadPeople()[0];
                    $family->getHeadPeople()[0];
                    $photoFileThumb = SystemURLs::getRootPath() . '/api/families/' . $family->getId() . '/photo';
                    $arr['ID'] = $family->getId();
                    $arr['Name'] = $family->getName();
                    $arr['Salutation'] = $family->getSaluation();
                    $arr['Address'] = $family->getAddress();
                    $arr['Thumbnail'] = $photoFileThumb;
                    $arr['Latitude'] = $family->getLatitude();
                    $arr['Longitude'] = $family->getLongitude();
                    $arr['Name'] = $family->getName();
                    $arr['Classification'] = $class->GetClsId();
                    $arr['type'] = 'family';          
                    array_push($arrPlotItems, $arr);
                }
            }
        } else {
            //plot Person
            foreach ($persons as $member) {
                $latLng = $member->getLatLng();
                $photoFileThumb = SystemURLs::getRootPath() . '/api/persons/' . $member->getId() . '/thumbnail';
                $arr['ID'] = $member->getId();
                $arr['Salutation'] = $member->getFullName();
                $arr['Name'] = $member->getFullName();
                $arr['Address'] = $member->getAddress();
                $arr['Thumbnail'] = $photoFileThumb;
                $arr['Latitude'] = $latLng['Latitude'];
                $arr['Longitude'] = $latLng['Longitude'];
                $arr['Name'] = $member->getFullName();
                $arr['Classification'] = $member->getClsId();
                $arr['type'] = 'person';
                array_push($arrPlotItems, $arr);
            }
        } //end IF $plotFamily
        
        // now we can add the Events
        foreach ($eventsArr as $ev) {
          //echo "coucou".$ev;
          
          $event = EventQuery::Create()->findOneById($ev);

          $photoFileThumb = SystemURLs::getRootPath() ."/skin/icons/event.png";
          $arr['ID'] = $ev;
          $arr['Salutation'] = $event->getTitle()." (".$event->getDesc().")";
          $arr['Name'] = $event->getTitle()." (".$event->getDesc().")";
          $arr['Text'] = $event->getText();
          $arr['Address'] = $event->getLocation();
          $arr['Thumbnail'] = $photoFileThumb;
          $arr['bigThumbnail'] = SystemURLs::getRootPath() ."/skin/icons/bigevent.png";
          $arr['Latitude'] = $event->getLatitude();
          $arr['Longitude'] = $event->getLongitude();
          $arr['Classification'] = 0;
          $arr['type'] = 'event';
          $arr['desc'] = $event->getDesc();
          array_push($arrPlotItems, $arr);          
        }
        
      ?>

      var plotArray = <?= json_encode($arrPlotItems) ?>;
      var bPlotFamily = <?= ($plotFamily) ? 'true' : 'false' ?>;
      if (plotArray.length == 0) {
          return;
      }
      //loop through the families/persons and add markers
      for (var i = 0; i < plotArray.length; i++) {
          if (plotArray[i].Latitude + plotArray[i].Longitude == 0)
              continue;

          //icon image
          var clsid = plotArray[i].Classification;
          var markerIcon = markerIcons[clsid];
          
          var iconurl = iconBase + markerIcon + '.png';
          
          if (plotArray[i].type == 'event') {
            iconurl = plotArray[i].Thumbnail;
          }
          

          var icon = L.icon({
              iconUrl: iconurl,
              iconSize:     [32, 32], // size of the icon
              iconAnchor:   [16, 32], // point of the icon which will correspond to marker's location
              popupAnchor:  [0, -32] // point from which the popup should open relative to the iconAnchor
          });

          //Latlng object
          var latlng = {lat:plotArray[i].Latitude, lng:plotArray[i].Longitude};

          //Infowindow Content
          var imghref, contentString;
          if (plotArray[i].type == 'family') {
              imghref = "FamilyView.php?FamilyID=" + plotArray[i].ID;
          } else if (plotArray[i].type == 'person') {
              imghref = "PersonView.php?PersonID=" + plotArray[i].ID;
          } else if (plotArray[i].type == 'event') {
              imghref = window.CRM.root+"/Calendar.php";
          }

          contentString = "<b><a href='" + imghref + "'>" + plotArray[i].Salutation + "</a></b>";
          contentString += '<p><a href="http://maps.google.com/?q=1  ' + plotArray[i].Address + '" target="_blank">' + plotArray[i].Address + '</a></p>';

          if (plotArray[i].Thumbnail.length > 0) {
              //contentString += "<div class='image-container'><p class='text-center'><a href='" + imghref + "'>";
              contentString += "<div class='image-container'><a href='" + imghref + "'>";
              if (plotArray[i].type == 'event') {
                contentString += "<img class='profile-user-img img-responsive img-circle' border='1' src='" + plotArray[i].bigThumbnail + "'></a>";
                
                if (plotArray[i].Text != '') {
                   contentString += "<b>"+i18next.t("Notes")+"</b>";
                   contentString += "<br>"+plotArray[i].Text+"</div>";
                }
              } else {
                 contentString += "<img class='profile-user-img img-responsive img-circle' border='1' src='" + plotArray[i].Thumbnail + "'></a>";
              }
          }

          //Add marker and infowindow
          addMarkerWithInfowindow(map, latlng, icon, plotArray[i].Name, contentString);
      }

      //push Legend to right bottom
      /*var legend = document.getElementById('maplegend');
      map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);*/
  }
  
  initialize();

</script>