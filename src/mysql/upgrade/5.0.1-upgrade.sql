-- bug correction
DELETE FROM `gdpr_infos` WHERE `gdpr_info_About`='Person' AND `gdpr_info_Name`='Suffix' AND `gdpr_info_Type`='3' AND `gdpr_info_comment` = '' LIMIT 1;
DELETE FROM `gdpr_infos` WHERE `gdpr_info_About`='Family' AND `gdpr_info_Name`='Wedding Date' AND `gdpr_info_Type`='2' AND `gdpr_info_comment` = '' LIMIT 1;
DELETE FROM `gdpr_infos` WHERE `gdpr_info_About`='Person' AND `gdpr_info_Name`='LinkedIn' AND `gdpr_info_Type`='3' AND `gdpr_info_comment` = '' LIMIT 1;

-- GDPR : add new comments for properties
ALTER TABLE `property_pro` ADD `pro_Comment` text NOT NULL default '' COMMENT 'comment for GDPR';

-- add a new security option
ALTER TABLE `user_usr` ADD `usr_showMenuQuery` tinyint(1) NOT NULL default '0';

-- fix the new roles
DELETE FROM `userrole_usrrol` WHERE `usrrol_id`='1';
DELETE FROM `userrole_usrrol` WHERE `usrrol_id`='2';
DELETE FROM `userrole_usrrol` WHERE `usrrol_id`='3';

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`, `usrrol_global`, `usrrol_permissions`, `usrrol_value`) VALUES
(1, 'User Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:1;QueryMenu:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1;Style:skin-red-light', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bExportSundaySchoolCSV:TRUE;bExportSundaySchoolPDF:TRUE;bCreateDirectory:TRUE;bExportCSV:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bExportSundaySchoolCSV:1;bExportSundaySchoolPDF:1;bCreateDirectory:1;bExportCSV:1;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1'),
(2, 'User Min', 'AddRecords:0;EditRecords:0;DeleteRecords:0;ShowCart:0;ShowMap:0;MenuOptions:0;ManageGroups:0;Finance:0;Notes:0;EditSelf:1;Canvasser:0;Admin:0;QueryMenu:0;MainDashboard:0;SeePrivacyData:0;MailChimp:0;GdrpDpo:0;PastoralCare:0;Style:skin-yellow-light', 'bEmailMailto:FALSE;sMailtoDelimiter:TRUE;bExportSundaySchoolCSV:FALSE;bExportSundaySchoolPDF:FALSE;bCreateDirectory:FALSE;bExportCSV:FALSE;bUSAddressVerification:FALSE;bShowTooltip:TRUE;sCSVExportDelemiter:FALSE;sCSVExportCharset:FALSE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE', 'bEmailMailto:;sMailtoDelimiter:,;bExportSundaySchoolCSV:;bExportSundaySchoolPDF:;bCreateDirectory:;bExportCSV:;bUSAddressVerification:;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1'),
(3, 'User Max but not Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1;Style:skin-red-light', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bExportSundaySchoolCSV:TRUE;bExportSundaySchoolPDF:TRUE;bCreateDirectory:TRUE;bExportCSV:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bExportSundaySchoolCSV:1;bExportSundaySchoolPDF:1;bCreateDirectory:1;bExportCSV:1;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1');

-- reclassifications in queryList
UPDATE `query_qry` SET qry_Type_ID = '4' WHERE qry_ID = 31;
UPDATE `query_qry` SET qry_Type_ID = '4' WHERE qry_ID = 32;
UPDATE `query_type` SET qry_type_Category = 'Pledges and Payments' WHERE qry_type_id = 4;


-- adding data constraint to person2volunteeropp_p2vo

-- First we clean person2volunteeropp_p2vo in case of lost datas
DELETE FROM `person2volunteeropp_p2vo` WHERE `p2vo_ID` IN (
select * 
From
(
SELECT t1. p2vo_ID
FROM person2volunteeropp_p2vo t1
    LEFT JOIN volunteeropportunity_vol t2 ON t1.p2vo_vol_ID = t2. vol_ID
WHERE t2. vol_ID IS NULL
)
AS tmp
);

-- now we upgrade the schema
ALTER TABLE `person2volunteeropp_p2vo`  MODIFY p2vo_vol_ID mediumint(9) unsigned NOT NULL;
ALTER TABLE `volunteeropportunity_vol` MODIFY vol_ID mediumint(9) unsigned NOT NULL auto_increment;

ALTER TABLE `person2volunteeropp_p2vo`
ADD   CONSTRAINT fk_p2vo_vol_ID
    FOREIGN KEY (p2vo_vol_ID) REFERENCES volunteeropportunity_vol(vol_ID)
    ON DELETE CASCADE;

ALTER TABLE `person2volunteeropp_p2vo`  MODIFY p2vo_per_ID mediumint(9) unsigned NOT NULL;

ALTER TABLE `person2volunteeropp_p2vo`
ADD   CONSTRAINT fk_p2vo_per_ID
    FOREIGN KEY (p2vo_per_ID) REFERENCES person_per(per_ID)
    ON DELETE CASCADE;
