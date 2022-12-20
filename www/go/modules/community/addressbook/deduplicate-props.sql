create temporary table addressbook_email_address_temp like addressbook_email_address;
insert into addressbook_email_address_temp select distinct * from addressbook_email_address;
delete from addressbook_email_address;
insert into addressbook_email_address select distinct * from addressbook_email_address_temp;

create temporary table addressbook_phone_number_temp like addressbook_phone_number;
insert into addressbook_phone_number_temp select distinct * from addressbook_phone_number;
delete from addressbook_phone_number;
insert into addressbook_phone_number select distinct * from addressbook_phone_number_temp;

create temporary table addressbook_date_temp like addressbook_date;
insert into addressbook_date_temp select distinct * from addressbook_date;
delete from addressbook_date;
insert into addressbook_date select distinct * from addressbook_date_temp;

create temporary table addressbook_url_temp like addressbook_url;
insert into addressbook_url_temp select distinct * from addressbook_url;
delete from addressbook_url;
insert into addressbook_url select distinct * from addressbook_url_temp;

create temporary table addressbook_address_temp like addressbook_address;
insert into addressbook_address_temp select distinct * from addressbook_address;
delete from addressbook_address;
insert into addressbook_address select distinct * from addressbook_address_temp;
