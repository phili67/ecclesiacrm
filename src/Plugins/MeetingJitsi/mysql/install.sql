--
-- Table structure for table `personmeeting_pm`
--

CREATE TABLE `personjitsimeeting_pm` (
    `jm_pm_ID` mediumint(9) NOT NULL auto_increment,
    `jm_pm_person_id` mediumint(9) unsigned NULL,
    `jm_pm_code` varchar(255) NOT NULL default '',
    `jm_pm_cr_date` datetime default NULL,
    PRIMARY KEY  (`jm_pm_ID`),
    UNIQUE KEY `jm_pm_ID` (`jm_pm_ID`),
    CONSTRAINT fk_jm_pm_person_id
        FOREIGN KEY (jm_pm_person_id) REFERENCES person_per(per_ID)
            ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `personmeeting_pm`
--

--
-- Table structure for table `lastpersonmeeting_lpm`
--

CREATE TABLE `personlastjitsimeeting_plm` (
     `jm_plm_ID` mediumint(9) NOT NULL auto_increment,
     `jm_plm_person_id` mediumint(9) NOT NULL,
     `jm_plm_personmeeting_pm_id` mediumint(9) NOT NULL,
     PRIMARY KEY  (`jm_plm_ID`),
     UNIQUE KEY `jm_lm_ID` (`jm_plm_ID`),
     CONSTRAINT fk_jm_plm_personmeeting_pm_id
         FOREIGN KEY (jm_plm_personmeeting_pm_id) REFERENCES personjitsimeeting_pm(jm_pm_ID)
             ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `personlastmeeting_plm`
--

INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`)
VALUES ('MeetingJitsi', 'Plugin for jitsi Meeting', 'Meeting', NULL, '', '0', '1.0', 'jm_');

-- insert the menu item
-- the first one is the main menu !!!
INSERT INTO `plugin_menu_barre` (`plgn_mb_plugin_name`, `plgn_mb_plugin_Display_name`, `plgn_mb_url`, `plgn_bm_icon`, `plgn_bm_grp_sec`) VALUES ('MeetingJitsi', 'Meetings', 'v2/meeting/dashboard', 'fas fa-video', 'usr_meeting');
