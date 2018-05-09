<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//

namespace EcclesiaCRM\MyVCalendar;

use Sabre\VObject;
use Sabre\VObject\Component\VCalendar as VCalendarBase;

class VCalendarExtension extends VCalendarBase { 
    protected function getDefaults() {
        return [
            'VERSION'  => '2.0',
            'PRODID'   => '-//EcclesiaCRM.// VObject ' . VObject\Version::VERSION . '//EN',
            'CALSCALE' => 'GREGORIAN',
        ];

    }
}
