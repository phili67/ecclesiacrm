INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`, `plgn_default_orientation`, `plgn_default_color`, `plgn_securities`, `plgn_UserRole_Dashboard_Availability`)
VALUES ('NewsDashboard', 'Dashboard Plugin : news', 'Dashboard', NULL, '', '1', '1.0', 'news_', 'inside_category_menu', 'top', 'bg-gradient-blue text-white',1073741824,1);

--
-- Table structure for table `news_nw`
--

CREATE TABLE `news_nw` (
    `news_nw_id` mediumint(9) NOT NULL auto_increment,
    `news_nw_user_id` mediumint(9) unsigned NOT NULL default '0' COMMENT 'user id ',
    `news_nw_title` varchar(255) NOT NULL default '' COMMENT 'Note Title',
    `news_nw_Text` longtext COMMENT 'Note',
    `news_nw_type` enum('infos','to_plan','to_note','important','very_important') NOT NULL default 'infos',
    `news_nw_DateEntered` datetime NOT NULL,
    `news_nw_DateLastEdited` datetime default NULL,
    PRIMARY KEY  (`news_nw_id`),
    UNIQUE KEY `wf_c_id` (`news_nw_id`),
    CONSTRAINT fk_news_nw_user_id
        FOREIGN KEY (news_nw_user_id) REFERENCES user_usr(usr_per_ID)
            ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;
