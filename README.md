# Group Office groupware and CRM

Group-Office is an enterprise CRM and groupware tool. 
Share projects, calendars, files and e-mail online with co-workers and clients. 
Easy to use and fully customizable.

# Install

If you're not a developer and you wish to use Group-Office please visit:

https://www.group-office.com/documentation.html

# Developers

## Docker
If you'd like to get started with Group-Office development please have a look at
our docker-compose project. You can get started in minutes with just a few commands:

https://github.com/Intermesh/docker-groupoffice-development

## Manual from source

1. Clone this repository
2. Change into the "www" directory.
3. Install composer libraries: 
   ```
   "composer install"
   ```
4. Compile sass: 
   ```
   sass views/Extjs3/themes/Paper/src/style.scss views/Extjs3/themes/Paper/style.css
   sass views/Extjs3/themes/Paper/src/style-mobile.scss views/Extjs3/themes/Paper/style-mobile.css
   ```
5. Launch it in the web browser and follow the installer's instructions.

