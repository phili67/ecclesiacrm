<?php
namespace EcclesiaCRM\dto;

use EcclesiaCRM\Utils\GeoUtils;

class ChurchMetaData
{
    public static function getChurchName()
    {
        return SystemConfig::getValue('sEntityName');
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
        return SystemConfig::getValue('sEntityAddress');
    }

    public static function getChurchCity()
    {
        return SystemConfig::getValue('sEntityCity');
    }

    public static function getChurchState()
    {
        return SystemConfig::getValue('sEntityState');
    }

    public static function getChurchZip()
    {
        return SystemConfig::getValue('sEntityZip');
    }

    public static function getChurchCountry()
    {
        return SystemConfig::getValue('sEntityCountry');
    }

    public static function getChurchEmail()
    {
        return SystemConfig::getValue('sEntityEmail');
    }

    public static function getChurchPhone()
    {
        return SystemConfig::getValue('sEntityPhone');
    }

    public static function getChurchWebSite()
    {
        return SystemConfig::getValue('sEntityWebSite');
    }
    
    public static function getChurchLatitude()
    {
      if (!empty(self::getChurchFullAddress())) {
        if (empty(SystemConfig::getValue('iEntityLatitude')) 
           || substr_count(SystemConfig::getValue('iEntityLatitude'),',') > 0) {
            self::updateLatLng();
        }
        return SystemConfig::getValue('iEntityLatitude');
      }
      
      return 0;
    }

    public static function getChurchLongitude()
    {
      if (!empty(self::getChurchFullAddress())) {
        if (empty(SystemConfig::getValue('iEntityLatitude')) 
        || substr_count(SystemConfig::getValue('iEntityLatitude'),',') > 0) {
            self::updateLatLng();
        }
        return SystemConfig::getValue('iEntityLongitude');
      }
      
      return 0;
    }

    private static function updateLatLng()
    {
        if (!empty(self::getChurchFullAddress())) {
            $latLng = GeoUtils::getLatLong(self::getChurchFullAddress());
            if (!empty($latLng['Latitude']) && !empty($latLng['Longitude'])) {
                SystemConfig::setValue('iEntityLatitude', $latLng['Latitude']);
                SystemConfig::setValue('iEntityLongitude', $latLng['Longitude']);
            }
        }
    }
}
