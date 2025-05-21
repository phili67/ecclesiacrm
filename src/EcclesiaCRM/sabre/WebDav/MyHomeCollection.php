<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//  Updated : 2018/05/13
//

namespace Sabre\DAVACL\FS;

use Sabre\DAV\FS;
use Sabre\Uri;
use Sabre\DAVACL\FS\HomeCollection;
use Sabre\DAVACL\PrincipalBackend\BackendInterface;

use Sabre\DAVACL\FS\MyHomeCollectionSharing;

use EcclesiaCRM\dto\SystemURLs;



class MyHomeCollection extends HomeCollection  {

    protected $authBackend;
    protected $principalBackend;

    protected $webDavBackend;

    public function __construct(BackendInterface $principalBackend, $authBackend, $webDavBackend, $principalPrefix = 'principals')
    {
        $this->authBackend = $authBackend;
        $this->principalBackend = $principalBackend;
        $this->webDavBackend = $webDavBackend;
        parent::__construct($principalBackend, SystemURLs::getRootPath().'private/userdir/',$principalPrefix);
    }

    /**
     * Returns a principals' collection of files.
     *
     * The passed array contains principal information, and is guaranteed to
     * at least contain a uri item. Other properties may or may not be
     * supplied by the authentication backend.
     *
     * @return \Sabre\DAV\INode
     */
    public function getChildForPrincipal(array $principalInfo)
    {
        $owner = $principalInfo['uri'];
        $acl = [
            [
                'privilege' => '{DAV:}all',
                'principal' => '{DAV:}owner',
                'protected' => true,
            ],
        ];

        list(, $principalBaseName) = Uri\split($owner);

        //$path = $this->storagePath.'/'.$principalBaseName;
        $path = $this->authBackend->getHomeFolderName();

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return new MyHomeCollectionSharing(
            $this->authBackend,
            $this->principalBackend,
            $this->webDavBackend,
            $path,
            $acl,
            $owner
        );
    }

    function getChildren() {
       $result = [];

       // for the login user
       $dir = new FS\Directory($this->authBackend->getHomeFolderName().'/');
       $result[] = $dir;

       return $result;
    }
}
