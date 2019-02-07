<?php
/*******************************************************************************
 *
 *  filename    : notinmailchimpemails.php
 *  last change : 2019/2/6
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2019/2/6 Philippe Logel
 *
 ******************************************************************************/

require $sRootDocument . '/Include/Header.php';
?>

<div class="box box-body">
<div class="box-header  with-border">
  <h3 class="box-title"><?= _("Families Not In MailChimp")?></h3>
  <div style="float:right">
    <a href="https://mailchimp.com/<?= $lang ?>/"><img src="<?= $sRootPath ?>/Images/Mailchimp_Logo-Horizontal_Black.png" height=25/></a>
  </div>
</div>
<table class="table table-striped table-bordered" id="familiesWithoutEmailTable" cellpadding="5" cellspacing="0"  width="100%"></table>

</div>

<script src="<?= $sRootPath ?>/skin/js/email/MailChimp/NotInMailChimpEmails.js" ></script>

<?php require $sRootDocument . '/Include/Footer.php'; ?>