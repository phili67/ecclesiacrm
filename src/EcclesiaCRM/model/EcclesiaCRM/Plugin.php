<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Plugin as BasePlugin;
use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SecurityOptions;

use EcclesiaCRM\PluginDependenciesQuery;

/**
 * Skeleton subclass for representing a row from the 'plugin' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Plugin extends BasePlugin
{
    public function  getPluginSecurityName() {

        $res = "";
        if ( $this->getSecurities() & SecurityOptions::bAdmin ) {
            $res .= " bAdmin";
        }
        if ( $this->getSecurities() & SecurityOptions::bPastoralCare ) {
            $res .= " bPastoralCare";
        }
        if ( $this->getSecurities() & SecurityOptions::bGdrpDpo ) {
            $res .= " bGdrpDpo";
        }
        if ( $this->getSecurities() & SecurityOptions::bMainDashboard ) { // is now deprecated
            $res .= " bMainDashboard";
        }
        if ( $this->getSecurities() & SecurityOptions::bSeePrivacyData ) {
            $res .= " bSeePrivacyData";
        }
        if ( $this->getSecurities() & SecurityOptions::bAddRecords ) {
            $res .= " bAddRecords";
        }
        if ( $this->getSecurities() & SecurityOptions::bEditRecords ) {
            $res .= " bEditRecords";
        }
        if ( $this->getSecurities() & SecurityOptions::bDeleteRecords ) {
            $res .= " bDeleteRecords";
        }
        if ( $this->getSecurities() & SecurityOptions::bMenuOptions ) {
            $res .= " bMenuOptions";
        }
        if ( $this->getSecurities() & SecurityOptions::bManageGroups ) {
            $res .= " bManageGroups";
        }
        if ( $this->getSecurities() & SecurityOptions::bFinance ) {
            $res .= " bFinance";
        }
        if ( $this->getSecurities() & SecurityOptions::bNotes ) {
            $res .= " bNotes";
        }
        if ( $this->getSecurities() & SecurityOptions::bCanvasser ) {
            $res .= " bCanvasser";
        }
        if ( $this->getSecurities() & SecurityOptions::bEditSelf ) {
            $res .= " bEditSelf";
        }
        if ( $this->getSecurities() & SecurityOptions::bShowCart ) {
            $res .= " bShowCart";
        }
        if ( $this->getSecurities() & SecurityOptions::bShowMap ) {
            $res .= " bShowMap";
        }
        if ( $this->getSecurities() & SecurityOptions::bEDrive ) {
            $res .= " bEDrive";
        }
        if ( $this->getSecurities() & SecurityOptions::bShowMenuQuery ) {
            $res .= " bShowMenuQuery";
        }
        if ( $this->getSecurities() & SecurityOptions::bSundaySchool ) {
            $res .= " bSundaySchool";
        }
        if ( $this->getSecurities() & SecurityOptions::bDonationFund ) {
            $res .= " bDonationFund";
        }
        
        if ( $this->getSecurities() & SecurityOptions::bDashBoardUser ) {
            $res .= " bDashboardUser";
        }

        return $res;
    }

    public function getJS_Dependencies() : void
    {        
        foreach (PluginDependenciesQuery::getJavascriptUrlsForPluginId((int)$this->getId()) as $dependencyUrl) {
            $dep = $dependencyUrl;
            if (str_contains($dependencyUrl, "**locale**")) {
                $dep = str_replace("**locale**", Bootstrapper::getCurrentLocale()->getLocale(), $dependencyUrl);
            }
            $path = SystemURLs::getDocumentRoot() . "/" . $dep;
            if (file_exists($path)) {// we write the code directely in the footer.php
                ?>
                <script src="<?= SystemURLs::getRootPath() ?>/<?= $dep ?>"></script>
                <?php
            }
        } 
    }

    public function getAllClassesServices() : array
    {
        return PluginDependenciesQuery::getClassServices($this);
    }

    public function isMailActive() : bool
    {
        if ($this->getCategory() != "Communication") {
            return false;
        }

        return PluginDependenciesQuery::isServiceActiveForPlugin($this);
    }
}
