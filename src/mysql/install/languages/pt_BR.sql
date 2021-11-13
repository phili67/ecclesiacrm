INSERT INTO `config_cfg` (`cfg_id`, `cfg_name`, `cfg_value`) VALUES
(64, 'sDistanceUnit', 'kilometers'),
(65, 'sTimeZone', 'Brazil/West'),
(100, 'sPhoneFormat', '99 9999 9999'),
(101, 'sPhoneFormatWithExt', '999 99 9999 9999'),
(102, 'sDateFormatLong', 'Y-d-m'),
(103, 'sDateFormatNoYear', 'd-m'),
(105, 'sDateTimeFormat', 'y-j-m G:i'),
(109, 'sDatePickerPlaceHolder', 'yyyy-jj-mm'),
(110, 'sDatePickerFormat', 'Y-d-m'),
(111, 'sPhoneFormatCell', '99 99999 9999'),
(112, 'sTimeFormat', '%H:%M'),
(1011, 'sTaxReport1', 'Esta carta é um lembrete de todas as doações para'),
(1012, 'sTaxReport2', 'Obrigado por nos apoiar este ano. Agradecemos muito sua dedicação!'),
(1013, 'sTaxReport3', 'Se você tiver alguma dúvida ou alteração no relatório, favor contatar sua igreja no número acima durante o horário de trabalho, entre 9h e 17h.'),
(1015, 'sReminder1', 'Esta carta é um resumo das informações enviadas para o ano fiscal atual'),
(1019, 'sConfirm1', 'Esta carta resume as informações que estão registradas em nosso banco de dados. Por favor, revise-o cuidadosamente, corrija e devolva-o à nossa igreja.'),
(1020, 'sConfirm2', 'Obrigado por nos ajudar a completar estas informações. Se você quiser informações sobre o banco de dados.'),
(1021, 'sConfirm3', 'Email _____________________________________ senha ________________'),
(1022, 'sConfirm4', '[  ] Não quero mais ser associado à igreja (verifique aqui para ser excluído de seus registros).'),
(1026, 'sPledgeSummary1', 'Resumo das promessas e pagamentos para este ano fiscal'),
(1027, 'sPledgeSummary2', 'pour le'),
(1028, 'sDirectoryDisclaimer1', 'Temos trabalhado para tornar estas informações o mais precisas possível. Se você encontrar algum erro ou omissão, por favor entre em contato conosco. Este diretório é usado para pessoas de'),
(1029, 'sDirectoryDisclaimer2', ', e as informações contidas não serão utilizadas para fins comerciais.'),
(1031, 'sZeroGivers', 'Esta carta resume os pagamentos de'),
(1032, 'sZeroGivers2', 'Obrigado por nos ajudar a fazer a diferença. Agradecemos muito sua participação!'),
(1033, 'sZeroGivers3', 'Se você tiver alguma dúvida ou precisar fazer correções neste relatório, entre em contato com nossa igreja no número acima durante o horário das 9:00 às 12:00 horas de segunda a sexta-feira.'),
(1048, 'sConfirmSincerely', 'A très bientôtVejo você em breve'),
(1049, 'sDear', 'Prezado'),
(1051, 'bTimeEnglish', ''),
(2050, 'bStateUnusefull', '1'),
(2051, 'sCurrency', 'R$'),
(2052, 'sUnsubscribeStart', 'Se você não quiser receber estes e-mails de'),
(2053, 'sUnsubscribeEnd', 'no futuro, entre em contato com os administradores da igreja'),
(1017, 'sReminderNoPledge', 'Doações: Não temos registro de nenhuma doação de vocês para este ano fiscal.'),
(1018, 'sReminderNoPayments', 'Pagamentos: Não temos registro de nenhum pagamento de você para este ano fiscal.')
ON DUPLICATE KEY UPDATE cfg_name=VALUES(cfg_name),cfg_value=VALUES(cfg_value);


INSERT INTO `donationfund_fun` (`fun_ID`, `fun_Active`, `fun_Name`, `fun_Description`) VALUES
(1, 'true', 'Dízimo', 'dinheiro para o orçamento.')
ON DUPLICATE KEY UPDATE fun_Active=VALUES(fun_Active),fun_Name=VALUES(fun_Name),fun_Description=VALUES(fun_Description);

INSERT INTO `event_types` (`type_id`, `type_name`) VALUES
(1, 'Serviço eclesial'),
(2, 'Escola dominical')
ON DUPLICATE KEY UPDATE type_name=VALUES(type_name);

INSERT INTO `eventcountnames_evctnm` (`evctnm_countid`, `evctnm_eventtypeid`, `evctnm_countname`, `evctnm_notes`) VALUES
(1, 1, 'Total', ''),
(2, 1, 'Membros', ''),
(3, 1, 'Visitantes', ''),
(4, 2, 'Total', ''),
(5, 2, 'Membros', ''),
(6, 2, 'Visitantes', '')
ON DUPLICATE KEY UPDATE evctnm_countname=VALUES(evctnm_countname),evctnm_notes=VALUES(evctnm_notes);

DELETE FROM list_lst;

INSERT INTO `list_lst` (`lst_ID`, `lst_OptionID`, `lst_OptionSequence`, `lst_Type`, `lst_OptionName`) VALUES
(1, 1, 1, 'normal', 'Responsável por'),
(1, 2, 2, 'normal', 'Membro'),
(1, 3, 3, 'normal', 'Participante regular'),
(1, 4, 4, 'normal', 'Convidado'),
(1, 5, 5, 'normal', 'Não-participante'),
(1, 6, 6, 'normal', 'Não-participantes (pessoal)'),
(1, 7, 7, 'normal', 'Falecido'),
(2, 1, 1, 'normal', 'Representante da família'),
(2, 2, 2, 'normal', 'Cônjuge'),
(2, 3, 3, 'normal', 'Criança'),
(2, 4, 4, 'normal', 'Outro membro da família'),
(2, 5, 5, 'normal', 'Não é um membro da família'),
(3, 1, 1, 'normal', 'Ministério'),
(3, 2, 2, 'normal', 'Equipe'),
(3, 3, 3, 'normal', 'Estudo bíblico'),
(3, 4, 1, 'sunday_school', 'Grupo 1'),
(3, 5, 2, 'sunday_school', 'Grupo 2'),
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
(1, 'p', 'Pessoa', 'Propriedades gerais das pessoas'),
(2, 'f', 'Família', 'Propriedades gerais das famílias'),
(3, 'g', 'Grupo', 'Propriedades gerais do grupo'),
(4, 'm', 'Menu', 'Para personalizar o cardápio da escola dominical.')
ON DUPLICATE KEY UPDATE prt_Name=VALUES(prt_Name),prt_Description=VALUES(prt_Description);

INSERT INTO `property_pro` (`pro_ID`, `pro_Class`, `pro_prt_ID`, `pro_Name`, `pro_Description`, `pro_Prompt`, `pro_Comment`) VALUES
(1, 'p', 1, 'Fora', 'Uma deficiência.', 'Qual é a sua natureza?',''),
(2, 'f', 2, 'Pai solitário', 'é um lar monoparental.', '',''),
(3, 'g', 3, 'Jovem', 'é motivado a trabalhar no trabalho dos jovens.', '','')
ON DUPLICATE KEY UPDATE pro_Name=VALUES(pro_Name),pro_Description=VALUES(pro_Description),pro_Prompt=VALUES(pro_Prompt);

INSERT INTO `userrole_usrrol` (`usrrol_id`, `usrrol_name`) VALUES
(1, 'Administrador do usuário'),
(2, 'Usuário mínimo'),
(3, 'Usuário Max mas não Admin'),
(4, 'Usuário Max mas não DPO e não Pastoral'),
(5, 'Usuário do DPO')
ON DUPLICATE KEY UPDATE usrrol_name=VALUES(usrrol_name);

--
-- last update for the new CRM 4.4.0
--

INSERT INTO `pastoral_care_type` (`pst_cr_tp_id`, `pst_cr_tp_title`, `pst_cr_tp_desc`, `pst_cr_tp_visible`, `pst_cr_tp_comment`) VALUES
 (1, 'Nota pastoral clássica', '', 1, ''),
 (2, 'Por que você veio para a igreja', '', 1, ''),
 (3, 'Por que você continua vindo?', '', 1, ''),
 (4, 'Você tem um pedido para nós?', '', 1, ''),
 (5, 'Como você ouviu falar sobre a igreja?', '', 1, ''),
 (6, 'Batismo', 'Treinamento', 0, ''),
 (7, 'Casamento', 'Treinamento', 0, ''),
 (8, 'Relações de ajuda', 'Terapia e acompanhamento', 0, '')
ON DUPLICATE KEY UPDATE pst_cr_tp_title=VALUES(pst_cr_tp_title),pst_cr_tp_desc=VALUES(pst_cr_tp_desc),pst_cr_tp_visible=VALUES(pst_cr_tp_visible);
