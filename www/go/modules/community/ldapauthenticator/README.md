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
    'street' => 'street',
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

