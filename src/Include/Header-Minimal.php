<?php

/*******************************************************************************
 *
 *  filename    : Include/Header-Minimal.php
 *  last change : 2025-09-12
 *  description : page header (Bare minimum, not for use with Footer.php)
 *
 *  http://www.ecclesiacrm.com/
 *  Copyright 2003 Chris Gebhardt 2025 Philippe Logel
 *
 ******************************************************************************/
require_once 'Header-Security.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">

    <?php 
    if (!isset($css_files)) { 
        require 'Header-HTML-Scripts.php'; 
    }
    ?>

    <script nonce="<?= SystemURLs::getCSPNonce() ?>">
        window.CRM = {
            root: "<?= SystemURLs::getRootPath() ?>",
            jwtToken: '<?= SessionUser::getUser()->getJwtTokenForApi() ?>'
        };
    </script>
    <?php if (isset($css_files)) {
        /* it could be an array like :
         $css_files = [
            0 => 'path to first css file without root',
            1 => 'path to second css file without root',
            ....
        ]; 
        
        or
        
        $css_files = 'path to unique css file without root';
        */
        if (is_array($css_files)) {
            foreach ($css_file as $css) {
                ?>
                <link rel="stylesheet" type="text/css" href="<?= SystemURLs::getRootPath() ?><?= $css['path'] ?>">
                <?php
            }
        } else {
            ?>
            <link rel="stylesheet" type="text/css" href="<?= SystemURLs::getRootPath() ?><?= $css_files ?>">
            <?php
        }
     } ?>
</head>

<body>