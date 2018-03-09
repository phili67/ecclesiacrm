-- We clear the table for the lost datas

DELETE FROM `eventcounts_evtcnt` WHERE `evtcnt_eventid` IN (
select * 
From
(
SELECT t1. evtcnt_eventid
FROM eventcounts_evtcnt t1
    LEFT JOIN `events_event` t2 ON t1.evtcnt_eventid = t2. event_id
WHERE t2. event_id IS NULL
)
AS tmp
);

-- we clear the table from the data lost
DELETE FROM `event_attend` WHERE `event_id` IN (
select * 
From
(
SELECT t1. event_id
FROM event_attend t1
    LEFT JOIN events_event t2 ON t1.event_id = t2. event_id
WHERE t2. event_id IS NULL
)
AS tmp
);


-- Now we can uprade the table for the constraint integrity

Alter TABLE `eventcounts_evtcnt` MODIFY COLUMN `evtcnt_eventid` int(11) NOT NULL DEFAULT '0';

ALTER TABLE `eventcounts_evtcnt`
ADD CONSTRAINT fk_evtcnt_event_ID 
  FOREIGN KEY (evtcnt_eventid) 
  REFERENCES events_event(event_id)
  ON DELETE CASCADE;

ALTER TABLE `event_attend`
ADD CONSTRAINT fk_attend_event_ID 
  FOREIGN KEY (event_id) 
  REFERENCES events_event(event_id)
  ON DELETE CASCADE;

ALTER TABLE events_event ADD `event_parent_id` int(11) DEFAULT NULL;
ALTER TABLE events_event
ADD CONSTRAINT fk_event_parent_id 
  FOREIGN KEY (event_parent_id) REFERENCES events_event(event_id)
  ON DELETE SET NULL;

Alter TABLE `eventcountnames_evctnm` MODIFY COLUMN `evctnm_eventtypeid` int(11) NOT NULL DEFAULT '0';

ALTER TABLE eventcountnames_evctnm
ADD CONSTRAINT fk_evctnm_eventtypeid
  FOREIGN KEY (evctnm_eventtypeid) REFERENCES event_types(type_id)
  ON DELETE CASCADE;
  
ALTER TABLE `eventcounts_evtcnt`
ADD CONSTRAINT fk_evtcnt_countid
  FOREIGN KEY (evtcnt_countid) REFERENCES eventcountnames_evctnm(evctnm_countid)
  ON DELETE CASCADE;


-- Add 2 new key for security reasons

ALTER TABLE `user_usr` ADD `usr_ShowCart` tinyint(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `user_usr` ADD `usr_ShowMap` tinyint(1) unsigned NOT NULL DEFAULT '0';
