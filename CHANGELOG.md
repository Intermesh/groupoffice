Address book: Added "Last contact at" column that updates when a comment is made or an e-mail is linked

02-12-2024: 6.8.88
- Calendar: When events are private but writable. Posting the event with CalDAV will not change the title or description
- Files: Detail panel will change to folder when a different folder is selected in the tree.
- Core: Fixed checkbox custom field filter to match null values when client sends 'false'
- ldapauth: Create postfixadmin account if domain matches serverclient domain
- Finance: Fixed group by employee when billing from projects
- History: History was depending on address book module by mistake
- Files: bugfix file notification
- Billing: Fixed catalog export number formatting
- Billing: Fixed order xls export
- ldapauth: handle error: Partial search results returned: Sizelimit exceeded
- Core: fixed displacement of context menu

25-11-2024: 6.8.87
- Core: Added new option to toggle use of ctrl + enter to send e-mail / comments
- Billing: Import and export custom fields and match by ID
- Newsletters: Fixed newsletter template attachments not saving.

18-11-2024: 6.8.86
- Files: cron job file notification
- Core: updated german translation
- Various: route to main grid when details resets / is deleted for mobile view
- Privacy settings: several bug fixes in addressbook overrides
- projects2: automatically convert old project_templates_events to tasks
- Collabora: Added lang variable for collabora
- Finance: Migration checks if all custom fields are available

11-11-2024: 6.8.85
- Billing: Fixed undefined index 'sort'

11-11-2024: 6.8.84
- Billing: Sort on price and supplier
- Core: Bugfix when editing custom fieldset. Also enable editing of fieldset with parent fieldset
- Core: Default request timeout to 30s instead 3 minutes
- Finance: Fixed error when dragging existing items to another group

04-11-2024: 6.8.83
- Email: Larger move old mail dialog. Also scrollable and resizable
- Core: Fixed issue in combobox resetting with promise race condition
- Tasks: Fixed horizontal scrollbar in some cases

04-11-2024: 6.8.82
- Core: performance hotfix

04-11-2024: 6.8.81
- Core: Admin can change passwords without using own password again
- Email: Set references header when forwarding mail
- Newsletters: fix php compatibility error when sending newsletters
- Supportclient: make creation dialog wider to fit format toolbar
- Calendar: fix more HTML encoding issues in Qtip

28-10-2024: 6.8.80
- Core: bugfix editing individual field set in entity
- Core: bugfix user export
- Core: Several bug fixes user import

24-10-2024: 6.8.79
- E-mail: Server side sort was disabled by accident since May 2024
- Caldav: Fixed CalDAV sync with DAVx5 error #1192
- ActiveSync: Fixed Sync in Outlook (Z-push/Activesync) problem #1193
- Core: Fixed problem in demo data creation

21-10-2024: 6.8.78
- E-mail: Set internal date on IMAP APPEND command's. This way the internal date when moving or copying messages to another account is preserved
- Core: added endpoint api/up.php that checks the database connection and filesystem disks for uptime monitoring.
- Core / e-mail: Fixed problem where typing ü would insert a , in the mail composer on german QWERTZ layout
- E-mail: Fix 'delete all attachments' bug.

14-10-2024: 6.8.77
- Timeregistration: Fixed time dialog tracking time starting at midnight
- Newsletters: fix missing property error when sending newsletter with attachment
- Smime: Handle two valid certficates for the same sender
- Comments / support: display download icon in order to directly download a comment attachment.

08-10-2024: 6.8.76
- ActiveSync: Fixed fatal error because of breaking change in z-push
- Calendar: fix invitations in readonly calendars

07-10-2024: 6.8.75
- Core: Make sure groupoffice core module is always sorted first
- Tasks: sort task combo by task list first, title second
- Core: tweak keyword splitting to support double surnames separated by a dash (e.g. Catherine Zeta-Jones)
- Finance: Don't send statements to customers with a negative amount to be paid

30-09-2024: 6.8.74
- Tasks: select default tasklist for user more intelligently
- Finance: Change document owner
- Contracts: Change document owner
- Workflow: Fixed using UTF8 in workflow history
- Workflow: Make workflow grid work for admins too
- Calendar: Fix several HTML encoding / decoding issues in Qtip
- E-mail: Update oauth2 token after refresh. Fixes authentication failed error.

23-09-2024: 6.8.73
- Core: fix error when importing CSV
- Finance: Check if project has a customer set before creating an invoice
- Email: Fix for email not showing ics file when method is not set.

16-09-2024: 6.8.72
- Calendar: fix HTML code in Qtip
- Core: Fixed PHP 8.3 compatibility issue: PHP Fatal error:  During inheritance of IteratorAggregate: Uncaught ErrorException: Return type of GO\\Base\\Db\\Statement::getIterator() should either be compatible with IteratorAggregate::getIterator(): Traversable, or the #[\\ReturnTypeWillChange] attribute should be used to temporarily suppress the notice in /var/customers/webs/office/office.domain.tld/go/base/db/Statement.php:578
- Caldav: Fixed users being able to write in read only calendars
- E-mail: Date was lost if message was an smime signed attachment with headers without the date
- Core: Provide date, time and number formatting for csv imports
- Email: fix error when moving or copying to a folder with a % in its name
- Business: add row actions to business grid, fix deleting finance books when deleting business record.
- Business: add row actions to activities grid.
- Finance: Add counter when multiple books of the same type are created upon installation
- Core: Updated German language
- Serverclient: urlencode token

09-09-2024: 6.8.71
- Files: Fix PHPMailer compatibility issue
- Core / Studio: fix error when initially loading combobox value
- Calendar: Fixed invalid error message when sending invites
- E-mail: Errors were not shown correctly
- Leavedays: Fixed rounding issue which lead to slightly off numbers in the decimals

05-09-2024: 6.8.70
- Sieve: text label Out of Office more clear
- Core: Mask hashes in history log
- Core: Fixed various invalid mail send() errors

04-09-2024: 6.8.69
- ActiveSync: Sending mail reported an error during sent even though it was actually sent

03-09-2024: 6.8.68
- Billing: Fixed Uncaught exception: Access level to GO\Billing\Pdf::$pageWidth must be protected (as in class GO\Base\Util\Pdf) or weaker at 2024-09-03T08:58:25+02:00

02-09-2024: 6.8.67
- Finance: Fixed additional PDF templates not working
- Tasks: Message field was mandatory by mistake
- Calendar: fixed sprintf() problem with repeating every 2 years
- Core: Use standard remove format button instead of word paste in html editor
- Email: Send charset for search as it didn't work without it when using utf8 with a large Polish provider
- Calendar / core: fix page width when printing current view
- Finance: Add articles from catalog dialog
- Catalog: Organize articles in categories
- Email: check 'automatically save in Sent' checkbox by default as per install script
- Tasks: In 'Continue task' dialog, make sure that all buttons are shown.
- Finance: Add project billing for finance
- Email: show next message in selected mailbox after moving current message

26-08-2024: 6.8.66
- Core: Fixed header Y coord not working on PDF templates
- Core: Add several filters to PDF template parser
- Finance: Fixed search in books
- Finance: Refresh statuses after book update
- Finance: Fixed Implicit conversion from float-string \"17.5000\" to int loses precision
- Finance: minor bugfix optional article description
- Time registration: fix display issue task combo in dialog
- Email: Remove starttls. tls does the job.

19-08-2024: 6.8.65
- Core: Attachments Custom field now shows files grid when configured as pictures
- Tasks / support: accent class for grouping separator
- Business: allow users with mayManageEmployees to create or destroy agreements
- Core: more helpful / less generic "Add link" button icon

13-08-2024: 6.8.64
- Core: PHP 8.2 compatibility fix
- Core: Fixed combobox not loading value in Account dialog causing DB error

12-08-2024: 6.8.63
- Core: Don't require security token check in legacy API when not using cookies for authentication
- Core: HTML field supports required
- Tasks: Fixed grouping in combo and showing numeric value. Related to support ticket #32767
- Email: Raised IMAP and SMTP username database length from 50 to 190
- Caldav: Fixed sending IMIP email invitations to participants with a comma in them
- Tasks: Added some indexes to speed up search query
- Webdav: Removed tickets directory from webdav as it will cause permission issues
- Email: Autolink checks didn't show when viewing for the first time
- Caldav: Include sabre/dav iCalendar Export Plugin #1169
- Studio: generate title() method properly in backend code. #1166

29-07-2024: 6.8.62
- ActiveSync: Restored correct ActiveSync Z-Push version

19-07-2024: 6.8.61
- Email: Fix attachment problem with ampersands in file name.
- Email: Display files with ampersands in file name correctly, fix download link.

15-07-2024: 6.8.60
- Business: allow users with mayManageEmployees to update their agreements
- Core: make conditionally required, required and conditionally hidden mutually exclusive

09-07-2024: 6.8.59
- caldav/carddav/webdav: Don't log not found as error
- Workflow: fix error when sending a file without a body.
- Core: fixed copy pasting images from a Microsoft Word Document in Windows
- Core: Updated DE translations. Danke, Daniel!
- Core: bugfix base XLS class: prevent exception when adding numeric array as record

05-07-2024: 6.8.58
- Support: auto-expire tickets

01-07-2024: 6.8.57
- Custom fields: Make sure fieldset is not collapsed. This may happen if it was a fieldset before and collapsed by the user.
- Core: fix issue where open source version requires SourceGuardian
- Finance: Create ZUGFeRD / Factur-X - Version 2.2.0 EN_16931 compliant PDF e-invoices
- Finance: Create UBL invoices for Peppol
- Addressbook: Fixed contact sorting on last name
- E-mail: Fixed option "Sort on last contact time"
- Tasks / support: group task list combo by task list grouping.
- Bookmarks: allow users with manage permissions to save bookmark categories

24-06-2024: 6.8.56
- E-mail: Fixed autocomplete bug where typed text would stick too
- Core: Import responded with invalid JSON due to echo statement.
- Core: improve verbosity for incorrectly parsed date in email message
- Supportclient: support larger number of support lists
- Carddav: Import title
- Carddav: Avoid some unnecessary saves
- Address book: Ignore vcardBlobId in history log
- Webdav: Fixed write access in shared folder

17-06-2024: 6.8.55
- Core: Fix part 2. Proxy headers (X-FORWARDED-FOR) are ignored for "Authorized clients" #1150
- Calendar: Fixed bug in invite mails
- Core: Added Polish holidays
- Email: Show and sort on internal date by default. Fixed: Go Is using FROM header date, showing a message in the future
       #1055
- Smime: Fixed error when both signing and encrypting
- Core: Fixed: Everyone group and all contacts translatable #636
- Core: save of install language to system settings
- Fixed: Auto-complete and semi-colons #951
- Core: Set html editor background color so it's always white in dark mode
- Core: Replace old favicon.ico with current one
- Email/Core: fixed: SanitizeHTML CSS comment fixup #1021
- Smime: Fixed error when both signing and encrypting
- Email: Show and sort on internal date by default. Fixed: Go Is using FROM header date, showing a message in the future #1055
- Fixed: Changing user passwords in system settings changes imap/smtp password in users with the same name before @ #1103
- Calendar: Fixed bug in invite mails Merijn Schering
- Tasks: Export .ics via Tasks -> Export -> vCalendar: BEGIN:VCALENDAR/END:VCALENDAR around every BEGIN:VTODO/END:VTODO #950
- Notes: Added notebook name to export and import
- Fixed: Changes on failure #1044

11-06-2024: 6.8.54
- Core: Sort modules in GUI
- Core: Core: Fixed inefficiency in SSE causing a huge amount of calls to the ACPU cache.
- Core: Fixed: Proxy headers (X-FORWARDED-FOR) are ignored for "Authorized clients" #1150
- Core: Default group permissions were no longer editable from system settings.
- Tasks / Support: Added "List" to export and import
- Finance: Don't send docs without number
- E-mail: Show edit button on drafts
- Contacts: Remove contacts from group with multi select

04-06-2024: 6.8.53
- ActiveSync: Z-push logging was always set to debug
- Wopi: Added "allow-downloads" permission to iframe to fix downloading copies

03-06-2024: 6.8.52
- Newsletters: Make 'Attachements' menu in composer more visible
- Newsletters: Fix permission error when adding address list

31-05-2024: 6.8.51
- Core: Permissions were not editable for admins
- Wopi: Fixed missing acl's that were cleaned up by garbage collection because foreign key was missing

27-05-2024: 6.8.50
- Sieve: Added "Mailing List" option
- Sieve: Fixed bug in custom filter where exists showed as "doesn't exist"
- Core: update Japanese holidays file. Arigato 2g@rdis.net .
- Multi instance: Pause transactions during instance deletion
- Billing: Fixed MT940 import
- Support: fix permission issue when creating support lists
- Core: Fixed support module uninstall
- Core: Fixed db check
- Studio: fix drag & drop ACL item between collections
- Core: Set custom select field to "undefined"
- Email / Core: Use "Enter" in recipient list combo
- Core: Editable language combo
- Core: fix sending mail with $config['debugEmail'] configuration option

23-05-2024: 6.8.49
- Core: Create entity filters by users without admin privileges possible
- Files: Fixed permissions error for admins
- Zpushadmin: fixed error loading files
- Core: Disable events during upgrade and install to prevent problems with modules that are not available.
- AddressBook: suppress display of default country in address if none is filled in
- Billing: Fixed MT940 import

21-05-2024: 6.8.48
- Email: Fixed: Feature request: icons up top in inbox (search, accounts, etc.) #947. Set minWidth for email panel
- Notes / Comments: Use StringUtil to remove style from notes and comments as old way could remove text unexpectedly
- Oauth: RefreshToken for Google was not obtained when using openid
- Oauth: Possible now to use a different smtp user for IMAP accounts.
- Oauth: disable saving to sent folder for Azure as they save sent items automatically.
- E-mail: Client side sorting in chunks to avoid error when sorting a large search result for Microsoft Exchange server that does not support server side sort.
- E-mail: New account option to disable saving of sent mail for Microsoft Exchange Servers because they do that on the server already.˚

13-05-2024: 6.8.47
- Email: Fixed CSS bleeding issue
- ldap: LDAP - Synchronize users checkbox #1144
- Core: Do not check module availability on listeners rebuild so it always rebuilds even if license fails

06-05-2024: 6.8.46
- Core: Set maximum password length to 255 to prevent brute force attacks
- Tasks/Support: Set to needs action if responsible user changes
- Email: Fixed unnamed attachment problem
- Core: fix validation for user password reset through reset token

29-04-2024: 6.8.45
- Studio: better handling of non-default package name
- Support: Fix migration from old tickets module
- Core: Double module loading bug causing problems like in e-mail search in current folder
- Core: Added Slovenian / Slovenščina translation
- Postfixadmin: Added "fts" option to enable full text search
- Email: Full text search is enabled if the mailserver returns XFTS as capability (custom capability used in Group-office mailserver) or
  when this config option is set:

'community' => [
	'email' => [
		'forceFTS' => [
			'<HOSTNAME>' => true
			]
		]
	]
];
- Email: Fix 'toggle unread' function in messeges list
- Billing: fix errors when generating emails from empty translated order statuses

23-04-2024: 6.8.44
- Studio: fix dependency and minor deprecation errors
- DAV core: DAV principals didn't apply user permissions
- Core: Don't re-open tabs on badge notification change
- Core: close tab with menu and not directly with right click
- Email: Allow full message search by default
- Email: Select current or all folders from menu
- Email: New config option to define a hidden folder that shows all:

'community' => [
	'email' => [
		'allFolder' => [
			'<HOSTNAME>' => 'virtual/All'
			]
		]
	]
];

This folder will be available in the group-office mailserver by default.

- Core: Hint admins to enter their admin password when changing a user password
- Email: minor bugfix in saved message

16-04-2024: 6.8.43
- E-mail: Fixed increasing padding when saving drafts multiple times
- Activesync: Use new community Z-push repo with version 2.7.1 and our pull request: https://github.com/Z-Hub/Z-Push/pull/57
- Apikeys: Select user for API key to limit permissions
- ActiveSync: Some email messages that were out of the date range specified could be sent
- Tasks/Support: Index e-mail of creator for tasks and tickets
- Support: Allow changing of createdBy / customer for tickets
- Support: Fixed Client Help portal got error you don't have access to business/support
- Studio: New feature to generate a module with two models. Collection and items.
- Studio: new default canCreate() function for ACL models
- OTP: Fix validation error when admin disables OTP for non-admin users
- Core: Display numeric values of Custom Fields with correct number of decimals in grid

02-04-2024: 6.8.42
- Support: Deleting lists was impossible
- Newsletters: Update list counter when deleting contacts.
- Newsletters: Reset counter on database check
- Haveibeenpwned: Only activate for local authentication. Not for IMAP and LDAP.
- Haveibeenpwned: Continue auth if API is not reachable
- E-mail: Remove "undisclosed-recipients:" from to when opening draft
- supportclient: Remove attachments
- Support: ticket counter updates fixed
- Core: Force password change didn't work anymore

29-03-2024: 6.8.41
- Core: User creation was broken. Renamed function so it's not an API property.

28-03-2024: 6.8.40
- Core: Added auth and lost password logs for fail2ban
- Core: Made change password play nice with password managers
- Core: Fixed upgrade SQL for MySQL 8

25-03-2024: 6.8.39
- Support / Core: GOUI was incompatible with 6.8 due to changes for 6.9+.
- Core: prevent timing attack on password recovery
- Core: prevent timing attack on login
- Core: Require admin rights for sending a system test message
- Core: Prevent automatic change of the "Expires" header. This caused a security issue where the expires header
  would be different on lost password requests when a valid email address was used.
- Core: Fixed bug in XSS detection
- Core: Disallow modification of modifiedAt, createdAt, modifiedBy and createdBy via API.
- Core: Create permissions were not checked on import
- OTP Authenticator: Hide secret. Only show it when just created
- Core: Create permissions for IP restrictions and SMTP accounts for admins only
- Core: Module management permissions enforced on server
- Core: destroy user sessions when admin changes password
- Core: Show less details in error messages
- Core: Admin password is required to change other users' passwords
- History: Remove sensitive hashes from log
- Core: Use status 202 on lost password so we can setup fail2ban rules for it
- Core: Implemented force user password change

11-03-2024: 6.8.38
- Core: Report if sourceguardian is not installed when setting license key
- Billing: handle double click submit in DuplicateDialog.js
- Caldav: Fixed creating exceptions in recurring series with participants

05-03-2024: 6.8.37
- Email: Resize folder subscription dialog to current theme
- Core: Fix bug in module selection in user profile
- Files: minor bugfix copy / paste with keypresses
- Zpushadmin: Available for admins only by default
- Carddav: Share carddav with internal on install
- Support: Export broken
- Core: update old dependency for spreadsheet export
- Support: Customer got notification of private comment
- Tasks: fix date render bug in tasks grid
- Core: Sabre dav upgrade to 4.5.1

26-02-2024: 6.8.36
- Calendar: Fixed changing color for calendars and categories issue #1112
- Calendar: Fixed: 6.8.34 bad translation german #1119
- smime: fixed signing with attachments issue #1120
- Caldav: fixed broken imip issue #1117
- Core: If ACL was empty it loaded default values for existing items in dialogs.
- Core: Fixed white text on white background in some emails using color: windowtext;
- Support: Fixed missing message when creating new ticket from GO
- Core: add $config['mailerDebugLevel'] to enable mail debugging
- ActiveSync: Support Global Address List
- Calendar: Fixed checkbox colors
- Newsletters: Add contact variable in users lists so templates are compatible

19-02-2023: 6.8.35
- Core: Include password for import
- Core: more button not visible on user management page
- Automation: fix foreign key to allow deletion of automated jobs
- Core: Search modules by package name
- Tasks/Core: Bug in copy() function where dates and other objects had reference to the source. This caused the task dates to change on recurrence.
- E-mail: Don't use assistant anymore for opening attachments
- Tickets: Fixed scrolling in new ticket message
- Email: Larger add filter window
- Core: fix $config['debug_usernames'] functionality
- Email: Fixed html toolbar not auto sized when switching from plain text to html in the composer
- Email / Core : underline html editor toolbar
- Newsletters: test message didn't work with e-mail account
- Email: Sometimes tree could collapse without reason
- Email: Fixed sorting and collapsing of e-mail template groups
- Caldav: Fixed: undefined method addReplyTo (Issue #1117)
- smime: Checkbox in email composer was sometimes not changable
- smime: Sent item wasn't signed.
- Calendar: User may only edit calendars when they have manage permissions
- Automation: Added BCC for email actions
- Multi instance: Added some filters to support follow up e-mails
- Multi instance: Fixed installing welcome message

12-02-2023: 6.8.34
- Finance: Don't find invoices that were already paid when importing payments
- SMIME: Extra certificates were not incluced. Potential fix for issue #1113
- Addressbook: bugfix import contacts

05-02-2023: 6.8.33
- Core: GOUI version updated to solve Help module not appearing
- Don't recreate tasklist, calendar, address books etc. for disabled/archived users
- Core: Colorfield didn't submit manually entered hex values anymore
- Finance: Business module is available if you have the billing license only now
- Billing: Fixed Customer report export
- Holidays: Fixed difference in number in list and year info details
- Holidays: Fixed missing years in selection
- Holidays: Fixed bug where holiday credit was off when no end date was set
- Core: $config['checkForUpdates'] added to disable update check
- Core / Finance: Fixed template condition on numbers starting with a 0.
- Finance: Detect invoice number from payment import in csv and excel as well.
- Finance: Also use amount paid to match documents when customer is found based on bank number
- Files: Normalize UTF8 Form C folder name when uploading folders

29-01-2024: 6.8.32
- Contracts: New option to bill in arrears
- Tasks: Add button in linked tasks
- Core: Updated German translation
- Core: Change delimiter for multiple values into | so it's less likely to be part of real names. Some compamy names have a , in them
- Billing: Vat reverse check incorrectly set on company when country was home country
- Finance: Unit cost field in contract too
- Tasks: Add task was broken when comments module was not installed
- Support: Help module for customers didn't load
- Finance: Use currency in debtor statements
- Files: Fixed error when pasting text into search field
- Core: Use Escape button to exit search
- Email: Fixed render issue

23-01-2024: 6.8.31
- Calendar: Fixed JVN#63567545: Group Office contains a stored cross-site scripting vulnerability
- Finance: Add page breaks
- Finance: Always set expiresat and move when date is modified
- Finance: Drag rows to another group
- Finance: Fixed invalid status filter when switching between books with custom statuses.
- Core: Respect sort_order prop of module

19-01-2024: 6.8.30
- Billing: Customer report is sortable
- Core: Exclude User and Search from SSE Push because it caused performance problems
- Core: Comment composer did not reset.
- Core: System settings dialog validation for notifications panel

15-01-2024: 6.8.29
- Finance: Update VAT rates when changing book in document dialog
- Finance: Fixed docs not loading when there was no quote book
- Core: Support double primary key in logging delete changes
- Finance: Confirm overwrite of finance doc line with article data.
- Files: Fixed Stored XSS Vulnerability via Malicious File Names in Upload Feature
- Calendar/summary/Core: Add user was broken when start module was not installed and calendar was installed
- Core: Confirm close window with changes

08-01-2024: 6.8.28
- Core: Added "strike through" button in html editor
- Finance: Customer filter can also select organizations now
- Support: Migrate couldn't be started.
- Business: E-mail account could not be selected.
- Core: Group membership not shown at user.
- Finance: Profiles of other businesses where shown in company dialog
- Finance: Handle if business has no VAT rates set.
- Filesearch: Fixed custom field filtering
- Core: Combobox can open a dialog when adding a new item
- Addressbook: When creating new contacts / organizations from a combobox a dialog will open
- Finance: Create new articles from finance document dialog
- Finance: check if there are unverified payments before sending out reminders
- Core: New tab could be closed unexpectedly when viewing pdf attachments
- Core: Fixed changing language after switching to user with another language

05-01-2024: 6.8.27
- Email: Added buttons to move or delete the complete search result.
- Email: Sieve rule now uses configured Spam / Junk folder
- Core: Set password via CLI: /cli.php core/System/setPassword --username=admin
- Email: fix sorting bug
- Calendar: fix several deprecation errors, cleaned up old stuff
- Email: increase size of 'move old mail' dialog, add more descriptive icons to mailbox context menu
- Core: Don't take over label color of background using javascript so we can use pure css for form fields.
- Core: Remove ellipsis on status badge in grid
- Finance: Don't set expiresAt until sentAt is set
- Finance: Added dutch translations
- Finance: Sort articles and wider list to pick from
- Core: Fixed saving reordering array relations when only the sort order was modified


22-12-2023: 6.8.26
- Email: Check if the IMAP server supports "MOVE" before using UID MOVE. Otherwise fall back on COPY + DELETE.
- Addressbook: Fixed age in birthday portlet when it's in january
- Addressbook: Added age column to main grid
- Email: Fix PHP deprecation error

18-12-2023: 6.8.25
- OAuth2Client: fix wrong path
- Email: Fixed problem with quotes in folders
- Tickets: External URL didn't work
- Newsletters: no paging in account combo
- Newsletters: Sort accounts like in the email tree
- Core: Fixed deprecation error in PHPMailer wrapper

12-12-2023: 6.8.24
- Core: Property->equals must accept any argument
- Finance: Create invoice number when changing status to sent

11-12-2023: 6.8.23
- Billing: Fixed Undefined array key 0" when sending billing mail
- Core: bugfix in file browser menu item
- Projects2: remove vestigial permissions panel
- Core/newsletters: Fixed error in authentication from newsletter accounts
- Newsletters: Added emailAllowed flag in contact dialog to disable all newsletters for a contact.

04-12-2023: 6.8.22
- Core: Added $config['lockWithFlock'] to force locking with flock version as we have a server that sometimes fails with sem_get()
- Core: Combo box could send typed search text instead of empty id.
- openid: register users and show authenticator icon in system settings
- core: Ability to set password for users that have no authentication option
- debian package: Added example to disable /install from the web
- Libreoffice: fix deprecation error in PHP8 / LibXML version
- Finance: Fixed The migration failed: Typed property go\modules\business\contracts\model\Contract::$contactId must not be accessed before initialization
- Core: Capture Cmd/ctrl + P and print the detail panel
- Addressbook: fix 'street' filter
- Billing: PHP compatibility PDF class
- Support: Fixed merge of support tickets
- Email: Fixed printing mail with corrupted images in Firefox
- Newsletters: Legacy email accounts load without an SMTP account
- Newsletters: Fixed permissions of address list not respected in sent items grid

27-11-2023: 6.8.21
- Core/Email: Fixed big fonts on some receiving e-mail clients (webclients for sure).
- Core/Email: Sent items didn't have BCC address header since v6.8
- Email: Fix for 'actionMoveToSpam' because of hardcoded 'Spam' folder
  Now you can define a global value: 'spam_folder' => 'INBOX.Junk' or
   use its defined value in account settings (account->spam)
- Core: moved cache back to data folder. Otherwise apache can't clear the cache that CLI uses.
- Address book: Fixed rotated thumbnail in address book
- Address book: fix deprecation error with VCards
- Finance: Fixed migration problem: Cannot set non-existing property 'showTotals' in 'go\modules\business\finance\model\FinanceDocumentItemGroup'
- Email: Abort send if one of the recipients fails. Before it would send to all of the others.
- Core: ID column in system settings > groups
- Newsletters: Accounts from the e-mail module can be selected too.
- ZpushAdmin: Make sure table zpa_devices exists

20-11-2023: 6.8.20
- Calendar: Don't try to match email if it's a reply
- Core: added openid service discovery alias. See https://github.com/Intermesh/groupoffice/discussions/1063#discussioncomment-7582806
- Finance: Sort on number too when sorting on date
- Finance: Show total and subtotal in debtor view
- Core: Disconnect mysql when calculating disk usage to avoid Mysql General error: 2006 MySQL server has gone away.
- Calendar: System email account was used on calendar invites even when user had an email account configured
- Tasks: Set progress to needs action when changing assigned to
- Finance: Add description from catalog too
- Files: fix searching in shared folders
- Email: Spam / Junk folder is now configurable.

14-11-2023: 6.8.19
- Core: fix error when autosaving relation to new entity
- Contracts: customer and contact variables were missing in the template for contract e-mails
- Core: Check if exec function is available and use default locale C.UTF8 it's not there.
- Contracts: Use business model of target finance book
- Core: better styling of invalid checkboxes
- Core: Fixed some minor security advisories from GitHub's CodeQL scanner

13-11-2023: 6.8.18
- Core: Fixed Wrong dependency on php-xsl on Debian 12? #1064

09-11-2023: 6.8.17
- Tasks: fix error when trying to delete or update task with alert
- Email: open links in window instead of routing to the module
- Files: Show context menu in files detail view
- Support: Fixed support accounts not being queried
- Core: show error message when module delete fails

06-11-2023: 6.8.16
- Core: fixed issue when creating new property with relations
- Finance: Fixed total calculation in groups
- Tasks: bugfix when no CC available
- Finance: remember selected statuses per book
- Core: fixed login screen for mobiles

03-11-2023: 6.8.15
- Core: Fixed security issue
- Finance: Improved charts
- Task: task counter only counts tasks that are due today
- Support: Customize the outgoing e-mail message with a template
- Support: Added support CC addresses

31-10-2023: 6.8.14
- Files: When using search only search current folder and below

31-10-2023: 6.8.13
- Core: upgrade failed due to fixed database name in update queries

31-10-2023: 6.8.12
- ActiveSync: Fixed extra day bug in for all day events
- Core/ActiveSync: DB connection wasn't close on SSE / Push
- Calendar: restore label 'Start' at start date field
- Calendar: fix deprecation error on calendar report
- Core: bugfix normalizing CC in new mail API
- Core: bugfix delete modules with entities

24-10-2023: 6.8.11
- Finance: Remove amount from document title to improve privacy
- Finance: Fix links when adding from detail view
- Core: Fixed some firefox quirks. Error when opening file and SSE canceled when document.location = was used. See
  old bug: https://bugzilla.mozilla.org/show_bug.cgi?id=564744
- Finance: migrate custom fields from billing
- Catalog: migrate custom fields from billing

18-10-2023: 6.8.10
- Core / newsletters: fix email template upload
- Finance: Copy items in finance was broken
- Support: Search found task and ticket
- Support: Links didn't show up in other entity

11-10-2023: 6.8.9
- Hotfix: business install and update scripts

10-10-2023: 6.8.8
- Core: Raised SSE check interval from 10s to 30s for performance
- Core: Moved disk cache to temp dir so it can be put on faster partitions
- Tasks: Add message field for first comment
- Supportclient: add a mask when submitting a new request
- Core: fix uncommon exception in databaseExists method

29-09-2023: 6.8.7
- Email: bcc and cc bug

26-09-2023: 6.8.6
- Core: stop and start checker and SSE when going off- or online
- Email: Fixed SMTP auth without verifying certificate
- Email: Fixed "Use IMAP credentials"
- Studio: Fix deprecation issues
- Email: Fixed Set read notification github issue #1052
- Support: Tickets have there own entity with custom fields and filters
- Support: When migrating from old tickets module, custom fields are migrated too.
- Addressbook: Added filter for has organization
- Core: Made cor/Notify/mail backwards compatible
- Core: fix casting error when saving cropped blob (e.g. avatar)
- Tasks: Fixed changing sort order when sorting on start
- Core: Multiselect custom field was broken for activerecord

14-09-2023: 6.8.5
- Email: SMTP Authentication was not performed
- Email: Fixed "Remove attachments" feature to work with new mail API0
- 6.7.47 fixes

12-09-2023: 6.8.3
- First public release
- Add privacy options module


