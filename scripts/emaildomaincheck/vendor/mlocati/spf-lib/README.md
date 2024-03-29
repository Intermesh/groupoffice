[![Tests](https://github.com/mlocati/spf-lib/workflows/Tests/badge.svg)](https://github.com/mlocati/spf-lib/actions?query=workflow%3A%22Tests%22)
[![Code Coverage](https://codecov.io/gh/mlocati/spf-lib/branch/master/graph/badge.svg)](https://codecov.io/gh/mlocati/spf-lib)


# SPF (Sender Policy Framework) Library

This PHP library allows you to:

- get the SPF record from a domain name
- decode and validate the SPF record
- create the value of a TXT record
- check if domains and IP addresses satisfy the SPF records

The implementation is based on [RFC 7208](https://tools.ietf.org/html/rfc7208).

AFAIK this is the only PHP library that passes the [Open SPF Test Suite for RFC 7208](http://www.open-spf.org/Test_Suite/).

## Short introduction about SPF

Here's a very simplified short description of the purpose of the SPF protocol.

When an email client contacts an email server in order to delivery an email message, the email server has this information:

1. the IP address of the email client that is sending the email
2. the domain that the email client specified at the beginning of the SMTP delivery (after the `HELO`/`EHLO` SMTP command)
3. the sender email address (as specified in the `MAIL FROM` SMTP command)

The email server can use the SPF protocol to determine if the client is allowed or not to send email addresses with the specified domains (the `HELO`/`EHLO` domain and/or the domain after the `@` in the `MAIL FROM` email address).

This is done by querying the SPF DNS records of the domain(s) being checked, which can tell the server if the client is allowed/non-allowed/probably not allowed to send the email.

You can use this PHP library to build, validate and check the SPF records.

## Installation

You can install this library with Composer:

```sh
composer require mlocati/spf-lib
```

## Usage

### Validating domains and IP addresses

Let's assume that the email client has the IP address `1.2.3.4`, specified `helo.domain` with the `HELO`/`EHLO` SMTP command, and specified `sender@domain.com` in the `MAIL FROM` email address.

These data are represented by the `SPFLib\Check\Environment` class: you can create it with:

```php
$environment = new \SPFLib\Check\Environment(`1.2.3.4`, `helo.domain`, `sender@domain.com`);
```

To check the SPF records, you can use the `SPFLib\Checker` class:

```php
$checker = new \SPFLib\Checker();
$checkResult = $checker->check($environment);
```

By default, the `check()` method checks both the `HELO`/`EHLO` and the `MAIL FROM` domains (if both are available and if they are different).
You can check just one by specifying `\SPFLib\Checker::FLAG_CHECK_HELODOMAIN` or `\SPFLib\Checker::FLAG_CHECK_MAILFROADDRESS` as the second argument of the `check()` method.
Otherwise you can specify an empty string in the related `Environment` constructor (for example: `new Environment($ip, $domain)` will check only the `HELO`/`EHLO` domain, `new Environment($ip, '', $mailFromAddress)` will check only the domain of the `MAIL FROM` email address).

`$checkResult` is an instance of `SPFLib\Term\Mechanism\Result`, that provides:

- the check result (`$checkResult->getCode()`), which is one of the [values specified in the RFC](https://tools.ietf.org/html/rfc7208#section-2.6).
- the SPF mechanism that provided the result, if available (`$checkResult->getMatchedMechanism()`)
- the failure description, if provided by the SPF records (`$checkResult->getFailExplanation()`)
- optional relevant messages from the check process (`$checkResult->getMessages()`)

So, the simplest example is:

```php
use SPFLib\Checker;
use SPFLib\Check\Environment;

$checker = new Checker();
$checkResult = $checker->check(new Environment('127.0.0.1', 'gmail.com'));
echo $checkResult->getCode();
```

which outputs

```
softfail
```


### Retrieving the SPF record from a domain name

An SPF record is composed by zero or more terms. Every term can be a mechanism or a modifier.

This library allows you to inspect them:

```php

$decoder = new \SPFLib\Decoder();
try {
    $record = $decoder->getRecordFromDomain('example.com');
} catch (\SPFLib\Exception $x) {
    // Problems retrieving the SPF record from example.com,
    // or problems decoding it
    return;
}
if ($record === null) {
    // SPF record not found for example.com
    return;
}
// List all terms (that is, mechanisms and modifiers)
foreach ($record->getTerms() as $term) {
    // do your stuff
}
// List all mechanisms
foreach ($record->getMechanisms() as $mechanism) {
    // do your stuff
}
// List all modifiers
foreach ($record->getModifiers() as $modifiers) {
    // do your stuff
}
```

Please note that:

- all [mechanisms](https://github.com/mlocati/spf-lib/tree/master/src/Term/Mechanism) extend the [`SPFLib\Term\Mechanism`](https://github.com/mlocati/spf-lib/blob/master/src/Term/Mechanism.php) abstract class.
- all [modifiers](https://github.com/mlocati/spf-lib/tree/master/src/Term/Modifier) extend the [`SPFLib\Term\Modifier`](https://github.com/mlocati/spf-lib/blob/master/src/Term/Modifier.php) abstract class.
- both mechanisms and modifiers implement the [`SPFLib\Term`](https://github.com/mlocati/spf-lib/blob/master/src/Term.php) interface.

### Decoding the SPF record from the value of a TXT DNS record

```php
$txtRecord = 'v=spf1 mx a -all';
$decoder = new \SPFLib\Decoder();
try {
    $record = $decoder->getRecordFromTXT($txtRecord);
} catch (\SPFLib\Exception $x) {
    // Problems decoding $txtRecord (it's malformed).
    return;
}
if ($record === null) {
    // $txtRecord is not an SPF record
    return;
}
```

### Creating the value of an SPF record

```php
use SPFLib\Term\Mechanism;

$record = new \SPFLib\Record('example.org');
$record
    ->addTerm(new Mechanism\MxMechanism(Mechanism::QUALIFIER_PASS))
    ->addTerm(new Mechanism\IncludeMechanism(Mechanism::QUALIFIER_PASS, 'example.com'))
    ->addTerm(new Mechanism\AllMechanism(Mechanism::QUALIFIER_FAIL))
;
echo (string) $record;
```

Output:

```
v=spf1 mx include:example.com -all
```

### Checking problems with an SPF record

```php
$spf = 'v=spf1 all redirect=example1.org redirect=example2.org ptr:foo.bar mx include=example3.org exp=test.%{p}';
$record = (new \SPFLib\Decoder())->getRecordFromTXT($spf);
$issues = (new \SPFLib\SemanticValidator())->validate($record);
foreach ($issues as $issue) {
    echo (string) $issue, "\n";
}
```

Output:

```
[warning] 'all' should be the last mechanism (any other mechanism will be ignored)
[warning] The 'redirect' modifier will be ignored since there's a 'all' mechanism
[notice] The 'ptr' mechanism shouldn't be used because it's slow, resource intensive, and not very reliable
[notice] The term 'exp=test.%{p}' contains the macro-letter 'p' that shouldn't be used because it's slow, resource intensive, and not very reliable
[notice] The modifiers ('redirect=example1.org', 'redirect=example2.org') should be after all the mechanisms
[fatal] The 'redirect' modifier is present more than once (2 times)
[notice] The 'include=example3.org' modifier is unknown
```

Please note that every item in the array returned by the `validate` method is an instance of the [`SPFLib\Semantic\Issue`](https://github.com/mlocati/spf-lib/blob/master/src/Semantic/Issue.php) class.

### Checking problems with an SPF record in real world

The `SemanticValidator` only look for issues in an SPF record, without inspecting include (or redirected-to) records.

In order to check an SPF record and all the referenced records you can use the `OnlineSemanticValidator`:

```php
$validator = new \SPFLib\OnlineSemanticValidator();
// Check an online domain
$issues = $validator->validateDomain('example.org');
// Check a raw SPF record
$issues = $validator->validateRawRecord('v=spf1 include:_sfp.example.org -all');
// Check an SPFLib\Record instance ($record in this case)
$issues = $validator->validateRecord($record);
```

The result of these methods are arrays of `SPFLib\Semantic\OnlineIssue` instances, which are very similar to the `SPFLib\Semantic\Issue` instances returned by the offline `SemanticValidator`.

## Do you want to really say thank you?

You can offer me a [monthly coffee](https://github.com/sponsors/mlocati) or a [one-time coffee](https://paypal.me/mlocati) :wink:
