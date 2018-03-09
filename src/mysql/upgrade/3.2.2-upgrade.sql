ALTER TABLE `deposit_dep` ADD `dep_Fund` mediumint(6) NOT NULL default '0';
ALTER TABLE `eventcounts_evtcnt` MODIFY `evtcnt_notes`  varchar(255) default NULL;