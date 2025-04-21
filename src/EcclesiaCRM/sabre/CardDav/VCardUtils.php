<?php

namespace EcclesiaCRM\CardDav;

use Sabre\VObject\Component\VCard;
use EcclesiaCRM\Person;

class VcardUtils {
    public static function Person2Vcard(Person $person) : VCard {        
        $vcard = new VCard();

        $vcard->add('NAME', $person->getLastName());
        $vcard->add('N', [$person->getLastName(),$person->getFirstName(),'',$person->getTitle(),'']);
        $vcard->add('TITLE', $person->getTitle());
        $vcard->add('FN',$person->getFullName());
        $vcard->add('UID',\Sabre\DAV\UUIDUtil::getUUID());

        if ( !empty($person->getWorkEmail()) ) {
            $vcard->add('EMAIL', $person->getWorkEmail(), ['type' => 'WORK']);        
        }
        if ( !empty($person->getEmail()) ) {
            $vcard->add('EMAIL', $person->getEmail(), ['type' => 'HOME']);        
        }

        if ( !empty($person->getHomePhone()) ) {
            $vcard->add('TEL', $person->getHomePhone(), ['type' => 'HOME']);            
        }

        if ( !empty($person->getCellPhone()) ) {
            $vcard->add('TEL', $person->getCellPhone(), ['type' => 'CELL']);            
        }

        if ( !empty($person->getWorkPhone()) ) {
            $vcard->add('TEL', $person->getWorkPhone(), ['type' => 'WORK']);            
        }

        if ( !empty($person->getAddress1()) || !empty($person->getCity()) || !empty($person->getZip()) ) {
            $vcard->add('ADR', ['', '', $person->getAddress1(), $person->getCity(),'', $person->getZip(), $person->getCountry()], ['type' => 'HOME']);  
            // ;;100 Waters Edge;Baytown;LA;30314;United States of America
            $vcard->add('LABEL', $person->getAddress1().' '.$person->getCity().$person->getZip().', '. $person->getCountry(), ['type' => 'HOME']);  
            $vcard->add('X-ABADR', 'fr', ['type' => 'HOME']);              
        } else if (!is_null ($person->getFamily())) {
            $vcard->add('ADR', ['', '', $person->getFamily()->getAddress1(),$person->getFamily()->getCity(),'', $person->getFamily()->getZip(), $person->getFamily()->getCountry()], ['type' => 'HOME']);  
            // ;;100 Waters Edge;Baytown;LA;30314;United States of America
            $vcard->add('LABEL', $person->getFamily()->getAddress1().' '.$person->getFamily()->getCity().$person->getFamily()->getZip().', '. $person->getFamily()->getCountry(), ['type' => 'HOME']);  
            $vcard->add('X-ABADR', 'fr', ['type' => 'HOME']);    
        }

        return $vcard; 
    }
}
