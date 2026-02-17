- Core: Check if export is not empty.
- LDAPAuthenticator enable and optionally enforce TOTP workflow (cherry-pick from 6.8)
- Support: Implemented quote collapsing in support
- Assistant: When file is locked warn about it and download it read only.
- Calendar: Added start page widget

09-02-2026: 26.0.6
- jitsimeet: Add sub to jwt
- davclient: Add option to disable SSL validation
- MS Teams: meeting gets decription with meeting id, passcode and meeting options link. Teams event is also set with subject and meeting time
- Finance: Migrate from billing didn't copy frontpage text to greeting and sometimes address was missing
- Finance: New book option to make items read only after they have been sent
- Core: Fixed inserting links with Safari in email composer

02-02-2026: 26.0.5
- Core: fixed Security vulnerability Remote Code Execution (RCE)
- Core: Only allow http(s) protocols in httpclient for security reasons. Fixes SSRF and File Read in WOPI service discovery.
- Core: Remove debug and info from client with ctrl+f7 for security reasons
- Core: removed old code and restricted User access to admins or users with "mayChangeUser" permissions
- Files: Remove url in link in FilePanel.js to avoid confusion with other links
- Finance: FinanceStatus::$color can be null
- Email: Fixed  RCE - Command Injection via TNEF Attachment Handler

 29-01-2026: 26.0.4
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
- PDF Editor: New module for annotating PDF files
- Email favorites: New module to bookmark email folders
- Core: Only lock set operations for the same entity as it blocked serverclient requests to the same group-office instance.
- Email: Fixed dropzone flickering when dropping files

20-01-2026: 26.0.3
- wopi: Was still not available without a license

19-01-2026: 26.0.2
Initial release: https://www.group-office.com/blog/2026/01/groupoffice-26.0-released
