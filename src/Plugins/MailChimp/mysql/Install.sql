INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`)
VALUES ('MailChimp', 'Plugin for MailChimp', 'Communication', NULL, '', '0', '1.0', 'mc_', 'inside_category_menu');


INSERT INTO `plugin_dependencies` ( `plgn_dep_plugin_ID`, `plgn_dep_url`, `plgn_dep_extension`) VALUES
(LAST_INSERT_ID(), 'Plugin\Synchronize\MailchimpDashboardItemPlugin', 'synchronize'),
(LAST_INSERT_ID(), 'Plugins/MailChimp/skin/js/synchronize/MailChimpDashboardItem.js ', 'js');


-- insert the menu item
-- the first one is the main menu !!!
INSERT INTO `plugin_menu_bar` (`plgn_mb_plugin_name`, `plgn_mb_plugin_Display_name`, `plgn_mb_url`, `plgn_bm_icon`, `plgn_bm_grp_sec`) VALUES
('MailChimp', 'MailChimp', 'v2/mailchimp2/dashboard', 'fab fa-mailchimp', ''),
('MailChimp', 'Dashboard', 'v2/mailchimp2/dashboard', 'fas fa-tachometer-alt', '');

-- we insert the rest of the links
INSERT INTO `plugin_menu_bar` (`plgn_mb_plugin_name`, `plgn_mb_plugin_Display_name`, `plgn_mb_url`, `plgn_bm_grp_sec`, `plgn_mb_parent_ID`) VALUES
('MailChimp', 'Persons Not In MailChimp', 'v2/mailchimp2/notinmailchimpemailspersons', '', LAST_INSERT_ID()),
('MailChimp', 'Families Not In MailChimp', 'v2/mailchimp2/notinmailchimpemailsfamilies', '', LAST_INSERT_ID()),
('MailChimp', 'Duplicate Emails', 'v2/mailchimp2/duplicateemails', '', LAST_INSERT_ID());


INSERT INTO `plugin_menu_bar` (`plgn_mb_plugin_name`, `plgn_mb_plugin_Display_name`, `plgn_mb_url`, `plgn_bm_icon`, `plgn_bm_grp_sec`, `plgn_mb_special_classes`) VALUES
('MailChimp', 'Email Lists', 'v2/mailchimp2/dashboard', 'fas fa-list', '', 'lists_class_menu'),
('MailChimp', 'Settings', 'v2/mailchimp2/settings', 'fas fa-cogs', 'usr_admin', '');

--
-- Table structure for table `mc_params`
--

CREATE TABLE `mc_params` (
     `mc_p_id` mediumint(9) NOT NULL auto_increment,
     `mc_p_api_key` varchar(255) NOT NULL default '' COMMENT 'MailChimp api key',
     `mc_p_request_timeout` int(11) NOT NULL default '3600' COMMENT 'MailChimp Request TimeOut',
     `mc_p_with_address_phone` boolean NOT NULL default '0' COMMENT 'MailChimp With Address Phone',
     `mc_p_email_sender` varchar(255) NOT NULL default '' COMMENT 'MailChimp Email Sender',
     `mc_p_contents_external_css_font` text COMMENT 'MailChimp Contents External Css Font',
     `mc_p_extra_font` text COMMENT 'MailChimp Extra Font',     
     PRIMARY KEY  (`mc_p_id`),
     UNIQUE KEY `mc_p_id` (`mc_p_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;


INSERT INTO `mc_params` (`mc_p_api_key`, `mc_p_request_timeout`, `mc_p_with_address_phone`, `mc_p_email_sender`, `mc_p_contents_external_css_font`, `mc_p_extra_font`) VALUES
('None', 3600, 0, '', '', '');


