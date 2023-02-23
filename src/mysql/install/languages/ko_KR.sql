INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'Shaku'),
(65, 'sTimeZone', 'Asia/Seoul'),
(100, 'sPhoneFormat', '9999 9999'),
(101, 'sPhoneFormatWithExt', '999 99 9999 9999'),
(102, 'sDateFormatLong', 'Y-m-d'),
(103, 'sDateFormatNoYear', 'm-d'),
(105, 'sDateTimeFormat', 'y-m-j G:i'),
(109, 'sDatePickerPlaceHolder', 'yyyy-mm-dd'),
(110, 'sDatePickerFormat', 'Y-m-d'),
(111, 'sPhoneFormatCell', '999 9999 9999'),
(112, 'sTimeFormat', '%H.%M'),
(1011, 'sTaxReport1', '이 편지는 다음과 같은 모든 기부에 대한 알림입니다.'),
(1012, 'sTaxReport2', '올 한 해 저희를 응원해주셔서 감사합니다. 여러분의 헌신에 진심으로 감사드립니다!'),
(1013, 'sTaxReport3', '보고서에 대한 질문이나 변경 사항이 있는 경우, 근무 시간인 오전 9시부터 오후 5시 사이에 위의 번호로 교회에 연락하세요.'),
(1015, 'sReminder1', '이 서신은 현재 회계연도에 대해 발송된 정보를 요약한 것입니다.'),
(1019, 'sConfirm1', '이 서신은 저희 데이터베이스에 기록된 정보를 요약한 것입니다. 꼼꼼히 교정한 후 수정하여 교회로 보내주시기 바랍니다.'),
(1020, 'sConfirm2', '이 정보를 작성하는 데 도움을 주셔서 감사합니다. 데이터베이스에 대한 정보를 원하시는 경우.'),
(1021, 'sConfirm3', '이메일 _____________________________________ 비밀번호 ________________'),
(1022, 'sConfirm4', '[  ] 더 이상 교회와 연결되고 싶지 않습니다(기록에서 삭제하려면 여기를 확인하세요).'),
(1026, 'sPledgeSummary1', '이번 회계 연도의 서약 및 지불금 요약'),
(1027, 'sPledgeSummary2', '에 대한'),
(1028, 'sDirectoryDisclaimer1', '당사는 이 정보를 최대한 정확하게 작성하기 위해 노력했습니다. 오류나 누락이 있는 경우 당사에 문의해 주시기 바랍니다. 이 디렉토리는 다음 사용자가 사용합니다.'),
(1029, 'sDirectoryDisclaimer2', ' 웹사이트에 포함된 정보는 상업적 목적으로 사용되지 않습니다.'),
(1031, 'sZeroGivers', '이 서신에는 다음과 같은 지불금이 요약되어 있습니다.'),
(1032, 'sZeroGivers2', '변화를 만드는 데 도움을 주셔서 감사합니다. 여러분의 참여에 진심으로 감사드립니다!'),
(1033, 'sZeroGivers3', '이 보고서에 대해 궁금한 점이 있거나 수정이 필요한 경우 월요일부터 금요일까지 오전 9시부터 오후 12시까지 위의 전화번호로 문의하시기 바랍니다.'),
(1048, 'sConfirmSincerely', '곧 뵙겠습니다.'),
(1049, 'sDear', '친애하는'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', '₩'),
(2052, 'sUnsubscribeStart', '다음 이메일의 수신을 원하지 않는 경우'),
(2053, 'sUnsubscribeEnd', '향후 교회 관리자에게 문의하세요.'),
(1017, 'sReminderNoPledge', '기부금: 이번 회계연도에는 회원님이 기부한 기록이 없습니다.'),
(1018, 'sReminderNoPayments', '결제: 이번 회계연도에는 귀하로부터 결제한 기록이 없습니다.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
  (1, 'true', 'Dîme', '예산을 위해 돈을 넣어야 합니다.')
    ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
  (1, '교회 봉사'),
  (2, '주일학교')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
  (1, 1, '합계', ''),
  (2, 1, '회원', ''),
  (3, 1, '방문자 수', ''),
  (4, 2, '합계', ''),
  (5, 2, '회원', ''),
  (6, 2, '방문자 수', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
    (1, 1, 1, 'normal', '담당 업무'),
    (1, 2, 2, 'normal', '회원'),
    (1, 3, 3, 'normal', '일반 참가자'),
    (1, 4, 4, 'normal', '게스트'),
    (1, 5, 5, 'normal', '비참가자'),
    (1, 6, 6, 'normal', '비참가자(팀)'),
    (1, 7, 7, 'normal', '사망자'),
    (2, 1, 1, 'normal', '가족 대표'),
    (2, 2, 2, 'normal', '배우자'),
    (2, 3, 3, 'normal', '자식'),
    (2, 4, 4, 'normal', '기타 가족 구성원'),
    (2, 5, 5, 'normal', '가족 구성원이 아님'),
    (3, 1, 1, 'normal', '부처'),
    (3, 2, 2, 'normal', '팀'),
    (3, 3, 3, 'normal', '성경 공부'),
    (3, 4, 1, 'sunday_school', '그룹 1'),
    (3, 5, 2, 'sunday_school', '그룹 2'),
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
  (1, 'p', '사람', '사람의 일반 속성'),
  (2, 'f', '가족', '가족의 일반 속성'),
  (3, 'g', '그룹', '일반 그룹 속성'),
  (4, 'm', '메뉴', '주일학교 메뉴를 맞춤 설정합니다.')
    ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
 (1, 'p', 1, '꺼짐', '장애에 대해', '장애의 본질은 무엇인가요?', ''),
 (2, 'f', 2, '나홀로 부모', '는 한부모 가정입니다.', '', ''),
 (3, 'g', 3, '젊은', '청소년 분야에서 일할 동기를 부여합니다.', '', '')
    ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
   (1, '사용자 관리자'),
   (2, '최소 사용자'),
   (3, '관리자가 아닌 최대 사용자'),
   (4, '최대 사용자이지만 DPO는 아니며 목회자 관리가 아닙니다.'),
   (5, 'DPO 사용자')
    ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);
--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
(1, '클래식 목회 노트', '', 1, ''),
(2, '교회에 오신 이유', '', 1, ''),
(3, '왜 계속 오시나요?', '', 1, ''),
(4, '요청 사항이 있으신가요?', '', 1, ''),
(5, '교회에 대해 어떻게 알게 되셨나요?', '', 1, ''),
(6, '세례', '교육', 0, ''),
(7, '웨딩', '교육', 0, ''),
(8, '관계 지원', '치료 및 후속 조치', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
