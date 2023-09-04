INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Oslo'),
(100, 'sPhoneFormat', '99 99 99 99'),
(101, 'sPhoneFormatWithExt', '999 99 99 99 99'),
(102, 'sDateFormatLong', 'd.m.Y'),
(103, 'sDateFormatNoYear', 'd.m'),
(105, 'sDateTimeFormat', 'j.m.y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd.mm.yyyy'),
(110, 'sDatePickerFormat', 'd.m.Y'),
(111, 'sPhoneFormatCell', '999 99 999'),
(112, 'sTimeFormat', '%H.%M'),
(113, 'sPhoneCountryCallingCode', '0047'),
(1011, 'sTaxReport1', 'Dette brevet minner om alle gaver'),
(1012, 'sTaxReport2', 'Takk for at du støtter oss i år. Vi likte din hengivenhet veldig godt!'),
(1013, 'sTaxReport3', 'Har du noen spørsmål eller forandringer som angår rapporten, kontakt kirken på topp nummer mellom kl. 09.00 og 17.00.'),
(1015, 'sReminder1', 'Dette brevet er en oppsummering av informasjonen som ble sendt til det nåværende finansåret'),
(1019, 'sConfirm1', 'Dette brevet oppsummerer informasjonen som er registrert i databasen vår. Les nøye, korriger dem og send denne formen til kirken vår.'),
(1020, 'sConfirm2', 'Takk for at du hjalp oss med å fullføre informasjonen. Hvis du vil ha informasjon om databasen.'),
(1021, 'sConfirm3', 'E-post _____________________________________ Passord. ________________'),
(1022, 'sConfirm4', '[  ] Jeg vil ikke bli koblet til kirken lenger (sjekket her for å bli slettet fra opptakene dine).'),
(1026, 'sPledgeSummary1', 'Summary for løftene om donasjoner og betaling for dette skatteåret'),
(1027, 'sPledgeSummary2', 'for ham'),
(1028, 'sDirectoryDisclaimer1', 'Vi har jobbet for å gjøre dataene så nøyaktig som mulig. Hvis du merker feil eller utelatelser, så kontakt oss. Dette registret brukes til folk fra'),
(1029, 'sDirectoryDisclaimer2', ', informasjonen inneholder ikke vil brukes til reklameforsøk.'),
(1031, 'sZeroGivers', 'Dette brevet oppsummerer betalingene til'),
(1032, 'sZeroGivers2', 'Takk for at du hjalp oss. Det store ved din deltakelse!'),
(1033, 'sZeroGivers3', 'Har du spørsmål eller rettelser til rapporten, kontakt kirken over nummeret på timer fra 09.00 til 12.00. Mandag til fredag.'),
(1048, 'sConfirmSincerely', 'Vi ses snart'),
(1049, 'sDear', 'Kjære (Kjære )'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'kr'),
(2052, 'sUnsubscribeStart', 'Hvis du ikke vil motta disse e-postene fra lenger'),
(2053, 'sUnsubscribeEnd', 'kontakt kirkeadministratorene i fremtiden'),
(1017, 'sReminderNoPledge', 'Donasjoner: Vi har ingen opptak av donasjoner fra deg i dette skatteåret.'),
(1018, 'sReminderNoPayments', 'Betalinger: Vi har ingen registreringsnummer for dette skatteåret.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Title', 'pengeinnlegg for budsjettet.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Kirketjeneste'),
  (2, 'Søndagsskole')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Totalt.', ''),
  (2, 1, 'Members', ''),
  (3, 1, 'Besøkere', ''),
  (4, 2, 'Totalt.', ''),
  (5, 2, 'Members', ''),
  (6, 2, 'Besøkere', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Ansvarlig'),
  (1, 2, 2, 'normal', 'Medlem'),
  (1, 3, 3, 'normal', 'Vanlig deltaker'),
  (1, 4, 4, 'normal', 'Gjest'),
  (1, 5, 5, 'normal', 'Ingen deltaker'),
  (1, 6, 6, 'normal', 'Ikke delta (Stabs)'),
  (1, 7, 7, 'normal', 'Død'),
  (2, 1, 1, 'normal', 'Familierepresentant'),
  (2, 2, 2, 'normal', 'Spouse'),
  (2, 3, 3, 'normal', 'Barn'),
  (2, 4, 4, 'normal', 'Et annet familiemedlem'),
  (2, 5, 5, 'normal', 'Det er ikke et familiemedlem'),
  (3, 1, 1, 'normal', 'Kirkedepartementet'),
  (3, 2, 2, 'normal', 'Team'),
  (3, 3, 3, 'normal', 'Les Bibelen'),
  (3, 4, 1, 'sunday_school', 'Gruppe 1'),
  (3, 5, 2, 'sunday_school', 'Gruppe 2'),
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
  (1, 'p', 'Ingen', 'General egenskaper av personer'),
  (2, 'f', 'Familie', 'General egenskaper av familier'),
  (3, 'g', 'Gruppe', 'General egenskaper for grupper'),
  (4, 'm', 'Menu', 'For å spesialisere søndags-menyen.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Deaktivert', 'For uføretrygd.', 'Hva er hans natur?',''),
  (2, 'f', 2, 'Foreldrene isolerer', 'er en enslig forelder.', '',''),
  (3, 'g', 3, 'Ung', 'er motivert til å jobbe i ungdommen.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Brukeradministrator'),
(2, 'Minimum bruker'),
(3, 'Bruker Max, men ikke Admin'),
(4, 'Bruker Max, men ikke DPO og ikke prestesituasjon'),
(5, 'DPO-brukere')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Klassisk pastorisk note', '', 1, ''),
(2, 'Hvorfor kom du i kirken?', '', 1, ''),
(3, 'Hvorfor kommer du stadig?', '', 1, ''),
(4, 'Har du en forespørsel om å komme med oss?', '', 1, ''),
(5, 'Hvordan hørte du om kirken?', '', 1, ''),
(6, 'Døpp', 'Trening', 0, ''),
(7, 'Ekteskap.', 'Trening', 0, ''),
(8, 'Hjelpe forholdet', 'Terapi og oppfølging', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
