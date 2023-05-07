<?php

//
// Philippe Logel :
// I re-put the code at the right place it was :
// Menu events should be in MenuEventsCount.php
// It's important for a new dev
// It was my code ...
// Last this code was two times in different parts
//

namespace EcclesiaCRM\Synchronize;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Synchronize\DashboardItemInterface;

class EDrivePageItem implements DashboardItemInterface {

    public static function getDashboardItemName() {
        return "EDriveDisplay";
    }

    public static function getDashboardItemValue() {
        $edriveUpdate = array ();

        return $edriveUpdate;
    }

    public static function shouldInclude($PageName) {
        return $PageName=="/v2/people/person/view/".SessionUser::getUser()->getPersonId() or $PageName == "/browser/browse.php?type=privateDocuments"; // this ID would be found on all pages.
    }
}
