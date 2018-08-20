--
--
-- This Table is no more usefull
--

DROP TABLE userprofile_usrprf;

-- --------------------------------------------------------

--
-- Table structure for table `userrole_usrrol`
--  

CREATE TABLE userrole_usrrol (
    `usrrol_id` mediumint(11) unsigned  NOT NULL AUTO_INCREMENT,
    `usrrol_name` VARCHAR(256) NOT NULL,
    `usrrol_global` TEXT COLLATE utf8_unicode_ci,
    `usrrol_permissions` TEXT COLLATE utf8_unicode_ci,
    `usrrol_value` TEXT COLLATE utf8_unicode_ci,
    PRIMARY KEY(usrrol_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`, `usrrol_global`, `usrrol_permissions`, `usrrol_value`) VALUES
(1, 'User Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bExportSundaySchoolCSV:TRUE;bExportSundaySchoolPDF:TRUE;bCreateDirectory:TRUE;bExportCSV:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bExportSundaySchoolCSV:1;bExportSundaySchoolPDF:1;bCreateDirectory:1;bExportCSV:1;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1'),
(2, 'User Min', 'AddRecords:0;EditRecords:0;DeleteRecords:0;ShowCart:0;ShowMap:0;MenuOptions:0;ManageGroups:0;Finance:0;Notes:0;EditSelf:1;Canvasser:0;Admin:0;MainDashboard:0;SeePrivacyData:0;MailChimp:0;GdrpDpo:0;PastoralCare:0', 'bEmailMailto:FALSE;sMailtoDelimiter:TRUE;bExportSundaySchoolCSV:FALSE;bExportSundaySchoolPDF:FALSE;bCreateDirectory:FALSE;bExportCSV:FALSE;bUSAddressVerification:FALSE;bShowTooltip:TRUE;sCSVExportDelemiter:FALSE;sCSVExportCharset:FALSE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE', 'bEmailMailto:;sMailtoDelimiter:,;bExportSundaySchoolCSV:;bExportSundaySchoolPDF:;bCreateDirectory:;bExportCSV:;bUSAddressVerification:;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1');


ALTER TABLE `user_usr` ADD `usr_role_id` mediumint(11) unsigned NULL;

ALTER TABLE `user_usr`
ADD CONSTRAINT fk_usr_role_id
  FOREIGN KEY (usr_role_id) REFERENCES userrole_usrrol(usrrol_id)
  ON DELETE SET NULL;


ALTER TABLE `user_usr` ADD `usr_webDavKey` VARCHAR(255) default NULL;

ALTER TABLE user_usr ADD UNIQUE (usr_webDavKey);



