<?php
/*******************************************************************************
 *
 *  filename    : debug.php
 *  last change : 2019/2/6
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019/2/6 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>

<div class="card card-body">

<p id="mailTest"><?= _("Testing connection .....") ?></p>

</div>

<?php require $sRootDocument . '/Include/Footer.php'; ?>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/Debug.js"></script>
