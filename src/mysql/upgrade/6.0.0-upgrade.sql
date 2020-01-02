-- add the new family property
ALTER TABLE `pastoral_care` ADD `pst_cr_family_id` mediumint(9) unsigned NULL default NULL;

ALTER TABLE `pastoral_care`
ADD CONSTRAINT fk_pst_cr_family_id
  FOREIGN KEY (pst_cr_family_id)
  REFERENCES family_fam(fam_ID)
  ON DELETE CASCADE;

-- change the the type of the pst_cr_person_id
ALTER TABLE `pastoral_care` MODIFY `pst_cr_person_id`  mediumint(9) unsigned NULL default NULL;
