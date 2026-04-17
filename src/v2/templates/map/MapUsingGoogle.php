<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//  Updated     : 2023/05/15
//

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;
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
    if (SystemConfig::getValue('sGoogleMapKey') == '') {
        ?>
        <div class="alert alert-warning">
            <?php if (SessionUser::getUser()->isAdmin()) : ?>
            <a href="<?= $sRootPath ?>/v2/systemsettings/mapsettings"><?= _('Google Map API key is not set. The Map will work for smaller set of locations. Please create a Key in the maps sections of the setting menu.') ?></a>
            <?php endif; ?> 
            <div class="card-body p-0 map-shell-body">
        <?php
    }


    foreach ($icons as $icon) {
        if ($icon->getUrl() == null) {
            ?>
            <div class="alert alert-danger">
                <a href="<?= $sRootPath ?>/v2/system/option/manager/classes" class="btn bg-info-active"><img
                        src='<?= $sRootPath . "/skin/icons/markers/../interrogation_point.png" ?>' width="40"/></a>
                <?= _("Missing Person Map classification icon for") . " : \"" . $icon->getOptionName() . "\". " . _("Clik") . ' <a href="' . $sRootPath . '/v2/system/option/manager/classes">' . _("here") . '</a> ' . _("to solve the problem.") ?>
            </div>
            <?php
            break;
        }
    }

    $arrPlotItemsSeperate = [];

    $arrPlotItemsSeperate["-2"] = array();
    $arrPlotItemsSeperate["-1"] = array();

    ?>
    <!--Google scripts -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?= SystemConfig::getValue('sGoogleMapKey') ?>">
    </script>

    <div class="card card-primary card-outline shadow-sm mb-0">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="card-title mb-0"><i class="fas fa-map-marked-alt mr-1"></i><?= _('Map Explorer') ?></h3>
                <small class="text-muted"><?= _('Explore people, families, calendars, and classes from one compact view.') ?></small>
            </div>
            <div class="mt-2 mt-sm-0">
                <?php if (SessionUser::getUser()->isAdmin()) : ?>
                <a href="<?= $sRootPath ?>/v2/systemsettings/mapsettings" class="btn btn-sm btn-outline-secondary mr-1">
                    <i class="fas fa-sliders-h mr-1"></i><?= _('Map Settings') ?>
                </a>
                <?php endif; ?>
                <button type="button" id="resetMapView" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-crosshairs mr-1"></i><?= _('Center on Church') ?>
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <!-- Google map div -->
            <div id="mapid" class="map-div"></div>
        </div>

        <!-- map Desktop legend-->
        <div id="maplegend" class="map-legend-view maplegend<?= $mapLegendClassSuffix ?> p-2 rounded">
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
                    <input type="checkbox" class="view mr-2" data-id="-2" id="Unassigned" name="feature" value="scales" checked/>
                    <label class="mb-0" for="Unassigned"><?= _('Unassigned') ?></label>
                </div>
                <div class="legenditem d-flex align-items-center">
                    <img src='<?= $sRootPath ?>/skin/icons/event.png' width="24" class="mr-2" alt="<?= _('Calendar') ?>"/>
                    <input type="checkbox" class="view mr-2" data-id="-1" id="calendar" name="feature" value="scales" checked/>
                    <label class="mb-0" for="calendar"><?= _("Calendar") ?></label>
                </div>
                <?php
                foreach ($icons as $icon) {
                    if ($icon->getOnlyInPersonView()) {
                        continue;
                    }
                    $arrPlotItemsSeperate[$icon->getOptionId()] = array();
                    $legendIconUrl = !empty($icon->getUrl()) ? $sRootPath . "/skin/icons/markers/" . $icon->getUrl() : $sRootPath . "/skin/icons/markers/../interrogation_point.png";
                    ?>
                    <div class="legenditem d-flex align-items-center">
                        <img src='<?= $legendIconUrl ?>' width="24" class="mr-2" alt="<?= $icon->getOptionName() ?>"/>
                        <input type="checkbox" class="view mr-2" data-id="<?= $icon->getOptionId() ?>" id="<?= $icon->getOptionId() ?>" name="feature" value="scales" checked/>
                        <label class="mb-0" for="<?= $icon->getOptionId() ?>"><?= $icon->getOptionName() ?></label>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- map Mobile legend-->
        <div class="maplegend-mobile box visible-xs-block">
            <div class="row legendbox">
                <div class="btn btn-primary col-xs-12"><i class="fas fa-layer-group mr-1"></i><?= _('Legend') ?> <span class="badge badge-light ml-1"><?= $visibleLegendCount ?></span></div>
            </div>
            <div class="row legendbox">
                <div class="col-xs-6 legenditem d-flex align-items-center">
                    <input type="checkbox" class="view mr-2" data-id="-2" name="feature" value="scales" checked/><img
                        class="legendicon mr-2" src='https://www.google.com/intl/en_us/mapfiles/ms/micons/red-pushpin.png' width="24" alt="<?= _('Unassigned') ?>"/>
                    <div class="legenditemtext"><?= _('Unassigned') ?></div>
                </div>
                <div class="col-xs-6 legenditem d-flex align-items-center">
                    <input type="checkbox" class="view mr-2" data-id="-1" name="feature" value="scales" checked/><img
                        src='<?= $sRootPath ?>/skin/icons/event.png' width="24" class="mr-2" alt="<?= _('Calendar') ?>"/>
                    <div class="legenditemtext"><?= _("Calendar") ?></div>
                </div>
                <?php
                foreach ($icons as $icon) {
                    if ($icon->getOnlyInPersonView()) {
                        continue;
                    }
                    $legendIconUrl = !empty($icon->getUrl()) ? $sRootPath . "/skin/icons/markers/" . $icon->getUrl() : $sRootPath . "/skin/icons/markers/../interrogation_point.png";
                    ?>
                    <div class="col-xs-6 legenditem d-flex align-items-center">
                        <input type="checkbox" class="view mr-2" data-id="<?= $icon->getOptionId() ?>" name="feature" value="scales" checked/>
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
    let churchloc = {
        lat: <?= floatval(ChurchMetaData::getChurchLatitude()) ?>,
        lng: <?= floatval(ChurchMetaData::getChurchLongitude()) ?>
    };


    let iconBase = window.CRM.root + '/skin/icons/markers/';
    let newPlotArray = null;

    const escapePopupHtml = (value) => {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    const getPopupTypeIcon = (plot) => {
        if (plot.type == 'family') {
            return 'fa-home';
        } else if (plot.type == 'person') {
            return 'fa-user';
        }

        return 'fa-calendar-alt';
    }

    const buildMarkerPopupContent = (plot, imghref) => {
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

    let infowindow = new google.maps.InfoWindow({
        maxWidth: 300
    });

    const addMarkerWithInfowindow = (map, marker_position, image, title, infowindow_content) => {
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

    // Initialize and add the map
    let map;

    const fitMapHeightToFooter = () => {
        const mapElement = document.getElementById("mapid");
        const footerElement = document.querySelector(".main-footer");

        if (!mapElement || !footerElement || window.innerWidth < 600) {
            return;
        }

        const mapTop = mapElement.getBoundingClientRect().top;
        const footerTop = footerElement.getBoundingClientRect().top;
        const availableHeight = Math.floor(footerTop - mapTop - 12);

        if (availableHeight > 320) {
            mapElement.style.height = availableHeight + "px";
        }
    }


    function initMap() {
        fitMapHeightToFooter();

        // Initialisation classique de la carte Google Maps
        map = new google.maps.Map(document.getElementById("mapid"), {
            zoom: <?= SystemConfig::getValue("iMapZoom")?>,
            center: churchloc
        });

        $('#resetMapView').off('click').on('click', function () {
            map.setCenter(churchloc);
            map.setZoom(<?= SystemConfig::getValue("iMapZoom")?>);
        });

        window.CRM.map = map;

        //Churchmark
        var churchMark = new google.maps.Marker({
            icon: window.CRM.root + "/skin/icons/church.png",
            position: new google.maps.LatLng(churchloc),
            map: map
        });

        google.maps.event.addListener(map, 'click', function () {
            infowindow.close();
        });

        <?php
        $arr = array();
        $familiesLack = "";

        if ($plotFamily) {
            foreach ($families as $family) {
                if ($family->hasLatitudeAndLongitude()) {
                    //this helps to add head people persons details: otherwise doesn't seems to populate
                    $member = $family->getHeadPeople()[0];

                    if (is_null($member)) {
                        $familiesLack .= "<a href=\"" . $sRootPath . "/v2/people/family/view/" . $family->getId() . "\">" . $family->getName() . "</a>, ";
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

            $photoFileThumb = $sRootPath . "/skin/icons/event.png";
            $arr['ID'] = $ev;
            $arr['Salutation'] = $event->getTitle() . " (" . $event->getDesc() . ")";
            $arr['Name'] = $event->getTitle() . " (" . $event->getDesc() . ")";
            $arr['Text'] = $event->getText();
            $arr['Address'] = $event->getLocation();
            $arr['Thumbnail'] = $photoFileThumb;
            $arr['bigThumbnail'] = $sRootPath . "/skin/icons/bigevent.png";
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
            window.CRM.DisplayAlert(i18next.t("Info"), i18next.t("Some families haven't any \"head of household\" role name defined or there's any activated members in this families:") + "<br>" + familiesLack);
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

        //push Legend to right bottom
        var legend = document.getElementById('maplegend');
        map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);
    }

    const add_marker = (plot) => {
        // icon image
        var iconurl = iconBase + plot.iconClassification;
        if (plot.type == 'event') {
            iconurl = plot.Thumbnail;
        }

        var image = {
            url: iconurl,
            scaledSize: new google.maps.Size(40, 40), // impose un scale 40x40 px
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(0, 32)
        };

        // Latlng object
        var latlng = new google.maps.LatLng(plot.Latitude, plot.Longitude);

        // Infowindow Content
        var imghref = '';
        if (plot.type == 'family') {
            imghref = window.CRM.root + "/v2/people/family/view/" + plot.ID;
        } else if (plot.type == 'person') {
            imghref = window.CRM.root + "/v2/people/person/view/" + plot.ID;
        } else if (plot.type == 'event') {
            imghref = window.CRM.root + "/v2/calendar";
        }

        // Add marker and infowindow
        plot.mark = addMarkerWithInfowindow(window.CRM.map, latlng, image, plot.Name, buildMarkerPopupContent(plot, imghref));
    }

    const add_all_markers_for_id = (id) => {
        var plotArray = newPlotArray[id];

        for (var i = 0; i < plotArray.length; i++) {
            if (plotArray[i].Latitude + plotArray[i].Longitude == 0)
                continue;
            //icon image
            add_marker(plotArray[i]);
        }
    }

    const delete_all_markers_for_id = (id) => {
        var plotArray = newPlotArray[id];

        for (var i = 0; i < plotArray.length; i++) {
            if (plotArray[i].mark != null) {
                plotArray[i].mark.setMap(null);
            }
            plotArray[i].mark = null;
        }
    }


    $('.view').on('change',function () {
        if ($(this).is(':checked') == false) {
            delete_all_markers_for_id($(this).data("id"));
        } else {
            add_all_markers_for_id($(this).data("id"));
        }
    });


    //initialize();
    initMap();

    $(window).on('resize', function () {
        fitMapHeightToFooter();

        if (map) {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(churchloc);
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
