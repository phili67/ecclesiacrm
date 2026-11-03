UPDATE `userconfig_ucfg` SET `ucfg_value` = 'light' WHERE `userconfig_ucfg`.`ucfg_name` = 'sStyleBrandLinkColor';
UPDATE `userconfig_ucfg` SET `ucfg_value` = 'light' WHERE `userconfig_ucfg`.`ucfg_name` = 'sStyleSideBar';
UPDATE `userconfig_ucfg` SET `ucfg_value` = 'light' WHERE `userconfig_ucfg`.`ucfg_name` = 'sStyleNavBarColor';


ALTER TABLE `plugin_menu_bar` ADD `plgn_mb_special_classes` text DEFAULT '' COMMENT 'special classe(s) for the menu bar link';

--
-- Table structure for table `plugin_dependencies`
--

CREATE TABLE `plugin_dependencies` (
  `plgn_dep_ID` mediumint(8) unsigned NOT NULL auto_increment,
  `plgn_dep_plugin_ID` mediumint(8) unsigned NOT NULL default '0',
  `plgn_dep_url` varchar(255) DEFAULT '',
  `plgn_dep_extension` varchar(255) DEFAULT '' COMMENT 'extension of the file to download js or php',
  CONSTRAINT fk_plgn_dep_plugin_id FOREIGN KEY (plgn_dep_plugin_ID) REFERENCES plugin(plgn_ID) ON DELETE CASCADE,
  PRIMARY KEY  (`plgn_dep_ID`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `plugin_dependencies`
--


-- 2026-07-01 12:00:00
ALTER TABLE user_usr DROP COLUMN usr_MailChimp;

--
-- Table structure for table `wopi_tokens` : access tokens used by Collabora Online (WOPI protocol)
--

CREATE TABLE `wopi_tokens` (
  `token` CHAR(64) NOT NULL,
  `file_id` CHAR(40) NOT NULL COMMENT 'sha1 of owner person id and relative path',
  `owner_id` mediumint(9) unsigned NOT NULL COMMENT 'person id of the eDrive owner',
  `user_id` mediumint(9) unsigned NOT NULL COMMENT 'person id of the user who edits the file',
  `rel_path` VARCHAR(1024) NOT NULL COMMENT 'path of the file in the owner eDrive',
  `can_write` TINYINT(1) NOT NULL DEFAULT 1,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`token`),
  INDEX `idx_wopi_expires` (`expires_at`),
  INDEX `idx_wopi_file` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 2026-10-31 21:10:00

--
-- Table structure for table `quick_access` : quick access items for main dashboard
--

CREATE TABLE `quick_access` (
  `qa_id` mediumint(8) unsigned NOT NULL auto_increment,
  `qa_font_awesome_icon` varchar(255) DEFAULT '' COMMENT 'Font Awesome icon for the quick access item',
  `qa_label` varchar(255) DEFAULT '' COMMENT 'name of the menu item bar',
  `qa_url` varchar(255) DEFAULT '' COMMENT 'URL for the quick access item',
  `qa_right` varchar(255) DEFAULT '' COMMENT 'bGDPR ... the right required to access this quick access item, none for plugins',
  `qa_user_right` int(11) DEFAULT 0 COMMENT 'bit value of the user right required to access this quick access item : SecurityOptions::bit in User.php, by default : 1073741824, 0 by default for plugins',
  `qa_is_plugin` tinyint(1) DEFAULT 0 COMMENT 'Indicates if the quick access item is a plugin',
  `qa_plugin_name` varchar(255) DEFAULT '' COMMENT 'Name of the plugin if the quick access item is a plugin, the right depends on it',
  PRIMARY KEY (`qa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `quick_access` (`qa_id`, `qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`) VALUES ('1', 'fa fa-cloud', 'eDrive', 'v2/edrive/dashboard', 'bEnabledEdrive', 131072, '0', '');
INSERT INTO `quick_access` (`qa_id`, `qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`) VALUES ('2', 'far fa-calendar-alt', 'Calendar', 'v2/calendar', 'bEnabledEvents', 1073741824, '0', '');
INSERT INTO `quick_access` (`qa_id`, `qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`) VALUES ('3', 'fas fa-shield-alt fa-lg', 'GDPR', 'v2/gdpr', 'bGDPR', 8, '0', '');
INSERT INTO `quick_access` (`qa_id`, `qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`) VALUES ('4', 'fas fa-search fa-lg', '', 'v2/people/list/none', '', 1073741824, '0', '');
INSERT INTO `quick_access` (`qa_id`, `qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`) VALUES ('5', 'fas fa-hands-helping', 'Volunteers', 'v2/volunteeropportunity', 'bEnabledVolunteers', 1073741824, '0', '');
INSERT INTO `quick_access` (`qa_id`, `qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`) VALUES ('6', 'far fa-user', 'Groups', 'v2/group/list', '', 1024, '0', '');
INSERT INTO `quick_access` (`qa_id`, `qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`) VALUES ('7', 'fa-solid fa-children', 'Sunday School', 'v2/sundayschool/dashboard', 'bEnabledSundaySchool', 1073741824, '0', '');
INSERT INTO `quick_access` (`qa_id`, `qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`) VALUES ('8', 'fas fa-heartbeat', 'Pastoral Care', 'v2/pastoralcare/dashboard', 'bEnabledPastoralCare', 2, '0', '');
INSERT INTO `quick_access` (`qa_id`, `qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`) VALUES ('9', 'fas fa-hand-holding-usd', 'Fundraiser', 'v2/fundraiser/find', 'bEnabledFundraiser', 1048576, '0', '');

