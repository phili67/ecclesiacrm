INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Tirane'),
(100, 'sPhoneFormat', '99 999 9999'),
(101, 'sPhoneFormatWithExt', '999 9 999 9999'),
(102, 'sDateFormatLong', 'Y-m-d'),
(103, 'sDateFormatNoYear', 'm-d'),
(105, 'sDateTimeFormat', 'y-m-j G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'Y-m-d'),
(111, 'sPhoneFormatCell', '99 999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'letra është kujtim për të gjitha dhuratat'),
(1012, 'sTaxReport2', 'faleminderit që na mbështetët këtë vit. Na pëlqeu shumë përkushtimi yt!'),
(1013, 'sTaxReport3', 'Nëse keni ndonjë pyetje apo ndryshime për të bërë në lidhje me raportin, kontaktoni kishën në sipër të orës së punës, gjatë orës 9:00 të mëngjesit dhe 5:00 pasdite.'),
(1015, 'sReminder1', 'Kjo letër është përmbledhja e informacionit të dërguar për vitin fiskal aktual'),
(1019, 'sConfirm1', 'Kjo letër përmbledh informacionin e regjistruar në bazën tonë të të dhënave. lexoni me kujdes, korrigojini dhe kthejeni këtë formë në kishën tonë.'),
(1020, 'sConfirm2', 'faleminderit që na ndihmove të plotësonim këtë informacion. Nëse do informacione për bazën e të dhënave.'),
(1021, 'sConfirm3', 'email. _____________________________________ fjalëkalimi ________________'),
(1022, 'sConfirm4', '[  ] Nuk dua të jem i lidhur me kishën më (tik-tak këtu për t''u fshirë nga regjistrimet e tua).'),
(1026, 'sPledgeSummary1', 'përmbledhja e premtimeve të dhurimeve dhe pagesave për këtë vit taksash'),
(1027, 'sPledgeSummary2', 'për të'),
(1028, 'sDirectoryDisclaimer1', 'Ne kemi punuar për t''i bërë këto të dhëna sa më të sakta të jetë e mundur. Nëse i vini re gabimet apo lëshimet, kontaktoni me ne. Kjo drejtori përdoret për njerëz'),
(1029, 'sDirectoryDisclaimer2', ', dhe informacioni i përmbajtur nuk do të përdoret për qëllime komerciale.'),
(1031, 'sZeroGivers', 'kjo letër përmbledh pagesat e'),
(1032, 'sZeroGivers2', 'faleminderit që na ndihmove të bëjmë ndryshimin. e vlerësojmë pjesëmarrjen tuaj!'),
(1033, 'sZeroGivers3', 'Nëse keni pyetje ose korrigjime të këtij raporti, kontaktoni kishën tonë në numrin e sipërm gjatë orës 9:00 të mëngjesit deri në 12:00 pasdite. të hënën deri të premten.'),
(1048, 'sConfirmSincerely', 'shihemi së shpejti'),
(1049, 'sDear', 'E dashur.'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'lek'),
(2052, 'sUnsubscribeStart', 'Nëse nuk do t''i marrësh më këto emaila'),
(2053, 'sUnsubscribeEnd', 'Nëse nuk do t''i marrësh më këto emaila'),
(1017, 'sReminderNoPledge', 'donacionet: nuk kemi regjistrim të donacioneve për këtë vit taksash.'),
(1018, 'sReminderNoPayments', 'pagesat: nuk kemi regjistrim në pjesën tuaj për këtë vit taksash.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'titulli', 'hyrje me para për buxhetin.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Shërbimi i kishës'),
  (2, 'të dielën në shkollë')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Total', ''),
  (2, 1, 'Anëtarë', ''),
  (3, 1, 'vizitorë.', ''),
  (4, 2, 'Total', ''),
  (5, 2, 'Anëtarë', ''),
  (6, 2, 'vizitorë.', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'E përgjegjshme.'),
  (1, 2, 2, 'normal', 'Anëtar.'),
  (1, 3, 3, 'normal', 'pjesëmarrës të zakonshëm'),
  (1, 4, 4, 'normal', 'mysafir'),
  (1, 5, 5, 'normal', 'Asnjë pjesëmarrës'),
  (1, 6, 6, 'normal', 'Asnjë pjesëmarrës (staf)'),
  (1, 7, 7, 'normal', 'Vdiq.'),
  (2, 1, 1, 'normal', 'Përfaqësues familjar'),
  (2, 2, 2, 'normal', 'në rregull.'),
  (2, 3, 3, 'normal', 'Fëmijë'),
  (2, 4, 4, 'normal', 'Një tjetër anëtar i familjes'),
  (2, 5, 5, 'normal', 'nuk është anëtar i familjes'),
  (3, 1, 1, 'normal', 'Departamenti i parë.'),
  (3, 2, 2, 'normal', 'skuadra.'),
  (3, 3, 3, 'normal', 'Studimi i biblës'),
  (3, 4, 1, 'sunday_school', 'Grupi 1'),
  (3, 5, 2, 'sunday_school', 'Grupi 2'),
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
  (1, 'p', 'Askush', 'Prona të përgjithshme të personave'),
  (2, 'f', 'Familja', 'prona të përgjithshme të familjeve'),
  (3, 'g', 'Grup', 'prona të përgjithshme të grupeve'),
  (4, 'm', 'Manu', 'Për të rregulluar menynë e së dielës të shkollës.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'u deaktivizua.', 'Për paaftësinë.', 'cila është natyra e tij?',''),
  (2, 'f', 2, 'Prindërit izolohen', 'është vetëm një familje prindërore.', '',''),
  (3, 'g', 3, 'i ri', 'është e motivuar për të punuar në rini.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Administrator i shfrytëzuesit'),
(2, 'Përdorues minimal'),
(3, 'Maksi përdorues, por jo Adminin'),
(4, 'Maksi përdorues, por jo DPO dhe monitorim pastor'),
(5, 'përdorues i dpo')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'shënim klasik pastor', '', 1, ''),
(2, 'pse erdhe në kishë?', '', 1, ''),
(3, 'pse vazhdon të vish?', '', 1, ''),
(4, 'Ke ndonjë kërkesë për të na bërë?', '', 1, ''),
(5, 'Si keni dëgjuar për kishën?', '', 1, ''),
(6, 'Pagëzimi', 'Trajnim', 0, ''),
(7, 'Martesa', 'Trajnim', 0, ''),
(8, 'Ndihmo lidhjen', 'terapi dhe ndjekje-up', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
