INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'miles'),
(65, 'sTimeZone', 'Europe/Stockholm'),
(100, 'sPhoneFormat', '999 999 999'),
(101, 'sPhoneFormatWithExt', '999 99 99 999 999'),
(102, 'sDateFormatLong', 'Y-m-d'),
(103, 'sDateFormatNoYear', 'm-d'),
(105, 'sDateTimeFormat', 'y-m-j G:i'),
(109, 'sDatePickerPlaceHolder', 'yyyy-mm-dd'),
(110, 'sDatePickerFormat', 'Y-m-d'),
(111, 'sPhoneFormatCell', '999 999 999'),
(112, 'sTimeFormat', '%H:%M'),
(113, 'sPhoneCountryCallingCode', '0046'),
(1011, 'sTaxReport1', 'Detta brev är en påminnelse om alla donationer till'),
(1012, 'sTaxReport2', 'Tack för att du stöder oss i år. Vi uppskattar verkligen ditt engagemang!'),
(1013, 'sTaxReport3', 'Om du har några frågor eller ändringar i rapporten kan du kontakta din kyrka på ovanstående nummer under arbetstid mellan kl. 9.00 och 17.00.'),
(1015, 'sReminder1', 'Denna skrivelse är en sammanfattning av den information som skickats för det innevarande räkenskapsåret.'),
(1019, 'sConfirm1', 'I detta brev sammanfattas den information som finns i vår databas. Vänligen läs igenom den noggrant, korrigera den och skicka tillbaka den till vår kyrka.'),
(1020, 'sConfirm2', 'Tack för att du hjälper oss att komplettera denna information. Om du vill ha information om databasen.'),
(1021, 'sConfirm3', 'E-post _____________________________________ lösenord ________________'),
(1022, 'sConfirm4', '[  ] Jag vill inte längre vara associerad med kyrkan (kryssa här för att bli borttagen från era register).'),
(1026, 'sPledgeSummary1', 'Sammanfattning av utfästelser och betalningar för detta räkenskapsår'),
(1027, 'sPledgeSummary2', 'för'),
(1028, 'sDirectoryDisclaimer1', 'Vi har strävat efter att göra denna information så korrekt som möjligt. Om du upptäcker några fel eller utelämnanden, vänligen kontakta oss. Denna katalog används för personer från'),
(1029, 'sDirectoryDisclaimer2', ', och informationen i den kommer inte att användas i kommersiellt syfte.'),
(1031, 'sZeroGivers', 'I denna skrivelse sammanfattas betalningarna från'),
(1032, 'sZeroGivers2', 'Tack för att du hjälper oss att göra skillnad. Vi uppskattar verkligen ditt deltagande!'),
(1033, 'sZeroGivers3', 'Om du har några frågor eller behöver göra korrigeringar i denna rapport, vänligen kontakta vår kyrka på numret ovan under tiden 9:00-12:00 måndag till fredag.'),
(1048, 'sConfirmSincerely', 'Vi ses snart'),
(1049, 'sDear', 'Kära'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'kr'),
(2052, 'sUnsubscribeStart', 'Om du inte vill ta emot dessa e-postmeddelanden från'),
(2053, 'sUnsubscribeEnd', 'i framtiden, kontakta kyrkans administratörer'),
(1017, 'sReminderNoPledge', 'Donationer: Vi har inga uppgifter om några donationer från er för detta räkenskapsår.'),
(1018, 'sReminderNoPayments', 'Betalningar: Vi har inga uppgifter om några betalningar från er för detta räkenskapsår.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Tionde', 'pengar i budgeten.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Gudstjänst'),
  (2, 'Söndagsskola')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Totalt', ''),
  (2, 1, 'Ledamöter', ''),
  (3, 1, 'Besökare', ''),
  (4, 2, 'Totalt', ''),
  (5, 2, 'Ledamöter', ''),
  (6, 2, 'Besökare', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Ansvarig för'),
  (1, 2, 2, 'normal', 'Ledamot'),
  (1, 3, 3, 'normal', 'Regelbunden deltagare'),
  (1, 4, 4, 'normal', 'Gäst'),
  (1, 5, 5, 'normal', 'Icke-deltagare'),
  (1, 6, 6, 'normal', 'Icke-deltagare (personal)'),
  (1, 7, 7, 'normal', 'Avlidna'),
  (2, 1, 1, 'normal', 'Företrädare för familjen'),
  (2, 2, 2, 'normal', 'Make/maka'),
  (2, 3, 3, 'normal', 'Barn'),
  (2, 4, 4, 'normal', 'Annan familjemedlem'),
  (2, 5, 5, 'normal', 'Är inte en familjemedlem'),
  (3, 1, 1, 'normal', 'Kyrklig verksamhet'),
  (3, 2, 2, 'normal', 'Team'),
  (3, 3, 3, 'normal', 'Bibelstudier'),
  (3, 4, 1, 'sunday_school', 'Grupp 1'),
  (3, 5, 2, 'sunday_school', 'Grupp 2'),
  (4, 1, 1, 'normal', 'True / False'),
  (4, 2, 2, 'normal', 'Date'),
  (4, 3, 3, 'normal', 'Text Field (50 char)'),
  (4, 4, 4, 'normal', 'Text Field (100 char)'),
  (4, 5, 5, 'normal', 'Text Field (Long)'),
  (4, 6, 6, 'normal', 'Year'),
  (4, 7, 7, 'normal', 'Season'),
  (4, 8, 8, 'normal', 'Number'),
  (4, 9, 9, 'normal', 'Person from Group'),
  (4, 10, 10, 'normal', 'Money'),
  (4, 11, 11, 'normal', 'Phone Number'),
  (4, 12, 12, 'normal', 'Custom Drop-Down List'),
  (5, 1, 1, 'normal', 'bAll'),
  (5, 2, 2, 'normal', 'bAdmin'),
  (5, 3, 3, 'normal', 'bAddRecords'),
  (5, 4, 4, 'normal', 'bEditRecords'),
  (5, 5, 5, 'normal', 'bDeleteRecords'),
  (5, 6, 6, 'normal', 'bMenuOptions'),
  (5, 7, 7, 'normal', 'bManageGroups'),
  (5, 8, 8, 'normal', 'bFinance'),
  (5, 9, 9, 'normal', 'bNotes'),
  (5, 10, 10, 'normal', 'bCommunication'),
  (5, 11, 11, 'normal', 'bCanvasser'),
  (10, 1, 1, 'normal', 'Teacher'),
  (10, 2, 2, 'normal', 'Student'),
  (11, 1, 1, 'normal', 'Member'),
  (12, 1, 1, 'normal', 'Teacher'),
  (12, 2, 2, 'normal', 'Student')
ON DUPLICATE KEY UPDATE lst_OptionName=VALUES(lst_OptionName);

INSERT INTO `propertytype_prt` (`prt_ID`, `prt_Class`, `prt_Name`, `prt_Description`) VALUES
  (1, 'p', 'Person', 'Allmänna egenskaper hos personer'),
  (2, 'f', 'Familj', 'Allmänna egenskaper hos familjer'),
  (3, 'g', 'Grupp', 'Allmänna gruppegenskaper'),
  (4, 'm', 'Meny', 'För att anpassa menyn i söndagsskolan.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Off', 'Ett funktionshinder.', 'Vad är dess natur?',''),
  (2, 'f', 2, 'Ensamstående förälder', 'är ett hushåll med en ensamstående förälder.', '',''),
  (3, 'g', 3, 'Unga', 'är motiverad att arbeta med ungdomsarbete.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Användaradministratör'),
(2, 'Minsta antal användare'),
(3, 'Användare Max men inte Admin'),
(4, 'User Max men inte DPO och inte Pastoral Care'),
(5, 'Användare av dataskyddsombud')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Klassisk pastoral ton', '', 1, ''),
(2, 'Varför kom du till kyrkan?', '', 1, ''),
(3, 'Varför fortsätter du att komma?', '', 1, ''),
(4, 'Har du en önskan till oss?', '', 1, ''),
(5, 'Hur hörde du talas om kyrkan?', '', 1, ''),
(6, 'Dop', 'Utbildning', 0, ''),
(7, 'Bröllop', 'Utbildning', 0, ''),
(8, 'Att hjälpa till med relationer', 'Behandling och uppföljning', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
