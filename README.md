# About GroupOffice

GroupOffice is an open-source groupware and CRM platform for enterprises and the public sector. It’s created and 
maintained by the Dutch company Intermesh. You can self-host the software or run it on Intermesh’s secure cloud 
infrastructure, where your data remains fully private.

Collaborate with integrated file sharing, video conferencing, E-mail, calendar and contact management, project and 
time tracking, quoting and invoicing, support ticketing, and the Collabora Online office suite. By combining all these 
tools in a single platform, GroupOffice eliminates the need for multiple separate systems and streamlines your workflow.
GroupOffice is easily extensible with custom modules, allowing you to tailor the platform to your specific needs.

# Install

If you're not a developer and you wish to use GroupOffice please visit:

https://www.group-office.com/documentation.html

# Developers

## Docker

### Production
Our Docker image can be found here:

https://github.com/Intermesh/docker-groupoffice

### Development
If you'd like to get started with GroupOffice development please have a look at
our docker compose project. You can get started in minutes with just a few commands:

https://github.com/Intermesh/docker-groupoffice-development

## Manual from source

1. Install regularly like on https://groupoffice.readthedocs.io/en/latest/install/install.html
2. If using the Debian packages then disable the APT repository to avoid overwritten source on update.
3. Install PHP Composer, SASS and npm.
4. Clone this repository including submodules:
   
   ```
   git clone --recurse-submodules https://github.com/intermesh/groupoffice
   ```
5. Run ./scripts/build.sh to compile SASS, Install composer packages and build the GOUI typescript modules.

   ```
   ./scripts/build.sh
   ```
6. Symlink the original source directory to your development files. For example:

   Move Debian package folder away:
   
   ```   
   mv /usr/share/groupoffice /usr/share/groupofficebak
   ```
      
   Create symlink to master clone:
  
   ```
   ln -s ~/Projects/groupoffice/master/www /usr/share/groupoffice
   ```
     
7. Launch it in the web browser and follow the installer's instructions.

Happy coding!
