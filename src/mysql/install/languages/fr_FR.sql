INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Paris'),
(100, 'sPhoneFormat', '99 99 99 99 99'),
(101, 'sPhoneFormatWithExt', '99 99 99 99 99'),
(102, 'sDateFormatLong', 'd/m/Y'),
(103, 'sDateFormatNoYear', 'd/m'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'd/m/Y'),
(111, 'sPhoneFormatCell', '99 99 99 99 99'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Cette lettre est un rappel de tous les dons pour'),
(1012, 'sTaxReport2', 'Merci de nous avoir soutenu cette année. Nous avons grandement apprécié votre dévouement !'),
(1013, 'sTaxReport3', 'Si vous avez des questions ou des modifications à faire concernant le rapport, contactez votre église au numéro ci-dessus pendant les heures de travail, entre 9h00 et 17h00.'),
(1015, 'sReminder1', 'Cette lettre est un récapitulatif des informations envoyés pour l\'année fiscale en cours'),
(1019, 'sConfirm1', 'Cette lettre résume les informations qui sont enregistrées dans notre base de données. Relisez  soigneusement, corrigez les et retournez-nous ce formulaire ci nécessaire à notre église.'),
(1020, 'sConfirm2', 'Merci pour nous avoir aidé à compléter ces informations. Si vous voulez des renseignements concernant la base de données.'),
(1021, 'sConfirm3', 'Email _____________________________________ mot de passe ________________'),
(1022, 'sConfirm4', '[  ] Je ne veux plus être associé à l\'église (coché ici pour être effacé de vos enregistrements).'),
(1026, 'sPledgeSummary1', 'Résumé des promesses de dons et paiement pour cette année fiscale'),
(1027, 'sPledgeSummary2', 'pour le'),
(1028, 'sDirectoryDisclaimer1', 'Nous avons travaillé à rendre ces données aussi exactes que possible. Si vous constatez des erreurs ou des omissions, contactez nous. Cet annuaire est utilisé pour les personnes de'),
(1029, 'sDirectoryDisclaimer2', ', et les informations contenus ne seront pas utilisées à des fins commerciales.'),
(1031, 'sZeroGivers', 'Cette lettre résume les paiements pour'),
(1032, 'sZeroGivers2', 'Merci pour de nous aider à faire la différence. Nous apprécions grandement  votre participation !'),
(1033, 'sZeroGivers3', 'Si vous avez des questions ou à apporter des corrections à ce rapport, contactez notre église au numéro ci-dessus pendant les heures de 9h00 à 12h00 du lundi au vendredi.'),
(1048, 'sConfirmSincerely', 'A très bientôt'),
(1049, 'sDear', 'Cher (Chère)'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '0'),
(2051, 'sCurrency', '€'),
(2052, 'sUnsubscribeStart', 'Si vous ne voulez plus recevoir ces emails de'),
(2053, 'sUnsubscribeEnd', 'dans le futur, contactez les administrateurs de l\'église'),
(1017, 'sReminderNoPledge', 'Dons: Nous n\'avons aucun enregistrement de dons de votre part pour cette année fiscale.'),
(1018, 'sReminderNoPayments', 'Paiements : Nous n\'avons aucun enregistrement de votre part pour cette année fiscale.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Dîme', 'entrée d\'argent pour pour le budget.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Service d\'église'),
  (2, 'Ecole du dimanche')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Total', ''),
  (2, 1, 'Membres', ''),
  (3, 1, 'Visiteurs', ''),
  (4, 2, 'Total', ''),
  (5, 2, 'Membres', ''),
  (6, 2, 'Visiteurs', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_OptionName`) VALUES
  (1, 1, 1, 'Responsable cellule'),
  (1, 2, 2, 'Membre'),
  (1, 3, 3, 'Participant régulier'),
  (1, 4, 4, 'Invité'),
  (1, 5, 5, 'Non participant'),
  (1, 6, 6, 'Non participant (staff)'),
  (1, 7, 7, 'Décédé'),
  (2, 1, 1, 'Représentant famille'),
  (2, 2, 2, 'Conjoint(e)'),
  (2, 3, 3, 'Enfant'),
  (2, 4, 4, 'Autre membre de la famille'),
  (2, 5, 5, 'N\'est pas membre de la famille'),
  (3, 1, 1, 'Ministère'),
  (3, 2, 2, 'Equipe'),
  (3, 3, 3, 'Etude de la bible'),
  (3, 4, 4, 'Classe école du dimanche'),
  (4, 1, 1, 'True / False'),
  (4, 2, 2, 'Date'),
  (4, 3, 3, 'Text Field (50 char)'),
  (4, 4, 4, 'Text Field (100 char)'),
  (4, 5, 5, 'Text Field (Long)'),
  (4, 6, 6, 'Year'),
  (4, 7, 7, 'Season'),
  (4, 8, 8, 'Number'),
  (4, 9, 9, 'Person from Group'),
  (4, 10, 10, 'Money'),
  (4, 11, 11, 'Phone Number'),
  (4, 12, 12, 'Custom Drop-Down List'),
  (5, 1, 1, 'bAll'),
  (5, 2, 2, 'bAdmin'),
  (5, 3, 3, 'bAddRecords'),
  (5, 4, 4, 'bEditRecords'),
  (5, 5, 5, 'bDeleteRecords'),
  (5, 6, 6, 'bMenuOptions'),
  (5, 7, 7, 'bManageGroups'),
  (5, 8, 8, 'bFinance'),
  (5, 9, 9, 'bNotes'),
  (5, 10, 10, 'bCommunication'),
  (5, 11, 11, 'bCanvasser'),
  (10, 1, 1, 'Teacher'),
  (10, 2, 2, 'Student'),
  (11, 1, 1, 'Member'),
  (12, 1, 1, 'Teacher'),
  (12, 2, 2, 'Student')
ON DUPLICATE KEY UPDATE lst_OptionName=VALUES(lst_OptionName);

INSERT INTO `propertytype_prt` (`prt_ID`, `prt_Class`, `prt_Name`, `prt_Description`) VALUES
  (1, 'p', 'Personne', 'Propriétés générales de personnes'),
  (2, 'f', 'Famille', 'Propriétés générales de familles'),
  (3, 'g', 'Groupe', 'Propriétés générales de groupes'),
  (4, 'm', 'Menu', 'Pour personnaliser le menu école du dimanche.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Désactivé', 'A une invalidité.', 'Quelle en est sa nature ?',''),
  (2, 'f', 2, 'Parent isolé', 'est un parent isolé dans sa famille.', '',''),
  (3, 'g', 3, 'Jeune', 'est orienté jeune.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Utilisateur Administrateur'),
(2, 'Utilisateur Minimum'),
(3, 'Utilisateur Max mais non Admin'),
(4, 'Utilisateur Max mais non DPO et non Suivi pastoral'),
(5, 'Utilisateur DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);


INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`, `usrrol_global`, `usrrol_permissions`, `usrrol_value`) VALUES
(1, 'User Admin', 'AddRecords:1;EditRecords:1;DeleteRecords:1;ShowCart:1;ShowMap:1;MenuOptions:1;ManageGroups:1;Finance:1;Notes:1;EditSelf:1;Canvasser:1;Admin:1;QueryMenu:1;CanSendEmail:1;ExportCSV:1;CreateDirectory:1;ExportSundaySchoolPDF:1;ExportSundaySchoolCSV:1;MainDashboard:1;SeePrivacyData:1;MailChimp:1;GdrpDpo:1;PastoralCare:1;Style:skin-red-light', 'bEmailMailto:TRUE;sMailtoDelimiter:TRUE;bUSAddressVerification:TRUE;bShowTooltip:TRUE;sCSVExportDelemiter:TRUE;sCSVExportCharset:TRUE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sMapExternalProvider:TRUE', 'bEmailMailto:1;sMailtoDelimiter:,;bExportSundaySchoolCSV:1;bExportSundaySchoolPDF:1;bCreateDirectory:1;bExportCSV:1;bUSAddressVerification:1;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1;sMapExternalProvider:GoogleMaps'),
(2, 'User Min', 'AddRecords:0;EditRecords:0;DeleteRecords:0;ShowCart:0;ShowMap:0;MenuOptions:0;ManageGroups:0;Finance:0;Notes:0;EditSelf:1;Canvasser:0;Admin:0;QueryMenu:0;CanSendEmail:0;ExportCSV:0;CreateDirectory:0;ExportSundaySchoolPDF:0;ExportSundaySchoolCSV:0;MainDashboard:0;SeePrivacyData:0;MailChimp:0;GdrpDpo:0;PastoralCare:0;Style:skin-yellow-light', 'bEmailMailto:FALSE;sMailtoDelimiter:TRUE;bExportSundaySchoolCSV:FALSE;bExportSundaySchoolPDF:FALSE;bCreateDirectory:FALSE;bExportCSV:FALSE;bUSAddressVerification:FALSE;bShowTooltip:TRUE;sCSVExportDelemiter:FALSE;sCSVExportCharset:FALSE;bSidebarExpandOnHover:TRUE;bSidebarCollapse:TRUE;sMapExternalProvider:TRUE', 'bEmailMailto:;sMailtoDelimiter:,;bExportSundaySchoolCSV:;bExportSundaySchoolPDF:;bCreateDirectory:;bExportCSV:;bUSAddressVerification:;bShowTooltip:1;sCSVExportDelemiter:,;sCSVExportCharset:UTF-8;bSidebarExpandOnHover:1;bSidebarCollapse:1;sMapExternalProvider:GoogleMaps'),


--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Note pastorale classique', '', 1, ''),
(2, 'Pourquoi êtes-vous venu à l\'église', '', 1, ''),
(3, 'Pourquoi continuez-vous à venir ?', '', 1, ''),
(4, 'Avez-vous une requêtes à nous faire ?', '', 1, ''),
(5, 'Comment avez-vous entendu parler de l\'église ?', '', 1, ''),
(6, 'Baptême', 'Formation', 0, ''),
(7, 'Mariage', 'Formation', 0, ''),
(8, 'Relation d\'aide', 'Thérapie et suivi', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);