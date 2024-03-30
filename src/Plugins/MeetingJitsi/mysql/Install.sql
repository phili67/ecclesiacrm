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

--
-- Dumping data for table `plugin_jitsimeeting_pref_pjmp`
--

CREATE TABLE `plugin_pref_jitsimeeting_pjmp` (
     `jm_pjmp_ID` mediumint(9) NOT NULL auto_increment,
     `jm_pjmp_personmeeting_pm_id` mediumint(8) unsigned NOT NULL,
     `jm_pjmp_domain` varchar(255) NOT NULL default '8x8.vc',
     `jm_pjmp_domainscriptpath` varchar(255) NOT NULL default 'https://8x8.vc/external_api.js',
     `jm_pjmp_apikey` varchar(255) NOT NULL default 'Your Key Here',
     PRIMARY KEY  (`jm_pjmp_ID`),
     UNIQUE KEY `jm_pjmp_ID` (`jm_pjmp_ID`),
     CONSTRAINT fk_jm_pjmp_personmeeting_pm_id
         FOREIGN KEY (jm_pjmp_personmeeting_pm_id) REFERENCES plugin(plgn_ID)
             ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `plugin_jitsimeeting_pref_pjmp`
--

INSERT INTO `plugin` ( `plgn_Name`, `plgn_Description`, `plgn_Category`, `plgn_image`, `plgn_installation_path`, `plgn_activ`, `plgn_version`, `plgn_prefix`, `plgn_position`)
VALUES ('MeetingJitsi', 'Plugin for jitsi Meeting', 'Meeting', NULL, '', '0', '1.0', 'jm_', 'after_category_menu');

INSERT INTO `plugin_pref_jitsimeeting_pjmp` (`jm_pjmp_personmeeting_pm_id`, `jm_pjmp_domain` ,`jm_pjmp_domainscriptpath`)
VALUES (LAST_INSERT_ID(),'meet.jit.si', 'https://meet.jit.si/external_api.js');

-- insert the menu item
-- the first one is the main menu !!!
INSERT INTO `plugin_menu_bar` (`plgn_mb_plugin_name`, `plgn_mb_plugin_Display_name`, `plgn_mb_url`, `plgn_bm_icon`, `plgn_bm_grp_sec`) VALUES
('MeetingJitsi', 'Jitsi', 'v2/meeting/dashboard', 'fas fa-video', ''),
('MeetingJitsi', 'Dashboard', 'v2/meeting/dashboard', 'fas fa-tachometer-alt', ''),
('MeetingJitsi', 'Settings', 'v2/meeting/settings', 'fas fa-cogs', 'usr_admin');
