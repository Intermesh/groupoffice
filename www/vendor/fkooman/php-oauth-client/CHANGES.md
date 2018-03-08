# Release History

## 0.5.2
* Implement support for scope being returnd as array (issue #41)

## 0.5.1
* Implement 'prefix' for database as second parameter to PdoStorage() to
  allow for table prefixes (issue #39)
* Implement support for 'exires_in' return value as string from OAuth server
  (issue #40)

## 0.5.0
* Rename Composer package to fkooman/oauth-client

## 0.4.0
* **BREAKS API**
** Context now has `array` as second parameter instead of `Scope`.
* Remove embedded `Scope` class and use php-oauth-lib-common instead
* Move exceptions to fkooman\OAuth\Client\Exception namespace
* Support requesting scopes using comma separation instead of space separation
  to satisfy GitHub spec violation (see README)
* Add GitHubClientConfig 

## 0.3.3
* Fix bug with GitHub by setting `Accept` header to `application/json`

## 0.3.2
* Add support for default_server_scope ClientConfig parameter for Nationbuilder 
  (issue #20)
* Add support for use_redirect_uri_on_refresh_token_request ClientConfig 
  parameter for Nationbuilder (issue #20) 
* Delete old refresh_token if a new one is obtained on getting a new 
  access_token (issue #20)

## 0.3.1
* Update README with information on how to do (token request) logging
* Support OAuth 2.0 AS which provides an invalid `expires_in` value (issue #17)
* Document all other service specific hacks

## 0.3.0
* **Breaks API!**, see README.md and `example` directory, all applications need
  to be updated!
* Introduce abstraction for scopes using a Scope class. This Scope class also
  needs to be used now for Api calls to indicate the requested Scope
* It is possible now to request no scope, but use the one the server returned
  (issue #10)
* Add missing TokenResponseException class (issue #11)
* Add support for default token type if the OAuth 2.0 AS does not return one,
  this violates the specification, but apparently this is used by SalesForce 
  (issue #13)
* Fix dealing with expired tokens: the token was removed, but the refresh
  token was not checked

## 0.2.0
* **Breaks API!**, see README.md and `example` directory, all applications need 
  to be updated!
* Major cleanup of `ClientConfig`, addition of `GoogleClientConfig`
* Cleanup of `Api` and `Callback` classes, move required dependencies to 
  constructor
* Introduce `AuthorizeException` for `Callback` class, thrown when the 
  authorization server returns a (non-fatal) error
* Fix the examples in `example` directory
* Some PSR2 code style fixes

## 0.1.1
* Fix PDO token storage backend again
* Update README to show how to use PDO
* Refactor AccessToken, RefreshToken, State and Token and make them check 
  more
* Add some Google helpers to the code
* Make SessionStorage way more reliable so it works with multiple users
  and client_config_ids in the same session storage

## 0.1.0
* Initial release
