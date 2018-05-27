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