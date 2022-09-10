<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Plugin as BasePlugin;

use EcclesiaCRM\SecurityOptions;

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
        if ( $this->getSecurities() & SecurityOptions::bMailChimp ) {
            $res .= " bMailChimp";
        }
        if ( $this->getSecurities() & SecurityOptions::bGdrpDpo ) {
            $res .= " bGdrpDpo";
        }
        if ( $this->getSecurities() & SecurityOptions::bMainDashboard ) {
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
        if ( $this->getSecurities() & SecurityOptions::bDashBoardUser ) {
            $res .= " bDashboardUser";
        }

        return $res;
    }
}
