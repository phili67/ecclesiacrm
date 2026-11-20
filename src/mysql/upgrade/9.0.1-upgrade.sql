CREATE TABLE `note_revision` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `note_id` mediumint(8) unsigned NOT NULL,
  `author_id` mediumint(8) NOT NULL,
  `created_at` datetime NOT NULL,
  `title` varchar(1000) DEFAULT '',
  `type` varchar(50) DEFAULT NULL,
  `content` longtext,
  PRIMARY KEY (`id`),
  KEY `idx_note_revision_note` (`note_id`, `id`),
  CONSTRAINT `fk_note_revision_note` FOREIGN KEY (`note_id`) REFERENCES `note_nte` (`nte_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
