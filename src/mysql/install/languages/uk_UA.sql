INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Kyiv'),
(100, 'sPhoneFormat', '999 99 99'),
(101, 'sPhoneFormatWithExt', '999 999 99 99'),
(102, 'sDateFormatLong', 'd.m.Y'),
(103, 'sDateFormatNoYear', 'd.m'),
(105, 'sDateTimeFormat', 'j.m.y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd.mm.yyyy'),
(110, 'sDatePickerFormat', 'd.m.Y'),
(111, 'sPhoneFormatCell', '999 999 99 99'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Цей лист є нагадуванням про всі пожертви для'),
(1012, 'sTaxReport2', 'Дякуємо, що підтримали нас цього року. Ми дуже цінуємо вашу відданість!'),
(1013, 'sTaxReport3', "Якщо у вас є запитання або зміни до звіту, будь ласка, зв'яжіться з вашою церквою за вищевказаним номером у робочий час, з 9:00 до 17:00."),
(1015, 'sReminder1', 'Цей лист є узагальненням інформації, надісланої за поточний фінансовий рік'),
(1019, 'sConfirm1', 'Цей лист узагальнює інформацію, яка міститься в нашій базі даних. Будь ласка, уважно вичитайте його, виправте і поверніть нашій церкві.'),
(1020, 'sConfirm2', 'Дякуємо, що допомогли нам заповнити цю інформацію. Якщо вам потрібна інформація про базу даних.'),
(1021, 'sConfirm3', 'Електронна пошта _____________________________________ пароль ________________'),
(1022, 'sConfirm4', "[  ] Я більше не бажаю бути пов'язаним з церквою (поставте тут галочку, щоб бути видаленим з ваших записів)."),
(1026, 'sPledgeSummary1', "Підсумки зобов'язань та виплат за цей фінансовий рік"),
(1027, 'sPledgeSummary2', 'для'),
(1028, 'sDirectoryDisclaimer1', "Ми працювали над тим, щоб зробити цю інформацію якомога точнішою. Якщо ви знайшли якісь помилки або пропуски, будь ласка, зв'яжіться з нами. Цим довідником користуються люди з"),
(1029, 'sDirectoryDisclaimer2', ', а інформація, що міститься в ньому, не буде використана в комерційних цілях.'),
(1031, 'sZeroGivers', 'У цьому листі підбито підсумки виплат'),
(1032, 'sZeroGivers2', 'Дякуємо, що допомагаєте нам змінювати світ на краще. Ми дуже цінуємо вашу участь!'),
(1033, 'sZeroGivers3',"Якщо у вас виникли питання або ви хочете внести виправлення до цього звіту, будь ласка, зв'яжіться з нашою церквою за вказаним вище номером з 9:00 до 12:00 з понеділка по п'ятницю."),
(1048, 'sConfirmSincerely', 'До зустрічі.'),
(1049, 'sDear', 'Шановний)'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', '₴'),
(2052, 'sUnsubscribeStart', 'Якщо ви не бажаєте отримувати ці листи від'),
(2053, 'sUnsubscribeEnd', 'в подальшому звертайтеся до адміністраторів церкви'),
(1017, 'sReminderNoPledge', 'Пожертви: У нас немає записів про будь-які пожертви від вас за цей фінансовий рік.'),
(1018, 'sReminderNoPayments', 'Платежі: У нас немає записів про будь-які платежі від вас за цей фінансовий рік.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Десятина.', 'гроші до бюджету.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Церковна служба'),
  (2, 'Недільна школа')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Всього', ''),
  (2, 1, 'Члени', ''),
  (3, 1, 'Відвідувачі', ''),
  (4, 2, 'Всього', ''),
  (5, 2, 'Члени', ''),
  (6, 2, 'Відвідувачі', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Відповідає за'),
  (1, 2, 2, 'normal', 'Учасник'),
  (1, 3, 3, 'normal', 'Постійний учасник'),
  (1, 4, 4, 'normal', 'Гість'),
  (1, 5, 5, 'normal', 'Не беруть участь'),
  (1, 6, 6, 'normal', 'Неучасники (команда)'),
  (1, 7, 7, 'normal', 'Померла.'),
  (2, 1, 1, 'normal', "Представник сім'ї"),
  (2, 2, 2, 'normal', 'Чоловік'),
  (2, 3, 3, 'normal', 'Дитинко.'),
  (2, 4, 4, 'normal', "Інший член сім'ї"),
  (2, 5, 5, 'normal', "Не є членом сім'ї"),
  (3, 1, 1, 'normal', 'Міністерство'),
  (3, 2, 2, 'normal', 'Команда'),
  (3, 3, 3, 'normal', 'Вивчення Біблії'),
  (3, 4, 1, 'sunday_school', 'Група 1'),
  (3, 5, 2, 'sunday_school', 'Група 2'),
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
  (1, 'p', 'Людина', 'Загальні властивості осіб'),
  (2, 'f', "Сім'я", 'Загальні властивості сімейств'),
  (3, 'g', 'Група', 'Загальні властивості групи'),
  (4, 'm', 'Меню', 'Персоналізувати меню недільної школи.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'Вимкнено', 'Інвалідність.', 'Яка його природа?',''),
  (2, 'f', 2, "Одинокий батько', 'є неповною сім'єю з одним із батьків.", '',''),
  (3, 'g', 3, 'Молодий', 'має мотивацію працювати в молодіжній роботі.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Користувач Адміністратор'),
(2, 'Мінімальний користувач'),
(3, 'Користувач Max, але не Admin'),
(4, 'Максимальний користувач, але не DPO і не душпастирська опіка'),
(5, 'Користувач DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Класична пасторальна нотатка', '', 1, ''),
(2, 'Чому ви прийшли до церкви?', '', 1, ''),
(3, 'Чому ви продовжуєте приходити?', '', 1, ''),
(4, 'У вас є запит до нас?', '', 1, ''),
(5, 'Як ви дізналися про церкву?', '', 1, ''),
(6, 'Хрещення', 'Навчання', 0, ''),
(7, 'Весілля', 'Навчання', 0, ''),
(8, 'Допомагаючи стосункам', 'Терапія та подальше спостереження', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
