CREATE TABLE IF NOT EXISTS  userprofile_usrprf (
    `usrprf_id` mediumint(11) unsigned  NOT NULL AUTO_INCREMENT,
    `usrprf_name` VARCHAR(256) NOT NULL,
    `usrprf_global` TEXT COLLATE utf8_unicode_ci,
    `usrprf_permissions` TEXT COLLATE utf8_unicode_ci,
    `usrprf_value` TEXT COLLATE utf8_unicode_ci,
    PRIMARY KEY(usrprf_id)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

INSERT IGNORE INTO `userprofile_usrprf` (`usrprf_id`, `usrprf_name`, `usrprf_global`, `usrprf_permissions`, `usrprf_value`) VALUES
(1, 'User Min', 'AddRecords:0;EditRecords:0;DeleteRecords:0;ShowCart:0;ShowMap:0;MenuOptions:0;ManageGroups:0;Finance:0;Notes:1;EditSelf:0;Canvasser:0;Admin:0;Style:skin-blue-light', 'bEmailMailto:FALSE;sMailtoDelimiter:FALSE;bCreateDirectory:FALSE;bExportCSV:FALSE;bUSAddressVerification:FALSE;bShowTooltip:TRUE;bAddEvent:FALSE;bSeePrivacyData:FALSE', 'bEmailMailto:;sMailtoDelimiter:,;bCreateDirectory:;bExportCSV:;bUSAddressVerification:;bShowTooltip:1;bAddEvent:;bSeePrivacyData:'),
(2, 'Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:1;Style:skin-red-light', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bCreateDirectory:TRUE;bExportCSV:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;bAddEvent:TRUE;bSeePrivacyData:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bCreateDirectory:1;bExportCSV:1;bUSAddressVerification:1;bShowTooltip:1;bAddEvent:1;bSeePrivacyData:1');

