INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
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
(1013, 'sTaxReport3', 'Sollten Sie irgendwelche Fragen oder Wünsche haben zum Bericht, Benachrichtigen Sie bitte uns ans Telefon nummer oben während der Arbeitszeit, von 9h00 bis 17h00 Uhr.'),
(1015, 'sReminder1', 'Dieser Brief ist ein zusammenfassenden der dem Daten für laufenden Steuerjahr '),
(1019, 'sConfirm1', 'Dieser Brief ist ein zusammenfassenden dem Daten die in der Datenbank protokolliert sind. Lesen Sie noch einmal sorgfältig, Korrigiert die Fehler und schicken sie uns dieses Formular zu unserer Kirche.'),
(1020, 'sConfirm2', 'Ich wollte euch nur dafür danken dass Sie uns dieser Daten gegeben haben. Wenn ihr was über Datenbank erfahren wollt ?'),
(1021, 'sConfirm3', 'Email _____________________________________ passwort ________________'),
(1022, 'sConfirm4', '[  ] I will nicht mehr in Verbindung mit der Kirche gebracht werden. (ankreuzen Sie der Kontrollbox für meinen Aufnahmen sofort zu löschen).'),
(1026, 'sPledgeSummary1', 'Zusammenfassung die Spenden und Zahlungen Versprechungen für dieses Jahres'),
(1027, 'sPledgeSummary2', 'ab'),
(1028, 'sDirectoryDisclaimer1', 'Wir haben gearbeitet für genaue Bewertung Daten abzugeben. Falls Sie eine Fehler oder ein Mängel feststellen, Informieren Sie uns bitte. Der Gesamtbericht ist genutzt für die person von'),
(1029, 'sDirectoryDisclaimer2', ', Und die Informationen enthält werden nicht für hauptsächlich kommerzielle Zwecke verwendet werden.'),
(1031, 'sZeroGivers', 'Dieser Brief fasst die Zahlungsermächtigungen für'),
(1032, 'sZeroGivers2', 'Danke vielmals dass Sie gemeinsam mit uns geholfen haben. Wir schätzen Ihrer Beteiligung  !'),
(1033, 'sZeroGivers3', 'Falls Sie irgendwelche Fragen oder Korrekturen für diesen Bericht haben, können Sie unseren Kirche während der Öffnungszeiten unter dieser Telefonnummer erreichen.'),
(1048, 'sConfirmSincerely', 'Herzlich danken'),
(1049, 'sDear', 'Lieber (Liebe)'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '0'),
(2051, 'sCurrency', '€'),
(2052, 'sUnsubscribeStart', 'Wenn Sie nicht länger diese E-Mails empfangen von'),
(2053, 'sUnsubscribeEnd', 'in der Zukunft, Bitte teilen Sie der Kirche Netzwerkadministrator'),
(1017, 'sReminderNoPledge', 'Spenden : Wir haben keine Aufzeichnung von ihm für dieses Jahres.'),
(1018, 'sReminderNoPayments', 'Zahlungen : Wir haben keine Aufzeichnung von ihm für dieses Jahres.')
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
  (2, 1, 1, 'Familienoberhaupt'),
  (2, 2, 2, 'Gemeinsam'),
  (2, 3, 3, 'Kinder'),
  (2, 4, 4, 'Weitere Familienangehörige '),
  (2, 5, 5, 'Nicht zur Familie gehört.'),
  (3, 1, 1, 'Ministerium'),
  (3, 2, 2, 'Kirche Team'),
  (3, 3, 3, 'Bibelstudien'),
  (3, 4, 4, 'Sonntagsschule Klasse'),
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
  (1, 'p', 'Personen', 'Personen Allgemeine Eigenschaften'),
  (2, 'f', 'Familie', 'Familie Allgemeine Eigenschaften'),
  (3, 'g', 'Gruppe', 'Gruppe Allgemeine Eigenschaften'),
  (4, 'm', 'Menu', 'Nicht zu ändern.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`) VALUES
  (1, 'p', 1, 'Deaktiviert', 'Hat ein Invalidität.', 'Welcher ?'),
  (2, 'f', 2, 'Alleinerziehende', 'Kommentar', ''),
  (3, 'g', 3, 'Jung', 'est orienté jeune.', '')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Administrator Benutzer'),
(2, 'Normal Benutzer ')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`) VALUES
(1, 'klassischen Pastoral Notizen', '', 1),
(2, 'Warum sind Sie in unsere Kirche gekommen ?', '', 1),
(3, 'Warum kommen sie dann immer wieder her ?', '', 1),
(4, 'Haben Sie irgendwelche ein Wünsche ?', '', 1),
(5, 'Wie sind Sie auf uns gekommen ?', '', 1),
(6, 'Taufe', 'Ausbildung', 0),
(7, 'Hochzeit', 'Ausbildung', 0),
(8, 'Hilfeleistungen ', 'Therapie', 0)
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);