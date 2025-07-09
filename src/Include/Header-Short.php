<?php
/*******************************************************************************
 *
 *  filename    : Include/Header-Short.php
 *  last change : 2003-05-29
 *  description : page header (simplified version with no menubar)
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2001-2002 Phillip Hullquist, Deane Barker
  *
 ******************************************************************************/

require_once 'Header-function.php';
require_once 'Header-Security.php';

use EcclesiaCRM\Theme;
use EcclesiaCRM\dto\SystemURLs;

// Turn ON output buffering
ob_start();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>
  <?php
  require 'Header-HTML-Scripts.php';
  Header_head_metatag($sPageTitle);
  Header_fav_icons();
  ?>
</head>

<body class="sidebar-mini <?= Theme::isDarkModeEnabled()?"dark-mode":"" ?> <?= Theme::getFontSize() ?>">

<script nonce="<?= SystemURLs::getCSPNonce() ?>">
        let matched = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if(matched) {// we're on dark mode
            $('.sidebar-mini').addClass('dark-mode');
        } else {// we're in light mode
            $('.sidebar-mini').removeClass('dark-mode');
        }
</script>

<table height="100%" width="100%" border="0" cellpadding="5" cellspacing="0" align="center">
  <tr>
    <td valign="top" width="100%" align="center">
      <table width="98%" border="0">
        <tr>
          <td valign="top">
            <br>

            <p class="PageTitle"><?= gettext($sPageTitle) ?></p>
