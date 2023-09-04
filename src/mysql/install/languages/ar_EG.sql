INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Africa/Cairo'),
(100, 'sPhoneFormat', '99 9999 9999'),
(101, 'sPhoneFormatWithExt', '99 9999 9999'),
(102, 'sDateFormatLong', 'd/m/Y'),
(103, 'sDateFormatNoYear', 'd/m'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd/mm/yyyy'),
(110, 'sDatePickerFormat', 'd/m/Y'),
(111, 'sPhoneFormatCell', '99 9999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(113, 'sPhoneCountryCallingCode', '0971'),
(1011, 'sTaxReport1', 'هذه الرسالة تذكر بكل الهدايا'),
(1012, 'sTaxReport2', 'شكراً لدعمك لنا هذا العام لقد استمتعنا بتفانيك كثيراً'),
(1013, 'sTaxReport3', 'إذا كان لديك أي أسئلة أو تغييرات لتجريها بخصوص التقرير، اتصل بكنيستك على الرقم المذكور أعلاه خلال ساعات العمل بين التاسعة صباحاً والخامسة مساءً'),
(1015, 'sReminder1', 'وهذه الرسالة موجزة للمعلومات التي أرسلت للسنة الضريبية الحالية'),
(1019, 'sConfirm1', 'وتلخص هذه الرسالة المعلومات المسجلة في قاعدة بياناتنا. اقرأ بعناية، صححهم وإعادة هذا الشكل إلى كنيستنا.'),
(1020, 'sConfirm2', 'شكراً لمساعدتنا على إكمال هذه المعلومات إذا كنت تريد معلومات عن قاعدة البيانات.'),
(1021, 'sConfirm3', 'البريد الإلكتروني _____________________________________ كلمة السر ________________'),
(1022, 'sConfirm4', '[  ] لا أريد أن أكون مرتبطة بالكنيسة بعد الآن (محوها هنا لكي يتم حذفها من تسجيلاتك).'),
(1026, 'sPledgeSummary1', 'موجز وعود التبرعات والدفع لهذه السنة الضريبية'),
(1027, 'sPledgeSummary2', 'من أجله'),
(1028, 'sDirectoryDisclaimer1', 'لقد عملنا لجعل هذه البيانات دقيقة قدر الإمكان. إذا لاحظت الأخطاء أو الإغفال، اتصل بنا. هذا الدليل يستخدم للناس من'),
(1029, 'sDirectoryDisclaimer2', 'والمعلومات التي تحتوي عليها لن تستخدم لأغراض تجارية.'),
(1031, 'sZeroGivers', 'وتلخص هذه الرسالة مدفوعات'),
(1032, 'sZeroGivers2', 'شكراً لمساعدتك لنا في إحداث فرق نحن نقدر مشاركتك!'),
(1033, 'sZeroGivers3', 'إذا كان لديك أسئلة أو تصحيحات لهذا التقرير، اتصل بكنيستنا على الرقم المذكور أعلاه خلال ساعات من الساعة 9: 00 إلى 12: 00 مساء. من الاثنين إلى الجمعة'),
(1048, 'sConfirmSincerely', 'أراك قريباً'),
(1049, 'sDear', 'عزيزي (عزيزي)'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'E£'),
(2052, 'sUnsubscribeStart', 'إذا كنت لا تريد أن تتلقى هذه الرسائل من بعد الآن'),
(2053, 'sUnsubscribeEnd', 'في المستقبل، اتصل بمديري الكنيسة'),
(1017, 'sReminderNoPledge', 'التبرعات: ليس لدينا تسجيل للتبرعات منك لهذه السنة الضريبية.'),
(1018, 'sReminderNoPayments', 'المدفوعات: ليس لدينا تسجيل من جانبك لهذه السنة الضريبية.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'العنوان', 'مدخل المال للميزانية')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'خدمة الكنيسة'),
  (2, 'مدرسة الأحد')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'المجموع', ''),
  (2, 1, 'الأعضاء', ''),
  (3, 1, 'الزوار', ''),
  (4, 2, 'المجموع', ''),
  (5, 2, 'الأعضاء', ''),
  (6, 2, 'الزوار', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'مسؤول'),
  (1, 2, 2, 'normal', 'عضو'),
  (1, 3, 3, 'normal', 'مشارك منتظم'),
  (1, 4, 4, 'normal', 'ضيف'),
  (1, 5, 5, 'normal', 'لا مشارك'),
  (1, 6, 6, 'normal', 'غير مشارك (موظفون)'),
  (1, 7, 7, 'normal', 'ميت'),
  (2, 1, 1, 'normal', 'ممثل العائلة'),
  (2, 2, 2, 'normal', 'ماري/زوجة'),
  (2, 3, 3, 'normal', 'الطفل'),
  (2, 4, 4, 'normal', 'عضو آخر من العائلة'),
  (2, 5, 5, 'normal', 'إنه ليس فرداً من العائلة'),
  (3, 1, 1, 'normal', 'اللغة الإنجليزية'),
  (3, 2, 2, 'normal', 'الفريق'),
  (3, 3, 3, 'normal', 'دراسة الإنجيل'),
  (3, 4, 1, 'sunday_school', 'المجموعة الأولى'),
  (3, 5, 2, 'sunday_school', 'المجموعة الثانية'),
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
  (1, 'p', 'لا أحد', '8- الخصائص العامة للأشخاص'),
  (2, 'f', 'الأسرة', 'الخصائص العامة للأسر'),
  (3, 'g', 'المجموعة', '8- الخصائص العامة للمجموعات'),
  (4, 'm', 'ألف - النبات', 'لتخصيص قائمة مدرسة الأحد')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'تم تعطيل', 'إلى الإعاقة', 'ما طبيعته؟',''),
  (2, 'f', 2, 'الأب منعزل', 'هو منزل والد واحد', '',''),
  (3, 'g', 3, 'يافعة', 'محفز للعمل في الشباب', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'مدير المستخدم'),
(2, 'أقل مستخدم'),
(3, 'المستخدم ماكس ولكن ليس أدمين'),
(4, 'مستخدم ماكس ولكن ليس إدارة شؤون الإعلام وليس مراقبة الرعوى'),
(5, 'مستخدم "إدارة شؤون الإعلام"')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'ملاحظة رعاية تقليدية', '', 1, ''),
(2, 'لماذا أتيت إلى الكنيسة؟', '', 1, ''),
(3, 'لماذا تستمر بالقدوم؟', '', 1, ''),
(4, 'هل لديك طلب لتجعلنا؟', '', 1, ''),
(5, 'كيف سمعت عن الكنيسة؟', '', 1, ''),
(6, 'التعميد', 'التدريب', 0, ''),
(7, 'الزواج', 'التدريب', 0, ''),
(8, 'مساعدة العلاقة', 'العلاج والمتابعة', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
