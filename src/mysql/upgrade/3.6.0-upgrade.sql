CREATE TABLE note_nte_share (
    `nte_sh_id` mediumint(9) unsigned  NOT NULL AUTO_INCREMENT,
    `nte_sh_note_ID` mediumint(9) unsigned NULL,
    `nte_sh_share_to_person_ID` mediumint(9) unsigned NULL,
    `nte_sh_share_to_family_ID` mediumint(9) unsigned NULL,
    `nte_sh_share_rights` smallint(2) NOT NULL default '1',
    PRIMARY KEY(nte_sh_id),
    CONSTRAINT fk_nte_note_ID 
      FOREIGN KEY (nte_sh_note_ID) 
      REFERENCES note_nte(nte_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_nte_share_from_person_ID 
      FOREIGN KEY (nte_sh_share_to_person_ID) 
      REFERENCES person_per(per_ID)
      ON DELETE CASCADE,
    CONSTRAINT fk_nte_share_from_family_ID 
      FOREIGN KEY (nte_sh_share_to_family_ID) 
      REFERENCES family_fam(fam_ID)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `note_nte` ADD `nte_Title` varchar(100) DEFAULT '';
ALTER TABLE `note_nte` ADD `nte_isEditedBy` mediumint(8) unsigned NOT NULL default '0';
ALTER TABLE `note_nte` ADD `nte_isEditedByDate` datetime default NULL;



