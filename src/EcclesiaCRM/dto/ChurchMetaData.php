<?php
namespace EcclesiaCRM\dto;

use EcclesiaCRM\Utils\GeoUtils;

class ChurchMetaData
{
    public static function getChurchName()
    {
        return SystemConfig::getValue('sChurchName');
    }

    public static function getChurchFullAddress()
    {
        $address = [];
        if (!empty(self::getChurchAddress())) {
            array_push($address, self::getChurchAddress());
        }

        if (!empty(self::getChurchCity())) {
            array_push($address, self::getChurchCity() . ',');
        }

        if (!empty(self::getChurchState())) {
            array_push($address, self::getChurchState());
        }

        if (!empty(self::getChurchZip())) {
            array_push($address, self::getChurchZip());
        }
        if (!empty(self::getChurchCountry())) {
            array_push($address, self::getChurchCountry());
        }

        return implode(' ', $address);
    }

    public static function getChurchAddress()
    {
        return SystemConfig::getValue('sChurchAddress');
    }

    public static function getChurchCity()
    {
        return SystemConfig::getValue('sChurchCity');
    }

    public static function getChurchState()
    {
        return SystemConfig::getValue('sChurchState');
    }

    public static function getChurchZip()
    {
        return SystemConfig::getValue('sChurchZip');
    }

    public static function getChurchCountry()
    {
        return SystemConfig::getValue('sChurchCountry');
    }

    public static function getChurchEmail()
    {
        return SystemConfig::getValue('sChurchEmail');
    }

    public static function getChurchPhone()
    {
        return SystemConfig::getValue('sChurchPhone');
    }

    public static function getChurchWebSite()
    {
        return SystemConfig::getValue('sChurchWebSite');
    }
    
    public static function getChurchLatitude()
    {
      if (!empty(self::getChurchFullAddress())) {
        if (empty(SystemConfig::getValue('iChurchLatitude')) 
           || substr_count(SystemConfig::getValue('iChurchLatitude'),',') > 0) {
            self::updateLatLng();
        }
        return SystemConfig::getValue('iChurchLatitude');
      }
      
      return 0;
    }

    public static function getChurchLongitude()
    {
      if (!empty(self::getChurchFullAddress())) {
        if (empty(SystemConfig::getValue('iChurchLatitude')) 
        || substr_count(SystemConfig::getValue('iChurchLatitude'),',') > 0) {
            self::updateLatLng();
        }
        return SystemConfig::getValue('iChurchLongitude');
      }
      
      return 0;
    }

    private static function updateLatLng()
    {
        if (!empty(self::getChurchFullAddress())) {
            $latLng = GeoUtils::getLatLong(self::getChurchFullAddress());
            if (!empty($latLng['Latitude']) && !empty($latLng['Longitude'])) {
                SystemConfig::setValue('iChurchLatitude', $latLng['Latitude']);
                SystemConfig::setValue('iChurchLongitude', $latLng['Longitude']);
            }
        }
    }
}
