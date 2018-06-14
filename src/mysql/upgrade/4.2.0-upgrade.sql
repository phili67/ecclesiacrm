--
-- We add the coordinates to the event to gain time in the GoogleMap
-- 
ALTER TABLE `events_event` ADD `event_coordinates` varchar(255) NOT NULL default '';