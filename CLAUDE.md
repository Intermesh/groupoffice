# GroupOffice

Write the product name as "GroupOffice" in new text (changelog, messages, comments, docs), not "Group-Office" or "Group Office". Older code and changelog entries still use "Group-Office"; leave those unless asked.

## Reuse existing code

Before writing any helper or utility (IP/CIDR, string, date, URL, file, crypto, etc.), search for existing functionality and reuse or extend it instead of duplicating it. Check at least:

- `www/go/core/` (e.g. `IpAddress`, `StringUtil`, `Url`, `DateTime`, `Crypt`)
- the surrounding module/namespace of the code you're changing
- `git log -S<keyword>` / grep for the concept by name and synonyms

Mention in your reply which existing util you found and reused, or that none exists.

## Prefer the new stack

We are migrating away from legacy code. When writing new code or changing existing code, favor the new frameworks:

- **Frontend: use GOUI where possible.** ExtJS is the old framework and the goal is to replace it with GOUI. Don't build new UI in ExtJS unless there is no GOUI alternative.
- **Backend: favour `go\core` over `GO\Base`.** `GO\Base` is the old code base; new code belongs in `go\core`.
- **API: JMAP.** The goal is to rewrite everything to JMAP. Expose new functionality through JMAP (`go\core\jmap`) rather than legacy controllers/RPC.


## Running tests

Run phpunit as `www-data`, never as root. Use `tools/phpunit <path>` from the docker-groupoffice-development checkout (it runs as `www-data`; `-c <service>` picks another container than `groupoffice`, `-m install|upgrade|existing` the mode, default `install`, and `existing` runs against the existing database with Xdebug) or `docker compose exec -u www-data groupoffice ...`. Running as root creates root-owned files in `/tmp/phpunit`, `/tmp/groupoffice` and `/var/lib/groupoffice` and breaks the tooling. Tests live in `tests/` (`/usr/local/share/src/tests` in the container).

## Environment

The app runs in Docker (the docker-groupoffice-development checkout, service `groupoffice`, with a `db` service for MariaDB). This repo is mounted as the source. Run PHP, phpunit and CLI commands inside the container, not on the host.

## Commits and changelog

- Commit each feature or fix in separate commits.
- Commit messages are short and start with a type or module: `Fixed: ...`, `Core: ...`, `Files: ...`. Reference the issue number when there is one (`#1593`).
- User-visible changes get a line in `CHANGELOG.md`. Unreleased entries go at the top of the file, above the newest version header, as `- Module: description #issue`. Security fixes are written so that a user understands the impact and any config they need to change.
  Never commit CHANGELOG.md. This will be reviewed manually and committed by hand.
- Never push to origin.

## Database changes

Schema changes go in the module's `install/updates.php` as a new entry keyed by timestamp (`$updates['YYYYMMDDHHMM'][] = "SQL"` or a closure). Never edit an existing update entry, because it has already run on installations. Core updates are in `www/go/core/install/updates.php`. Update `install.sql` only if the module has one for fresh installs.

## Translations

Never hardcode UI text. Use `go()->t("Text")` in PHP. Strings live in the module's `language/<lang>.php` (English is the source, `en.php`). Add new strings to `en.php`. Core translations are in `www/go/core/language/`.

- The English text is the key, so it must be correct English: no typos, no Dutch or German. Fixing a key means changing the source and every language file; if the corrected text already exists (often in core), remove the old entries instead of renaming them.
- Argument order differs: `go()->t($text, $package, $module)`, legacy `GO::t($text, $module, $package)`, ExtJS (`.js`) `t($text, $module, $package)` and GOUI (`.ts`) `t($text, $package, $module)`. Both default to the current module. Swapped arguments fall back to core without an error, so the module's translations are silently unused; the language export lists them as "Unknown package/module".
- Lookup falls back from the module to core. A string that two or more modules use, or that core already translates (check the Dutch core file `www/go/core/language/nl.php`), belongs in the core language file instead of each module.
- When adding translations, append entries before the closing `);` or `];` in the file's own style and indentation. Don't rewrite a file with `var_export()`; that reformats every line.
- Keep placeholders exactly as in the source (`{count}`, `{{contact.name}}`, `%s`, HTML tags, `\n`). Reuse the existing terms of the language file and its register: Dutch "u", German "Sie", French "vous", Spanish "usted".
- Short strings are ambiguous ("Read", "Bill", "Archive", "Split", "Quit", "To"). Check where they're used before translating: a permission level, a button (verb), a column header (noun) and a view name each need a different translation.
- To add missing translations, use the dev module: export the missing strings of a language to CSV, fill in the last column, and import it. Export finds `t()` in JS/TS and `go()->t()` / `GO::t()` in PHP and puts strings that core or several modules use in core. Import changes existing entries in place, appends new ones in the file's style, and skips translations whose placeholders differ from the English:
  ```
  docker compose exec -u www-data groupoffice php www/cli.php community/dev/Language/export --language=it --missingOnly=1 --skipPackages=agya,customs,dakram,humblebuildings,udo,intermesh,goneo,duurzaamgebouw > it.csv
  docker compose exec -u www-data groupoffice php www/cli.php community/dev/Language/import --path=it.csv
  ```
  `--skipPackages` leaves out customer modules; for legacy modules that link to another repository, give that folder name. Then check with `php -l` and the git diff.
- Language files are cached. Check new translations with a `go\core\Language` object after `initExport()`, or rebuild the cache.

## Permissions and security

- Every new JMAP entity and controller must enforce ACL. Use `Entity::hasPermissionLevel()`, `applyAclToQuery()` and `internalGetPermissionLevel()`, and don't skip them for convenience.
- Admin-only or privileged behaviour needs a test that proves the guard actually fires. Recent fixes (#1593) were guards that never triggered.
- Don't trust client-supplied properties on save, and never expose secrets to the browser.
- Take the client IP from the existing `IpAddress` util, never from `X-Forwarded-For` directly.
- In controller methods, load entities with `$this->getEntity()` or `findEntity()`, which check read permission, not `Model::findById($params[...])`, which checks nothing. Methods that change data or send mail need a write or manage check on the entity or its container.
- Don't override `checkModulePermissions()` to return true without another guard, don't pass `$params` straight to `setValues()`, and don't return SQL in responses unless the debugger is enabled.
- When you change a public controller method, check its permission guard first. Every public method of a JMAP controller can be called by any user with access to the module.
- Document the permission of every custom controller method with `@apiPermission` (shown in `/api/doc.php`). `tests/go/core/jmap/ControllerPermissionTest.php` fails when a public controller method has no recognisable permission check. When access to the module is really enough, start the text with `Module access:` and give the reason; when the check is elsewhere, start with `Checked in <Class::method()>:`.

## Writing tests

- Tests are PHPUnit (config `tests/phpunit.xml`, suites `core`, `modules`, `api`). They run against a real installed database, not mocks, via `tests/bootstrap-install.php`.
- Mirror the source namespace: `go\core\util\IpAddress` is tested in `tests/go/core/util/IpAddressTest.php`. Module tests go in `tests/go/modules/<package>/<module>/`.
- Classes extend `PHPUnit\Framework\TestCase`, are named `<Class>Test`, and use `testSomething` methods. Use data providers for many inputs (see `IpAddressTest`). A leading underscore (`_testFoo`) disables a test, so don't use it for new ones.
- For ORM and JMAP behaviour, follow `tests/go/core/orm/EntityTest.php` and `tests/go/core/jmap/`. Test entities live in the `community/test` module.
- Suggest creating tests when they will benefit security and stability of the system
- Leave the database as you found it: delete the entities you create.
- Write tests with performance in mind; the whole suite runs often. `setUp()` runs before every test and every data provider row:
  - Create fixtures that tests only read (users that act, groups, permissions, books, module tables of `ModuleTablesTrait`) once per class in `setUpBeforeClass()` and remove them in `tearDownAfterClass()`. Create what a test changes in the test itself or in `setUp()`. See `tests/go/core/controller/UserTest.php` and `tests/go/modules/business/finance/controller/FinanceDocumentTest.php`.
  - PHPUnit doesn't call `tearDownAfterClass()` when `setUpBeforeClass()` fails, so catch the exception there, clean up and throw it again.
  - Check a class with shared fixtures with `--order-by=random`: a test must not depend on what another test changed.
  - Hash passwords and secrets with `go\core\util\Password::hash()`, not `password_hash()`. The tests set a low cost with `passwordHashCost` in `tests/config.php`; the default cost takes 0.2 seconds per hash.
  - Don't call external services. The test bootstrap disables the pwned module for that reason.
  - Find slow tests with `--log-junit <file>`, which records the time of every test.
- Run a single test: `tools/phpunit <path>` (see "Running tests").

## Converting ExtJS 3.4 to GOUI

- The legacy UI is ExtJS 3.4 (API docs: https://docs.sencha.com/extjs/3.4.0/). Use them to understand what an old component does, then rebuild it with GOUI components instead of porting it line by line.
- GOUI docs: https://goui.io (source of the docs site: https://github.com/Intermesh/goui-docs). Source and issues: https://github.com/Intermesh/goui. A local copy with type definitions is in `www/node_modules/@intermesh/goui`. The Group-Office wrappers are in `www/views/goui/groupoffice-core`. Read these for the available components before inventing your own.
- The notes module (`www/go/modules/community/notes`) is the reference module: implement other modules the way it does. For example, its `views/goui/script/Main.ts` extends `MainThreeColumnPanel` and uses the base class buttons (`showWestButton`, `openWestButton`, `hideWestButton`, `closeWestButton`, `hideEastButton`, `closeEastButton`) to open and close the west and east panels, and `menucolumn` for row menus.
- Modules already converted are the best examples: `www/go/modules/community/calendar/views/goui`, `comments`, `apikeys` and `oidc`. Copy their structure (`package.json`, build setup) for a new module UI.
- Legacy ExtJS code lives in `views/extjs3` folders. The backend behind it should move to JMAP at the same time.

## Building the GOUI frontend

Every module with a GOUI UI has its own `package.json` in its `views/goui` folder (e.g. `www/go/modules/community/calendar/views/goui`), and so does core (`www/views/goui`). Build from that folder with `npm run build`, or `npm run watch` while developing.

## Static analysis

- PHPStan (level 3, config `tests/phpstan.neon`, covers `www/api` and `www/go`): from `www/` run `composer go:analyse` (inside the container). New code should not add errors.
- Semgrep (`scripts/semgrep.sh`, security rule sets for PHP, JS/TS and secrets) and Qodana (`qodana.yaml`) run on the codebase as well. Don't introduce findings such as SQL built from user input, and don't commit secrets.
- Don't analyse or edit vendored code, `activesync` or Z-Push (excluded in the configs).

## PHP style

- Indent with tabs, not spaces. Opening brace placement follows the file you're editing (existing code mixes styles, so don't reformat lines you aren't changing).
- Use namespaced `go\...` classes with PSR-4 style paths matching the namespace, and `go()` instead of `GO::`.
- Add parameter and return types to new methods (the minimum supported PHP version is 8.4, so no 8.5+ features such as the pipe operator `|>`, `clone()` with properties, `#[\NoDiscard]` or `array_first()`/`array_last()`). Write a short docblock for public methods, as in `go\core\util\IpAddress`.
- Don't make unrelated formatting changes. They hide the real change in diffs.
