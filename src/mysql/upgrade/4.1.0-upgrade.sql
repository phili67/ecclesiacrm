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
      FOREIGN KEY (grp_mgr_per_person_ID) 
      REFERENCES group_grp(grp_ID)
      ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;