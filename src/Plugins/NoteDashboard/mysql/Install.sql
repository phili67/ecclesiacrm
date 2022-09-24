INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`, `plgn_default_orientation`, `plgn_default_color`, `plgn_securities`)
VALUES ('NoteDashboard', 'Plugin to a little note for a each user', 'Dashboard', NULL, '', '1', '1.0', 'nd_', 'inside_category_menu', 'center', 'bg-gradient-yellow text-black',1073741824);

--
-- Table structure for table `tdl_list`
--

CREATE TABLE `NoteDashboard_nd` (
    `nd_id` mediumint(9) NOT NULL auto_increment,
    `nd_user_id` mediumint(9) unsigned NOT NULL default '0' COMMENT 'user id ',
    `nd_note` text COMMENT 'All the workflow summary',
    PRIMARY KEY  (`nd_id`),
    UNIQUE KEY `nd_id` (`nd_id`),
    CONSTRAINT fk_nd_user_id
        FOREIGN KEY (nd_user_id) REFERENCES user_usr(usr_per_ID)
            ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;
