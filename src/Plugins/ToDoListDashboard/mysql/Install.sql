INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`, `plgn_default_orientation`, `plgn_default_color`, `plgn_securities`)
VALUES ('ToDoListDashboard', 'Plugin to manage todo list', 'Dashboard', NULL, '', '1', '1.0', 'tdl_', 'inside_category_menu', 'left', 'bg-gradient-red text-white',1073741824);

--
-- Table structure for table `tdl_list`
--

CREATE TABLE `tdl_list` (
     `tdl_l_id` mediumint(9) NOT NULL auto_increment,
     `tdl_l_name` varchar(255) NOT NULL default '' COMMENT 'Name list',
     `tdl_l_user_id` mediumint(9) unsigned NOT NULL default '0' COMMENT 'user id ',
     `tdl_l_visible` BOOLEAN NOT NULL default 0 COMMENT 'list is visible',
     PRIMARY KEY  (`tdl_l_id`),
     UNIQUE KEY `wf_c_id` (`tdl_l_id`),
     CONSTRAINT fk_tdl_l_user_id
         FOREIGN KEY (tdl_l_user_id) REFERENCES user_usr(usr_per_ID)
             ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;


--
-- Table structure for table `tdl_l_item`
--

CREATE TABLE `tdl_l_item` (
    `tdl_l_i_id` mediumint(9) NOT NULL auto_increment,
    `tdl_l_i_list` mediumint(9) NOT NULL default '0' COMMENT 'the list the item belong',
    `tdl_l_i_checked` BOOLEAN NOT NULL default 0 COMMENT 'item is checked',
    `tdl_l_i_name` varchar(255) NOT NULL default '' COMMENT 'Name of the item',
    `tdl_l_i_date_time` datetime default NULL,
    `tdl_l_i_place` mediumint(9) unsigned default 0 COMMENT 'position in the list',
    PRIMARY KEY  (`tdl_l_i_id`),
    UNIQUE KEY `wf_c_id` (`tdl_l_i_id`),
    CONSTRAINT fk_tdl_l_i_list
        FOREIGN KEY (tdl_l_i_list) REFERENCES tdl_list(tdl_l_id)
            ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;
