<?php

/*******************************************************************************
 *
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2021
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';

?>
    <div class="error-page">
        <h2 class="headline text-yellow">404</h2>

        <div class="error-content">
            <h3><i class="fa fa-warning text-yellow"></i> <?= gettext("Oops! Can't find route for ") . " " . strtoupper($Method) ?></h3>

            <p>
                <?= gettext("for")?> : <?= str_replace(' ', '/',$uri) ?>
            </p>
        </div>
    </div>

<?php
require '../Include/Footer.php';
?>
