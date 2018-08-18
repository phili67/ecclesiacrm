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
use EcclesiaCRM\Map\ListOptionIconTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Utils\OutputUtils;

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
    <?= gettext('Missing Families?').'<a href="'.SystemURLs::getRootPath().'/UpdateAllLatLon.php" >'.' '.gettext('Update Family Latitude or Longitude now.') ?></a>
</div>

<?php if (ChurchMetaData::getChurchLatitude() == '') {
    ?>
    <div class="callout callout-danger">
        <?= gettext('Unable to display map due to missing Church Latitude or Longitude. Please update the church Address in the settings menu.') ?>
    </div>
    <?php
} else {
    if (SystemConfig::getValue('sBingMapKey') == '') {
            ?>
        <div class="callout callout-warning">
          <a href="<?= SystemURLs::getRootPath() ?>/SystemSettings.php"><?= gettext('Google Map API key is not set. The Map will work for smaller set of locations. Please create a Key in the maps sections of the setting menu.') ?></a>
        </div>
  <?php
    }
    
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
      ->addJoin(ListOptionTableMap::COL_LST_OPTIONID,ListOptionIconTableMap::COL_LST_IC_LST_OPTION_ID,Criteria::LEFT_JOIN)
      ->addAsColumn('url',ListOptionIconTableMap::COL_LST_IC_LST_URL)
      ->addAsColumn('onlyInPersonView',ListOptionIconTableMap::COL_LST_IC_ONLY_PERSON_VIEW)
      ->find();
      
    foreach ($icons as $icon) {
      if ($icon->getUrl() == null) {
        ?>
           <div class="callout callout-danger">
                <a href="<?= SystemURLs::getRootPath() ?>/OptionManager.php?mode=classes" class="btn bg-info-active"><img src='<?= SystemURLs::getRootPath()."/skin/icons/markers/../interrogation_point.png" ?>' height=20/></a>
                <?= gettext("Missing Person Map classification icon for")." : \"".$icon->getOptionName()."\". ".gettext("Clik").' <a href="'.SystemURLs::getRootPath().'/OptionManager.php?mode=classes">'.gettext("here").'</a> '.gettext("to solve the problem.") ?>                
            </div>
        <?php
        break;
      }
    }
      
    $arrPlotItemsSeperate = [];
    
    $arrPlotItemsSeperate["-2"] = array();
    $arrPlotItemsSeperate["-1"] = array();
?>


    <div class="box">
        <!-- Google map div -->
        <div id="mapid" class="map-div"></div>

        <!-- map Desktop legend-->
        <div id="maplegend" style="z-index: 1000; position: absolute; bottom: 127px; left: 0px;"><h4><?= gettext('Legend') ?></h4>
            <div class="row legendbox">
                <div class="legenditem">
                    <input type="checkbox" class="view" data-id="-2" name="feature"
               value="scales" checked /><img
                        src='https://www.google.com/intl/en_us/mapfiles/ms/micons/red-pushpin.png'/>
                    <?= gettext('Unassigned') ?>
                </div>
                <div class="legenditem">
                    <input type="checkbox" class="view" data-id="-1" name="feature"
               value="scales" checked /><img
                        src='<?= SystemURLs::getRootPath() ?>/skin/icons/event.png'/>
                    <?= gettext("Calendar") ?>
                </div>
                <?php
                foreach ($icons as $icon) {
                  if ($icon->getOnlyInPersonView()) {
                    continue;
                  }
                   $arrPlotItemsSeperate[$icon->getOptionId()] =  array();
                    ?>
                    <div class="legenditem">
                        <input type="checkbox" class="view" data-id="<?= $icon->getOptionId() ?>" name="feature" value="scales" checked />
                        <?php 
                          if (!empty($icon->getUrl())) {
                        ?>
                          <img src='<?= SystemURLs::getRootPath()."/skin/icons/markers/".$icon->getUrl()?>'/>
                        <?php
                          } else {
                        ?>
                          <img src='<?= SystemURLs::getRootPath()."/skin/icons/markers/../interrogation_point.png" ?>'/>
                        <?php
                          }
                        ?>
                        <?= $icon->getOptionName() ?>
                    </div>
                    <?php
                } 
                ?>                
            </div>
        </div>
        
        <!-- map Mobile legend-->
        <div id="maplegend-mobile" class="box visible-xs-block">
            <div class="row legendbox">
                <div class="btn bg-primary col-xs-12"><?= gettext('Legend') ?></div>
            </div>
            <div class="row legendbox">
                <div class="col-xs-6 legenditem">
                    <input type="checkbox" class="view" data-id="-2" name="feature"
               value="scales" checked /><img
                        class="legendicon" src='https://www.google.com/intl/en_us/mapfiles/ms/micons/red-pushpin.png'/>
                    <div class="legenditemtext"><?= gettext('Unassigned') ?></div>
                </div>
                <div class="legenditem">
                    <input type="checkbox" class="view" data-id="-1" name="feature"
               value="scales" checked /><img
                        src='<?= SystemURLs::getRootPath() ?>/skin/icons/event.png'/>
                    <?= gettext("Calendar") ?>
                </div>
                <?php
                foreach ($icons as $icon) {
                  if ($icon->getOnlyInPersonView()) {
                    continue;
                  }
                    ?>
                    <div class="col-xs-6 legenditem">
                        <input type="checkbox" class="view" data-id="<?= $icon->getOptionId() ?>" name="feature" value="scales" checked />
                        <?php 
                          if (!empty($icon->getUrl())) {
                        ?>
                          <img src='<?= SystemURLs::getRootPath()."/skin/icons/markers/".$icon->getUrl()?>'/>
                        <?php
                          } else {
                        ?>
                          <img src='<?= SystemURLs::getRootPath()."/skin/icons/markers/../interrogation_point.png" ?>'/>
                        <?php
                          }
                        ?>
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
      lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
      lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};


  var iconBase = window.CRM.root+'/skin/icons/markers/';
  var newPlotArray = null;
  
  
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
  
  
  function initialize() {
      // init map
      map = new Microsoft.Maps.Map('#mapid', {});
      
      window.CRM.map = map;// the Map is stored in the DOM

      //Churchmark
      var icon = { 
           icon: window.CRM.root + "/skin/icons/church.png",
      };

      addMarkerWithInfowindow(map,churchloc,icon,"titre","<?= SystemConfig::getValue('sChurchName') ?>");
      
      <?php
        $arr = array();
        $familiesLack = "";
        
        if ($plotFamily) {
            foreach ($families as $family) {
                if ($family->hasLatitudeAndLongitude()) {
                    //this helps to add head people persons details: otherwise doesn't seems to populate
                    $member = $family->getHeadPeople()[0];

                    if (is_null($member)) {
                      $familiesLack .= "<a href=\"".SystemURLs::getRootPath()."/FamilyView.php?FamilyID=".$family->getId()."\">".$family->getName()."</a>, ";
                      continue;
                    }

                    if ($member->getOnlyVisiblePersonView()) {
                      continue;
                    }

                    $photoFileThumb = SystemURLs::getRootPath() . '/api/families/' . $family->getId() . '/photo';
                    $arr['ID'] = $family->getId();
                    $arr['Name'] = $family->getName();
                    $arr['Salutation'] = $family->getSaluation();
                    $arr['Address'] = $family->getAddress();
                    $arr['Thumbnail'] = $photoFileThumb;
                    $arr['Latitude'] = $family->getLatitude();
                    $arr['Longitude'] = $family->getLongitude();
                    $arr['Name'] = $family->getName();
                    $arr['iconClassification'] = $member->getUrlIcon();
                    $arr['type'] = 'family';          
                    $arr['mark'] = null;
                    
                    if ($member->getClsId() == 0) {
                      array_push($arrPlotItemsSeperate["-2"], $arr);
                    } else {
                      array_push($arrPlotItemsSeperate[$member->getClsId()], $arr);
                    }
                }
            }
        } else {
            //plot Person
            foreach ($persons as $member) {
                if ($member->getOnlyVisiblePersonView()) {
                  continue;
                }

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
                $arr['iconClassification'] = $member->getUrlIcon();
                $arr['type'] = 'person';
                $arr['mark'] = null;
                
                if ($member->getClsId() == 0) {
                  array_push($arrPlotItemsSeperate["-2"], $arr);
                } else {
                  array_push($arrPlotItemsSeperate[$member->getClsId()], $arr);
                }
            }
        } //end IF $plotFamily
        
        // now we can add the Events
        foreach ($eventsArr as $ev) {          
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
          $arr['iconClassification'] = '';
          $arr['type'] = 'event';
          $arr['desc'] = $event->getDesc();
          $arr['mark'] = null;
          
          array_push($arrPlotItemsSeperate["-1"], $arr);
        }
        
      ?>

      newPlotArray = <?= json_encode($arrPlotItemsSeperate) ?>;
      
      var bPlotFamily = <?= ($plotFamily) ? 'true' : 'false' ?>;

      var familiesLack = '<?= $familiesLack ?>';
      
      if (familiesLack != '') {
          window.CRM.DisplayAlert(i18next.t("Error"),i18next.t("Some families haven't any \"head of household\" role name defined or there's any activated members in this families:")+"<br>"+familiesLack);
      }
      
      //loop through the families/persons and add markers
      for (var key in newPlotArray) {
        var plotArray = newPlotArray[key];
        
        for (var i = 0; i < plotArray.length; i++) {
            if (plotArray[i].Latitude + plotArray[i].Longitude == 0)
                continue;

            add_marker(plotArray[i]);
        }
      }
    
  }
  
  function add_marker (plot) {
    var iconurl = iconBase + plot.iconClassification;
    
    if (plot.type == 'event') {
      iconurl = plot.Thumbnail;
    }
    

    //Churchmark
    var icon = { 
         icon: iconurl,
    };

    //Latlng object
    var latlng = {lat:plot.Latitude, lng:plot.Longitude};

    //Infowindow Content
    var imghref, contentString;
    if (plot.type == 'family') {
        imghref = "FamilyView.php?FamilyID=" + plot.ID;
    } else if (plot.type == 'person') {
        imghref = "PersonView.php?PersonID=" + plot.ID;
    } else if (plot.type == 'event') {
        imghref = window.CRM.root+"/Calendar.php";
    }

    //contentString = "<b><a href='" + imghref + "'>" + plot.Salutation + "</a></b>";
    contentString = '<p><a href="http://maps.google.com/?q=1  ' + plot.Address + '" target="_blank">' + plot.Address + '</a></p>';

    if (plot.Thumbnail.length > 0) {
        //contentString += "<div class='image-container'><p class='text-center'><a href='" + imghref + "'>";
        contentString += "<div class='image-container'><a href='" + imghref + "'>";
        if (plot.type == 'event') {
          contentString += "<img class='profile-user-img img-responsive img-circle' border='1' src='" + plot.bigThumbnail + "'></a>";
          
          if (plot.Text != '') {
             contentString += "<b>"+i18next.t("Notes")+"</b>";
             contentString += "<br>"+plot.Text+"</div>";
          }
        } else {
           contentString += "<img class='profile-user-img img-responsive img-circle' border='1' src='" + plot.Thumbnail + "'></a>";
        }
    }

    //Add marker and infowindow
    plot.mark = addMarkerWithInfowindow(window.CRM.map, latlng, icon, plot.Salutation, contentString);
  }
  
  function add_all_markers_for_id (id) {
    var plotArray = newPlotArray[id];
        
    for (var i = 0; i < plotArray.length; i++) {
        if (plotArray[i].Latitude + plotArray[i].Longitude == 0)
            continue;
        //icon image
        add_marker(plotArray[i]);
    }
  }
  
  function delete_all_markers_for_id (id) {
    var plotArray = newPlotArray[id];
        
    for (var i = 0; i < plotArray.length; i++) {
      if (plotArray[i].mark != null) {
        window.CRM.map.entities.remove(plotArray[i].mark)
      }
      plotArray[i].mark = null;
    }
  }

  $('.view').change(function() {
    if ($(this).is(':checked') == false) {
      delete_all_markers_for_id ($(this).data("id"));
    } else {
      add_all_markers_for_id ($(this).data("id"));      
    }
  });
  
  
  function GetMap() {
      initialize();
  }

</script>