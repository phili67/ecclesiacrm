--
-- We create group_manager_person table
-- 
CREATE TABLE group_manager_person (
    `grp_mgr_per_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `grp_mgr_per_person_ID` mediumint(9) unsigned NOT NULL,
    `grp_mgr_per_group_ID` mediumint(9) unsigned NOT NULL,
    PRIMARY KEY(grp_mgr_per_id),
    CONSTRAINT fk_grp_mgr_per_person_ID
      FOREIGN KEY (grp_mgr_per_person_ID) 
      REFERENCES person_per(per_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_grp_mgr_per_group_ID
      FOREIGN KEY (grp_mgr_per_group_ID) 
      REFERENCES group_grp(grp_ID)
      ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


--
-- We add the primary key to the groupprop_master table, so we can define a real propel class
-- 
ALTER TABLE `groupprop_master` ADD `grp_mster_id` mediumint(9) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- We create ckeditor_templates table
-- 
CREATE TABLE ckeditor_templates (
    `cke_tmp_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `cke_tmp_per_ID` mediumint(9) unsigned NOT NULL,
    `cke_tmp_title` varchar(255) NOT NULL default '',
    `cke_tmp_desc` varchar(255) default NULL,
    `cke_tmp_text` text,
    `cke_tmp_image` varchar(255) default NULL,
    PRIMARY KEY(cke_tmp_id),
    CONSTRAINT fk_cke_tmp_per_ID
      FOREIGN KEY (cke_tmp_per_ID) 
      REFERENCES person_per(per_ID)
      ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

--
-- We add the primary key to the record2property_r2p table, so we can define a real propel class
-- 
ALTER TABLE `record2property_r2p` ADD `r2p_id` mediumint(9) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT;

--
-- Now the menuconfig_mcf is no more usefull
-- 
DROP TABLE IF EXISTS menuconfig_mcf;
