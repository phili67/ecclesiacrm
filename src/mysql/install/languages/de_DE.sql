INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(39, 'sLanguage', 'de_DE'),
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Berlin'),
(100, 'sPhoneFormat', '999 9999999'),
(101, 'sPhoneFormatWithExt', '999 9999999'),
(102, 'sDateFormatLong', 'd/m/Y'),
(103, 'sDateFormatNoYear', 'd/m'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'd/m/Y'),
(111, 'sPhoneFormatCell', '999 9999999'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Dieser Brief ist eine Erinnerung auf alle Spenden für'),
(1012, 'sTaxReport2', 'Danke, dass Sie uns unterstützt haben dieses Jahr. Wir wissen Ihre Besorgnis zu schätzen !'),
(1013, 'sTaxReport3', 'Si vous avez des questions ou des modifications à faire concernant le rapport, contactez votre église au numéro ci-dessus pendant les heures de travail, entre 9h00 et 17h00.'),
(1015, 'sReminder1', 'Cette lettre est un récapitulatif des informations envoyés pour l\'année fiscale en cours'),
(1019, 'sConfirm1', 'Cette lettre résume les informations qui sont enregistrées dans notre base de données. Relisez  soigneusement, corrigez les et retournez-nous ce formulaire ci nécessaire à notre église.'),
(1020, 'sConfirm2', 'Merci pour nous avoir aidé à compléter ces informations. Si vous voulez des renseignements concernant la base de données.'),
(1021, 'sConfirm3', 'Email _____________________________________ passwort ________________'),
(1022, 'sConfirm4', '[  ] I will nicht mehr in Verbindung mit der Kirche gebracht werden. (ankreuzen Sie der Kontrollbox für meinen Aufnahmen sofort zu löschen).'),
(1026, 'sPledgeSummary1', 'Zusammenfassung die Spenden und Zahlungen Versprechungen für dieses Jahres'),
(1027, 'sPledgeSummary2', 'wie'),
(1028, 'sDirectoryDisclaimer1', 'Nous avons travaillé à rendre ces données aussi exactes que possible. Si vous constatez des erreurs ou des omissions, contactez nous. Cet annuaire est utilisé pour les personnes de'),
(1029, 'sDirectoryDisclaimer2', ', et les informations contenus ne seront pas utilisées à des fins commerciales.'),
(1031, 'sZeroGivers', 'Dieser Brief fasst die Zahlungsermächtigungen für'),
(1032, 'sZeroGivers2', 'Merci pour de nous aider à faire la différence. Nous apprécions grandement  votre participation !'),
(1033, 'sZeroGivers3', 'Si vous avez des questions ou à apporter des corrections à ce rapport, contactez notre église au numéro ci-dessus pendant les heures de 9h00 à 12h00 du lundi au vendredi.'),
(1048, 'sConfirmSincerely', 'Auf sehr bald'),
(1049, 'sDear', 'Lieber (Liebe)'),
(1051, 'sTimeEnglish', ''),
(2050, 'bStateUnuseful', '0'),
(2051, 'sCurrency', '€'),
(2052, 'sUnsubscribeStart', 'Wenn Sie nicht länger diese E-Mails empfangen von'),
(2053, 'sUnsubscribeEnd', 'in der Zukunft, Bitte teilen Sie der Kirche Systemverwaltung'),
(1017, 'sReminderNoPledge', 'Spenden : Wir haben keine Aufzeichnung von ihm für dieses Jahres.'),
(1018, 'sReminderNoPayments', 'Zahlungen : Wir haben keine Aufzeichnung von ihm für dieses Jahres.'),
(2055, 'sTimeZoneSet', 'Europe/Berlin')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Zehnten', 'Geld, das hereinkam für Budgets.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Fachstelle Kirche'),
  (2, 'Sonntagsschule')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Total', ''),
  (2, 1, 'Mitglieder', ''),
  (3, 1, 'Besucher', ''),
  (4, 2, 'Gesamtzahl', ''),
  (5, 2, 'Mitglieder', ''),
  (6, 2, 'Besucher', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_OptionName`) VALUES
  (1, 1, 1, 'Zelle Referent'),
  (1, 2, 2, 'Mitglieder'),
  (1, 3, 3, 'Regelmäßigen Abständen'),
  (1, 4, 4, 'Besucher'),
  (1, 5, 5, 'nicht teilnehmenden'),
  (1, 6, 6, 'nicht teilnehmenden (staff)'),
  (1, 7, 7, 'Verstorben'),
  (2, 1, 1, 'Représentant famille'),
  (2, 2, 2, 'Gemeinsam'),
  (2, 3, 3, 'Kinder'),
  (2, 4, 4, 'Weitere Familienangehörige '),
  (2, 5, 5, 'Nicht zur Familie gehört.'),
  (3, 1, 1, 'Ministerium'),
  (3, 2, 2, 'Kirche Team'),
  (3, 3, 3, 'Bibelstudien'),
  (3, 4, 4, 'Sonntagsschule Klasse')
ON DUPLICATE KEY UPDATE lst_OptionName=VALUES(lst_OptionName);

INSERT INTO `propertytype_prt` (`prt_ID`, `prt_Class`, `prt_Name`, `prt_Description`) VALUES
  (1, 'p', 'Personne', 'Propriétés générales de personnes'),
  (2, 'f', 'Famille', 'Propriétés générales de familles'),
  (3, 'g', 'Groupe', 'Propriétés générales de groupes'),
  (4, 'm', 'Menu', 'Pour personnaliser le menu école du dimanche.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`) VALUES
  (1, 'p', 1, 'Désactivé', 'A une invalidité.', 'Quelle en est sa nature ?'),
  (2, 'f', 2, 'Parent isolé', 'est un parent isolé dans sa famille.', ''),
  (3, 'g', 3, 'Jeune', 'est orienté jeune.', '')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userprofile_usrprf` (`usrprf_id`, `usrprf_name`) VALUES
(1, 'Utilisateur Administrateur'),
(2, 'Utilisateur Minimum')
ON DUPLICATE KEY UPDATE usrprf_name=VALUES(usrprf_name);


--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`) VALUES
(1, 'Pourquoi êtes-vous venu à l\'église', '', 1),
(2, 'Pourquoi continuez-vous à venir ?', '', 1),
(3, 'Avez-vous une requêtes à nous faire ?', '', 1),
(4, 'Comment avez-vous entendu parler de l\'église ?', '', 1),
(5, 'Baptême', 'Formation', 0),
(6, 'Mariage', 'Formation', 0),
(7, 'Relation d\'aide', 'Thérapie et suivi', 0)
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);