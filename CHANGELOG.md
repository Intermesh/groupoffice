- Core: fix order of database migrations
- Studio: automatically patch modules to 25.0 code base

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
