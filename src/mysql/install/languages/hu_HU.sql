INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Budapest'),
(100, 'sPhoneFormat', '99 99 999 999'),
(101, 'sPhoneFormatWithExt', '999 99 9 999 999'),
(102, 'sDateFormatLong', 'Y-m-d'),
(103, 'sDateFormatNoYear', 'm-d'),
(105, 'sDateTimeFormat', 'y-m-j G:i'),
(109, 'sDatePickerPlaceHolder', 'yyyy-mm-dd'),
(110, 'sDatePickerFormat', 'Y-m-d'),
(111, 'sPhoneFormatCell', '99 999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Ez a levél emlékeztetőül szolgál az összes adományozásra a'),
(1012, 'sTaxReport2', 'Köszönjük, hogy idén is támogattak minket. Nagyra értékeljük elkötelezettségüket!'),
(1013, 'sTaxReport3', 'Ha bármilyen kérdése van, vagy a jelentéssel kapcsolatban bármilyen módosításra van szüksége, kérjük, vegye fel a kapcsolatot az egyházzal a fenti telefonszámon munkaidőben, reggel 9 és délután 17 óra között.'),
(1015, 'sReminder1', 'Ez a levél a folyó pénzügyi évre vonatkozóan megküldött információk összefoglalója.'),
(1019, 'sConfirm1', 'Ez a levél összefoglalja az adatbázisunkban rögzített információkat. Kérjük, gondosan olvassa el, javítsa ki és küldje vissza templomunkba.'),
(1020, 'sConfirm2', 'Köszönjük, hogy segített nekünk az információk kiegészítésében. Ha az adatbázisról szeretne információt kapni.'),
(1021, 'sConfirm3', 'E-mail _____________________________________ jelszó ________________'),
(1022, 'sConfirm4', '[  ] Nem szeretnék többé kapcsolatba kerülni a gyülekezettel (jelölje be itt, hogy töröljék a nyilvántartásból).'),
(1026, 'sPledgeSummary1', 'Az idei pénzügyi évre vonatkozó ígéretek és kifizetések összefoglalása'),
(1027, 'sPledgeSummary2', 'a'),
(1028, 'sDirectoryDisclaimer1', 'Igyekeztünk a lehető legpontosabbá tenni ezeket az információkat. Ha bármilyen hibát vagy hiányosságot talál, kérjük, lépjen kapcsolatba velünk. Ezt a könyvtárat a következő személyek használják'),
(1029, 'sDirectoryDisclaimer2', ', és a benne foglalt információk nem használhatók fel kereskedelmi célokra.'),
(1031, 'sZeroGivers', 'Ez a levél összefoglalja a következő kifizetéseket'),
(1032, 'sZeroGivers2', 'Köszönjük, hogy segítesz nekünk, hogy változtassunk a dolgokon. Nagyra értékeljük a részvételt!'),
(1033, 'sZeroGivers3', 'Ha bármilyen kérdése van, vagy javítani szeretné ezt a jelentést, kérjük, lépjen kapcsolatba egyházunkkal a fenti telefonszámon hétfőtől péntekig 9:00 és 12:00 óra között.'),
(1048, 'sConfirmSincerely', 'Hamarosan találkozunk'),
(1049, 'sDear', 'Kedves'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'HUF'),
(2052, 'sUnsubscribeStart', 'Ha nem szeretne ilyen e-maileket kapni a következőktől'),
(2053, 'sUnsubscribeEnd', 'a jövőben, lépjen kapcsolatba az egyházi adminisztrátorokkal'),
(1017, 'sReminderNoPledge', 'Adományok: Ebben a pénzügyi évben nincs nyilvántartásunk az Öntől származó adományokról.'),
(1018, 'sReminderNoPayments', 'Kifizetések: Nincs nyilvántartásunk arról, hogy ebben a pénzügyi évben bármilyen kifizetést kaptunk volna Öntől.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Tized', 'pénzt a költségvetésbe.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Templomi istentisztelet'),
  (2, 'Vasárnapi iskola')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Összesen', ''),
  (2, 1, 'Tagok', ''),
  (3, 1, 'Látogatók', ''),
  (4, 2, 'Összesen', ''),
  (5, 2, 'Látogatók', ''),
  (6, 2, 'Visiteurs', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Felelős a következőkért'),
  (1, 2, 2, 'normal', 'Tag'),
  (1, 3, 3, 'normal', 'Rendszeres résztvevő'),
  (1, 4, 4, 'normal', 'Vendég'),
  (1, 5, 5, 'normal', 'Nem résztvevő'),
  (1, 6, 6, 'normal', 'Nem résztvevők (személyzet)'),
  (1, 7, 7, 'normal', 'Elhunyt'),
  (2, 1, 1, 'normal', 'A család képviselője'),
  (2, 2, 2, 'normal', 'Házastárs'),
  (2, 3, 3, 'normal', 'Gyermek'),
  (2, 4, 4, 'normal', 'Más családtag'),
  (2, 5, 5, 'normal', 'Nem családtag'),
  (3, 1, 1, 'normal', 'Templomi istentisztelet'),
  (3, 2, 2, 'normal', 'Csapat'),
  (3, 3, 3, 'normal', 'Bibliatanulmányozás'),
  (3, 4, 1, 'sunday_school', '1. csoport'),
  (3, 5, 2, 'sunday_school', '2. csoport'),
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
  (1, 'p', 'Személy', 'A személyek általános tulajdonságai'),
  (2, 'f', 'Család', 'A családok általános tulajdonságai'),
  (3, 'g', 'Csoport', 'Általános csoporttulajdonságok'),
  (4, 'm', 'Menü', 'A vasárnapi iskolai menü személyre szabása.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Off', 'Egy fogyatékosság.', 'Mi a természete?',''),
  (2, 'f', 2, 'Egyedülálló szülő', 'egyszülős háztartás.', '',''),
  (3, 'g', 3, 'Fiatal', 'motivált az ifjúsági munka területén dolgozni.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Felhasználó adminisztrátor'),
(2, 'Minimális felhasználó'),
(3, 'User Max de nem Admin'),
(4, 'Felhasználó Max, de nincs adatvédelmi tisztviselő és nincs lelkigondozói szolgálat.'),
(5, 'DPO felhasználó')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Klasszikus pásztori jegyzet', '', 1, ''),
(2, 'Miért jöttél a templomba', '', 1, ''),
(3, 'Miért jössz folyton?', '', 1, ''),
(4, 'Van valami kérése számunkra?', '', 1, ''),
(5, 'Hogyan hallottál a templomról?', '', 1, ''),
(6, 'Keresztelés', 'Képzés', 0, ''),
(7, 'Esküvő', 'Képzés', 0, ''),
(8, 'Segítő kapcsolatok', 'Terápia és nyomon követés', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
