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

class VCalendarExtension extends VCalendarBase { 
    protected function getDefaults() {
        return [
            'VERSION'  => '2.0',
            'PRODID'   => '-//EcclesiaCRM.// VObject ' . VObject\Version::VERSION . '//EN',
            'CALSCALE' => 'GREGORIAN',
        ];
    }
    
    public function serialize()
    {
       $ret = parent::serialize();
       
       $ret = str_replace(" commaGMAP ",",",$ret);
       
       return $ret;
    }
}
