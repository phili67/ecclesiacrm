<?php

namespace EcclesiaCRM\CardDav;

use Sabre\VObject\Component\VCard;
use EcclesiaCRM\Person;

class VcardUtils {
    public static function Person2Vcard(Person $person) : VCard {
        $carArr = [
            //'N' => $person->getLastName().';'.$person->getFirstName().";;;",
            'NAME' => $person->getLastName(),
            'TITLE' => $person->getTitle(),
            'FN'  => $person->getFullName(),
            "UID" => \Sabre\DAV\UUIDUtil::getUUID()
        ];

        if ( !empty($person->getWorkEmail()) ) {
            $carArr['EMAIL;type=INTERNET;type=WORK;type=pref'] = $person->getWorkEmail();
        }
        if ( !empty($person->getEmail()) ) {
            $carArr["EMAIL;type=INTERNET;type=HOME;type=pref"] = $person->getEmail();
        }

        if ( !empty($person->getHomePhone()) ) {
            $carArr["TEL;type=HOME;type=VOICE;type=pref"] = $person->getHomePhone();;
        }

        if ( !empty($person->getCellPhone()) ) {
            $carArr["TEL;type=CELL;type=VOICE"] = $person->getCellPhone();
        }

        if ( !empty($person->getWorkPhone()) ) {
            $carArr["TEL;type=WORK;type=VOICE"] = $person->getWorkPhone();
        }

        if ( !empty($person->getAddress1()) || !empty($person->getCity()) || !empty($person->getZip()) ) {
            $carArr["item1.ADR;type=HOME;type=pref"] = $person->getAddress1().' '.$person->getCity().' '.$person->getZip();
            $carArr["item1.X-ABADR"] = "fr";
        } else if (!is_null ($person->getFamily())) {
            $carArr["item1.ADR;type=HOME;type=pref"] = $person->getFamily()->getAddress1().' '.$person->getFamily()->getCity().' '.$person->getFamily()->getZip();
            $carArr["item1.X-ABADR"] = "fr";
        }

        $vcard = new VCard($carArr);

        return $vcard; 
    }
}
