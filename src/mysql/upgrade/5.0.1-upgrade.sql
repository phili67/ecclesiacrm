DELETE FROM `gdpr_infos` WHERE `gdpr_info_About`='Person' AND `gdpr_info_Name`='Suffix' AND `gdpr_info_Type`='3' AND `gdpr_info_comment` = '' LIMIT 1;
DELETE FROM `gdpr_infos` WHERE `gdpr_info_About`='Family' AND `gdpr_info_Name`='Wedding Date' AND `gdpr_info_Type`='2' AND `gdpr_info_comment` = '' LIMIT 1;
DELETE FROM `gdpr_infos` WHERE `gdpr_info_About`='Person' AND `gdpr_info_Name`='LinkedIn' AND `gdpr_info_Type`='3' AND `gdpr_info_comment` = '' LIMIT 1;

ALTER TABLE `property_pro` ADD `pro_Comment` text NOT NULL default '' COMMENT 'comment for GDPR';