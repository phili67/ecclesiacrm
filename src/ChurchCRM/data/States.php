<?php

namespace ChurchCRM\data;

use ChurchCRM\dto\SystemConfig;
use ChurchCRM\dto\LocaleInfo;
use ChurchCRM\dto\SystemURLs;

class States
{
    private $countryCode;
    private $states = [];
    
    public function __construct()
    {       
        $localeInfo = new LocaleInfo(SystemConfig::getValue('sLanguage'));
        $this->countryCode = $localeInfo->getShortLocale();
        
        $stateFileName = SystemURLs::getDocumentRoot() . '/locale/states/'. $this->countryCode .'.json';
        
        if( is_file($stateFileName)) {
            $satesFile = file_get_contents($stateFileName);
            $this->states = json_decode($satesFile, true);
        }
    }
    
    public function getNames()
    {
        if (!empty($states)) {
          return array_values($this->states);
        } else {
          return [];
        }
    }
    public function getAll()
    {
        return $this->states;
    }
}