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
