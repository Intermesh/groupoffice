SET SESSION foreign_key_checks=0;

-- Address books
DROP TABLE IF EXISTS ab_addressbooks, ab_companies, ab_contacts, ab_contacts_vcard_props;
DROP TABLE IF EXISTS ab_addresslist_companies,ab_addresslists, ab_addresslist_group, ab_addresslist_contacts;
DROP TABLE IF EXISTS ab_default_email_account_templates, ab_default_email_templates, ab_email_templates;
DROP TABLE IF EXISTS ab_portlet_birthdays, ab_search_queries, ab_sent_mailing_companies, ab_sent_mailing_contacts;
DROP TABLE IF EXISTS ab_sent_mailings, ab_settings;
DROP TABLE IF EXISTS cf_ab_companies, cf_ab_contacts;

-- Bookmarks old version
DROP TABLE IF EXISTS bm_categories, bm_bookmarks, bm_settings;

-- Calendar
DELETE FROM cal_calendars WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM cal_events WHERE calendar_id NOT IN (SELECT id FROM cal_calendars);
DELETE FROM cal_participants WHERE event_id NOT IN (SELECT id FROM cal_events);
DELETE FROM cal_exceptions WHERE event_id NOT IN (SELECT id FROM cal_events);
DELETE FROM cal_views WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM cal_views_calendars WHERE view_id NOT IN (SELECT id FROM cal_views);
DELETE FROM cal_views_groups WHERE view_id NOT IN (SELECT id FROM cal_views);
DELETE FROM cal_views_groups WHERE group_id NOT IN (SELECT id FROM core_group);
DROP TABLE IF EXISTS cf_cal_events;

-- Email
DELETE FROM em_accounts WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM em_aliases WHERE account_id NOT IN (SELECT id FROM em_accounts);
DELETE FROM em_accounts_collapsed WHERE account_id NOT IN (SELECT id FROM em_accounts);
DELETE FROM em_accounts_sort WHERE account_id NOT IN (SELECT id FROM em_accounts);
DELETE FROM em_contacts_last_mail_times WHERE contact_id NOT IN (SELECT id FROM addressbook_contact);
DELETE FROM em_filters WHERE account_id NOT IN (SELECT id FROM em_accounts);
DELETE FROM em_folders WHERE account_id NOT IN (SELECT id FROM em_accounts);
DELETE FROM em_folders_expanded WHERE folder_id NOT IN (SELECT id FROM em_folders);
DELETE FROM em_labels WHERE account_id NOT IN (SELECT id FROM em_accounts);
DELETE FROM em_portlet_folders WHERE account_id NOT IN (SELECT id FROM em_accounts);
DROP TABLE IF EXISTS cf_em_accounts;

-- FreeBusy permissions
DELETE FROM fb_acl WHERE user_id NOT IN (SELECT id FROM core_user);

-- Files
DELETE FROM fs_bookmarks WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM fs_bookmarks WHERE folder_id NOT IN (SELECT id FROM fs_folders);

-- Notes < 6.4
DROP TABLE IF EXISTS `no_categories`;
DROP TABLE IF EXISTS `no_notes`;
DROP TABLE IF EXISTS cf_no_categories;

-- old mailing lists module
DROP TABLE IF EXISTS `ml_mailing_lists`, `ml_mailings`, `ml_sendmailing_users`;

-- Old project management module <6.2
DROP TABLE IF EXISTS `pm_budget_categories`, `pm_custom_reports`, `pm_default_fees`, `pm_employees`, `pm_emploment_agreements`;
DROP TABLE IF EXISTS `pm_expense_budgets`, `pm_expense_types`, `pm_expenses`, `pm_fee_categories`, `pm_hours`;
DROP TABLE IF EXISTS `pm_mileage_registrations`, `pm_milestones`, `pm_order_unsummarized_items`, `pm_portlet_statuses`;
DROP TABLE IF EXISTS `pm_projects`, `pm_report_pages`, `pm_report_projects`, `pm_report_templates`, `pm_report_templates_csv`;
DROP TABLE IF EXISTS `pm_report_templates_odf`, `pm_report_templates_pdf`,`pm_resources`, `pm_standard_tasks`, `pm_statuses`;
DROP TABLE IF EXISTS `pm_templates`, `pm_templates_events`, `pm_timers`, `pm_types`;

-- sync (old version)
DROP TABLE IF EXISTS `sy_anchors`, `sy_devices`, `sy_maps`;

-- Sync (current version)
DELETE FROM sync_calendar_user WHERE calendar_id NOT IN (SELECT id FROM cal_calendars);
DELETE FROM sync_calendar_user WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM sync_addressbook_user WHERE userId NOT IN (SELECT id FROM core_user);
DELETE FROM sync_addressbook_user WHERE addressbookId NOT IN (SELECT id FROM addressbook_addressbook);
DELETE FROM sync_settings WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM sync_tasklist_user WHERE userId NOT IN (SELECT id FROM core_user);
DELETE FROM sync_tasklist_user WHERE tasklistId NOT IN (SELECT id FROM tasks_tasklist);

-- Summary
DELETE FROM su_notes WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM su_rss_feeds WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM su_visible_calendars WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM su_visible_calendars WHERE calendar_id NOT IN (SELECT id FROM cal_calendars);
DELETE FROM cal_visible_tasklists WHERE tasklist_id NOT IN (SELECT id FROM tasks_tasklist);
DELETE FROM su_latest_read_announcement_records WHERE user_id NOT IN (SELECT id FROM core_user);
DROP TABLE IF EXISTS su_visible_lists;

-- Projects2
DROP TABLE IF EXISTS `pr2_employees`;
DROP TABLE IF EXISTS `pr2_standard_tasks`;
DROP TABLE IF EXISTS `pr2_employee_activity_rate`;

-- IP Whitelists
DROP TABLE IF EXISTS `wl_ip_addresses`, `wl_enabled_groups`;

-- Tasks < 6.6
DROP TABLE IF EXISTS ta_tasklists, ta_tasks, ta_categories, ta_settings, ta_portlet_tasklists;
DROP TABLE IF EXISTS cf_ta_tasks;
DROP TABLE IF EXISTS dav_tasks;

-- SMime
DELETE FROM smi_certs WHERE user_id NOT IN (SELECT id FROM core_user);

-- ??
DELETE FROM bl_ips WHERE userid NOT IN (SELECT id FROM core_user);

-- old core tables
DELETE FROM go_reminders_users WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM go_settings WHERE user_id > 0 && user_id NOT IN (SELECT id FROM core_user);
DELETE FROM go_state WHERE user_id NOT IN (SELECT id FROM core_user);
DELETE FROM core_user_group WHERE userId NOT IN (SELECT id FROM core_user);
DELETE FROM core_user_group WHERE groupId NOT IN (SELECT id FROM core_group);


DROP TABLE IF EXISTS go_log;

DROP TABLE IF EXISTS cf_blocks, cd_disable_categories,cf_enabled_categories,cf_enabled_blocks;
DROP TABLE IF EXISTS cf_bs_orders, cf_bs_products;
DROP TABLE IF EXISTS cf_pr2_hours, cf_pr2_projects;
DROP TABLE IF EXISTS `go_links_ab_addresslists`, `go_links_ab_companies`, `go_links_ab_contacts`, `go_links_bs_orders`;
DROP TABLE IF EXISTS `go_links_cal_events`, `go_links_em_links`, `go_links_fs_files`, `go_links_fs_folders`;
DROP TABLE IF EXISTS `go_links_pr2_projects`, `go_links_ta_tasks`, `go_links_ti_tickets`, `go_link_descriptions`, `go_link_folders`;
DROP TABLE IF EXISTS `go_links_go_users`, `go_links_no_notes`, `go_links_pm_projects`, `go_link_pm_report_templates`, `go_link_pr2_report_templates`;
DROP TABLE IF EXISTS cf_select_tree_options, cf_tree_select_options;

SET SESSION foreign_key_checks=1;
