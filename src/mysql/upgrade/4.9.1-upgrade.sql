-- 
-- Alter the table to have the real primary key
-- 
ALTER TABLE `person_custom_master` ADD `custom_comment` text NOT NULL default '' COMMENT 'comment for GDPR';
ALTER TABLE `family_custom_master` ADD `fam_custom_comment` text NOT NULL default '' COMMENT 'comment for GDPR';