INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'Shaku'),
(65, 'sTimeZone', 'Asia/Tokyo'),
(100, 'sPhoneFormat', '999 999 9999'),
(101, 'sPhoneFormatWithExt', '999 999 9999'),
(102, 'sDateFormatLong', 'Y/m/d'),
(103, 'sDateFormatNoYear', 'm/d'),
(105, 'sDateTimeFormat', 'j/m/y G:i'),
(109, 'sDatePickerPlaceHolder', 'YYYY/mm/dd'),
(110, 'sDatePickerFormat', 'Y/m/d'),
(111, 'sPhoneFormatCell', '999 999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'この手紙は、すべての寄付のためのリマインダーです。'),
(1012, 'sTaxReport2', '今年も応援よろしくお願いします。あなたの献身にとても感謝しています。'),
(1013, 'sTaxReport3', 'ご質問や報告書の変更がある場合は、9時から17時までの営業時間内に、上記の電話番号で教会にご連絡ください。'),
(1015, 'sReminder1', 'この手紙は、今年度に送られた情報をまとめたものです。'),
(1019, 'sConfirm1', 'この手紙は、私たちのデータベースに記録されている情報を要約したものです。慎重に校正し、修正して、私たちの教会に返送してください。'),
(1020, 'sConfirm2', 'この手紙は、私たちのデータベースに記録されている情報を要約したものです。慎重に校正し、修正して、私たちの教会に返送してください。'),
(1021, 'sConfirm3', 'Eメール _____________________________________ パスワード ________________'),
(1022, 'sConfirm4', '[  ] 私はもう教会との関わりを持ちたくありません（記録から削除する場合はここにチェックを入れてください）。'),
(1026, 'sPledgeSummary1', '今年度の誓約書・支払書の概要'),
(1027, 'sPledgeSummary2', 'のためのものです。'),
(1028, 'sDirectoryDisclaimer1', '可能な限り正確な情報を提供するよう努めています。誤字・脱字を発見された方は、ご連絡ください。このディレクトリは、以下の人々のために使用されます。'),
(1029, 'sDirectoryDisclaimer2', ', また、掲載されている情報を商業目的で使用することはできません。'),
(1031, 'sZeroGivers', 'の支払いをまとめた手紙です。'),
(1032, 'sZeroGivers2', '私たちの活動にご協力いただきありがとうございます。皆様のご参加を心よりお待ちしております。'),
(1033, 'sZeroGivers3', '本報告書に関するご質問や修正が必要な場合は、月曜から金曜の9:00amから12:00pmの時間内に、上記の電話番号で当教会までご連絡ください。'),
(1048, 'sConfirmSincerely', 'また、お会いしましょう'),
(1049, 'sDear', 'ディア'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', '¥'),
(2052, 'sUnsubscribeStart', 'からのこれらのメールの受信を希望しない場合は、以下のようになります。'),
(2053, 'sUnsubscribeEnd', '今後は、教会管理者に連絡してください。'),
(1017, 'sReminderNoPledge', '寄付金： 今年度、皆様からの寄付金の記録はありません。'),
(1018, 'sReminderNoPayments', '支払い。今年度、あなたからの支払いの記録はありません。')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'タイツ', '予算のためにお金を入れる。')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, '教会サービス'),
  (2, '日曜学校')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, '合計', ''),
  (2, 1, 'メンバー', ''),
  (3, 1, 'ビジター', ''),
  (4, 2, '合計', ''),
  (5, 2, 'メンバー', ''),
  (6, 2, 'ビジター', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
  (1, 1, 1, 'normal', '責任の所在'),
  (1, 2, 2, 'normal', 'メンバー'),
  (1, 3, 3, 'normal', 'レギュラー参加'),
  (1, 4, 4, 'normal', 'ゲスト'),
  (1, 5, 5, 'normal', 'ノンパーティシパント'),
  (1, 6, 6, 'normal', 'ノンパーティシパント（スタッフ）'),
  (1, 7, 7, 'normal', '故人'),
  (2, 1, 1, 'normal', '家族代表'),
  (2, 2, 2, 'normal', '配偶者'),
  (2, 3, 3, 'normal', '子供'),
  (2, 4, 4, 'normal', 'その他のご家族'),
  (2, 5, 5, 'normal', 'ご家族ではない方'),
  (3, 1, 1, 'normal', '省'),
  (3, 2, 2, 'normal', 'チーム'),
  (3, 3, 3, 'normal', '聖書研究'),
  (3, 4, 1, 'sunday_school', 'グループ1'),
  (3, 5, 2, 'sunday_school', 'グループ2'),
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
  (1, 'p', '人物', '人物の一般特性'),
  (2, 'f', 'ファミリー', 'ファミリーの一般的な特性'),
  (3, 'g', 'グループ', '一般的なグループの特性'),
  (4, 'm', 'メニュー', '日曜学校のメニューをパーソナライズするために')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
  (1, 'p', 1, 'オフ', '障がいがあること。', 'その本質は何か？',''),
  (2, 'f', 2, '一人親方', 'は一人親世帯です。', '',''),
  (3, 'g', 3, 'ヤング', 'は、ユースワークで働くことに意欲を感じています。', '','')
  ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'ユーザー管理者'),
(2, '最小限のユーザー'),
(3, 'ユーザーマックスでもアドミンではない'),
(4, 'ユーザーマックスではなく、DPOでもなく、パストラルケアでもなく'),
(5, 'DPOユーザー')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, 'クラシックなパストラルノート', '', 1, ''),
(2, 'なぜ教会に来たのですか？', '', 1, ''),
(3, 'なぜ来てくれるの？', '', 1, ''),
(4, '何かご要望はありますか？', '', 1, ''),
(5, 'どのようにして教会をお知りになりましたか？', '', 1, ''),
(6, 'バプテスマ', 'トレーニング', 0, ''),
(7, 'ウェディング', 'トレーニング', 0, ''),
(8, '人間関係を助ける', '治療とフォローアップ', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
