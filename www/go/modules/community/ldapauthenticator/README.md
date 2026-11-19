Test environment
----------------

Use this docker image: https://hub.docker.com/r/rroemhild/test-openldap/

```
docker pull rroemhild/test-openldap
docker run --privileged -d -p 389:389 rroemhild/test-openldap
```


The default mapping:

```
$config['ldapMapping'] = [
    'enabled' => function($record) {
        //return $record->ou[0] != 'Delivering Crew';
        return true;
    },
    'diskQuota' => function($record) {
        //return 1024 * 1024 * 1024;
        return null;
    },
    'email' => 'mail',
    'recoveryEmail' => 'mail',
    'displayName' => 'cn',
    'firstName' => 'givenname',
    'lastName' => 'sn',
    'initials' => 'initials',

    'jobTitle' => 'title',
    'department' => 'department',
    'notes' => 'info',

//				'addressType' => function($record) {
//					return \go\modules\community\addressbook\model\Address::TYPE_WORK;
//				},
    'address' => 'street',
    'zipCode' => 'postalCode',
    'city' => 'l',
    'state' => 's',
//				'countryCode' => function($record) {
//					return "NL";
//				},

    'homePhone' => 'homePhone',
    'mobile' => 'mobile',
    'workFax' => 'facsimiletelephonenumber',
    'workPhone' => 'telephonenumber',

    'organization' => 'organizationname'
];
````

Archiving users
---------------

Add an `archived` key to the mapping to archive users in the LDAP sync. When it returns true, the user is disabled
and archived: it is removed from all groups except "Everyone", its shares are removed and its profile moves to the
"Archived users" address book. This happens once; the next syncs leave the archived user alone. When it returns false
again, the user stays as it is and must be restored by an administrator.

```
'archived' => function($record) {
    return !isset($record->{"UniHGW-Affiliation"});
},
```

At login the user is only disabled. Archiving needs the right to change users, which the sync has.

