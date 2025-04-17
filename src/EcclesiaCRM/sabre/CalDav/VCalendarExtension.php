<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//  Updated     : 2018/05/13
//

namespace EcclesiaCRM\MyVCalendar;

use Sabre\VObject;
use Sabre\VObject\Component\VCalendar as VCalendarBase;

use EcclesiaCRM\dto\SystemConfig;

class VCalendarExtension extends VCalendarBase {
    protected function getDefaults() {
        return [
            'VERSION'  => '2.0',
            'PRODID'   => '-//EcclesiaCRM.// VObject ' . VObject\Version::VERSION . '//EN',
            'CALSCALE' => 'GREGORIAN',
        ];
    }

    public function addVTimeZone () {
        $vt = new VObject\Component\VTimeZone($this,'VTIMEZONE');

        $vt->TZID = SystemConfig::getValue('sTimeZone');
        $vt->{'X-LIC-LOCATION'} = SystemConfig::getValue('sTimeZone');

        // the dayLight
        $dl = $this->createComponent('DAYLIGHT');
        $dl->DTSTART = '19810329T020000';
        $dl->RRULE = 'FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU';
        $dl->TZNAME = 'UTC+2';
        $dl->TZOFFSETFROM = '+0100';
        $dl->TZOFFSETTO = '+0200';
        $vt->add($dl);
        
        $std = $this->createComponent('STANDARD');
        $std->DTSTART = '19961027T030000';
        $std->RRULE = 'FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU';
        $std->TZNAME = 'UTC+1';
        $std->TZOFFSETFROM = '+0200';
        $std->TZOFFSETTO = '+0100';
        $vt->add($std);
        
        $this->add ($vt);
    }

    public function serialize($suppressCommaGMAP = true)
    {
       $ret = parent::serialize();

       if ($suppressCommaGMAP) {
           $ret = str_replace(" commaGMAP ", ",", $ret);
       }

       return $ret;
    }
}
