<?php

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Bootstrapper;
use Geocoder\Provider\BingMaps\BingMaps;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\StatefulGeocoder;
use EcclesiaCRM\FamilyQuery;
use Http\Client\Curl\Client;
use Geocoder\Query\GeocodeQuery;

class GeoUtils
{

    public static function getKey()
    {
        if (SystemConfig::getValue('sMapProvider') == 'OpenStreetMap') {
            return SystemConfig::getValue("sNominatimLink");
        } else if (SystemConfig::getValue('sMapProvider') == 'GoogleMaps'){
            return SystemConfig::getValue("sGoogleMapKey");
        } else if (SystemConfig::getValue('sMapProvider') == 'BingMaps') {
            return SystemConfig::getValue("sBingMapKey");
        }
    }

    public static function getLatLong($address)
    {

        $logger = LoggerUtils::getAppLogger();

        $provider = null;
        $options = [
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        $adapter  = new Client(null, null, $options);

        $lat = 0;
        $long = 0;
        try {
            switch (SystemConfig::getValue("sMapProvider")) {
                case "GoogleMaps":
                    $provider = new GoogleMaps($adapter, null, null, true, SystemConfig::getValue("sGoogleMapKey"));
                    break;
                case "BingMaps":
                    $provider = new BingMaps($adapter, SystemConfig::getValue("sBingMapKey"));
                    break;
                case "OpenStreetMap":
                    $provider = new Nominatim($adapter, SystemConfig::getValue("sNominatimLink"), SystemConfig::getValue("sChurchEmail") );
                    break;
            }
            $logger->debug("Using: Geo Provider -  ". $provider->getName());
            $geoCoder = new StatefulGeocoder($provider, Bootstrapper::GetCurrentLocale()->getShortLocale());
            $result = $geoCoder->geocodeQuery(GeocodeQuery::create($address));
            $logger->debug("We have " . $result->count() . " results");
            if (!empty($result)) {
                $firstResult = $result->get(0);
                $coordinates = $firstResult->getCoordinates();
                $lat = $coordinates->getLatitude();
                $long = $coordinates->getLongitude();
            }
        } catch (\Exception $exception) {
            $logger->warn("issue creating geoCoder " . $exception->getMessage());
        }

        return array(
            'Latitude' => $lat,
            'Longitude' => $long
        );

    }

    public static function DrivingDistanceMatrix($address1, $address2)
    {
        $logger = LoggerUtils::getAppLogger();
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?";
        $url = $url . "language=" . Bootstrapper::GetCurrentLocale()->getShortLocale();
        $url = $url . "&origins=" . urlencode($address1);
        $url = $url . "&destinations=" . urlencode($address2);
        $logger->debug($url);
        $gMapsResponse = file_get_contents($url);
        $details = json_decode($gMapsResponse, TRUE);
        $matrixElements = $details['rows'][0]['elements'][0];
        return array(
            'distance' => $matrixElements['distance']['text'],
            'duration' => $matrixElements['duration']['text']
        );
    }

    // Function takes latitude and longitude
    // of two places as input and returns the
    // distance in miles.
    public static function LatLonDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Formula for calculating radians between
        // latitude and longitude pairs.

        // Uses the Spherical Law of Cosines to find great circle distance.
        // Length of arc on surface of sphere

        // convert to radians to work with trig functions

        // earth radius
        // http://www.movable-type.co.uk/scripts/latlong.html
        $R = 6371.0; //kilometers

        $phi1 = deg2rad($lat1); // φ, λ in radians
        $phi2 = deg2rad($lat2);
        $var_phi = deg2rad($lat2-$lat1);
        $var_lambda = deg2rad($lon2-$lon1);

        $a = pow(sin($var_phi/2.0) ,2) +
            cos($phi1) * cos($phi2) *
            pow(sin($var_lambda/2.0) ,2);

        $c = 2.0 * atan2(sqrt($a), sqrt(1-$a));

        $distance = $R * $c; // in kilometers

        $unit = strtoupper(SystemConfig::getValue('sDistanceUnit'));

        if ($unit == "MILES") {
            $distance = $distance * 0.621371;
        } elseif ($unit == 'LI') { //China
            $distance = $distance*2;
        } elseif ($unit == 'SHAKU') {// Japan
            $distance = $distance*3300;
        } elseif ($unit == 'WAH') {
            $distance = $distance*500;
        }

        // Return distance to three figures
        if ($distance < 10.0) {
            $distance_f = round($distance,2);
        } elseif ($distance < 100.0) {
            $distance_f = round($distance,1);
        } else {
            $distance_f = round($distance);
        }

        return $distance_f;

        // Formula for calculating radians between
        // latitude and longitude pairs.

        // Uses the Spherical Law of Cosines to find great circle distance.
        // Length of arc on surface of sphere

        // convert to radians to work with trig functions

        /*$lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // determine angle between between points in radians
        $radians = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lon1 - $lon2));

        // mean radius of Earth in kilometers
        $radius = 6371.0;

        // distance in kilometers is $radians times $radius
        $distance = $radians * $radius;

        $unit = strtoupper(SystemConfig::getValue('sDistanceUnit'));

        // convert to miles
        if ($unit == "MILES") {
            $distance = $distance * 0.621371;
        } elseif ($unit == 'LI') { //China
            $distance = $distance*2;
        } elseif ($unit == 'SHAKU') {// Japan
            $distance = $distance*3300;
        }

        // Return distance to three figures
        if ($distance < 10.0) {
            $distance_f = round($distance,2);
        } elseif ($distance < 100.0) {
            $distance_f = round($distance,1);
        } else {
            $distance_f = round($distance);
        }

        return $distance_f;*/
    }

    public static function LatLonBearing($lat1, $lon1, $lat2, $lon2)
    {
        // Formula for determining the bearing from ($lat1,$lon1) to ($lat2,$lon2)

        // This is the initial bearing which if followed in a straight line will take
        // you from the start point to the end point; in general, the bearing you are
        // following will have varied by the time you get to the end point (if you were
        // to go from say 35°N,45°E (Baghdad) to 35°N,135°E (Osaka), you would start on
        // a bearing of 60° and end up on a bearing of 120°!).

        // If you are standing at ($lat1,$lon1) and pointing the shortest distance to
        // ($lat2,$lon2) this function tells you which direction you are pointing.
        // Returns one of the following 16 directions.
        // N, NNE, NE, ENE, E, ESE, SE, SSE, S, SSW, SW, WSW, W, WNW, NW, NNW

        // convert to radians to work with trig functions
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $y = sin($lon2 - $lon1) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($lon2 - $lon1);
        $bearing = atan2($y, $x);

        // Covert from radians to degrees
        $bearing = sprintf('%5.1f', rad2deg($bearing));

        // Convert to directions
        // -180=S   -135=SW   -90=W   -45=NW   0=N   45=NE   90=E   135=SE   180=S
        if ($bearing < -191.25) {
            $bearing += 360;
        }
        if ($bearing >= 191.25) {
            $bearing -= 360;
        }

        if ($bearing < -191.25) {
            $direction = '---';
        } elseif ($bearing < -168.75) {
            $direction = gettext('S');
        } elseif ($bearing < -146.25) {
            $direction = gettext('SSW');
        } elseif ($bearing < -123.75) {
            $direction = gettext('SW');
        } elseif ($bearing < -101.25) {
            $direction = gettext('WSW');
        } elseif ($bearing < -78.75) {
            $direction = gettext('W');
        } elseif ($bearing < -56.25) {
            $direction = gettext('WNW');
        } elseif ($bearing < -33.75) {
            $direction = gettext('NW');
        } elseif ($bearing < -11.25) {
            $direction = gettext('NNW');
        } elseif ($bearing < 11.25) {
            $direction = gettext('N');
        } elseif ($bearing < 33.75) {
            $direction = gettext('NNE');
        } elseif ($bearing < 56.25) {
            $direction = gettext('NE');
        } elseif ($bearing < 78.75) {
            $direction = gettext('ENE');
        } elseif ($bearing < 101.25) {
            $direction = gettext('E');
        } elseif ($bearing < 123.75) {
            $direction = gettext('ESE');
        } elseif ($bearing < 146.25) {
            $direction = gettext('SE');
        } elseif ($bearing < 168.75) {
            $direction = gettext('SSE');
        } elseif ($bearing < 191.25) {
            $direction = gettext('S');
        } else {
            $direction = '+++';
        }

//    $direction  = $bearing . " " . $direction;

        return $direction;
    }

    public static function CompareDistance($elem1, $elem2)
    {
        if ($elem1['Distance'] > $elem2['Distance']) {
            return 1;
        } elseif ($elem1['Distance'] == $elem2['Distance']) {
            return 0;
        } else {
            return -1;
        }
    }

    public static function SortByDistance($array)
    {
        $newArr = $array;
        usort($newArr, array(GeoUtils::class,"CompareDistance"));
        return $newArr;
    }

    // Create an associated array of family information sorted by distance from
    // a particular family.
    public static function FamilyInfoByDistance($iFamily)
    {
        // Handle the degenerate case of no family selected by just making the array without
        // distance and bearing data, and don't bother to sort it.
        if ($iFamily) {
            // Get info for the selected family
            $selectedFamily = FamilyQuery::create()
                ->findOneById($iFamily);
        }

        // Compute distance and bearing from the selected family to all other families
        $families = FamilyQuery::create()
            ->filterByDateDeactivated(null)
            ->find();

        foreach ($families as $family) {
            $familyID = $family->getId();
            if ($iFamily) {
                $results[$familyID]['Distance'] = GeoUtils::LatLonDistance($selectedFamily->getLatitude(), $selectedFamily->getLongitude(), $family->getLatitude(), $family->getLongitude());
                $results[$familyID]['Bearing'] = GeoUtils::LatLonBearing($selectedFamily->getLatitude(), $selectedFamily->getLongitude(), $family->getLatitude(), $family->getLongitude());
            }
            $results[$familyID]['fam_Name'] = $family->getName();
            $results[$familyID]['fam_Address'] = $family->getAddress();
            $results[$familyID]['fam_Latitude'] = $family->getLatitude();
            $results[$familyID]['fam_Longitude'] = $family->getLongitude();
            $results[$familyID]['fam_ID'] = $familyID;
        }

        if ($iFamily) {
            $resultsByDistance = GeoUtils::SortByDistance($results);
        } else {
            $resultsByDistance = $results;
        }
        return $resultsByDistance;
    }
}
