<?php

/* copyright 2018 Philippe Logel all rights reserved */

namespace PersonalServer;

use Sabre\DAV\Collection;
use Sabre\DAV\FS;

class HomeCollection extends Collection {

    protected $authBackend;

    function __construct($authBackend) {
        $this->authBackend = $authBackend;
    }

    function getChildren() {
       $result = [];
       
       // for the login user
       $dir = new FS\Directory($this->authBackend->getHomeFolderName().'/');
       $result[] = $dir;
       
       return $result;
    }

    function getName() {
        return "home";
    }

}