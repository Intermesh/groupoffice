# GroupOffice

## Reuse existing code

Before writing any helper or utility (IP/CIDR, string, date, URL, file, crypto, etc.), search for existing functionality and reuse or extend it instead of duplicating it. Check at least:

- `www/go/core/` (e.g. `IpAddress`, `StringUtil`, `Url`, `DateTime`, `Crypt`)
- the surrounding module/namespace of the code you're changing
- `git log -S<keyword>` / grep for the concept by name and synonyms

Mention in your reply which existing util you found and reused, or that none exists.

## Prefer the new stack

We are migrating away from legacy code. When writing new code or changing existing code, favour the new frameworks:

- **Frontend: use GOUI where possible.** ExtJS is the old framework and the goal is to replace it with GOUI. Don't build new UI in ExtJS unless there is no GOUI alternative.
- **Backend: favour `go\core` over `GO\Base`.** `GO\Base` is the old code base; new code belongs in `go\core`.
- **API: JMAP.** The goal is to rewrite everything to JMAP. Expose new functionality through JMAP (`go\core\jmap`) rather than legacy controllers/RPC.


## Running tests

Run phpunit as `www-data`, never as root. Use `tools/phpunit groupoffice <path>` from the docker-groupoffice-development checkout or `docker compose exec -u www-data groupoffice ...`. Running as root creates root-owned files in `/tmp/phpunit`, `/tmp/groupoffice` and `/var/lib/groupoffice` and breaks the tooling. Tests live in `tests/` (`/usr/local/share/src/tests` in the container).

## Environment

The app runs in Docker (the docker-groupoffice-development checkout, service `groupoffice`, with a `db` service for MariaDB). This repo is mounted as the source. Run PHP, phpunit and CLI commands inside the container, not on the host.

## Commits and changelog

- Commit messages are short and start with a type or module: `Fixed: ...`, `Core: ...`, `Files: ...`. Reference the issue number when there is one (`#1593`).
- User-visible changes get a line in `CHANGELOG.md`, in a separate commit. Unreleased entries go at the top of the file, above the newest version header, as `- Module: description #issue`. Security fixes are written so that a user understands the impact and any config they need to change.

## Database changes

Schema changes go in the module's `install/updates.php` as a new entry keyed by timestamp (`$updates['YYYYMMDDHHMM'][] = "SQL"` or a closure). Never edit an existing update entry, because it has already run on installations. Core updates are in `www/go/core/install/updates.php`. Update `install.sql` only if the module has one for fresh installs.

## Translations

Never hardcode UI text. Use `go()->t("Text")` in PHP. Strings live in the module's `language/<lang>.php` (English is the source, `en.php`). Add new strings to `en.php`. Core translations are in `www/go/core/language/`.

## Permissions and security

- Every new JMAP entity and controller must enforce ACL. Use `Entity::hasPermissionLevel()`, `applyAclToQuery()` and `internalGetPermissionLevel()`, and don't skip them for convenience.
- Admin-only or privileged behaviour needs a test that proves the guard actually fires. Recent fixes (#1593) were guards that never triggered.
- Don't trust client-supplied properties on save, and never expose secrets to the browser.
- Take the client IP from the existing `IpAddress` util, never from `X-Forwarded-For` directly.

## Writing tests

- Tests are PHPUnit (config `tests/phpunit.xml`, suites `core`, `modules`, `api`). They run against a real installed database, not mocks, via `tests/bootstrap-install.php`.
- Mirror the source namespace: `go\core\util\IpAddress` is tested in `tests/go/core/util/IpAddressTest.php`. Module tests go in `tests/go/modules/<package>/<module>/`.
- Classes extend `PHPUnit\Framework\TestCase`, are named `<Class>Test`, and use `testSomething` methods. Use data providers for many inputs (see `IpAddressTest`). A leading underscore (`_testFoo`) disables a test, so don't use it for new ones.
- For ORM and JMAP behaviour, follow `tests/go/core/orm/EntityTest.php` and `tests/go/core/jmap/`. Test entities live in the `community/test` module.
- Every bug fix and every new security guard gets a test that fails without the fix. For permissions, test both the allowed and the denied user.
- Leave the database as you found it: delete the entities you create.
- Run a single test: `tools/phpunit groupoffice <path>` (see "Running tests").

## Converting ExtJS 3.4 to GOUI

- The legacy UI is ExtJS 3.4 (API docs: https://docs.sencha.com/extjs/3.4.0/). Use them to understand what an old component does, then rebuild it with GOUI components instead of porting it line by line.
- GOUI docs: https://goui.io (source of the docs site: https://github.com/Intermesh/goui-docs). Source and issues: https://github.com/Intermesh/goui. A local copy with type definitions is in `www/node_modules/@intermesh/goui`. The Group-Office wrappers are in `www/views/goui/groupoffice-core`. Read these for the available components before inventing your own.
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
- Add parameter and return types to new methods (the minimum supported PHP version is 8.2, so no 8.3+ features such as typed class constants, `json_validate()` or property hooks and asymmetric visibility from 8.4). Write a short docblock for public methods, as in `go\core\util\IpAddress`.
- Don't make unrelated formatting changes. They hide the real change in diffs.
