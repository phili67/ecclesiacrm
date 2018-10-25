-- 
-- Alter the table to have the real primary key
-- 
ALTER TABLE `person_custom_master` ADD `custom_comment` text NOT NULL default '' COMMENT 'comment for GDPR';
ALTER TABLE `pastoral_care_type` ADD `pst_cr_tp_comment` text NOT NULL default '' COMMENT 'comment for GDPR';
ALTER TABLE `family_custom_master` ADD `fam_custom_comment` text NOT NULL default '' COMMENT 'comment for GDPR';

CREATE TABLE `gdpr_infos` (
  `gdpr_info_id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `gdpr_info_About` enum('Person','Family') NOT NULL default 'Person',
  `gdpr_info_Name` varchar(40) NOT NULL default '',
  `gdpr_info_Type` tinyint(4) NOT NULL default '0',
  `gdpr_info_comment` text NOT NULL default '' COMMENT 'comment for GDPR',
  PRIMARY KEY  (`gdpr_info_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

INSERT INTO `gdpr_infos` (`gdpr_info_About`, `gdpr_info_Name`, `gdpr_info_Type`, `gdpr_info_comment`) VALUES 
('Person', 'Gender', '3', ''),
('Person', 'Title', '3', ''),
('Person', 'First Name', '3', ''),
('Person', 'Middle Name', '3', ''),
('Person', 'Last Name', '3', ''),
('Person', 'Suffix', '3', ''),
('Person', 'Birth Month', '12', ''),
('Person', 'Birth Day', '12', ''),
('Person', 'Birth Year', '6', ''),
('Person', 'Hide Age', '3', ''),
('Person', 'Role', '12', ''),
('Person', 'Home Phone', '3', ''),
('Person', 'Work Phone', '3', ''),
('Person', 'Mobile Phone', '3', ''),
('Person', 'Email', '3', ''),
('Person', 'Work / Other Email', '3', ''),
('Person', 'Facebook ID', '3', ''),
('Person', 'Twitter', '3', ''),
('Person', 'LinkedIn', '3', ''),
('Person', 'Classification', '12', ''),
('Person', 'Membership Date','2',''),
('Person', 'Friend Date','2',''),
('Family', 'Family Name', '3', ''),
('Family', 'Address 1', '4', ''),
('Family', 'Address 2', '4', ''),
('Family', 'City', '3', ''),
('Family', 'Country', '12', ''),
('Family', 'State', '3', ''),
('Family', 'Latitude', '7', ''),
('Family', 'Longitude', '7', ''),
('Family', 'Home Phone', '3', ''),
('Family', 'Work Phone', '3', ''),
('Family', 'Mobile Phone', '3', ''),
('Family', 'Email', '3', ''),
('Family', 'Send Newsletter', '1', ''),
('Family', 'Wedding Date', '2', ''),
('Family', 'Ok To Canvass', '2', '');