<?php
// pour le debug on se met au bon endroit : https://192.168.151.205/mysql/upgrade/8.0.0-pre-upgrade.php
// et il faut décommenter
/*define("webdav", "1");
require '../../Include/Config.php';*/

use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Utils\MiscUtils;

$logger = LoggerUtils::getAppLogger();

$logger->info("Start to delete : all unusefull files");

unlink(SystemURLs::getDocumentRoot() . "/skin/js/calendar/BingMapEvent.js");
unlink(SystemURLs::getDocumentRoot() . "/v2/templates/map/MapUsingGoogle.php");

MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/skin/external/jquery.steps/");

unlink(SystemURLs::getDocumentRoot() . "/Images/jitsi_logo.png");

# mailchim is now a plugin, so we need to remove all the files related to mailchimp in the core of CRM
unlink(SystemURLs::getDocumentRoot() . "/api/routes/mailchimp.php");

unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/APIControllers/MailchimpController.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/Service/MailChimpService.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/Synchronize/MailchimpDashboardItem.php");
unlink(SystemURLs::getDocumentRoot() . "/EcclesiaCRM/VIEWControllers/VIEWMailchimpController.php");


MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/skin/js/email/");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/v2/routes/email/");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/v2/templates/email/");
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/images/temp/", true);// we remove only the content of the temp directory, not the directory itself
MiscUtils::removeDirectory(SystemURLs::getDocumentRoot() . "/backuptools/");// now the backuptools name directory is now renamed to tools



unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/dropdownmenumanager/README.md");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/dropdownmenumanager/icons/__dropdown.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/dropdownmenumanager/icons/dropdown.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/dropdownmenumanager/plugin.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/mergeTags.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/mergeTags_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/newpage.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/newpage_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/saveTemplate.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/saveTemplate_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/saveWord.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/saveWord_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/templates.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/hidpi/templates_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/mergeTags.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/mergeTags_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/newpage.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/newpage_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/saveTemplate_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/saveWord.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/saveWord_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/templates.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/ckeditorExtraPlugin/icons/templates_black.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/configs/calendar_event_editor_config.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/configs/campaign_editor_config.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/configs/event_editor_config.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/configs/note_editor_config.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/configs/note_editor_config_min.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/configs/note_editor_config_minimal.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/dialog.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/dialog_ie.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/dialog_ie7.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/dialog_ie8.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/dialog_iequirks.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/editor.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/editor_gecko.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/editor_ie.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/editor_ie7.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/editor_ie8.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/editor_iequirks.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/icons.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/icons_hidpi.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/images/arrow.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/images/close.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/images/hidpi/close.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/images/hidpi/lock-open.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/images/hidpi/lock.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/images/hidpi/refresh.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/images/lock-open.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/images/lock.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/images/refresh.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/readme.md");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/moono-dark/skin.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/dialog.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/dialog_ie.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/dialog_ie8.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/dialog_iequirks.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/editor.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/editor_gecko.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/editor_ie.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/editor_ie8.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/editor_iequirks.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/icons.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/icons_hidpi.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/arrow.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/close.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/hidpi/close.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/hidpi/lock-open.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/hidpi/lock.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/hidpi/refresh.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/lock-open.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/lock.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/refresh.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/images/spinner.gif");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/readme.md");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/n1theme/skin.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/README.md");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/blue.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/dialog.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/dialog_ie.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/dialog_ie8.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/dialog_iequirks.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/editor.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/editor_gecko.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/editor_ie.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/editor_ie8.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/editor_iequirks.css");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/flat_white.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/gold.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/arrow.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/close.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/hidpi/close.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/hidpi/lock-open.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/hidpi/lock.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/hidpi/refresh.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/lock-open.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/lock.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/refresh.png");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/images/spinner.gif");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/dialog.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/dialog_ie.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/dialog_ie8.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/dialog_iequirks.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/editor.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/editor_gecko.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/editor_ie.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/editor_ie8.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/editor_iequirks.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_colorpanel.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_elementspath.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_icons.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_mainui.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_menu.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_notifications.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_panel.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_reset.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_richcombo.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_toolbar.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/sass/partials/_variables.scss");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/skin.js");
unlink(SystemURLs::getDocumentRoot() . "/skin/js/ckeditor/themes/prestige/white_bevel.png");



$logger->info("End of delete :  all unusefull files");
