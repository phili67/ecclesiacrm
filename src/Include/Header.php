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
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\Cart;

if (!SystemService::isDBCurrent()) {  //either the DB is good, or the upgrade was successful.
    Redirect('SystemDBUpdate.php');
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
  Header_head_metatag();
  ?>
</head>

<body class="hold-transition <?= $_SESSION['sStyle'] ?> sidebar-mini <?= ($_SESSION['bSidebarCollapse'])?"sidebar-collapse":"" ?>" id="sidebar-mini">
<?php
  Header_system_notifications();
 ?>
<!-- Site wrapper -->
<div class="wrapper">
  <?php
  Header_modals();
  Header_body_scripts();

  $loggedInUserPhoto = SystemURLs::getRootPath().'/api/persons/'.$_SESSION['user']->getPersonId().'/thumbnail';
  $MenuFirst = 1;
  ?>

  <header class="main-header">
    <!-- Logo -->
    <a href="<?= SystemURLs::getRootPath() ?>/Menu.php" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src="<?= SystemURLs::getRootPath() ?>/icon-small.png" height=36"/></span>
      <!-- logo for regular state and mobile devices -->
      <?php
      $headerHTML = '<img src="'.SystemURLs::getRootPath().'/icon-large.png" height=36"/>'.SystemService::getDBMainVersion();
      $sHeader = SystemConfig::getValue("sHeader");
      if (!empty($sHeader)) {
          $headerHTML = html_entity_decode($sHeader, ENT_QUOTES);
      }
      ?>
      <span class="logo-lg"><?= $headerHTML ?></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only"><?= gettext('Toggle navigation') ?></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
            <!-- Cart Functions: style can be found in dropdown.less -->
            <?php 
               if ($_SESSION['user']->isShowCartEnabled()) { 
            ?>
            <li class="dropdown notifications-menu" id="CartBlock" >
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="<?= gettext('Your Cart') ?>">
                    <i class="fa fa-shopping-cart"></i>
                    <span id="iconCount" class="label label-success"><?= Cart::CountPeople() ?></span>
                </a>
                <ul class="dropdown-menu" id="cart-dropdown-menu"></ul>
            </li>
            <?php 
               }
            ?>
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" id="dropdown-toggle" data-toggle="dropdown" title="<?= gettext('Your settings and more') ?>">
              <img src="<?= SystemURLs::getRootPath()?>/api/persons/<?= $_SESSION['user']->getPersonId() ?>/thumbnail" class="user-image initials-image" alt="User Image">
              <span class="hidden-xs"><?= $_SESSION['user']->getName() ?> </span>

            </a>
            <ul class="hidden-xxs dropdown-menu">
              <li class="user-header" id="yourElement" style="height:205px">
                <table border=0 class="table-dropdown-menu">
                <tr style="border-bottom: 1pt solid black;">
                <td valign="middle" width=110>
                  <img width="80" src="<?= SystemURLs::getRootPath()?>/api/persons/<?= $_SESSION['user']->getPersonId() ?>/thumbnail" class="initials-image profile-user-img img-responsive img-circle" alt="User Image" style="width:85px;height:85px">                
                </td>
                <td valign="middle" align="left" style="padding-top:10px">   
                  <a href="<?= SystemURLs::getRootPath()?>/PersonView.php?PersonID=<?= $_SESSION['user']->getPersonId() ?>" class="item_link" data-toggle="tooltip" title="<?= gettext("For your documents family etc ...")?>" data-placement="right">
                      <p ><i class="fa fa fa-user"></i> <?= gettext("Private Space") ?></p></a>
                  <a href="<?= SystemURLs::getRootPath() ?>/UserPasswordChange.php" class="item_link"  data-toggle="tooltip" title="<?= gettext("You can change here your password")?>" data-placement="right">
                      <p ><i class="fa fa fa-key"></i> <?= gettext('Change Password') ?></p></a>
                  <a href="<?= SystemURLs::getRootPath() ?>/SettingsIndividual.php" class="item_link"  data-toggle="tooltip" title="<?= gettext("Change Custom Settings")?>" data-placement="right">
                      <p ><i class="fa fa fa-gear"></i> <?= gettext('Change Settings') ?></p></a>
                  <a href="Login.php?session=Lock" class="item_link" data-toggle="tooltip" title="<?= gettext("Lock your session")?>" data-placement="right">
                      <p ><i class="fa fa fa-pause"></i> <?= gettext('Lock') ?></p></a>
                  <a href="<?= SystemURLs::getRootPath() ?>/Logoff.php" class="item_link"  data-toggle="tooltip" title="<?= gettext("Quit EcclesiaCRM and close your session")?>" data-placement="right">
                      <p ><i class="fa fa fa-sign-out"></i> <?= gettext('Sign out') ?></p></a>
                </td>
                </tr>
                </table>
                <p style="color:#fff"><b><?= $_SESSION['user']->getName() ?></b></p>
              </li>
            </ul>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="dropdown-toggle" data-toggle="dropdown" title="<?= gettext('Help & Support') ?>">
              <i class="fa fa-support"></i>
            </a>
            <ul class="dropdown-menu">
              <li class="hidden-xxs">
                <a href="<?= SystemURLs::getSupportURL() ?>" target="_blank" title="<?= gettext('Help & Manual') ?>" class="main-help-menu">
                  <i class="fa fa-question-circle"></i> <?= gettext('Help & Manual') ?>
                </a>
              </li>
              <li class="hidden-xxs">
                <a href="#" data-toggle="modal" data-target="#IssueReportModal" title="<?= gettext('Report an issue') ?>">
                  <i class="fa fa-bug"></i> <?= gettext('Report an issue') ?>
                </a>
              </li>
              <li class="hidden-xxs">
                <a href="https://gitter.im/ecclesiacrm/Lobby" target="_blank" title="<?= gettext('Developer Chat') ?>">
                  <i class="fa fa-commenting-o"></i> <?= gettext('Developer Chat') ?>
                </a>
              </li>              
              <li class="hidden-xxs">
                <a href="https://github.com/phili67/ecclesiacrm/issues/" target="_blank" title="<?= gettext('Contributing') ?>">
                  <i class="fa fa-github"></i> <?= gettext('Contributing') ?>
                </a>
              </li>              
            </ul>
          </li>
          <?php
          $tasks = $taskService->getCurrentUserTasks();
          $taskSize = count($tasks);
          ?>
          <li class="dropdown settings-dropdown">
            <a href="#" data-toggle="control-sidebar" title="<?= gettext('Your tasks') ?>">
              <i class="fa fa-gears"></i>
              <span class="label label-danger"><?= $taskSize ?></span>
            </a>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar" style="background:repeating-linear-gradient(0deg,rgba(255,255,255,0.95),rgba(128,128,128,0.95)),url(<?= SystemURLs::getRootPath() ?>/Images/sidebar.jpg);background-repeat: repeat-y;">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- search form -->
      <form action="#" method="get" class="sidebar-form">

        <select class="form-control multiSearch"  style="width:100%">
        </select>

      </form>
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li>
          <a href="<?= SystemURLs::getRootPath() ?>/Menu.php">
            <i class="fa fa-dashboard"></i> <span><?= gettext('Dashboard') ?></span>
          </a>
        </li>
        <?php addMenu('root'); ?>
      </ul>
    </section>
  </aside>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $sPageTitle; ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="main-box-body clearfix" style="display:none" id="globalMessage">
          <div class="callout fade in" id="globalMessageCallOut">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fa fa-exclamation-triangle fa-fw fa-lg"></i><span id="globalMessageText"></span>
          </div>
        </div>
