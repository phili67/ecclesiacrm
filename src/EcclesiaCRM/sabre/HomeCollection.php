<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//  Updated : 2018/05/13
//

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