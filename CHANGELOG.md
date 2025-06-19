- Projects3: Improved finance integration
- Core: Upgraded SourceGuardian encoder to fully support PHP 8.4

16-06-2025: 25.0.30
- Calendar: Fixed display issue in e-mail invite
- Core: Auto dismiss alerts for entities no longer known
- Timeregistration2: Tasks only querying in subscribed lists
- Core: PHP 8.4 compatibility
- Studio: patch generated models with type hinted attributes
- Studio: generate new models with type hinted attributes
- Files: Use load() instead of reload() otherwise it might retrash the files as it will send the last load param
- Core: All model properties have types so keys will always be a string according to JMAP spec
- Tasks/Core: Fixed invalid change event in tasklist combo where the tasklist name would be the value in the change event
- Tasks: percent complete render error when hidden
- Tasks/Support: Fixed comment load on new ticket / task
- Addressbook: City, state and country in grid
- Files: only display 'Share' button if any menu items available
- Core: Clearer label for 'allowRegistration' system setting

05-06-2025: 25.0.29
- Tasks: Sync subscribed tasklists and remove sync settings
- Comments: show principal name
- Leavedays: refactor notification code
- Core: module panels not visible to admins in user settings
- Core: Fixed custom fields loading problem in system settings
- Savemailas: Save link description when creating links
- Maildomains: Return sums as int to hopefully fix issue quota / postfixverwaltung #1272
- ActiveSync: Fix for all day events spanning an extra day

02-06-2025: 25.0.28
- Calendar: Custom fields not saved
- Core: TreeSelect was broken in GOUI

02-06-2025: 25.0.27
- Leavedays: fix status loop when disapproving leave day requests
- Leavedays: show number of open requests
- Freebusy: Fix non editable free busy permissions after user creation
- Supportclient: Fixed grouping in support lists
- Core: XSS error fixed in my account -> sync
- Core: Reflected XSS in Look and feel section of the application

30-05-2025: 25.0.26
- Core: Fixed store load error when using custom filters
- Address book: Removed duplicate action date in detail
- Calendar: removed displayfield() for description as it had some unexpected results when the description was overridden for an occurrence
- Calendar: New event without any changes wouldn't save anymore
- Calendar: all events disappeared in week view when deleting an event that has participants or is recurring
- Core: Stretch custom date fields so larger labels don't run out of the field
- Core: Fixed creating double user groups and duplicates will be removed.
- Addressbook: filter hasemailaddresses and hasphonenumbers work with false too
- Finance: Added "nextContractDate" to finance document model
- Core: Added dateAdd() and subtr() function to template parser

27-05-2025: 25.0.25
- Calendar: Show import button if calendar event is not a valid scheduling object
- Calendar: Show open calendar button on all invites
- Projects2: Find project tasks in time registration window

26-05-2025: 25.0.24
- Files: fix permission error when restoring file as end user
- ActiveSync: Z-Push problem fixed: z-push sync for notes and tasks #1289
- Newsletters: When setting max messages per minute to 0 it will send as fast as possible and doesn't default to 120 messages per minute
- Newsletters: When a sending limit is applied the sending time is now taken in to account too.

20-05-2025: 25.0.23
- Projects: Problem with loading project panel
- Z-Push: Created new Z-Push repository

19-05-2025: 25.0.22
- Core: Z-Push upgraded to 2.7.5 (With some patches from us).
- Files: Fixed opening file from link or search result

12-05-2025: 25.0.21
- Core: fix database error in disk space calculation cronjob
- ActiveSync: Fixed error in calendar sync because ownerId was changed to getOwnerId()
- CalDAV: Fixed error in calendar sync because ownerId was changed to getOwnerId()

08-05-2025: 25.0.20
- Core: Fixed: Blind Stored XSS in Phone Number Field Enables Forced Redirect and Unauthorized Actions
- Core: Fixed:  DOM-Based XSS in all Date Input Fields Allow Arbitrary JavaScript Execution
- Core: fixed Stored XSS in Tasks Comment Section
- Files: Fixed: Group-Office vulnerable to path traversal Vulnerability ID: JVN#23673287
- History: Fixed cross site scripting vulnerability JVN#30520482, JVN#87138325 and JVN#72111431
- Email: fix deprecation error
- ActiveSync: Fix broken utf-8 when recreating MIME for Z-push
- Comments: use translations for tasks feedback
- Core: Reset create link button on close so it won't fail when dialogs perform an async task before opening. This happened with documents in finance.
- Finance: Add payment due date to Zugferd invoice
- Addressbook: Filter ICD combo by selected country from address


25-04-2025: 25.0.19
- Maildomains: Wrong quota display and cleaned up code

15-04-2024: 25.0.18
- Files: Disable new webdav locking by default to test it more. can be enabled with $config['webdavEnableLocks'] = true;

14-04-2024: 25.0.17
- Files: If files on disk match the entity path eg. addressbook/Public/contacts/A/Albert Foo/ then it will be connected by the database check.
- Address book: Option to hide index character and icon to show more contacts on screen
- Address book: update action date form detail view
- Core: Use geo: https://en.wikipedia.org/wiki/Geo_URI_scheme for address links so user can choose the maps app
- sync: workaround for Z-push error SyncObject->Check(): object from type SyncMail: parameter 'to' contains an invalid email address 'undisclosed-recipients:'. Address is removed.
- tasks: correct invalid state where due is before start date
- Core: Updated German language thanks to the University of Greifswald

08-04-2025: 25.0.16
- Demo data: Longer timeout so it won't time out after 30s.
- Tasks: Default color for task lists
- Files: fs_trash folder was missing for some users

08-04-2025: 25.0.15
- Calendar: Upgrade errors if files not installed part 2

08-04-2025: 25.0.14
- Maildomains: Cannot create Alias #1255
- Calendar: Upgrade errors if files not installed
- ActiveSync: Was still not fixed.

08-04-2025: 25.0.13
- Files: If you had files module installed but disabled the upgrade failed
- ActiveSync: Wrong lib included which made sync fail

07-04-2025: 25.0.12
- Maildomains: Fixed bug in dialogs not splitting user and domain
- Maildomains: Fixed bug in checking max aliases and max mailboxes
- Webdav: Creating new files was broken
- Studio: fix creator and modifier names in grid
- Core: refactor old calendar event models out of system tools

01-04-2025: 25.0.11
- Core: fix order of database migrations
- Studio: automatically patch modules to 25.0 code base
- Core: fix multiple upgrade errors
- Core: fix theme colors

25-03-2025: 25.0.10
- Calendar: Fixed install on MySQL 8
- Calendar: Auto install on fresh install
- Core: Catch database exception in legacy session handler to catch the case where a session exists but the
  database is not installed yet. Reported in issue #1242.
- Fixed exception in database check. Reported in issue #1242.
- Supportclient: add 'select all lists' checkbox to sidebar
- Finance: When duplicating quotes or invoices etc. default to the same book
- Finance: Optimized database index
- Core: Optimized database indexes for core_change, search.
- ActiveSync: Optimized notification queries
- Core: Updated German translation thanks to University of Greifswald

18-03-2025: 25.0.9
- Core: Build error
- Core: maximum call stack exceeded error

18-03-2025: 25.0.8
- E_mail: Save mail as project
- E-mail: Save mail as appointment
- Calendar: Create and browse links from event dialog
- Calendar: Fixed unsubscribe from calendar
- Core: Color picker render issue

10-03-2025: 25.0.7
- Calendar: fix cancellation event
- Time zone fix
- Phpstan
- Create link button
- several updated dependencies

06-03-2025: 25.0.6
- Calendar time tracking build problem fixed

03-03-2025: 25.0.5
- Calendar time tracking module
- Tasks / Support: Easier task dialog

18-02-2025: 25.0.4
- Many bug fixes and tweaks
 
27-01-2025: 25.0.3
- Calendar: CalDAV Client module added so you can add external caldav calendars
- Project3: Module was missing

10-01-2025: 25.0.2
- Calendar: Calendar didn't load in Docker version
