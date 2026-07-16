# Changelog


## 2026-07-03
- SECURITY: encrypt the marketplace password at rest and stop serializing it to the browser. It is now a `protected` property with `setPassword()` (Crypt::encrypt, blank = keep unchanged) and a non-getter `decryptPassword()`; a safe `getPasswordConfigured()` bool is exposed for the UI instead. Public `getX()` methods are auto-exposed as API properties, so the previous public cleartext property was fetchable by the client.
- Add a migration to encrypt the existing plaintext password in `core_setting` (idempotent — skips already-encrypted `{GOCRYPT}` values); the settings-panel field is write-only (blank keeps the stored value) and the "configured?" check reads `passwordConfigured`.
- Fix a stray empty translation key (`"" => ""`) — replaced with `Leave blank to keep unchanged`.
