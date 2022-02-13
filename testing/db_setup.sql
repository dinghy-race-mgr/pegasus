delete from t_racestate WHERE `eventid` = XXXXX;
delete from t_lap WHERE `eventid` = XXXXX;
delete from t_race WHERE `eventid` = XXXXX;
truncate table t_finish;
update t_event set `event_date` = 'yyyy-mm-dd', `event_status` = 'scheduled', `timerstart` = 0 WHERE id = XXXXX;
update t_entry set `status` = 'N' WHERE `eventid` = XXXXX;