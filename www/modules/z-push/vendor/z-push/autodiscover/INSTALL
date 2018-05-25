Z-Push AutoDiscover manual
--------------------------
This manual gives an introduction to the Z-Push AutoDiscover service, discusses 
technical details and explains the installation.

Introduction
------------
AutoDiscover is the service used to simplify the configuration of collaboration 
accounts for clients, especially for mobile phones.
While in the past the user was required to enter the server name, user name and 
password manually into his mobile phone in order to connect, with AutoDiscover 
the user is only required to fill in his email address and the password. 
AutoDiscover will try several methods to reach the correct server automatically.


How does it work?
-----------------
When speaking about AutoDiscover, this includes two distinct realms:
- AutoDiscover is a specification which defines the steps a client should take 
  in order to contact a service to request additional data.
- The AutoDiscover service is piece of software which accepts requests from the 
  clients, authenticates them, requests some additional data from the 
  collaboration server and sends this data back to the client.
The specification suggests several ways for client to contact the responsible 
server to receive additional information. Tests have shown, that basically all 
mobile phones tested support only the most basic ways. These are sufficient for 
almost all types of scenarios and are the ones implemented by Z-Push AutoDiscover. 
Please refer to the Mobile Compatibility List (http://z-push.org/compatibility) 
for an overview of supported and tested devices.
The used email address is the key for the process. The client splits it up into 
the local and domain part (before and after the @-sign). The client then tries 
to connect to this domain in order to get in contact with the AutoDiscover 
service. The local part of the email address is used as "login" to the 
AutoDiscover service. There is also an option, to use the full email address as 
login name (see "Configuration" section below for details).


                     ---------------
                     |    Client   |
                     | e.g. mobile |
                     ---------------
                    /               \
   1. Searches for /                 \ 2. Data access
     information  /                   \
                 /                     \
                V                       V
 ----------------                       --------------
 | AutoDiscover |      redirects to     |   Z-Push   |
 |              | --------------------> | ActiveSync |
 ----------------                       --------------
                \                       /
       Authen-   \                     /  Synchronizes
       ticates    \                   /
       via Z-Push  \                 /
       Backend      V               V
                    -----------------
                    | Collaboration |
                    |    Platform   |
                    -----------------

Requirements
------------
As described in the previous chapter, the local part of the email address or 
the email address is used in order to log in. 
Your configuration requires that this type of login is possible:
- either the user name is used to login and must be used in the email address 
  entered on the mobile, or
- the entire email address is used to login.
 
Which option is used has to be configured in the AutoDiscover configuration and
in the underlying platform (e.g. KC (hosting mode)).
Most companies use the user name as local part of the email by default. From the
AutoDiscover point of view, it is not required that user is able to receive 
emails at the used email address. It is recommended allowing that in order not 
to confuse end users.

AutoDiscover also requires a valid SSL certificate to work as expected. A very 
little percentage of mobiles support self-signed certificates (showing a 
pop-up alerting the user). Most mobiles silently ignore self-signed certificates 
and just declare the AutoDiscover process as failed in such cases. 
If AutoDiscover fails, the user is generally redirected to the 
"manual configuration" of the client.

If you do not plan to acquire an official certificate, you will probably not be 
able to use the AutoDiscover service.
Depending on your setup, it could be necessary to add new DNS entries for your 
mail domain.


Domain setup
------------
There are two general ways the AutoDiscover process can be configured:
1. Directly with "yourdomain.com" website ("www.yourdomain.com" will most 
   probably not work)
2. With the sub-domain "autodiscover.yourdomain.com"
In both cases, an official SSL certificate is required. If you already have a 
certificate for your domain, the webserver answering for that domain could be 
reconfigured to allow AutoDiscover requests as well. In the case that you do 
not have direct access to this type of configuration (e.g. hosting provider), 
it's recommended to acquire a dedicated certificate for 
"autodiscover.yourdomain.com". Please note, that this sub-domain can NOT be 
renamed. In general, "wildcard" certificates can be used, as long they are 
valid for the required domain.


Software requirements
---------------------
Like Z-Push, AutoDiscover is written in PHP, where PHP 5.1 or newer is required. 
Please consult the Z-Push INSTALL file for further information about PHP versions.
If only AutoDiscover is to be executed on a host, the Z-Push PHP dependencies do 
NOT need to be installed.
AutoDiscover has one direct dependency, the php-xml parser library.

These packages vary in names between the distributions.
- Generally install the packages:                   php-xml
- On Suse (SLES & OpenSuse) install the packages:   php53-xml
- On RHEL based systems install the package:        php-xml


Installation
------------
AutoDiscover is part of the Z-Push package and uses some of the functionality 
available in Z-Push. 
It is possible to install AutoDiscover on the same host as Z-Push, or to 
install them on different hosts.

Currently, independently from the setup, it's recommended to extract the entire 
z-push tarball and configure the services as required.
Please follow the install instructions from the Z-Push INSTALL file (section 
"How to install") to copy the files to your server.
If you do not want to setup Z-Push on the host, do not add the "Alias" for 
ActiveSync.

To setup the SSL certificate, please refer to one of the many setup guides 
available on the internet, like that one:
http://www.apache.com/resources/how-to-setup-an-ssl-certificate-on-apache/

The mobiles requests these URLs (where "yourdomain.com" corresponds to the 
domain part of the email used in the client):
    https://yourdomain.com/Autodiscover/Autodiscover.xml                and/or
    https://autodiscover.yourdomain.com/Autodiscover/Autodiscover.xml

Add the following line to the apache site configuration file.
    AliasMatch (?i)/Autodiscover/Autodiscover.xml "/usr/share/z-push/autodiscover/autodiscover.php"

This line assumes that Z-Push is installed in /usr/share/z-push. If the path 
is different, please adjust it accordingly.
    
Note: some mobiles use different casings, like "AutoDiscover" in the URL. The 
above statement is valid for these as well.

Please restart Apache afterwards.


Configuration
-------------
There are several parameters in the configuration file, which allow to customize 
the behaviour of the AutoDiscover Service.
The configuration, generally is located in the z-push/autodiscover directory and 
is called "config.php".

The parameters:
BASE_PATH               This property specifies where the AutoDiscover files are 
                        located. Normally there is no need to adjust this parameter.
SERVERURL               This is the full URL where the Z-Push server is available. 
                        You should adjust it to the domain/server where Z-Push is 
                        installed.
                        
USE_FULLEMAIL_FOR_LOGIN If this is set to "true", AutoDiscover will attempt to 
                        login on the collaboration server with the full email 
                        address sent by the client. If disabled (default), the 
                        local part of the email address is used.

AUTODISCOVER_LOGIN_TYPE If the local part of the email address doesn't match the
                        user name, this parameter helps to convert it for some common cases.
Possible values:
    AUTODISCOVER_LOGIN_EMAIL            uses the local part of the email address
                                        as provided when setting up the account.
    AUTODISCOVER_LOGIN_NO_DOT           removes the '.' from email address:
                                        email: first.last@domain.com -> resulting username: firstlast
    AUTODISCOVER_LOGIN_F_NO_DOT_LAST    cuts the first part before '.' after the first letter
                                        and removes the '.' from email address:
                                        email: first.last@domain.com -> resulting username: flast
    AUTODISCOVER_LOGIN_F_DOT_LAST       cuts the part before '.' after the first letter and
                                        leaves the part after '.' as is:
                                        email: first.last@domain.com -> resulting username: f.last

LOGFILEDIR              The directory where logfiles are created.

LOGFILE                 The default AutoDiscover log file.

LOGERRORFILE            The default AutoDiscover error log file.

LOGLEVEL                The loglevel, set it to WBXML to see the data received 
                        and sent from/to clients.
                        
LOGAUTHFAIL             Set to true, to explicitly log failed login attempts.

BACKEND_PROVIDER        The backend to be used. If empty (default) the code 
                        will auto detect which backend to use.

Please note: the desired backend also needs to be configured, in the 
"backends/<backend>/config.php" file.


Test installation
-----------------
If everything is correct, accessing with a browser the URL for your setup, you 
should see:
    1. a pop-up asking for your username + password. Always use the email 
       address which you would also enter on the mobile (independently from 
       the configuration).
    2. if the authentication was successful, you will see a Z-Push informational 
       page (like when accessing the Z-Push location).
    
Note: The same test can also be performed in the mobiles web browser to check 
if the access works correctly from the mobile network.

If the authentication fails, please check the configuration options of AutoDiscover.
Also check the logfiles for possible failures.

If the manual method works, try setting up your mobile phone! :)
