delete from calendar_calendar;
delete from calendar_event;
delete from calendar_event_custom_fields;
delete from calendar_event_alert;
delete from calendar_preferences;
delete from calendar_resource_group;
delete from calendar_category;
update core_module set version = 0 where name='calendar';



# select * from calendar_calendar_event where eventId not in (select eventId from calendar_event);

# should have 0 rows
select name, uuid, calendar_id from cal_events e where uuid not in (
    select uid from calendar_event ce
    inner join calendar_calendar_event cce on ce.eventId = cce.eventId
               where e.calendar_id = cce.calendarId
    )



