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
    class="sidebar-mini layout-navbar-fixed layout-fixed <?= Theme::isSidebarCollapseEnabled() ?> <?= Theme::getFontSize() ?> <?= Theme::isDarkModeEnabled()?"dark-mode":"" ?>"
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
    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        /* for the theme before jquery load is finished */
        if (window.CRM.sLightDarkMode == "automatic") {
            let matched = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if(matched) {// we're on dark mode
                $('.sidebar-mini').addClass('dark-mode');
                window.CRM.bDarkMode = true;
            } else {// we're in light mode
                $('.sidebar-mini').removeClass('dark-mode');
                window.CRM.bDarkMode = false;
            }
        }
    </script>


    <nav class="main-header navbar navbar-expand <?= Theme::getCurrentNavBarFontColor() ?> <?= Theme::getCurrentNavBarColor()?>">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= SystemURLs::getRootPath() ?>/v2/people/person/view/<?= SessionUser::getUser()->getPersonId() ?>" class="nav-link"><?= _("Private Space") ?></a>
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

        <?php if(isset($_SESSION['ControllerAdminUserId']))  { ?>
        <button class="btn btn-primary exit-control-account" data-userid="<?= $_SESSION['ControllerAdminUserId'] ?>"><?= _("Exit Control") ?></button>
        <?php } ?>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Cart Functions: style can be found in dropdown.less -->
            <?php
            if (SessionUser::getUser()->isShowCartEnabled()) {
                ?>
                <li class="nav-item dropdown notifications-menu" id="CartBlock">
                    <a href="#" class="nav-link" data-toggle="dropdown" title="<?= _('Your Cart') ?>">
                        <i class="fas fa-shopping-cart"></i>
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
                <ul class="hidden-xxs dropdown-menu <?= Theme::getCurrentNavBarColor()?>" style="margin-top:8px;margin-left:0px;height:240px;width:293px">
                    <li class="user-header" id="yourElement" style="height:205px">
                        <table border=0 class="table-dropdown-menu <?= Theme::isDarkModeEnabled()?"dark-mode":"" ?>" style="width:293px">
                            <tr style="border-bottom: 1pt solid black;">
                                <td valign="middle" style="width:110px;padding-left:10px">
                                    <img width="80"
                                         src="<?= SystemURLs::getRootPath() ?>/api/persons/<?= SessionUser::getUser()->getPersonId() ?>/thumbnail"
                                         class="initials-image profile-user-img img-responsive img-circle"
                                         alt="User Image" style="width:85px;height:85px">
                                </td>
                                <td valign="middle" align="left" style="width:183px">
                                    <a href="<?= SystemURLs::getRootPath() ?>/v2/people/person/view/<?= SessionUser::getUser()->getPersonId() ?>"
                                       class="dropdown-item main-help-menu">
                                        <p><i class="fas fa-user"></i> <?= _("Private Space") ?></p></a>
                                    <a href="<?= SystemURLs::getRootPath() ?>/v2/users/change/password" class="dropdown-item main-help-menu">
                                        <p><i class="fas fa-key"></i> <?= _('Change Password') ?></p></a>
                                    <a href="<?= SystemURLs::getRootPath() ?>/v2/users/settings" class="dropdown-item main-help-menu">
                                        <p><i class="fas fa-cog"></i> <?= _('Change Settings') ?></p></a>
                                    <a href="<?= SystemURLs::getRootPath() ?>/Login.php?session=Lock" class="dropdown-item main-help-menu">
                                        <p><i class="fas fa-pause"></i> <?= _('Lock') ?></p></a>
                                    <a href="<?= SystemURLs::getRootPath() ?>/Logoff.php" class="dropdown-item main-help-menu">
                                        <p><i class="fas fa-sign-out-alt"></i> <?= _('Sign out') ?></p></a>
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
                    <i class="fas fa-life-ring"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header"><?= _("Help Center") ?></span>
                    <div class="dropdown-divider"></div>
                    <a href="<?= SystemURLs::getSupportURL() ?>" target="_blank" title="<?= _('Help & Manual') ?>"
                       class="dropdown-item main-help-menu" class="dropdown-item">
                        <i class="far fa-question-circle"></i> <?= _('Help & Manual') ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" data-toggle="modal" data-target="#IssueReportModal" title="<?= _('Report an issue') ?>"
                       class="dropdown-item">
                        <i class="fas fa-bug"></i> <?= _('Report an issue') ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="https://gitter.im/ecclesiacrm/Lobby" target="_blank" title="<?= _('Developer Chat') ?>"
                       class="dropdown-item">
                        <i class="fas fa-comments"></i> <?= _('Developer Chat') ?>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="https://github.com/phili67/ecclesiacrm/issues/" target="_blank"
                       title="<?= _('Contributing') ?>" class="dropdown-item">
                        <i class="fab fa-github"></i> <?= _('Contributing') ?>
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
                    <i class="fas fa-th-large"></i>
                    <span class="badge badge-danger navbar-badge"><?= $taskSize ?></span>
                </a>
            </li>

            <li class="nav-item d-none d-sm-inline-block">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </nav>


    <!-- Left side column. contains the sidebar -->
    <aside class="main-sidebar <?= Theme::getCurrentSideBarTypeColor() ?> <?= Theme::isSidebarExpandOnHoverEnabled() ?> elevation-2" <?= (Theme::getCurrentSideBarMainColor() == 'light')?'style="background:repeating-linear-gradient(0deg,rgba(255,255,255,0.95),rgba(200,200,200,0.95)),url(/Images/sidebar.jpg);background-repeat: repeat-y;"':'style="background: repeating-linear-gradient(to top, rgba(0, 0, 0, 0.95), rgba(114, 114, 114, 0.95)),url(/Images/sidebar.jpg);background-repeat: repeat-y;"' ?>>
        <!-- sidebar: style can be found in sidebar.less -->
        <a href="<?= SystemURLs::getRootPath() ?>/v2/dashboard" class="brand-link <?= Theme::getCurrentNavBrandLinkColor() ?>">
            <img src="<?= SystemURLs::getRootPath() ?>/icon-small.png" alt="EcclesiaCRM Logo"
                 class="brand-image img-circle-20 elevation-1" style="opacity: .8">
            <span
                class="brand-text font-weight-light">Ecclesia<b>CRM</b> <?= SystemService::getDBMainVersion() ?> (B41)</span>
        </a>

        <section class="sidebar">
             <!-- sidebar menu: : style can be found in sidebar.less -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar nav-child-indent flex-column" data-widget="treeview" role="menu" data-accordion="true">
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon  fas fa-search"></i>
                            <p>
                                <!-- search form -->
                                <select class="form-control multiSearch select2-hidden-accessible left-search-field-menu-bar" data-select2-id="1" tabindex="-1" aria-hidden="true"></select>
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
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1><?= (!empty($sPageTitleSpan)) ? $sPageTitleSpan : $sPageTitle ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= SystemURLs::getRootPath()?>/menu"><i class="fas fa-home"></i> <?= _("Home")?></a></li>
                            <li class="breadcrumb-item active"><?= $sPageTitle; ?></li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="main-box-body clearfix" style="display:none" id="globalMessage">
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-exclamation-triangle"></i><span id="globalMessageText"></span>
                </div>
            </div>
