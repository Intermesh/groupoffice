Test environment
----------------

Use this docker image: https://hub.docker.com/r/rroemhild/test-openldap/

```
docker pull rroemhild/test-openldap
docker run --privileged -d -p 389:389 rroemhild/test-openldap
```


Example mapping:

```
$config['ldapMapping'] = [
'enabled' => function($record) {
    return $record->ou[0] != 'Delivering Crew';
},
'diskQuota' => function($record) {
    return 1024 * 1024 * 1024;
},
'email' => 'mail',
'firstName' => 'givenname',
'lastName' => 'sn',
'jobTitle' => 'description'
];
````

