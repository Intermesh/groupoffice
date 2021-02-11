delete from core_group where isUserGroupFor is not null and isUserGroupFor not in (select id from core_user);
delete from go_reminders where user_id not in (select id from core_user);
delete from go_reminders_users where user_id not in (select id from core_user);
delete from go_reminders where user_id not in (select reminder_id from go_reminders_users);
delete from go_settings where user_id > 0 && user_id not in (select id from core_user);
delete from go_working_weeks where user_id not in (select id from core_user);


# delete from em_accounts where user_id not in (select id from core_user);
# delete from em_aliases where account_id not in (select id from em_accounts);
# delete from em_accounts_collapsed where account_id not in (select id from em_accounts);
# delete from em_accounts_sort where account_id not in (select id from em_accounts);
# delete from em_contacts_last_mail_times where contact_id not in (select id from addressbook_contact);
# delete from em_filters where account_id not in (select id from em_accounts);
# delete from em_folders where account_id not in (select id from em_accounts);
# delete from em_folders_expanded where folder_id not in (select id from em_folders);
# delete from em_labels where account_id not in (select id from em_accounts);
# delete from em_portlet_folders where account_id not in (select id from em_accounts);
#
# delete from email_default_email_account_templates where account_id not in (select id from em_accounts);
# delete from email_default_email_templates where user_id not in (select id from core_user);
#
#
#
# delete from ta_tasklists where user_id not in (select id from core_user);
# delete from ta_tasks where tasklist_id not in (select id from ta_tasklists);
# delete from ta_categories where user_id not in (select id from core_user);
# delete from ta_settings where user_id not in (select id from core_user);
# delete from ta_portlet_tasklists where user_id not in (select id from core_user);
# delete from ta_portlet_tasklists where tasklist_id not in (select id from ta_tasklists);
#
# delete from sync_calendar_user where calendar_id not in (select id from cal_calendars);
# delete from sync_calendar_user where user_id not in (select id from core_user);
# delete from sync_addressbook_user where userId not in (select id from core_user);
# delete from sync_addressbook_user where addressBookId not in (select id from ab_addressbooks);
# delete from sync_user_note_book where userId not in (select id from core_user);
# delete from sync_settings where user_id not in (select id from core_user);
# delete from sync_tasklist_user where user_id not in (select id from core_user);
# delete from sync_tasklist_user where tasklist_id not in (select id from ta_tasklists);
#
# delete from su_visible_lists where user_id not in (select id from core_user);
# delete from su_visible_lists where tasklist_id not in (select id from ta_tasklists);
# delete from cal_visible_tasklists where tasklist_id not in (select id from ta_tasklists);
#
#
# delete from smi_certs where user_id not in (select id from core_user);

