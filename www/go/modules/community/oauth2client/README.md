# README

## Development environment (docker)

When using the official [Group Office development environment](https://github.com/Intermesh/docker-groupoffice-development), you need to add a manual change to your Apache
configuration:

1. Open a shell to your web environment: `docker-compose exec groupoffice bash` 
2. Open the apache configuration: `nano /etc/apache2/sites-enabled/000-default.conf`
3. Add the following lines to the default VirtualHost:
    > Alias /gauth /usr/local/share/groupoffice/go/modules/community/oauth2client/gauth.php
4. Reload the apache configuration: `service apache2 reload`

---