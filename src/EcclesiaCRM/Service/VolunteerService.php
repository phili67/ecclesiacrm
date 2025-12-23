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
use EcclesiaCRM\PersonVolunteerOpportunityQuery;

class VolunteerService
{
    static private function renderHierarchicalView($voldId, $orig_selected_VolId): string
    {
        $res = "";

        $opportunity = VolunteerOpportunityQuery::create()->findOneById($voldId);
        $res .= '<a ' . (($opportunity->getId() == $orig_selected_VolId) ? 'style="color:red"' : '') . ' href="' . SystemURLs::getRootPath() . '/v2/volunteeropportunity/' . $opportunity->getId() . '/view">' . $opportunity->getName() . '</a>';

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

    static public function getHirearchicalView($voId, $orig_selected_VolId): string
    {

        $res = "";
        $opportunity = VolunteerOpportunityQuery::create()->findOneById($voId);

        if ($opportunity->getParentId() == null) {
            $res = self::renderHierarchicalView($opportunity->getId(), $orig_selected_VolId);
        } else {
            $res = self::getHirearchicalView($opportunity->getParentId(), $orig_selected_VolId);
        }

        return $res;
    }

    static private function loopHierarchicalManager($personId, $voldId, $orig_selected_VolId): string
    {
        $res = False;

        $opportunities = VolunteerOpportunityQuery::create()->findByParentId($voldId);

        foreach ($opportunities as $opportunity) {  
            $person = PersonVolunteerOpportunityQuery::create()
                ->filterByVolunteerOpportunityId($opportunity->getId())
                ->findOneByPersonId($personId);

            if (!is_null($person) and $opportunity->isManagers()) {
                return True;
            }

            $res = self::loopHierarchicalManager($personId, $opportunity->getId(), $orig_selected_VolId);

            if ($res == True) {
                return $res;
            }
        }

        return $res;
    }

    static public function getHirearchicalManager($personId, $voId, $orig_selected_VolId): Bool
    {
        $opportunity = VolunteerOpportunityQuery::create()->findOneById($voId);

        $person = PersonVolunteerOpportunityQuery::create()
                ->filterByVolunteerOpportunityId($voId)
                ->findOneByPersonId($personId);

        if (!is_null($person) and $opportunity->isManagers()) {
            return True;
        }

        $res = False;

        if ($opportunity->getParentId() == null) {
            $res = self::loopHierarchicalManager($personId, $opportunity->getId(), $orig_selected_VolId);
        } else {
            $res = self::getHirearchicalManager($personId, $opportunity->getParentId(), $orig_selected_VolId);
        }

        return $res;
    }
}
