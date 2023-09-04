INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Europe/Moscow'),
(100, 'sPhoneFormat', '9 999 999 99 99'),
(101, 'sPhoneFormatWithExt', '9999 999 999 99 99'),
(102, 'sDateFormatLong', 'd.m.Y'),
(103, 'sDateFormatNoYear', 'd.m'),
(105, 'sDateTimeFormat', 'j.m.y G:i'),
(109, 'sDatePickerPlaceHolder', 'dd.mm.yyyy'),
(110, 'sDatePickerFormat', 'd.m.Y'),
(111, 'sPhoneFormatCell', '9 999 999 99 99'),
(112, 'sTimeFormat', '%H:%M'),
(113, 'sPhoneCountryCallingCode', '0007'),
(1011, 'sTaxReport1', 'Это письмо - напоминание о всех пожертвованиях для'),
(1012, 'sTaxReport2', 'Спасибо, что поддержали нас в этом году. Мы очень ценим вашу преданность!'),
(1013, 'sTaxReport3', 'Если у вас возникли вопросы или изменения в отчете, пожалуйста, свяжитесь с церковью по указанному выше номеру в рабочее время, с 9 утра до 5 вечера.'),
(1015, 'sReminder1', 'Данное письмо представляет собой краткое изложение информации, отправленной за текущий финансовый год'),
(1019, 'sConfirm1', 'В этом письме обобщается информация, которая занесена в нашу базу данных. Пожалуйста, внимательно вычитайте его, исправьте и верните в нашу церковь.'),
(1020, 'sConfirm2', 'Спасибо, что помогли нам заполнить эту информацию. Если вам нужна информация о базе данных.'),
(1021, 'sConfirm3', 'Email _____________________________________ пароль ________________'),
(1022, 'sConfirm4', '[  ] Я больше не хочу быть связанным с этой церковью (отметьте здесь, чтобы меня удалили из ваших записей).'),
(1026, 'sPledgeSummary1', 'Сводка обязательств и платежей за этот финансовый год'),
(1027, 'sPledgeSummary2', 'для'),
(1028, 'sDirectoryDisclaimer1', 'Мы постарались сделать эту информацию как можно более точной. Если вы обнаружили какие-либо ошибки или упущения, пожалуйста, свяжитесь с нами. Этот каталог используется для людей из'),
(1029, 'sDirectoryDisclaimer2', ', и содержащаяся в нем информация не будет использоваться в коммерческих целях.'),
(1031, 'sZeroGivers', 'В данном письме приведена краткая информация о выплатах'),
(1032, 'sZeroGivers2', 'Спасибо, что помогли нам изменить ситуацию. Мы очень ценим ваше участие!'),
(1033, 'sZeroGivers3', 'Если у вас возникли вопросы или вам необходимо внести исправления в этот отчет, пожалуйста, свяжитесь с нашей церковью по указанному выше номеру в часы с 9:00 до 12:00 с понедельника по пятницу.'),
(1048, 'sConfirmSincerely', 'До скорой встречи'),
(1049, 'sDear', 'Уважаемый'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', '₽'),
(2052, 'sUnsubscribeStart', 'Если вы не хотите получать эти электронные письма от'),
(2053, 'sUnsubscribeEnd', 'в будущем, свяжитесь с администраторами церкви'),
(1017, 'sReminderNoPledge', 'Пожертвования: У нас нет данных о каких-либо пожертвованиях от вас за этот финансовый год.'),
(1018, 'sReminderNoPayments', 'Платежи: У нас нет данных о каких-либо платежах от вас за этот финансовый год.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Десятина', 'деньги в бюджет.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, 'Церковная служба'),
  (2, 'Воскресная школа')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, 'Всего', ''),
  (2, 1, 'Члены', ''),
  (3, 1, 'Посетители', ''),
  (4, 2, 'Всего', ''),
  (5, 2, 'Члены', ''),
  (6, 2, 'Посетители', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', 'Ответственный за'),
  (1, 2, 2, 'normal', 'Член'),
  (1, 3, 3, 'normal', 'Постоянный участник'),
  (1, 4, 4, 'normal', 'Гость'),
  (1, 5, 5, 'normal', 'Не участвующий'),
  (1, 6, 6, 'normal', 'Неучастники (сотрудники)'),
  (1, 7, 7, 'normal', 'Умерший'),
  (2, 1, 1, 'normal', 'Представитель семьи'),
  (2, 2, 2, 'normal', 'Супруг'),
  (2, 3, 3, 'normal', 'Ребенок'),
  (2, 4, 4, 'normal', 'Другой член семьи'),
  (2, 5, 5, 'normal', 'Не является членом семьи'),
  (3, 1, 1, 'normal', 'Церковное служение'),
  (3, 2, 2, 'normal', 'Команда'),
  (3, 3, 3, 'normal', 'Изучение Библии'),
  (3, 4, 1, 'sunday_school', 'Группа 1'),
  (3, 5, 2, 'sunday_school', 'Группа 2'),
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
  (1, 'p', 'Человек', 'Общие свойства лиц'),
  (2, 'f', 'Семья', 'Общие свойства семейств'),
  (3, 'g', 'Группа', 'Общие свойства группы'),
  (4, 'm', 'Меню', 'Чтобы персонализировать меню воскресной школы.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'На сайте', 'Инвалидность.', 'Какова его природа?',''),
  (2, 'f', 2, 'Одинокий родитель', 'это домохозяйство с одним родителем.', '',''),
  (3, 'g', 3, 'Молодой', 'мотивирован на работу в сфере работы с молодежью.', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Администратор пользователя'),
(2, 'Минимальный пользователь'),
(3, 'Пользователь Макс, но не Администратор'),
(4, 'Пользователь Макс, но не DPO и не Пасторское попечение'),
(5, 'Пользователь DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'Классическая пасторальная нота', '', 1, ''),
(2, 'Почему вы пришли в церковь', '', 1, ''),
(3, 'Почему вы продолжаете приходить?', '', 1, ''),
(4, 'У вас есть просьба к нам?', '', 1, ''),
(5, 'Как вы узнали о церкви?', '', 1, ''),
(6, 'Крещение', 'Обучение', 0, ''),
(7, 'Свадьба', 'Обучение', 0, ''),
(8, 'Помощь во взаимоотношениях', 'Помощь во взаимоотношениях', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
