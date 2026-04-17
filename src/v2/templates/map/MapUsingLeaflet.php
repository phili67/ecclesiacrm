<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2023/05/15
//

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\FamilyQuery;

use EcclesiaCRM\EventQuery;
use EcclesiaCRM\SessionUser;

require $sRootDocument . '/Include/Header.php';

$empty_families = FamilyQuery::create()->filterByLongitude(0)->_and()->filterByLatitude(0)->find();
$mapLegendClassSuffix = \EcclesiaCRM\Theme::isDarkModeEnabled() ? '-dark' : '';
$visibleLegendCount = 2;

foreach ($icons as $legendIcon) {
  if ($legendIcon->getOnlyInPersonView()) {
    continue;
  }

  $visibleLegendCount++;
}

?>

<?php if ($empty_families->count()) { ?>
<div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap mb-3">
  <div>
    <i class="fas fa-location-crosshairs mr-1"></i>
    <?= _('Missing Families?') ?>
    <strong><?= $empty_families->count() ?></strong>
  </div>
  <a href="<?= $sRootPath ?>/v2/people/UpdateAllLatLon" class="btn btn-sm btn-info mt-2 mt-sm-0">
    <i class="fas fa-map-marker-alt mr-1"></i><?= _('Update Coordinates') ?>
  </a>
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


    <div class="card card-primary card-outline shadow-sm mb-0 map-shell-card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap map-shell-header">
        <div>
          <h3 class="card-title mb-0"><i class="fas fa-map-marked-alt mr-1"></i><?= _('Map Explorer') ?></h3>
          <small class="text-muted"><?= _('Explore people, families, calendars, and classes from one compact view.') ?></small>
        </div>
        <div class="mt-2 mt-sm-0 map-shell-actions">
          <?php if (SessionUser::getUser()->isAdmin()) : ?>
          <a href="<?= $sRootPath ?>/v2/systemsettings/mapsettings" class="btn btn-sm btn-outline-secondary mr-1 map-shell-button map-shell-button-secondary">
            <i class="fas fa-sliders-h mr-1"></i><?= _('Map Settings') ?>
          </a>
          <?php endif; ?>
          <button type="button" id="resetMapView" class="btn btn-sm btn-outline-primary map-shell-button">
            <i class="fas fa-crosshairs mr-1"></i><?= _('Center on Church') ?>
          </button>
        </div>
      </div>

      <div class="card-body p-0 map-shell-body">
    <div id="mapid" class="map-div"></div>

        <!-- map Desktop legend-->
    <div class="map-legend-view maplegend<?= $mapLegendClassSuffix ?> p-2 rounded">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <h4 class="mb-1"><i class="fas fa-layer-group mr-1"></i><?= _('Legend') ?></h4>
          <small><?= _('Toggle visible layers') ?></small>
                </div>
        <span class="badge badge-primary"><?= $visibleLegendCount ?></span>
      </div>
      <div class="row legendbox">
        <div class="legenditem d-flex align-items-center">
          <img src='https://www.google.com/intl/en_us/mapfiles/ms/micons/red-pushpin.png' width="24" class="mr-2" alt="<?= _('Unassigned') ?>"/>
          <input type="checkbox" class="view mr-2" data-id="-2" id="Unassigned" name="feature" value="scales" checked />
          <label class="mb-0" for="Unassigned"><?= _('Unassigned') ?></label>
                </div>
        <div class="legenditem d-flex align-items-center">
          <img src='<?= $sRootPath ?>/skin/icons/event.png' width="24" class="mr-2" alt="<?= _('Calendar') ?>"/>
          <input type="checkbox" class="view mr-2" data-id="-1" name="feature" id="calendar" value="scales" checked />
          <label class="mb-0" for="calendar"><?= _("Calendar") ?></label>
        </div>
                <?php
                foreach ($icons as $icon) {
                  if ($icon->getOnlyInPersonView()) {
                    continue;
                  }
                   $arrPlotItemsSeperate[$icon->getOptionId()] =  array();
                   $legendIconUrl = !empty($icon->getUrl()) ? $sRootPath."/skin/icons/markers/".$icon->getUrl() : $sRootPath."/skin/icons/markers/../interrogation_point.png";
                    ?>
                    <div class="legenditem d-flex align-items-center">
                      <img src='<?= $legendIconUrl ?>' width="24" class="mr-2" alt="<?= $icon->getOptionName() ?>"/>
                      <input type="checkbox" class="view mr-2" data-id="<?= $icon->getOptionId() ?>" id="<?= $icon->getOptionId() ?>" name="feature" value="scales" checked />
                      <label class="mb-0" for="<?= $icon->getOptionId() ?>"><?= $icon->getOptionName() ?></label>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
      </div>

        <!-- map Mobile legend-->
              <div class="maplegend-mobile box visible-xs-block">
                <div class="row legendbox">
                  <div class="btn btn-primary col-xs-12"><i class="fas fa-layer-group mr-1"></i><?= _('Legend') ?> <span class="badge badge-light ml-1"><?= $visibleLegendCount ?></span></div>
                </div>
                <div class="row legendbox">
                  <div class="col-xs-6 legenditem d-flex align-items-center">
                    <input type="checkbox" class="view mr-2" data-id="-2" name="feature" value="scales" checked />
                    <img class="legendicon mr-2" src='https://www.google.com/intl/en_us/mapfiles/ms/micons/red-pushpin.png' width="24" alt="<?= _('Unassigned') ?>"/>
                    <div class="legenditemtext"><?= _('Unassigned') ?></div>
                </div>
                  <div class="col-xs-6 legenditem d-flex align-items-center">
                    <input type="checkbox" class="view mr-2" data-id="-1" name="feature" value="scales" checked />
                    <img src='<?= $sRootPath ?>/skin/icons/event.png' width="24" class="mr-2" alt="<?= _('Calendar') ?>"/>
                    <div class="legenditemtext"><?= _("Calendar") ?></div>
                </div>
                <?php
                foreach ($icons as $icon) {
                  if ($icon->getOnlyInPersonView()) {
                    continue;
                  }
                    $legendIconUrl = !empty($icon->getUrl()) ? $sRootPath."/skin/icons/markers/".$icon->getUrl() : $sRootPath."/skin/icons/markers/../interrogation_point.png";
                    ?>
                    <div class="col-xs-6 legenditem d-flex align-items-center">
                      <input type="checkbox" class="view mr-2" data-id="<?= $icon->getOptionId() ?>" name="feature" value="scales" checked />
                      <img src='<?= $legendIconUrl ?>' width="24" class="mr-2" alt="<?= $icon->getOptionName() ?>"/>
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


 <script nonce="<?= \EcclesiaCRM\dto\SystemURLs::getCSPNonce() ?>">
  var churchloc = {
      lat: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLatitude()) ?>,
      lng: <?= OutputUtils::number_dot(ChurchMetaData::getChurchLongitude()) ?>};

  var iconBase = window.CRM.root+'/skin/icons/markers/';
  var newPlotArray = null;

    function escapePopupHtml(value) {
      return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function getPopupTypeIcon(plot) {
      if (plot.type == 'family') {
        return 'fa-home';
      } else if (plot.type == 'person') {
        return 'fa-user';
      }

      return 'fa-calendar-alt';
    }

    function buildMarkerPopupContent(plot, imghref) {
      var title = escapePopupHtml(plot.Salutation || plot.Name);
      var addressLink = plot.Address ? window.CRM.tools.getLinkMapFromAddress(plot.Address) : '';
      var imageSrc = plot.type == 'event' ? plot.bigThumbnail : plot.Thumbnail;
      var notesBlock = '';

      if (plot.type == 'event' && plot.Text) {
        notesBlock = "<div class='mt-2 pt-2 border-top'>"
          + "<div class='text-muted small text-uppercase font-weight-bold'>" + i18next.t("Notes") + "</div>"
          + "<div class='small'>" + plot.Text + "</div>"
          + "</div>";
      }

      var imageBlock = '';
      if (imageSrc && imageSrc.length > 0) {
        imageBlock = "<a href='" + imghref + "' class='mr-2 flex-shrink-0'>"
          + "<img class='profile-user-img img-responsive img-circle shadow-sm' border='1' src='" + imageSrc + "' width='44'>"
          + "</a>";
      }

      return "<div class='card border-0 shadow-none mb-0' style='min-width:220px; max-width:280px;'>"
        + "<div class='card-body p-2'>"
        + "<div class='d-flex align-items-start'>"
        + imageBlock
        + "<div class='flex-grow-1'>"
        + "<div class='d-flex align-items-center text-primary mb-1'><i class='fas " + getPopupTypeIcon(plot) + " mr-2'></i><span class='small text-uppercase font-weight-bold'>" + escapePopupHtml(plot.type) + "</span></div>"
        + "<div class='font-weight-bold mb-1'><a href='" + imghref + "'>" + title + "</a></div>"
        + (addressLink ? "<div class='small text-muted'>" + addressLink + "</div>" : "")
        + notesBlock
        + "</div>"
        + "</div>"
        + "</div>"
        + "</div>";
    }

  function addMarkerWithInfowindow(map, marker_position, image, title, infowindow_content) {
      var mark = L.marker([marker_position.lat, marker_position.lng], {icon: image})
       .bindPopup(infowindow_content, {maxWidth: 300, minWidth: 220})
         .addTo(map);

      return mark;
  }

    function fitMapHeightToFooter() {
      var mapElement = document.getElementById("mapid");
      var footerElement = document.querySelector(".main-footer");
      var cardElement = mapElement ? mapElement.closest('.map-shell-card') : null;
      var headerElement = cardElement ? cardElement.querySelector('.map-shell-header') : null;
      var mobileLegendElement = cardElement ? cardElement.querySelector('.maplegend-mobile') : null;

      if (!mapElement || !footerElement || window.innerWidth < 600) {
        return;
      }

      var cardTop = cardElement ? cardElement.getBoundingClientRect().top : mapElement.getBoundingClientRect().top;
      var footerTop = footerElement.getBoundingClientRect().top;
      var reservedHeight = 12;

      if (headerElement) {
        reservedHeight += headerElement.getBoundingClientRect().height;
      }

      if (mobileLegendElement && window.getComputedStyle(mobileLegendElement).display !== 'none') {
        reservedHeight += mobileLegendElement.getBoundingClientRect().height;
      }

      var availableHeight = Math.floor(footerTop - cardTop - reservedHeight);

      if (availableHeight > 320) {
        mapElement.style.height = availableHeight + "px";
      }
    }

  function initialize() {
      fitMapHeightToFooter();

      // init map
      var map = L.map('mapid',{
              tap: false
      }).setView([churchloc.lat, churchloc.lng], <?= SystemConfig::getValue("iMapZoom")?>);

      window.CRM.map = map;// the Map is stored in the DOM

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.ecclesiacrm.com">EcclesiaCRM</a>'
      }).addTo(map);

      window.requestAnimationFrame(function () {
        fitMapHeightToFooter();
        map.invalidateSize(false);
      });

        $('#resetMapView').off('click').on('click', function () {
          map.setView([churchloc.lat, churchloc.lng], <?= SystemConfig::getValue("iMapZoom")?>);
        });

      //Churchmark
      var icon = L.icon({
          iconUrl: window.CRM.root + "/skin/icons/church.png",
          iconSize:     [32, 37], // size of the icon
          iconAnchor:   [16, 37], // point of the icon which will correspond to marker's location
          popupAnchor:  [0, -37] // point from which the popup should open relative to the iconAnchor
      });

      addMarkerWithInfowindow(map,churchloc,icon,"titre","<?= SystemConfig::getValue('sEntityName') ?>");


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
            iconSize:     [32, null], // size of the icon
            iconAnchor:   [16, 32], // point of the icon which will correspond to marker's location
            popupAnchor:  [0, -32] // point from which the popup should open relative to the iconAnchor
        });

        var latlng = {lat:plot.Latitude, lng:plot.Longitude};

        //Infowindow Content
        var imghref;
        if (plot.type == 'family') {
            imghref = window.CRM.root + "/v2/people/family/view/" + plot.ID;
        } else if (plot.type == 'person') {
            imghref = window.CRM.root + "/v2/people/person/view/" + plot.ID;
        } else if (plot.type == 'event') {
            imghref = window.CRM.root + "/v2/calendar";
        }

        plot.mark = addMarkerWithInfowindow(window.CRM.map, latlng, icon, plot.Name, buildMarkerPopupContent(plot, imghref));
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


  $('.view').on('change',function() {
    if ($(this).is(':checked') == false) {
      delete_all_markers_for_id ($(this).data("id"));
    } else {
      add_all_markers_for_id ($(this).data("id"));
    }
  });

  initialize();

    $(window).on('resize', function () {
      fitMapHeightToFooter();

      if (window.CRM.map) {
        window.CRM.map.invalidateSize(false);
        window.CRM.map.setView([churchloc.lat, churchloc.lng], window.CRM.map.getZoom(), {animate: false});
      }
    });

  window.matchMedia('(prefers-color-scheme: dark)').addListener(function (e) {    
      let matched = window.matchMedia('(prefers-color-scheme: dark)').matches;
      
      if (matched) {
          $('.map-legend-view').removeClass('maplegend');
          $('.map-legend-view').addClass('maplegend-dark');
      } else {
          $('.map-legend-view').removeClass('maplegend-dark');
          $('.map-legend-view').addClass('maplegend');
      }
  }); 

</script>
