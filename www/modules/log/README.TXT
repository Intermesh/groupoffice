Log entries older then 14 days can be dumped to /var/log/groupoffice/*.csv with command:

./groupofficecli.php -r=log/log/rotate

You can setup to rotate the log by creating a config file for logrotate in /etc/logrotate.d/groupoffice.

Put this in it:

/var/log/groupoffice/*.csv {
        weekly
        missingok
        rotate 52
        compress
        delaycompress
        notifempty
        create 640 root adm
        sharedscripts        
}

Debian packages will install this automatically.
