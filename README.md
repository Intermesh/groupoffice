# Group Office groupware and CRM

Group-Office is an enterprise CRM and groupware tool. 
Share projects, calendars, files and e-mail online with co-workers and clients. 
Easy to use and fully customizable.

# Install

If you're not a developer and you wish to use Group-Office please visit:

https://www.group-office.com/documentation.html

# Developers

## Docker

### Production
Our Docker image can be found here:

https://github.com/Intermesh/docker-groupoffice

### Development
If you'd like to get started with Group-Office development please have a look at
our docker-compose project. You can get started in minutes with just a few commands:

https://github.com/Intermesh/docker-groupoffice-development

## Manual from source

1. Install regularly like on https://groupoffice.readthedocs.io/en/latest/install/install.html
2. If using the Debian packages then disable the APT repository to avoid overwritten source on update.
3. Clone this repository
4. Change into the "www" directory.
5. Create subdirectories for the GOUI Framework and the GroupOffice Core libraries:
```
mkdir -p views/goui/goui
mkdir -p views/goui/groupoffice-core
```
6. Clone the GOUI and GroupOffice Core libraries in the newly created directories. Please note that these are to be git
submodules:
```
git submodule add git@github.com:Intermesh/goui.git www/views/goui/goui/
git submodule add git@github.com:Intermesh/groupoffice-core.git www/views/goui/groupoffice-core/
```

7. Initialize the submodules:
```
git submodule init
git submodule update
```

8. Follow the instructions in the README.md file for the GOUI library. This will install dependencies and compile the
typescript code.
9. Do the same for the GroupOffice Core library. As this library depends on the GOUI library.

10. Install composer libraries:
   ```
   "composer install"
   ```
11. Compile sass:
   ```
   sass views/Extjs3/themes/Paper/src/style.scss views/Extjs3/themes/Paper/style.css
   sass views/Extjs3/themes/Paper/src/style-mobile.scss views/Extjs3/themes/Paper/style-mobile.css
   ```
12. Symlink the original source directory to your development files. For example:

   Move Debian package folder away:
   
   ```   
   mv /usr/share/groupoffice /usr/share/groupofficebak
   ```
      
   Create symlink to master clone:
  
   ```
   ln -s ~/Projects/groupoffice/master/www /usr/share/groupoffice
   ```
     
13. Launch it in the web browser and follow the installer's instructions.

Happy coding!