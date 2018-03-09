UPDATE `propertytype_prt` SET prt_Name='Person' WHERE prt_ID=1;
UPDATE `propertytype_prt` SET prt_Name='Family' WHERE prt_ID=2;
UPDATE `propertytype_prt` SET prt_Name='Group' WHERE prt_ID=3;
UPDATE `propertytype_prt` SET prt_Class='m' WHERE prt_ID=4;

INSERT IGNORE INTO `query_qry` (`qry_ID`, `qry_SQL`, `qry_Name`, `qry_Description`, `qry_Count`) VALUES
(33, 'SELECT per_ID as AddToCart, per_LastName, per_FirstName FROM `person_per`\r\nwhere per_BirthYear<~the_year~ AND per_cls_ID IN (1,2) AND per_fam_ID<>3 AND `per_ID` NOT IN (SELECT p2g2r_per_ID FROM `person2group2role_p2g2r`)\r\norder by per_LastName ASC, per_FirstName ASC', 'Person not assigned to a group', 'Returns all the persons not assigned to a group.', 1),
(34, 'SELECT per_ID as AddToCart,per_FirstName, per_LastName, grp_Name FROM `person2group2role_p2g2r`, `person_per`, group_grp WHERE per_cls_ID IN (1,2) AND per_fam_ID<>3 AND p2g2r_per_ID=per_ID and grp_ID=p2g2r_grp_ID\r\norder by per_FirstName ASC, per_LastName ASC, grp_Name ASC', 'Person assigned to a group', 'Returns all persons assigned to a group.', 1);

INSERT IGNORE INTO `queryparameters_qrp` (`qrp_ID`, `qrp_qry_ID`, `qrp_Type`, `qrp_OptionSQL`, `qrp_Name`, `qrp_Description`, `qrp_Alias`, `qrp_Default`, `qrp_Required`, `qrp_InputBoxSize`, `qrp_Validation`, `qrp_NumericMax`, `qrp_NumericMin`, `qrp_AlphaMinLength`, `qrp_AlphaMaxLength`) VALUES
(34, 33, 0, NULL, 'Year', 'Get all persons who were born before the Year you mentioned.', 'the_year', '2100', 0, 5, 'n', 2100, 0, NULL, NULL);
