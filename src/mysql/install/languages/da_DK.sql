INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Copenhagen'),
(100, 'sPhoneFormat', '99 99 99 99  99 99 99 99'),
(101, 'sPhoneFormatWithExt', '99 99 99 99  99 99 99 99'),
(102, 'sDateFormatLong', 'Y-m-d'),
(103, 'sDateFormatNoYear', 'm-d'),
(105, 'sDateTimeFormat', 'y-m-j G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'Y-m-d'),
(111, 'sPhoneFormatCell', '99 99 99 99  99 99 99 99'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Dette brev er en påmindelse om alle de donationer til'),
(1012, 'sTaxReport2', 'Tak, fordi du støtter os i år. Vi sætter stor pris på dit engagement!'),
(1013, 'sTaxReport3', 'Hvis du har spørgsmål eller ændringer til rapporten, bedes du kontakte din kirke på ovenstående nummer i arbejdstiden mellem kl. 9.00 og 17.00.'),
(1015, 'sReminder1', 'Dette brev er et resumé af de oplysninger, der er sendt for det indeværende regnskabsår'),
(1019, 'sConfirm1', 'Dette brev opsummerer de oplysninger, der er registreret i vores database. Læs den omhyggeligt igennem, ret den og send den tilbage til kirken.'),
(1020, 'sConfirm2', 'Tak, fordi du hjælper os med at udfylde disse oplysninger. Hvis du vil have oplysninger om databasen.'),
(1021, 'sConfirm3', 'E-mail _____________________________________ adgangskode ________________'),
(1022, 'sConfirm4', '[  ] Jeg ønsker ikke længere at være tilknyttet kirken (afkryds her for at blive slettet fra dine optegnelser).'),
(1026, 'sPledgeSummary1', 'Oversigt over løfter og betalinger for dette regnskabsår'),
(1027, 'sPledgeSummary2', 'for den'),
(1028, 'sDirectoryDisclaimer1', 'Vi har bestræbt os på at gøre disse oplysninger så nøjagtige som muligt. Hvis du finder fejl eller udeladelser, bedes du kontakte os. Denne mappe bruges til personer fra'),
(1029, 'sDirectoryDisclaimer2', ', og oplysningerne heri vil ikke blive anvendt til kommercielle formål.'),
(1031, 'sZeroGivers', 'Dette brev indeholder en oversigt over betalingerne af'),
(1032, 'sZeroGivers2', 'Tak, fordi du hjælper os med at gøre en forskel. Vi sætter stor pris på din deltagelse!'),
(1033, 'sZeroGivers3', 'Hvis du har spørgsmål eller har brug for at foretage rettelser i denne rapport, bedes du kontakte vores kirke på ovenstående nummer i tidsrummet kl. 9.00-12.00 mandag til fredag.'),
(1048, 'sConfirmSincerely', 'Vi ses snart'),
(1049, 'sDear', 'Kære'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'kr.'),
(2052, 'sUnsubscribeStart', 'Hvis du ikke ønsker at modtage disse e-mails fra'),
(2053, 'sUnsubscribeEnd', 'i fremtiden, kontakt kirkens administratorer'),
(1017, 'sReminderNoPledge', 'Donationer: Vi har ikke registreret nogen donationer fra dig i dette regnskabsår.'),
(1018, 'sReminderNoPayments', 'Betalinger: Vi har ikke registreret nogen betalinger fra dig for dette regnskabsår.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Tiende', 'entrée d\'argent pour pour le budget.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Gudstjeneste'),
  (2, 'Søndagsskole')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'I alt', ''),
  (2, 1, 'Medlemmer', ''),
  (3, 1, 'Besøgende', ''),
  (4, 2, 'I alt', ''),
  (5, 2, 'Medlemmer', ''),
  (6, 2, 'Besøgende', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Ansvarlig for'),
  (1, 2, 2, 'normal', 'Medlem'),
  (1, 3, 3, 'normal', 'Regelmæssig deltager'),
  (1, 4, 4, 'normal', 'Gæster'),
  (1, 5, 5, 'normal', 'Ikke-deltagende'),
  (1, 6, 6, 'normal', 'Ikke-deltagere (staff)'),
  (1, 7, 7, 'normal', 'Afdøde'),
  (2, 1, 1, 'normal', 'Repræsentant for familien'),
  (2, 2, 2, 'normal', 'Ægtefælle'),
  (2, 3, 3, 'normal', 'Barn'),
  (2, 4, 4, 'normal', 'Andet familiemedlem'),
  (2, 5, 5, 'normal', 'ikke er et familiemedlem'),
  (3, 1, 1, 'normal', 'Ministeriet'),
  (3, 2, 2, 'normal', 'Team'),
  (3, 3, 3, 'normal', 'Bibelstudie'),
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
  (1, 'p', 'Person', 'Generelle egenskaber ved personer'),
  (2, 'f', 'Familie', 'Generelle egenskaber ved familier'),
  (3, 'g', 'Gruppe', 'Generelle gruppeegenskaber'),
  (4, 'm', 'Menu', 'Til at personliggøre søndagsskolemenuen.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Off', 'Et handicap.', 'Hvad er dens natur?', ''),
  (2, 'f', 2, 'Enlig forælder', 'er en husstand med en enkelt forælder.', '', ''),
  (3, 'g', 3, 'Ung', 'er motiveret til at arbejde med ungdomsarbejde.', '', '')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Brugeradministrator'),
(2, 'Minimum bruger'),
(3, 'Bruger Max, men ikke Admin'),
(4, 'Bruger Max, men ikke DPO og ikke Pastoral Care'),
(5, 'DPO-bruger')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);
--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Klassisk pastoral note', '', 1, ''),
(2, 'Hvorfor kom du til kirken', '', 1, ''),
(3, 'Hvorfor bliver du ved med at komme?', '', 1, ''),
(4, 'Har du en anmodning til os?', '', 1, ''),
(5, 'Hvordan hørte du om kirken?', '', 1, ''),
(6, 'Baptême', 'Uddannelse', 0, ''),
(7, 'Bryllup', 'Uddannelse', 0, ''),
(8, 'Hjælp til relationer', 'Terapi og opfølgning', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
