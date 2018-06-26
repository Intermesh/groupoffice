Test environment
----------------

Use this docker image: https://hub.docker.com/r/rroemhild/test-openldap/

```
docker pull rroemhild/test-openldap
docker run --privileged -d -p 389:389 rroemhild/test-openldap
```

