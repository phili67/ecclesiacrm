<?php


namespace EcclesiaCRM;

use EcclesiaCRM\SessionUser;
use EcclesiaCRM\UserConfigQuery;


abstract class SideBarBehaviourStyles
{
    const SidebarExpandOnHover = 13;
    const SidebarCollapse = 14;
}

abstract class ThemeStyles
{
    const StyleBrandFontSize = 15;
    const StyleSideBar = 16;
    const StyleSideBarColor = 17;
    const StyleNavBarColor = 18;
    const StyleBrandLinkColor = 19;
    const StyleDarkMode = 20;
}

class Theme
{
    static function first_load()
    {
        for ($i = 13; $i <= 20; $i++) {
            $userDefault = UserConfigQuery::create()->filterById($i)->findOneByPersonId(0);
            $userCFG = UserConfigQuery::Create()->filterById($i)->findOneByPersonId(SessionUser::getUser()->getPersonId());

            if ( !is_null($userDefault) && is_null($userCFG) ) {
                $userConf = new UserConfig();

                $userConf->setPersonId(SessionUser::getUser()->getPersonId());
                $userConf->setId($i);
                $userConf->setName($userDefault->getName());
                $userConf->setValue($userDefault->getValue());
                $userConf->setType($userDefault->getType());
                $userConf->setChoicesId($userDefault->getChoicesId());
                $userConf->setTooltip(htmlentities(addslashes($userDefault->getTooltip()), ENT_NOQUOTES, 'UTF-8'));
                $userConf->setPermission('TRUE');
                $userConf->setCat($userDefault->getCat());

                $userConf->save();
            }
        }
    }

    static function getFontSize()
    {
        // we search if the config exist
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleBrandFontSize)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleBrandFontSize)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        $sStyleBrandFontSize = $theme->getValue();

        return ($sStyleBrandFontSize == 'Small') ? "text-sm" : "";
    }

    static function getCurrentSideBarTypeColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBar)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBar)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        $styleSideBar = $theme->getValue();
        $sStyleSideBarColor = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBarColor)->findOneByPersonId(SessionUser::getUser()->getPersonId())->getValue();

        return "sidebar-" . $styleSideBar . "-" . $sStyleSideBarColor;
    }

    static function getCurrentRightSideBarTypeColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBar)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $styleSideBar = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBar)->findOneByPersonId(SessionUser::getUser()->getPersonId())->getValue();

        return "control-sidebar-".$styleSideBar;
    }

    static function getCurrentSideBarMainColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBar)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBar)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        return $theme->getValue();
    }

    static function getCurrentNavBarColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleNavBarColor)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleNavBarColor)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        return "navbar-" . $theme->getValue();
    }

    static function getCurrentNavBarFontColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleNavBarColor)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleNavBarColor)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        $styleNavBar = $theme->getValue();

        if ($styleNavBar == 'yellow' || $styleNavBar == 'orange' || $styleNavBar == 'light') {
            return "navbar-light";
        }

        return "navbar-dark";
    }

    static function getCurrentNavBrandLinkColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleBrandLinkColor)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleBrandLinkColor)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        $styleNavBar = $theme->getValue();

        return "navbar-" . $styleNavBar;
    }

    static function isSidebarExpandOnHoverEnabled()
    {
        $theme = UserConfigQuery::Create()->filterById(SideBarBehaviourStyles::SidebarExpandOnHover)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(SideBarBehaviourStyles::SidebarExpandOnHover)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        $styleNavBar = $theme->getValue();

        return (!$styleNavBar) ? "sidebar-no-expand" : "";
    }

    static function isSidebarCollapseEnabled()
    {
        $theme = UserConfigQuery::Create()->filterById(SideBarBehaviourStyles::SidebarCollapse)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(SideBarBehaviourStyles::SidebarCollapse)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        $styleNavBar = $theme->getValue();

        return ($styleNavBar) ? "sidebar-collapse" : "";
    }

    static function isDarkModeEnabled()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleDarkMode)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleDarkMode)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        $styleNavBar = $theme->getValue();

        return ($styleNavBar == 'dark') ? true : false;
    }

    static function LightDarkMode()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleDarkMode)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        if (is_null($theme)) {
            Theme::first_load();
        }

        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleDarkMode)->findOneByPersonId(SessionUser::getUser()->getPersonId());

        $styleNavBar = $theme->getValue();

        return $styleNavBar;
    }
}
