- calendar: Fixed create link field in event dialog
- Core: Cron jobs no output buffering and debug memory
- Core: New config options for mail debugging (mailerDebugHost and mailerDebugPort)
- Core: Exit with error immediately when config file can't be loaded. It hung endlessly on require.
- Core: New config options for mail debugging (mailerDebugHost and mailerDebugPort)
- Calendar: Fixed calendar load problem if a relation with id="0" was fetched. In this case modifiedBy was '0' after]
  migrating an ancient 4.0 version.
- Wopi: Removed references to business package. Fixes 26.0.3 module store complains about missing module files #1409
- Business: cascade managers when user is deleted
- Calendar: Add participant on blur
- Core: updated Italian translation. Thanks Alexander!
- Kanban: was missing

20-01-2026: 26.0.3
- wopi: Was still not available without a license

19-01-2026: 26.0.2
Initial release: https://www.group-office.com/blog/2026/01/groupoffice-26.0-released