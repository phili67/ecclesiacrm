--
-- Pastoral care type for a person
-- 
CREATE TABLE pastoral_care_type (
    `pst_cr_tp_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `pst_cr_tp_title` varchar(255) NOT NULL default '',
    `pst_cr_tp_desc` varchar(255) NOT NULL default '',
    `pst_cr_tp_visible` BOOLEAN NOT NULL default 0,
    PRIMARY KEY(pst_cr_tp_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

INSERT INTO `pastoral_care_type` (`pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`) VALUES
  ('Why did you come to the church?','', true),
  ('Why do you keep coming?','', true),
  ('Do you have any suggestions for us?','', true),
  ('How did you learn of the church?','', true),
  ('Baptism', 'Baptism formation', false),
  ('Mariage', 'Mariage formation', false),
  ('Psychology', 'Psychology therapy', false);

--
-- Pastoral care for a person
-- 
CREATE TABLE pastoral_care (
    `pst_cr_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `pst_cr_person_id` mediumint(9) unsigned NOT NULL,
    `pst_cr_pastor_id` mediumint(9) unsigned NULL,
    `pst_cr_pastor_Name` varchar(255) NOT NULL default '',
    `pst_cr_Type_id` mediumint(9) unsigned NOT NULL,
    `pst_cr_date` datetime default NULL,
    `pst_cr_visible` BOOLEAN NOT NULL default 0,
    `pst_cr_Text` text,
    PRIMARY KEY(pst_cr_id),
    CONSTRAINT fk_pst_cr_person_id
      FOREIGN KEY (pst_cr_person_id) 
      REFERENCES person_per(per_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_pst_cr_pastor_id
      FOREIGN KEY (pst_cr_pastor_id) 
      REFERENCES person_per(per_ID)
      ON DELETE SET NULL,
    CONSTRAINT fk_pst_cr_Type_id
      FOREIGN KEY (pst_cr_Type_id) 
      REFERENCES pastoral_care_type(pst_cr_tp_id)
      ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


--
-- ALTER TABLE `query_qry` DROP COLUMN `qry_Type`;
--
ALTER TABLE `user_usr` ADD COLUMN `usr_PastoralCare` tinyint(1) DEFAULT '0';
ALTER TABLE `user_usr` ADD COLUMN `usr_MailChimp` tinyint(1) DEFAULT '0';
ALTER TABLE `user_usr` ADD COLUMN `usr_MainDashboard` tinyint(1) DEFAULT '0';
ALTER TABLE `user_usr` ADD COLUMN `usr_SeePrivacyData` tinyint(1) DEFAULT '0';

delete from `userconfig_ucfg` where `ucfg_name` = 'bSeePrivacyData';

-- 
-- GDRP update
--
ALTER TABLE `user_usr` ADD COLUMN `usr_GDRP_DPO` tinyint(1) DEFAULT '0';
ALTER TABLE `person_per` ADD COLUMN `per_DateDeactivated` datetime default NULL;

-- 
-- Note_nte update
--

-- alter nte_per_ID for foreign key
ALTER TABLE note_nte CHANGE nte_per_ID  nte_per_ID mediumint(9) unsigned NULL;

UPDATE `note_nte`
   SET nte_per_ID = NULL
WHERE nte_ID in (
  select tt.nte_ID 
    from 
    (
    SELECT nte_ID FROM `note_nte` WHERE nte_per_ID=0
    ) as tt
);

ALTER TABLE note_nte
ADD CONSTRAINT fk_nte_per_ID 
  FOREIGN KEY (nte_per_ID) REFERENCES person_per(per_ID)
  ON DELETE CASCADE;

-- alter nte_fam_ID for foreign key
ALTER TABLE note_nte CHANGE nte_fam_ID  nte_fam_ID mediumint(9) unsigned NULL;

UPDATE `note_nte`
   SET nte_fam_ID = NULL
WHERE nte_ID in (
  select tt.nte_ID 
    from 
    (
    SELECT nte_ID FROM `note_nte` WHERE nte_fam_ID=0
    ) as tt
);


ALTER TABLE note_nte
ADD CONSTRAINT fk_nte_fam_ID
  FOREIGN KEY (nte_fam_ID) REFERENCES family_fam(fam_ID)
  ON DELETE CASCADE;
