<?php
/*******************************************************************************
 *
 *  filename    : Debug.php
 *  last change : 2014-11-29
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2014
 *
 ******************************************************************************/

require '../../Include/Config.php';
require '../../Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;


if (!($_SESSION['user']->isMailChimpEnabled())) {
    Redirect('Menu.php');
    exit;
}

//Set the page title
$sPageTitle = gettext('Debug Email Connection');

require '../../Include/Header.php';
?>

<section class="content">

<pre id="mailTest"><?= _("Testing connection .....") ?></pre>

</section>

<?php
require '../../Include/Footer.php';
?>
<script src="<?= SystemURLs::getRootPath() ?>/skin/js/email/MailChimp/Debug.js"></script>
