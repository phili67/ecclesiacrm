INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'li'),
(65, 'sTimeZone', 'Asia/Chongqing'),
(100, 'sPhoneFormat', '999 9999'),
(101, 'sPhoneFormatWithExt', '9999 999 9999'),
(102, 'sDateFormatLong', 'Y-d-m'),
(103, 'sDateFormatNoYear', 'd-m'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'yyyy-dd-mm'),
(110, 'sDatePickerFormat', 'Y-d-m'),
(111, 'sPhoneFormatCell', '999 9999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', '这封信是为了提醒大家注意所有为'),
(1012, 'sTaxReport2', '感谢你今年对我们的支持。我们非常感谢您的奉献精神!'),
(1013, 'sTaxReport3', '如果你对报告有任何疑问或修改，请在工作时间内，即上午9点至下午5点之间，通过上述电话与你的教会联系。'),
(1015, 'sReminder1', '本函是本财政年度所发信息的摘要'),
(1019, 'sConfirm1', '这封信总结了我们数据库中记录的信息。请仔细校对，更正后交回本教会。'),
(1020, 'sConfirm2', '感谢你帮助我们完成这些信息。如果你想知道关于数据库的信息。'),
(1021, 'sConfirm3', '电子邮件 _____________________________________ 密码 ________________'),
(1022, 'sConfirm4', '[  ] 我不想再与教会有联系（在此打勾，以便从记录中删除）。'),
(1026, 'sPledgeSummary1', '本财政年度的认捐和付款摘要'),
(1027, 'sPledgeSummary2', '为'),
(1028, 'sDirectoryDisclaimer1', '我们已经努力使这些信息尽可能准确。如果你发现任何错误或遗漏，请联系我们。这个目录是用于来自'),
(1029, 'sDirectoryDisclaimer2', ', 并且所包含的信息不会被用于商业目的。'),
(1031, 'sZeroGivers', '这封信总结了以下的付款情况'),
(1032, 'sZeroGivers2', '感谢你帮助我们做出改变。我们非常感谢您的参与!'),
(1033, 'sZeroGivers3', '如果你有任何问题或需要对本报告进行修改，请在周一至周五上午9:00至中午12:00的时间内通过上述电话与我们的教会联系。'),
(1048, 'sConfirmSincerely', '再见'),
(1049, 'sDear', '亲爱的'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'Yuan'),
(2052, 'sUnsubscribeStart', '如果你不希望收到这些来自于'),
(2053, 'sUnsubscribeEnd', '在未来，请联系教会管理人员'),
(1017, 'sReminderNoPledge', '捐赠：本财政年度我们没有收到您的任何捐赠记录。'),
(1018, 'sReminderNoPayments', '支付费用。我们没有你在本财政年度的任何付款记录。')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', '什一税', '拨款给预算。')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, '教会服务'),
  (2, '主日学')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, '共计', ''),
  (2, 1, '成员', ''),
  (3, 1, '访客', ''),
  (4, 2, '共计', ''),
  (5, 2, '成员', ''),
  (6, 2, '访客', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', '负责'),
  (1, 2, 2, 'normal', '会员'),
  (1, 3, 3, 'normal', '普通参与者'),
  (1, 4, 4, 'normal', '访客'),
  (1, 5, 5, 'normal', '未参加的人'),
  (1, 6, 6, 'normal', '非参赛者（工作人员）'),
  (1, 7, 7, 'normal', '已故'),
  (2, 1, 1, 'normal', 'R家庭代表'),
  (2, 2, 2, 'normal', '配偶'),
  (2, 3, 3, 'normal', 'Enfant'),
  (2, 4, 4, 'normal', 'A儿童'),
  (2, 5, 5, 'normal', '不是家庭成员'),
  (3, 1, 1, 'normal', '部会'),
  (3, 2, 2, 'normal', '团队'),
  (3, 3, 3, 'normal', '学习圣经'),
  (3, 4, 1, 'sunday_school', '第1组'),
  (3, 5, 2, 'sunday_school', '第2组'),
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
  (1, 'p', 'Personne', '人员的一般属性'),
  (2, 'f', 'Famille', '家庭的一般属性'),
  (3, 'g', 'Groupe', '一般群体属性'),
  (4, 'm', 'Menu', '为了使主日学的菜单个性化。')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, '关闭', '一种残疾。', '它的性质是什么？',''),
  (2, 'f', 2, '单亲家庭', '是其家庭中的单亲。.', '',''),
  (3, 'g', 3, '年轻人', '是面向青年的。', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, '用户管理员'),
(2, '最小用户'),
(3, '用户最大，但不是管理员'),
(4, '用户最大，但不是DPO，也不是教务处'),
(5, 'DPO用户')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, '经典的牧歌音符', '', 1, ''),
(2, '你为什么要到教堂来？', '', 1, ''),
(3, '你为什么一直来？', '', 1, ''),
(4, '你对我们有什么要求吗？', '', 1, ''),
(5, '你是怎么听说这个教会的？', '', 1, ''),
(6, '洗礼', '培训', 0, ''),
(7, '婚礼', '培训', 0, ''),
(8, '帮助关系', '治疗和随访', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
