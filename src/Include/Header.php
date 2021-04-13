<?php
/*******************************************************************************
 *
 *  filename    : Include/Header.php
 *  website     : http://www.ecclesiacrm.com
 *  description : page header used for most pages
 *
 *  Copyright 2001-2004 Phillip Hullquist, Deane Barker, Chris Gebhardt, Michael Wilt
 *  Copyright 2017 Philippe Logel
 ******************************************************************************/

use EcclesiaCRM\Service\SystemService;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\utils\RedirectUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\view\MenuRenderer;
use EcclesiaCRM\Theme;


if (!Bootstrapper::isDBCurrent()) {  //either the DB is good, or the upgrade was successful.
    RedirectUtils::Redirect('SystemDBUpdate.php');
    exit;
}

use EcclesiaCRM\Service\TaskService;

$taskService = new TaskService();

//
// Turn ON output buffering
ob_start();

require_once 'Header-function.php';
require_once 'Header-Security.php';

// Top level menu index counter
$MenuFirst = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <?php
    require 'Header-HTML-Scripts.php';
    Header_head_metatag($sPageTitle);
    Header_fav_icons();
    ?>
</head>

<body
    class="sidebar-mini <?= Theme::isSidebarCollapseEnabled() ?> <?= Theme::getFontSize() ?> <?= Theme::isDarkModeEnabled() ?>"
    id="sidebar-mini" >
<?php
Header_system_notifications();
?>
<!-- Site wrapper -->
<div class="wrapper">
    <?php
    Header_modals();
    Header_body_scripts();

    $loggedInUserPhoto = SystemURLs::getRootPath() . '/api/persons/' . SessionUser::getUser()->getPersonId() . '/thumbnail';
    $MenuFirst = 1;
    ?>


    <nav class="main-header navbar navbar-expand <?= Theme::getCurrentNavBarFontColor() ?> <?= Theme::getCurrentNavBarColor()?>">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fa fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= SessionUser::getUser()->getPersonId() ?>" class="nav-link"><?= _("Private Space") ?></a>
            </li>
        </ul>
        <!--<form class="form-inline ml-3">
            <div class="input-group input-group-sm">

                <select class="form-control multiSearch" style="width:120px"></select>


                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-navbar" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>-->
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Cart Functions: style can be found in dropdown.less -->
            <?php
            if (SessionUser::getUser()->isShowCartEnabled()) {
                ?>
                <li class="nav-item dropdown notifications-menu" id="CartBlock">
                    <a href="#" class="nav-link" data-toggle="dropdown" title="<?= _('Your Cart') ?>">
                        <i class="fa fa-shopping-cart"></i>
                        <span id="iconCount" class="badge badge-warning navbar-badge"><?= Cart::CountPeople() ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-lg-left" id="cart-dropdown-menu"></div>
                </li>
                <?php
            }
            ?>
            <!-- User Account: style can be found in dropdown.less -->
            <li class="nav-item dropdown user user-menu">
                <a href="#" class="nav-link" id="dropdown-toggle" data-toggle="dropdown"
                   title="<?= _('Your settings and more') ?>">
                    <img
                        src="<?= SystemURLs::getRootPath() ?>/api/persons/<?= SessionUser::getUser()->getPersonId() ?>/thumbnail"
                        class="user-image initials-image" alt="User Image">
                    <span class="hidden-xs"><?= SessionUser::getUser()->getName() ?> </span>

                </a>
                <ul class="hidden-xxs dropdown-menu <?= Theme::getCurrentNavBarColor()?>">
                    <li class="user-header" id="yourElement" style="height:205px">
                        <table border=0 class="table-dropdown-menu">
                            <tr style="border-bottom: 1pt solid black;">
                                <td valign="middle" width=110>
                                    <img width="80"
                                         src="<?= SystemURLs::getRootPath() ?>/api/persons/<?= SessionUser::getUser()->getPersonId() ?>/thumbnail"
                                         class="initials-image profile-user-img img-responsive img-circle"
                                         alt="User Image" style="width:85px;height:85px">
                                </td>
                                <td valign="middle" align="left" style="padding-top:10px">
                                    <a href="<?= SystemURLs::getRootPath() ?>/PersonView.php?PersonID=<?= SessionUser::getUser()->getPersonId() ?>"
                                       class="item_link">
                                        <p><i class="fa fa fa-user"></i> <?= _("Private Space") ?></p></a>
                                    <a href="<?= SystemURLs::getRootPath() ?>/UserPasswordChange.php" class="item_link">
                                        <p><i class="fa fa fa-key"></i> <?= _('Change Password') ?></p></a>
                                    <a href="<?= SystemURLs::getRootPath() ?>/SettingsIndividual.php" class="item_link">
                                        <p><i class="fa fa fa-gear"></i> <?= _('Change Settings') ?></p></a>
                                    <a href="<?= SystemURLs::getRootPath() ?>/Login.php?session=Lock" class="item_link">
                                        <p><i class="fa fa fa-pause"></i> <?= _('Lock') ?></p></a>
                                    <a href="<?= SystemURLs::getRootPath() ?>/Logoff.php" class="item_link">
                                        <p><i class="fa fa fa-sign-out"></i> <?= _('Sign out') ?></p></a>
                                </td>
                            </tr>
                        </table>
                        <p class="nav-link"><b><?= SessionUser::getUser()->getName() ?></b></p>
                    </li>
                </ul>
            </li>

            <li class="nav-item dropdown">
                <a href="#" class="nav-link" id="dropdown-toggle" data-toggle="dropdown"
                   title="<?= _('Help & Support') ?>" aria-expanded="true">
                    <i class="fa fa-support"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header"><?= _("Help Center") ?></span>
                    <div class="dropdown-divider"></div>
                    <a href="<?= SystemURLs::getSupportURL() ?>" target="_blank" title="<?= _('Help & Manual') ?>"
                       class="dropdown-item main-help-menu" class="dropdown-item">
                        <i class="fa fa-question-circle"></i> <?= _('Help & Manual') ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" data-toggle="modal" data-target="#IssueReportModal" title="<?= _('Report an issue') ?>"
                       class="dropdown-item">
                        <i class="fa fa-bug"></i> <?= _('Report an issue') ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="https://gitter.im/ecclesiacrm/Lobby" target="_blank" title="<?= _('Developer Chat') ?>"
                       class="dropdown-item">
                        <i class="fa fa-commenting-o"></i> <?= _('Developer Chat') ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="https://github.com/phili67/ecclesiacrm/issues/" target="_blank"
                       title="<?= _('Contributing') ?>" class="dropdown-item">
                        <i class="fa fa-github"></i> <?= _('Contributing') ?>
                    </a>
                </div>
            </li>
            <?php
            $tasks = $taskService->getCurrentUserTasks();
            $taskSize = count($tasks);
            ?>


            <li class="nav-item">
                <a href="#" class="nav-link" data-widget="control-sidebar" data-slide="true"
                   title="<?= _('Your tasks') ?>" role="button">
                    <i class="fa fa-gears"></i>
                    <span class="badge badge-danger navbar-badge"><?= $taskSize ?></span>
                </a>
            </li>
        </ul>
    </nav>


    <!-- Left side column. contains the sidebar -->
    <aside class="main-sidebar <?= Theme::getCurrentSideBarTypeColor() ?> <?= Theme::isSidebarExpandOnHoverEnabled() ?> elevation-4" <?= (Theme::getCurrentSideBarMainColor() == 'light')?'style="background:repeating-linear-gradient(0deg,rgba(255,255,255,0.95),rgba(200,200,200,0.95)),url(/Images/sidebar.jpg);background-repeat: repeat-y;"':'style="background: repeating-linear-gradient(to top, rgba(0, 0, 0, 0.95), rgba(114, 114, 114, 0.95)),url(/Images/sidebar.jpg);background-repeat: repeat-y;"' ?>>
        <!-- sidebar: style can be found in sidebar.less -->
        <a href="<?= SystemURLs::getRootPath() ?>/v2/dashboard" class="brand-link <?= Theme::getCurrentNavBrandLinkColor() ?>">
            <img src="<?= SystemURLs::getRootPath() ?>/icon-small.png" alt="EcclesiaCRM Logo"
                 class="brand-image img-circle elevation-3" style="opacity: .8">
            <span
                class="brand-text font-weight-light">Ecclesia<b>CRM</b> <?= SystemService::getDBMainVersion() ?> B1</span>
        </a>

        <section class="sidebar">
             <!-- sidebar menu: : style can be found in sidebar.less -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu" data-accordion="true">
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon  fa fa-search"></i>
                            <p>
                                <!-- search form -->
                                <select class="form-control multiSearch select2-hidden-accessible" style="width:175px" data-select2-id="1" tabindex="-1" aria-hidden="true"></select>
                                <!-- /.search form -->
                            </p>
                        </a>
                    </li>
                    <?php MenuRenderer::RenderMenu() ?>
                </ul>
            </nav>
        </section>
    </aside>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <section class="content-header">
            <h1><?= (strlen($sPageTitleSpan)) ? $sPageTitleSpan : $sPageTitle ?></h1>
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="main-box-body clearfix" style="display:none" id="globalMessage">
                <div class="callout fade in" id="globalMessageCallOut">
                    <!--<button type="button" class="close" data-dismiss="callout" aria-hidden="true">×</button>-->
                    <i class="fa fa-exclamation-triangle fa-fw fa-lg"></i><span id="globalMessageText"></span>
                </div>
            </div>
