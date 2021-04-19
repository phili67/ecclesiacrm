DELETE FROM userconfig_ucfg WHERE ucfg_id="20";

INSERT INTO `userconfig_choices_ucfg_ch` (`ucfg_ch_id`,`ucfg_name`,`ucfg_choices`) VALUES
(6,'DarkMode', 'light,dark,automatic' );

INSERT INTO `userconfig_ucfg` (`ucfg_per_id`, `ucfg_id`, `ucfg_name`, `ucfg_value`, `ucfg_type`, `ucfg_choices_id`, `ucfg_tooltip`, `ucfg_permission`, `ucfg_cat`) VALUES
(0, 20, 'sDarkMode', 'automatic', 'choice', '6', 'AdminLTE 3.1 Dark Mode', 'TRUE', ''),
(1, 20, 'sDarkMode', 'automatic', 'choice', '6','AdminLTE 3.1 Dark Mode color style', 'TRUE', '');
