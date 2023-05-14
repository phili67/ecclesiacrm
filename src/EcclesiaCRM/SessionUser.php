<?php
namespace EcclesiaCRM;

use EcclesiaCRM\dto\SystemURLs;

class SessionUser
{
    public static function isActive()
    {
      return isset($_SESSION['user']);
    }
    /**
     * @return User
     */

    public static function getUser()
    {
        return $_SESSION['user'];
    }
    public static function isAdmin()
    {
        if (self::isActive()) {
            return self::getUser()->isAdmin();
        } else {
            return false;
        }
    }
    public static function setCurrentPageName($pageName) {
        $pageName = str_replace(SystemURLs::getRootPath(), "", $pageName);
        if ($pageName[0] == '/') {
            $pageName = substr($pageName, 1);
        }
        $_SESSION['currentPageName'] = $pageName;
    }
    public static function setMustChangePasswordRedirect ($flag = false) {
        $_SESSION['MustChangePasswordRedirect'] = $flag;
    }
    public static function getMustChangePasswordRedirect () {
        return $_SESSION['MustChangePasswordRedirect'];
    }
    public static function getCurrentPageName() {
        return $_SESSION['currentPageName'];
    }
    public static function getId()
    {
        if (self::isActive()) {
            return self::getUser()->getId();
        } else {
            return 0;
        }
    }

    public static function isManageCalendarResources()
    {
        if (self::isActive()) {
            return (self::getUser()->getManageCalendarResources() or self::getUser()->isAdmin());
        } else {
            return 0;
        }
    }
}
