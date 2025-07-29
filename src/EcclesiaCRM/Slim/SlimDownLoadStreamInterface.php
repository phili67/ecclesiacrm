<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2025/07/28
//

namespace SlimDownloadEnd;

use Psr\Http\Message\StreamInterface;

use EcclesiaCRM\FileSystemUtils;
use EcclesiaCRM\dto\SystemURLs;

class SlimDownLoadStreamInterface extends \Slim\Psr7\Stream
{
    public function __construct($stream, ?StreamInterface $cache = null)
    {
        parent::__construct($stream, $cache);
    }

    private function cleanUpTMP() : void {
        if (file_exists(SystemURLs::getDocumentRoot().'/tmp_attach/backup_result.json')) {
            unlink(SystemURLs::getDocumentRoot().'/tmp_attach/backup_result.json');
        }
        FileSystemUtils::recursiveRemoveDirectory(SystemURLs::getDocumentRoot() . '/tmp_attach/', true);   
    }

    function __destruct() {
        $this->cleanUpTMP();
    }
}