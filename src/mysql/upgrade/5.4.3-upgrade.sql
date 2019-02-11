ALTER TABLE `pastoral_care_type` MODIFY pst_cr_tp_comment text NOT NULL COMMENT 'comment for GDPR';
ALTER TABLE `family_custom_master` MODIFY fam_custom_comment text NOT NULL COMMENT 'comment for GDPR';
ALTER TABLE `person_custom_master` MODIFY custom_comment text NOT NULL COMMENT 'comment for GDPR';
ALTER TABLE `property_pro` MODIFY pro_Comment text NOT NULL COMMENT 'comment for GDPR';
ALTER TABLE `gdpr_infos` MODIFY gdpr_info_comment text NOT NULL COMMENT 'comment for GDPR';