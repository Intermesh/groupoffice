# Rename a domain and create aliases from old domain to the new domain.
# Create a new domain with the new name first.
# after executing this script you must also rename the folder in /var/mail/vhosts !!!

#Execute first part on the mailserver
set names utf8mb4 COLLATE utf8mb4_unicode_ci;
set @old_domain = 'groupoffice.localhost';
set @new_domain = 'intermesh.localhost';

set @new_domain_id = (select id from pa_domains where domain = @new_domain);

# make sure aliases are unique.
drop index address on pa_aliases;

create unique index address
    on pa_aliases (address);


# make sure usernames are unique.
drop index username on pa_mailboxes;

create unique index username
    on pa_mailboxes (username);


# Actual renaming part
update pa_mailboxes set
                        domain_id = @new_domain_id,
                        username = replace(username, concat('@', @old_domain), concat('@', @new_domain)),
                        maildir = replace(maildir,  @old_domain, @new_domain),
                        homedir = replace(homedir, @old_domain, @new_domain)
where username like concat('%', '@', @old_domain);

update pa_aliases set goto = replace(goto, concat('@', @old_domain), concat('@', @new_domain));

insert ignore into pa_aliases
select null,@new_domain_id,replace(address, concat('@', @old_domain), concat('@', @new_domain)),goto,ctime,mtime,active from pa_aliases where address like concat('%', '@', @old_domain);

insert ignore into pa_aliases
select null,@new_domain_id,replace(username, concat('@', @new_domain), concat('@', @old_domain)), username,unix_timestamp(),unix_timestamp(),'1' from pa_mailboxes where username like concat('%', '@', @new_domain) and `active` = '1';


# Execute this on the client groupoffice instance (might be the same db)
# set names utf8mb4 COLLATE utf8mb4_unicode_ci;
# set @old_domain = 'intermesh.localhost';
# set @new_domain = 'groupoffice.localhost';

update em_accounts set username = replace(username, concat('@', @old_domain), concat('@', @new_domain)), smtp_username = replace(smtp_username, concat('@', @old_domain), concat('@', @new_domain));
update em_aliases set email = replace(email, concat('@', @old_domain), concat('@', @new_domain));

