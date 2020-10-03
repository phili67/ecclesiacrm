DELETE FROM `userconfig_ucfg` WHERE `ucfg_id`=12;
DELETE FROM `userconfig_ucfg` WHERE `ucfg_id`=13;

DELETE FROM `userconfig_ucfg` WHERE `ucfg_id`=14;
DELETE FROM `userconfig_ucfg` WHERE `ucfg_id`=15;

DELETE FROM `userconfig_ucfg` WHERE `ucfg_id`=16;
DELETE FROM `userconfig_ucfg` WHERE `ucfg_id`=17;
DELETE FROM `userconfig_ucfg` WHERE `ucfg_id`=18;
DELETE FROM `userconfig_ucfg` WHERE `ucfg_id`=19;

DELETE FROM `userconfig_choices_ucfg_ch` WHERE `ucfg_ch_id`=0;
DELETE FROM `userconfig_choices_ucfg_ch` WHERE `ucfg_ch_id`=1;
DELETE FROM `userconfig_choices_ucfg_ch` WHERE `ucfg_ch_id`=2;
DELETE FROM `userconfig_choices_ucfg_ch` WHERE `ucfg_ch_id`=3;
DELETE FROM `userconfig_choices_ucfg_ch` WHERE `ucfg_ch_id`=4;
DELETE FROM `userconfig_choices_ucfg_ch` WHERE `ucfg_ch_id`=5;

INSERT INTO `userconfig_choices_ucfg_ch` (`ucfg_ch_id`,`ucfg_name`,`ucfg_choices`) VALUES
(0,'Maps','GoogleMaps,AppleMaps,BingMaps'),
(1,'StyleFontSize', 'Small,Large' ),
(2,'StyleSideBarType', 'dark,light'),
(3,'StyleSideBarColor','blue,secondary,green,cyan,yellow,red,fuchsia,blue,yellow,indigo,navy,purple,pink,maroon,orange,lime,teal,olive,black,gray-dark,gray,light' ),
(4,'StyleNavBarColor', 'blue,secondary,green,cyan,yellow,red,fuchsia,blue,yellow,indigo,navy,purple,pink,maroon,orange,lime,teal,olive,black,gray-dark,gray,light' ),
(5,'StyleBrandLinkColor', 'blue,secondary,green,cyan,yellow,red,fuchsia,blue,yellow,indigo,navy,purple,pink,maroon,orange,lime,teal,olive,black,gray-dark,gray,light' );


INSERT INTO `userconfig_ucfg` (`ucfg_per_id`, `ucfg_id`, `ucfg_name`, `ucfg_value`, `ucfg_type`, `ucfg_choices_id`, `ucfg_tooltip`, `ucfg_permission`, `ucfg_cat`) VALUES
(0, 12, 'sMapExternalProvider', 'GoogleMaps', 'choice', '0', 'Map providers for external view', 'TRUE', ''),
(1, 12, 'sMapExternalProvider', 'GoogleMaps', 'choice', '0', 'Map providers for external view', 'TRUE', ''),
(0, 13, 'bSidebarExpandOnHover', '1', 'boolean', NULL, 'Enable sidebar expand on hover effect for sidebar mini', 'TRUE', ''),
(1, 13, 'bSidebarExpandOnHover', '1', 'boolean', NULL, 'Enable sidebar expand on hover effect for sidebar mini', 'TRUE', ''),
(0, 14, 'bSidebarCollapse', '1', 'boolean', NULL, 'The sidebar is collapse by default', 'TRUE', ''),
(1, 14, 'bSidebarCollapse', '1', 'boolean', NULL, 'The sidebar is collapse by default', 'TRUE', ''),
(0, 15, 'sStyleFontSize', 'Small', 'choice', '1', 'AdminLTE 3.0 sideBar style', 'TRUE', ''),
(1, 15, 'sStyleFontSize', 'Small', 'choice', '1','AdminLTE 3.0 sideBar style', 'TRUE', ''),
(0, 16, 'sStyleSideBar', 'dark', 'choice', '2', 'AdminLTE 3.0 sideBar style', 'TRUE', ''),
(1, 16, 'sStyleSideBar', 'dark', 'choice', '2','AdminLTE 3.0 sideBar style', 'TRUE', ''),
(0, 17, 'sStyleSideBarColor', 'blue', 'choice', '3', 'AdminLTE 3.0 sideBar color style', 'TRUE', ''),
(1, 17, 'sStyleSideBarColor', 'blue', 'choice', '3','AdminLTE 3.0 sideBar color style', 'TRUE', ''),
(0, 18, 'sStyleNavBarColor', 'gray', 'choice', '4', 'AdminLTE 3.0 navbar color style', 'TRUE', ''),
(1, 18, 'sStyleNavBarColor', 'gray', 'choice', '4','AdminLTE 3.0 navbar color style', 'TRUE', ''),
(0, 19, 'sStyleBrandLinkColor', 'gray', 'choice', '5', 'AdminLTE 3.0 brand link color style', 'TRUE', ''),
(1, 19, 'sStyleBrandLinkColor', 'gray', 'choice', '5','AdminLTE 3.0 brand link color style', 'TRUE', '');


DELETE FROM `userrole_usrrol` WHERE `usrrol_id`=1;
DELETE FROM `userrole_usrrol` WHERE `usrrol_id`=2;
DELETE FROM `userrole_usrrol` WHERE `usrrol_id`=3;
DELETE FROM `userrole_usrrol` WHERE `usrrol_id`=4;
DELETE FROM `userrole_usrrol` WHERE `usrrol_id`=5;

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`, `usrrol_global`, `usrrol_permissions`, `usrrol_value`) VALUES
(1, 'User Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:1;QueryMenu:1;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray'),
(2, 'User Min',  'AddRecords:0;EditRecords:0;DeleteRecords:0;ShowCart:0;ShowMap:0;EDrive:0;MenuOptions:0;ManageGroups:0;Finance:0;Notes:0;EditSelf:1;Canvasser:0;Admin:0;QueryMenu:0;CanSendEmail:0;ExportCSV:0;CreateDirectory:0;ExportSundaySchoolPDF:0;ExportSundaySchoolCSV:0;MainDashboard:0;SeePrivacyData:0;MailChimp:0;GdrpDpo:0;PastoralCare:0', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:FALSE;sCSVExportCharset:FALSE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray'),
(3, 'User Max but not Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:0;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray'),
(4, 'User Max but not DPO and not Pastoral Care',  'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:0;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:0;PastoralCare:0', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray'),
(5, 'User DPO', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;EDrive:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:0;QueryMenu:0;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:0', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;sMapExternalProvider:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sStyleFontSize:TRUE;sStyleSideBar:TRUE;sStyleSideBarColor:TRUE;sStyleNavBarColor:TRUE;sStyleBrandLinkColor:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;sMapExternalProvider:GoogleMaps;bSidebarExpandOnHover:1;bSidebarCollapse:1;sStyleFontSize:Small;sStyleSideBar:dark;sStyleSideBarColor:blue;sStyleNavBarColor:gray;sStyleBrandLinkColor:gray');


-- new meeting table
CREATE TABLE `personmeeting_pm` (
    `pm_ID` mediumint(9) NOT NULL auto_increment,
    `pm_person_id` mediumint(9) unsigned NULL,
    `pm_code` varchar(255) NOT NULL default '',
    `pm_cr_date` datetime default NULL,
    PRIMARY KEY  (`pm_ID`),
    UNIQUE KEY `pm_ID` (`pm_ID`),
    CONSTRAINT fk_pm_person_id
        FOREIGN KEY (pm_person_id) REFERENCES person_per(per_ID)
            ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE `personlastmeeting_plm` (
     `plm_ID` mediumint(9) NOT NULL auto_increment,
     `plm_person_id` mediumint(9) NOT NULL,
     `plm_personmeeting_pm_id` mediumint(9) NOT NULL,
     PRIMARY KEY  (`plm_ID`),
     UNIQUE KEY `plm_ID` (`plm_ID`),
     CONSTRAINT fk_plm_personmeeting_pm_id
         FOREIGN KEY (plm_personmeeting_pm_id) REFERENCES personmeeting_pm(pm_ID)
             ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1 ;

-- bug correction
UPDATE `query_qry` SET `qry_SQL` = 'SELECT per_ID as AddToCart, CONCAT(\'<a href=PersonView.php?PersonID=\',per_ID,\'>\',per_FirstName,\' \',per_LastName,\'</a>\') AS Name, per_DateDeactivated as \'GDPR\' FROM person_per LEFT JOIN person2volunteeropp_p2vo ON per_id = p2vo_per_ID WHERE p2vo_vol_ID = ~volopp~ ORDER BY per_LastName' WHERE `query_qry`.`qry_ID` = 25;


-- reset the 'file' and 'folder' notes
DELETE FROM note_nte WHERE nte_ID IN (
    SELECT * FROM (
                      SELECT nte_ID FROM note_nte WHERE `nte_Type`='file'
                  ) AS p
);

DELETE FROM note_nte WHERE nte_ID IN (
    SELECT * FROM (
                      SELECT nte_ID FROM note_nte WHERE `nte_Type`='folder'
                  ) AS p
);

DELETE FROM note_nte_share WHERE 1;


-- Attention for the RC4

-- upgrade the schema of paddlenum_pn
ALTER TABLE `paddlenum_pn`  MODIFY pn_per_ID mediumint(9) unsigned NOT NULL;

ALTER TABLE `paddlenum_pn`
ADD CONSTRAINT fk_paddlenum_person_id
    FOREIGN KEY (pn_per_ID) REFERENCES person_per(per_ID)
    ON DELETE CASCADE;

ALTER TABLE `paddlenum_pn`
    ADD CONSTRAINT fk_paddlenum_pn_fundraiser_id
    FOREIGN KEY (pn_fr_ID) REFERENCES fundraiser_fr(fr_ID)
    ON DELETE CASCADE;


-- upgrade the schema of multibuy_mb
ALTER TABLE `multibuy_mb`  MODIFY mb_per_ID mediumint(9) unsigned NOT NULL;

ALTER TABLE `multibuy_mb`
    ADD CONSTRAINT fk_multibuy_mb_person_id
    FOREIGN KEY (mb_per_ID) REFERENCES person_per(per_ID)
    ON DELETE CASCADE;

ALTER TABLE `multibuy_mb`  MODIFY mb_item_ID mediumint(9) unsigned NOT NULL;

ALTER TABLE `multibuy_mb`
    ADD CONSTRAINT fk_multibuy_mb_donateditem_di_id
    FOREIGN KEY (mb_item_ID) REFERENCES donateditem_di(di_ID)
    ON DELETE CASCADE;

-- upgrade the schema of donateditem_di
ALTER TABLE `donateditem_di`
ADD CONSTRAINT fk_donateditem_di_fundraiser_id
    FOREIGN KEY (di_FR_ID) REFERENCES fundraiser_fr(fr_ID)
    ON DELETE CASCADE;

-- fk : donor
UPDATE `donateditem_di` SET `di_donor_ID` = NULL WHERE `donateditem_di`.`di_donor_ID` = 0;

ALTER TABLE  `donateditem_di`  MODIFY  `di_donor_ID` mediumint(9) unsigned NULL;

ALTER TABLE `donateditem_di`
    ADD CONSTRAINT fk_donor_person_id
    FOREIGN KEY (di_donor_ID)
    REFERENCES person_per(per_ID)
    ON DELETE SET NULL;

-- fk : buyer
UPDATE `donateditem_di` SET `di_buyer_ID` = NULL WHERE `donateditem_di`.`di_buyer_ID` = 0;

ALTER TABLE  `donateditem_di`  MODIFY  `di_buyer_ID` mediumint(9) unsigned NULL;

ALTER TABLE `donateditem_di`
    ADD CONSTRAINT fk_buyer_person_id
       FOREIGN KEY (di_buyer_ID)
       REFERENCES person_per(per_ID)
       ON DELETE SET NULL;


-- RC5
ALTER TABLE `tokens` ADD `Comment` TEXT NULL DEFAULT NULL AFTER `remainingUses`;
