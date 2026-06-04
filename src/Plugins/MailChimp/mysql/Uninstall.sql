DELETE FROM `plugin` WHERE `plgn_Name`='MailChimp';
DELETE FROM `plugin_menu_bar` WHERE `plgn_mb_plugin_name`='MailChimp';

DROP TABLE `mc_params`;
DROP TABLE `mc_attributes`;
DROP TABLE `mc_account_test`;


