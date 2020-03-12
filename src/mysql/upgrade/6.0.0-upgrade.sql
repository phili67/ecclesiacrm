-- add the new family property
ALTER TABLE `pastoral_care` ADD `pst_cr_family_id` mediumint(9) unsigned NULL default NULL;

ALTER TABLE `pastoral_care`
ADD CONSTRAINT fk_pst_cr_family_id
  FOREIGN KEY (pst_cr_family_id)
  REFERENCES family_fam(fam_ID)
  ON DELETE CASCADE;

-- change the the type of the pst_cr_person_id
ALTER TABLE `pastoral_care` MODIFY `pst_cr_person_id`  mediumint(9) unsigned NULL default NULL;



-- we have to delete the lost datas, sometimes when a group is deleted the member are not deleted too
DELETE FROM person2group2role_p2g2r WHERE p2g2r_grp_ID IN (
SELECT *
FROM
(
SELECT DISTINCT t1. p2g2r_grp_ID
FROM `person2group2role_p2g2r` t1
WHERE t1.p2g2r_grp_ID NOT IN (
	SELECT  grp_ID
	FROM `group_grp`
	WHERE 1
)
)
AS tmp
)
