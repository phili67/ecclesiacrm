<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2023/05/15
//

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\FamilyQuery;

use EcclesiaCRM\EventQuery;

require $sRootDocument . '/Include/Header.php';

$families = FamilyQuery::create()->filterByLongitude(0)->_and()->filterByLatitude(0)->find();

?>

<?php if ($families->count()) { ?>
<div class="alert alert-info">
    <a href="<?= $sRootPath ?>/v2/people/UpdateAllLatLon" class="btn bg-green-active"><i class="fas fa-map-marker-alt"></i> </a>
    <?= _('Missing Families?').'<a href="'.$sRootPath.'/v2/people/UpdateAllLatLon" >'.' '._('Update Family Latitude or Longitude now.'). ' : ' . $families->count() ?></a>
</div>
<?php } ?>

<?php if (ChurchMetaData::getChurchLatitude() == '') {
    ?>
    <div class="alert alert-danger">
        <?= _('Unable to display map due to missing Church Latitude or Longitude. Please update the church Address in the settings menu.') ?>
    </div>
    <?php
} else {

    foreach ($icons as $icon) {
      if ($icon->getUrl() == null) {
        ?>
           <div class="alert alert-danger">
                <a href="<?= $sRootPath ?>/v2/system/option/manager/classes" class="btn bg-info-active"><img src='<?= $sRootPath."/skin/icons/markers/../interrogation_point.png" ?>' height=20/></a>
                <?= _("Missing Person Map classification icon for")." : \"".$icon->getOptionName()."\". "._("Clik").' <a href="'.$sRootPath.'/v2/system/option/manager/classes">'._("here").'</a> '._("to solve the problem.") ?>
            </div>
        <?php
        break;
      }
    }

    $arrPlotItemsSeperate = [];

    $arrPlotItemsSeperate["-2"] = array();
    $arrPlotItemsSeperate["-1"] = array();
?>


    <div class="card">
        <!-- Google map div -->
        <div id="mapid" class="map-div"></div>

        <!-- map Desktop legend-->
        <div class="map-legend-view maplegend<?= \EcclesiaCRM\Theme::isDarkModeEnabled()?'-dark':'' ?>"><h4><?= _('Legend') ?></h4>
            <div class="row legendbox">
                <div class="legenditem">
                    <img src='https://www.google.com/intl/en_us/mapfiles/ms/micons/red-pushpin.png'/>
                    <input type="checkbox" class="view" data-id="-2" id="Unassigned" name="feature" value="scales" checked />
                    <label for="Unassigned"><?= _('Unassigned') ?></label>
                </div>
                <div class="legenditem">
                    <img src='<?= $sRootPath ?>/skin/icons/event.png'/>
                    <input type="checkbox" class="view" data-id="-1" name="feature" id="calendar" value="scales" checked />
                    <label for="calendar"><?= _("Calendar") ?></label>
                </div>
                <?php
                foreach ($icons as $icon) {
                  if ($icon->getOnlyInPersonView()) {
                    continue;
                  }
                   $arrPlotItemsSeperate[$icon->getOptionId()] =  array();
                    ?>
                    <div class="legenditem">
                        <?php
                          if (!empty($icon->getUrl())) {
                        ?>
                          <img src='<?= $sRootPath."/skin/icons/markers/".$icon->getUrl()?>'/>
                        <?php
                          } else {
                        ?>
                          <img src='<?= $sRootPath."/skin/icons/markers/../interrogation_point.png" ?>'/>
                        <?php
                          }
                        ?>
                        <input type="checkbox" class="view" data-id="<?= $icon->getOptionId() ?>" id="<?= $icon->getOptionId() ?>" name="feature" value="scales" checked />
                        <label for="<?= $icon->getOptionId() ?>">
                        <?= $icon->getOptionName() ?>
                        </label>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- map Mobile legend-->
        <div class="maplegend-mobile box visible-xs-block">
            <div class="row legendbox">
                <div class="btn bg-primary col-xs-12"><?= _('Legend') ?></div>
            </div>
            <div class="row legendbox">
                <div class="col-xs-6 legenditem">
                    <input type="checkbox" class="view" data-id="-2" name="feature"
               value="scales" checked /><img
                        class="legendicon" src='https://www.google.com/intl/en_us/mapfiles/ms/micons/red-pushpin.png'/>
                    <div class="legenditemtext"><?= _('Unassigned') ?></div>
                </div>
                <div class="legenditem">
                    <input type="checkbox" class="view" data-id="-1" name="feature"
               value="scales" checked /><img
                        src='<?= $sRootPath ?>/skin/icons/event.png'/>
                    <?= _("Calendar") ?>
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
                          <img src='<?= $sRootPath."/skin/icons/markers/".$icon->getUrl()?>'/>
                        <?php
                          } else {
                        ?>
                          <img src='<?= $sRootPath."/skin/icons/markers/../interrogation_point.png" ?>'/>
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

  require $sRootDocument . '/Include/Footer.php';
?>


 <script nonce="<?= SystemURLs::getCSPNonce() ?>">
  var churchloc = {
      lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
      lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};

  var iconBase = window.CRM.root+'/skin/icons/markers/';
  var newPlotArray = null;

  function addMarkerWithInfowindow(map, marker_position, image, title, infowindow_content) {
      var mark = L.marker([marker_position.lat, marker_position.lng], {icon: image})
         .bindPopup(infowindow_content)
         .addTo(map);

      return mark;
  }

  function initialize() {
      // init map
      var map = L.map('mapid',{
              tap: false
      }).setView([churchloc.lat, churchloc.lng], <?= SystemConfig::getValue("iMapZoom")?>);

      window.CRM.map = map;// the Map is stored in the DOM

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
        $familiesLack = "";

        if ($plotFamily) {
            foreach ($families as $family) {
                if ($family->hasLatitudeAndLongitude()) {
                    //this helps to add head people persons details: otherwise doesn't seems to populate
                    $member = $family->getHeadPeople()[0];

                    if (is_null($member)) {
                      $familiesLack .= "<a href=\"".$sRootPath."/v2/people/family/view/".$family->getId()."\">".$family->getName()."</a>, ";
                      continue;
                    }

                    if ($member->getOnlyVisiblePersonView()) {
                      continue;
                    }

                    $photoFileThumb = $sRootPath . '/api/families/' . $family->getId() . '/photo';
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

                    // new part

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
                $photoFileThumb = $sRootPath . '/api/persons/' . $member->getId() . '/thumbnail';
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

          $photoFileThumb = $sRootPath ."/skin/icons/event.png";
          $arr['ID'] = $ev;
          $arr['Salutation'] = $event->getTitle()." (".$event->getDesc().")";
          $arr['Name'] = $event->getTitle()." (".$event->getDesc().")";
          $arr['Text'] = $event->getText();
          $arr['Address'] = $event->getLocation();
          $arr['Thumbnail'] = $photoFileThumb;
          $arr['bigThumbnail'] = $sRootPath ."/skin/icons/bigevent.png";
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
          window.CRM.DisplayAlert(i18next.t("Info"),i18next.t("Some families haven't any \"head of household\" role name defined or there's any activated members in this families:")+"<br>"+familiesLack);
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


        var icon = L.icon({
            iconUrl: iconurl,
            iconSize:     [32, 32], // size of the icon
            iconAnchor:   [16, 32], // point of the icon which will correspond to marker's location
            popupAnchor:  [0, -32] // point from which the popup should open relative to the iconAnchor
        });

        var latlng = {lat:plot.Latitude, lng:plot.Longitude};

        //Infowindow Content
        var imghref, contentString;
        if (plot.type == 'family') {
            imghref = window.CRM.root + "/v2/people/family/view/" + plot.ID;
        } else if (plot.type == 'person') {
            imghref = window.CRM.root + "/v2/people/person/view/" + plot.ID;
        } else if (plot.type == 'event') {
            imghref = window.CRM.root + "/v2/calendar";
        }

        contentString = "<b><a href='" + imghref + "'>" + plot.Salutation + "</a></b>";
        contentString += '<p>' + window.CRM.tools.getLinkMapFromAddress (plot.Address) + '</p>';

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

        plot.mark = addMarkerWithInfowindow(window.CRM.map, latlng, icon, plot.Name, contentString);
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
        window.CRM.map.removeLayer(plotArray[i].mark);
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

  initialize();

  window.CRM.AutomaticDarkModeFunction = function (darkMode)
  {
      if (darkMode) {
          $('.map-legend-view').removeClass('maplegend');
          $('.map-legend-view').addClass('maplegend-dark');
      } else {
          $('.map-legend-view').removeClass('maplegend-dark');
          $('.map-legend-view').addClass('maplegend');
      }
  }

  <!-- for the theme before jquery load is finished -->
  if (window.CRM.sLightDarkMode == "automatic") {
      let matched = window.matchMedia('(prefers-color-scheme: dark)').matches;

      if(matched) {// we're on dark mode
          $('.map-legend-view').removeClass('maplegend');
          $('.map-legend-view').addClass('maplegend-dark');
      } else {// we're in light mode
          $('.map-legend-view').removeClass('maplegend-dark');
          $('.map-legend-view').addClass('maplegend');
      }
  }

</script>
