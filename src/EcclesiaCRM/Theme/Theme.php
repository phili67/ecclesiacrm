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
}

class Theme
{
    static function getFontSize ()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleBrandFontSize)->findOneByPersonId(SessionUser::getUser()->getPersonId());
        if (is_null($theme)) return "text-sm";

        $sStyleBrandFontSize =  $theme->getValue();

        return ($sStyleBrandFontSize == 'Small')?"text-sm":"";
    }

    public function getStyle()
    {
        // we search if the config exist
        $userConf = UserConfigQuery::Create()->filterById(15)->findOneByPersonId($this->getPersonId());

        if ( is_null($userConf) ) {
            $userDefault = UserConfigQuery::create()->filterById(15)->findOneByPersonId (0);

            if ( !is_null ($userDefault) ) {
                $userConf = new UserConfig();

                $userConf->setPersonId ($this->getPersonId());
                $userConf->setId (15);
                $userConf->setName($userDefault->getName());
                $userConf->setValue($userDefault->getValue());
                $userConf->setType($userDefault->getType());
                $userConf->setChoicesId($userDefault->getChoicesId());
                $userConf->setTooltip(htmlentities(addslashes($userDefault->getTooltip()), ENT_NOQUOTES, 'UTF-8'));
                $userConf->setPermission('FALSE');
                $userConf->setCat($userDefault->getCat());

                $userConf->save();
            } else {
                return 'skin-blue-light';
            }
        }

        return $userConf->getValue();
    }

    static function getCurrentSideBarTypeColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBar)->findOneByPersonId(SessionUser::getUser()->getPersonId());
        if (is_null($theme)) return "sidebar-dark-blue";

        $styleSideBar =  $theme->getValue();
        $sStyleSideBarColor = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBarColor)->findOneByPersonId(SessionUser::getUser()->getPersonId())->getValue();

        return "sidebar-".$styleSideBar."-".$sStyleSideBarColor;
    }

    static function getCurrentSideBarMainColor ()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleSideBar)->findOneByPersonId(SessionUser::getUser()->getPersonId());
        if (is_null($theme)) return "dark";

        return $theme->getValue();
    }

    static function getCurrentNavBarColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleNavBarColor)->findOneByPersonId(SessionUser::getUser()->getPersonId());
        if (is_null($theme)) return "navbar-gray";

        return "navbar-".$theme->getValue();
    }

    static function getCurrentNavBarFontColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleNavBarColor)->findOneByPersonId(SessionUser::getUser()->getPersonId());
        if (is_null($theme)) return "navbar-light";

        $styleNavBar = $theme->getValue();

        if ($styleNavBar == 'yellow' || $styleNavBar == 'orange'  || $styleNavBar == 'light') {
            return "navbar-light";
        }

        return "navbar-dark";
    }

    static function getCurrentNavBrandLinkColor()
    {
        $theme = UserConfigQuery::Create()->filterById(ThemeStyles::StyleBrandLinkColor)->findOneByPersonId(SessionUser::getUser()->getPersonId());
        if (is_null($theme)) return "navbar-gray";

        $styleNavBar = $theme->getValue();

        return "navbar-".$styleNavBar;
    }

    static function isSidebarExpandOnHoverEnabled()
    {
        $theme = UserConfigQuery::Create()->filterById(SideBarBehaviourStyles::SidebarExpandOnHover)->findOneByPersonId(SessionUser::getUser()->getPersonId());
        if (is_null($theme)) return "sidebar-no-expand";

        $styleNavBar = $theme->getValue();

        return (!$styleNavBar)? "sidebar-no-expand":"";
    }

    static function isSidebarCollapseEnabled()
    {
        $theme = UserConfigQuery::Create()->filterById(SideBarBehaviourStyles::SidebarCollapse)->findOneByPersonId(SessionUser::getUser()->getPersonId());
        if (is_null($theme)) return "sidebar-collapse";

        $styleNavBar = $theme->getValue();

        return ($styleNavBar)? "sidebar-collapse":"";
    }
}
