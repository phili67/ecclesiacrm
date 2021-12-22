INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Amsterdam'),
(100, 'sPhoneFormat', '9999 999999'),
(101, 'sPhoneFormatWithExt', '99 99 99 99 99'),
(102, 'sDateFormatLong', 'd-m-Y'),
(103, 'sDateFormatNoYear', 'd-m'),
(105, 'sDateTimeFormat', 'j-m-y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd-mm-yyyy'),
(110, 'sDatePickerFormat', 'd-m-Y'),
(111, 'sPhoneFormatCell', '99 99999999'),
(112, 'sTimeFormat', '%H.%M'),
(1011, 'sTaxReport1', 'Deze brief is een herinnering aan alle donaties voor'),
(1012, 'sTaxReport2', 'Dank u dat u ons dit jaar steunt. Wij waarderen uw toewijding zeer!'),
(1013, 'sTaxReport3', 'Voor vragen of wijzigingen in het verslag kunt u tijdens kantooruren tussen 9.00 en 17.00 uur contact opnemen met de kerk op bovenstaand nummer.'),
(1015, 'sReminder1', 'Deze brief is een samenvatting van de informatie die voor het lopende fiscale jaar is verzonden'),
(1019, 'sConfirm1', 'Deze brief is een samenvatting van de informatie die in onze databank is opgeslagen. Lees het zorgvuldig door, corrigeer het en stuur het terug naar onze kerk.'),
(1020, 'sConfirm2', 'Dank u voor uw hulp bij het invullen van deze informatie. Als u informatie wilt over de database.'),
(1021, 'sConfirm3', 'E-mail _____________________________________ wachtwoord ________________'),
(1022, 'sConfirm4', '[  ] Ik wil niet langer geassocieerd worden met de kerk (vink hier aan om verwijderd te worden uit uw bestanden).'),
(1026, 'sPledgeSummary1', 'Overzicht van toezeggingen en betalingen voor dit fiscale jaar'),
(1027, 'sPledgeSummary2', 'voor de'),
(1028, 'sDirectoryDisclaimer1', 'Wij hebben ons best gedaan om deze informatie zo nauwkeurig mogelijk te maken. Als u fouten of omissies vindt, neem dan contact met ons op. Deze map wordt gebruikt voor mensen van'),
(1029, 'sDirectoryDisclaimer2', ', en de daarin vervatte informatie zal niet voor commerciële doeleinden worden gebruikt.'),
(1031, 'sZeroGivers', 'Deze brief geeft een samenvatting van de betalingen van'),
(1032, 'sZeroGivers2', 'Dank u dat u ons helpt een verschil te maken. Wij stellen uw deelname zeer op prijs!'),
(1033, 'sZeroGivers3', 'Als u vragen hebt of correcties in dit verslag wilt aanbrengen, kunt u contact opnemen met onze kerk op bovenstaand nummer tijdens de uren van 9.00 tot 12.00 uur van maandag tot vrijdag.'),
(1048, 'sConfirmSincerely', 'Tot ziens.'),
(1049, 'sDear', 'Beste'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', '€'),
(2052, 'sUnsubscribeStart', 'Indien u deze emails niet wenst te ontvangen van'),
(2053, 'sUnsubscribeEnd', 'in de toekomst, neem contact op met de kerkbeheerders'),
(1017, 'sReminderNoPledge', 'Donaties: Wij hebben geen gegevens over uw donaties voor dit boekjaar.'),
(1018, 'sReminderNoPayments', 'Betalingen: Wij hebben geen gegevens over betalingen van u voor dit belastingjaar.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Tithe', 'geld in voor de begroting.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Kerkdienst'),
  (2, 'Zondagsschool')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Totaal', ''),
  (2, 1, 'Leden', ''),
  (3, 1, 'Bezoekers', ''),
  (4, 2, 'Totaal', ''),
  (5, 2, 'Leden', ''),
  (6, 2, 'Bezoekers', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Verantwoordelijk voor'),
  (1, 2, 2, 'normal', 'Lid'),
  (1, 3, 3, 'normal', 'Regelmatige deelnemer'),
  (1, 4, 4, 'normal', 'Gast'),
  (1, 5, 5, 'normal', 'Niet-deelnemer'),
  (1, 6, 6, 'normal', 'Niet-deelnemers (personeel)'),
  (1, 7, 7, 'normal', 'Overleden'),
  (2, 1, 1, 'normal', 'Gezinsvertegenwoordiger'),
  (2, 2, 2, 'normal', 'Echtgenoot'),
  (2, 3, 3, 'normal', 'Kind'),
  (2, 4, 4, 'normal', 'Ander familielid'),
  (2, 5, 5, 'normal', 'Is geen familielid'),
  (3, 1, 1, 'normal', 'Kerkelijk ambt'),
  (3, 2, 2, 'normal', 'Team'),
  (3, 3, 3, 'normal', 'Bijbelstudie'),
  (3, 4, 1, 'sunday_school', 'Groep 1'),
  (3, 5, 2, 'sunday_school', 'Groep 2'),
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
  (1, 'p', 'Persoon', 'Algemene eigenschappen van personen'),
  (2, 'f', 'Familie', 'Algemene eigenschappen van families'),
  (3, 'g', 'Groep', 'Algemene groepseigenschappen'),
  (4, 'm', 'Menu', 'Om het zondagsschool menu te personaliseren.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Uit', 'Een handicap.', 'Wat is zijn aard?',''),
  (2, 'f', 2, 'Alleenstaande ouder', 'is een eenoudergezin.', '',''),
  (3, 'g', 3, 'Jong', 'gemotiveerd is om in het jeugdwerk te werken.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Gebruiker Beheerder'),
(2, 'Minimale gebruiker'),
(3, 'Gebruiker Max maar niet Admin'),
(4, 'Gebruiker Max, maar geen DPO en geen Pastorale Zorg'),
(5, 'DPO-gebruiker')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Klassieke pastorale noot', '', 1, ''),
(2, 'Waarom ben je naar de kerk gekomen ?', '', 1, ''),
(3, 'Waarom blijf je komen?', '', 1, ''),
(4, 'Heb je een verzoek voor ons?', '', 1, ''),
(5, 'Hoe heb je over de kerk gehoord?', '', 1, ''),
(6, 'Doop', 'Opleiding', 0, ''),
(7, 'Bruiloft', 'Opleiding', 0, ''),
(8, 'Relaties helpen', 'Therapie en follow-up', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
