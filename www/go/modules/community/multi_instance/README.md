# Installation

1. Make sure the main install database user has permissions to create databases
   
   ```
   GRANT ALL PRIVILEGES ON *.* TO 'groupoffice'@'%' REQUIRE NONE WITH GRANT OPTION MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
   ```

2. Create "multi_instance" config folder

	 ````````````````````````````````````````````````````````````````````````````````````````````````
	 mkdir /etc/groupoffice/multi_instance && chown www-data:www-data /etc/groupoffice/multi_instance
	 ````````````````````````````````````````````````````````````````````````````````````````````````

3. Create "multi_instance" data folder

   ````````````````````````````````````````````````````````````````````````````````````````````````
   mkdir /var/lib/groupoffice/multi_instance && chown www-data:www-data /var/lib/groupoffice/multi_instance
   ````````````````````````````````````````````````````````````````````````````````````````````````

4. Install this module and create Group-Office instances!
