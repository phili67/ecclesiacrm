<?php

/**
 * Created by Visual Studio Code.
 * User: Philippe Logel
 * Date: 2025-11-16
 * Time: 1:28 PM
 */

namespace EcclesiaCRM\Service;

use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\dto\SystemURLs;

class VolunteerService
{
    static private function renderHierarchicalView($voldId, $orig_selected_VolId) : string {
        $res = "";

        $opportunity = VolunteerOpportunityQuery::create()->findOneById($voldId);
        $res .= '<a '. (($opportunity->getId() == $orig_selected_VolId)?'style="color:red"':'') .' href="'. SystemURLs::getRootPath() .'/v2/volunteeropportunity/'. $opportunity->getId() . '/view">'.$opportunity->getName().'</a>';
        
        $opportunities = VolunteerOpportunityQuery::create()->findByParentId($voldId);
        
        if ($opportunities->count() > 0) {
            $res .= "<ul>";
            foreach ($opportunities as $opportunity) {
                $res .= "<li>";   
                $res .= self::renderHierarchicalView($opportunity->getId(), $orig_selected_VolId);            
                $res .= "</li>";   
            }        
            $res .= "</ul>";
        }

        return $res;
    }
    static public function getHirearchicalView($voId, $orig_selected_VolId) : string {

        $res = "";
        $opportunity = VolunteerOpportunityQuery::create()->findOneById($voId);

        if ($opportunity->getParentId() == null) {
            $res = self::renderHierarchicalView($opportunity->getId(), $orig_selected_VolId);
        } else {
            $res = self::getHirearchicalView($opportunity->getParentId(), $orig_selected_VolId);
        }

        return $res;
    }
}