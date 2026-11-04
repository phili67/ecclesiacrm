INSERT INTO `quick_access` (`qa_font_awesome_icon`, `qa_label`, `qa_url`, `qa_right`, `qa_user_right`, `qa_is_plugin`, `qa_plugin_name`)
SELECT 'fas fa-video', 'Jitsi', 'v2/meeting/dashboard', '', 0, '1', 'MeetingJitsi'
WHERE NOT EXISTS (
    SELECT 1 FROM `quick_access` WHERE `qa_is_plugin` = 1 AND `qa_plugin_name` = 'MeetingJitsi'
);
