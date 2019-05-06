--
--
-- Important : this update can only be done if you're login to the crm
--
--

ALTER TABLE `user_usr` DROP COLUMN usr_Style;

ALTER TABLE userconfig_ucfg MODIFY COLUMN ucfg_type ENUM('text','number','date','boolean','textarea','choice');

-- --------------------------------------------------------

--
-- Table structure for table `userconfig_choices_ucfg_ch`
--

CREATE TABLE `userconfig_choices_ucfg_ch` (
  `ucfg_ch_id` mediumint(9) unsigned NOT NULL,  
  `ucfg_name` text,
  `ucfg_choices` varchar(200) NOT NULL,
  PRIMARY KEY  (`ucfg_ch_id`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


INSERT INTO `userconfig_choices_ucfg_ch` (`ucfg_ch_id`,`ucfg_name`,`ucfg_choices`) VALUES
(0,'Maps','GoogleMaps,AppleMaps,BingMaps'),
(1,'Styles', 'skin-blue-light,skin-yellow-light,skin-green-light,skin-purple-light,skin-red-light');


ALTER TABLE `userconfig_ucfg` ADD `ucfg_choices_id` mediumint(9) unsigned NULL AFTER `ucfg_type`;

ALTER TABLE `userconfig_ucfg`
ADD CONSTRAINT fk_ucfg_choices_id
  FOREIGN KEY (ucfg_choices_id) REFERENCES userconfig_choices_ucfg_ch(ucfg_ch_id)
  ON DELETE SET NULL;


-- we insert the two new keys
INSERT INTO `userconfig_ucfg` (`ucfg_per_id`, `ucfg_id`, `ucfg_name`, `ucfg_value`, `ucfg_type`, `ucfg_choices_id`, `ucfg_tooltip`, `ucfg_permission`, `ucfg_cat`) VALUES
(0, 14, 'sMapExternalProvider', 'GoogleMaps', 'choice', '0', 'Map providers for external view', 'TRUE', ''),
(1, 14, 'sMapExternalProvider', 'GoogleMaps', 'choice', '0', 'Map providers for external view', 'TRUE', '');

INSERT INTO `userconfig_ucfg` (`ucfg_per_id`, `ucfg_id`, `ucfg_name`, `ucfg_value`, `ucfg_type`, `ucfg_choices_id`, `ucfg_tooltip`, `ucfg_permission`, `ucfg_cat`) VALUES
(0, 15, 'sStyle', 'skin-blue-light', 'choice', '1', 'AdminLTE style ', 'TRUE', ''),
(1, 15, 'sStyle', 'skin-red-light', 'choice', '1','AdminLTE style', 'TRUE', '');



-- fix the new roles
DELETE FROM `userrole_usrrol`;

-- the new settings
INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`, `usrrol_global`, `usrrol_permissions`, `usrrol_value`) VALUES
(1, 'User Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:1;QueryMenu:1;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sMapExternalProvider:TRUE;sStyle:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1;sMapExternalProvider:GoogleMaps;sStyle:skin-red-light'),
(2, 'User Min', 'AddRecords:0;EditRecords:0;DeleteRecords:0;ShowCart:0;ShowMap:0;EDrive:0;MenuOptions:0;ManageGroups:0;Finance:0;Notes:0;EditSelf:1;Canvasser:0;Admin:0;QueryMenu:0;CanSendEmail:0;ExportCSV:0;CreateDirectory:0;ExportSundaySchoolPDF:0;ExportSundaySchoolCSV:0;MainDashboard:0;SeePrivacyData:0;MailChimp:0;GdrpDpo:0;PastoralCare:0', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:FALSE;sCSVExportCharset:FALSE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sMapExternalProvider:TRUE;sStyle:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1;sMapExternalProvider:GoogleMaps;sStyle:skin-blue-light'),
(3, 'User Max but not Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:0;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sMapExternalProvider:TRUE;sStyle:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1;sMapExternalProvider:GoogleMaps;sStyle:skin-purple-light'),
(4, 'User Max but not DPO and not Pastoral Care', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:0;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:0;PastoralCare:0', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sMapExternalProvider:TRUE;sStyle:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1;sMapExternalProvider:GoogleMaps;sStyle:skin-green-light'),
(5, 'User DPO', 'AddRecords:0;EditRecords:0;DeleteRecords:0;ShowCart:0;ShowMap:0;EDrive:0;MenuOptions:0;ManageGroups:0;Finance:0;Notes:0;EditSelf:1;Canvasser:0;Admin:0;QueryMenu:0;CanSendEmail:0;ExportCSV:0;CreateDirectory:0;ExportSundaySchoolPDF:0;ExportSundaySchoolCSV:0;MainDashboard:0;SeePrivacyData:0;MailChimp:0;GdrpDpo:1;PastoralCare:0', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:FALSE;sCSVExportCharset:FALSE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sMapExternalProvider:TRUE;sStyle:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1;sMapExternalProvider:GoogleMaps;sStyle:skin-yellow-light');


-- delete the old user configs
DELETE FROM `userconfig_ucfg` WHERE `ucfg_name`='bExportSundaySchoolCSV';
DELETE FROM `userconfig_ucfg` WHERE `ucfg_name`='bExportSundaySchoolPDF';
DELETE FROM `userconfig_ucfg` WHERE `ucfg_name`='bCreateDirectory';
DELETE FROM `userconfig_ucfg` WHERE `ucfg_name`='bExportCSV';


-- delete the old user configs
ALTER TABLE `user_usr` ADD  `usr_ExportCSV` tinyint(1) NOT NULL default '0' AFTER `usr_showMenuQuery`;
ALTER TABLE `user_usr` ADD  `usr_CreateDirectory` tinyint(1) NOT NULL default '0' AFTER `usr_showMenuQuery`;
ALTER TABLE `user_usr` ADD  `usr_ExportSundaySchoolPDF` tinyint(1) NOT NULL default '0' AFTER `usr_showMenuQuery`;
ALTER TABLE `user_usr` ADD  `usr_ExportSundaySchoolCSV` tinyint(1) NOT NULL default '0' AFTER `usr_showMenuQuery`;

-- add the new one
ALTER TABLE `user_usr` ADD  `usr_CanSendEmail` tinyint(1) NOT NULL default '0' AFTER `usr_showMenuQuery`;
ALTER TABLE `user_usr` ADD  `usr_EDrive` tinyint(1) NOT NULL default '0';

--
-- sunday school real group
--

--
-- Table structure for table `addressbooks`
--
CREATE TABLE `group_type` (
    `grptp_id` INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `grptp_grp_ID` mediumint(8) unsigned NOT NULL,
    `grptp_lst_OptionID` mediumint(8) unsigned NOT NULL default '0',
    CONSTRAINT fk_grptp_grp_ID FOREIGN KEY (grptp_grp_ID) REFERENCES group_grp(grp_ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE  `list_lst` ADD `lst_Type` enum('normal','sunday_school') NOT NULL default 'normal' AFTER `lst_OptionSequence`;

-- UPDATE `list_lst` SET lst_Type = 'sunday_school' WHERE lst_ID = 3 AND lst_OptionID = 4 AND lst_OptionSequence = 4;


--
-- bug in the addressbookshare table, addressbooksid foreign key : Solution change the name of the column addressbooksid to addressbookid
--
ALTER TABLE `addressbookshare` DROP FOREIGN KEY `fk_addressbooksid`;
ALTER TABLE `addressbookshare` CHANGE COLUMN `addressbooksid` `addressbookid` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `addressbookshare`
ADD CONSTRAINT fk_addressbookid FOREIGN KEY (addressbookid) REFERENCES addressbooks(id) ON DELETE CASCADE;


--

DELETE FROM `query_qry` WHERE `qry_ID`=1;

INSERT INTO `query_qry` (`qry_ID`, `qry_SQL`, `qry_Name`, `qry_Description`, `qry_Count`, `qry_Type_ID`) VALUES
  (1, 'SELECT CONCAT(''<a href=FamilyView.php?FamilyID='',fam_ID,''>'',fam_Name,''</a>'') AS ''Family Name''   FROM family_fam Where fam_WorkPhone != ""', 'Family With Work Phone', 'Returns each family with a work phone', 0, 2);
