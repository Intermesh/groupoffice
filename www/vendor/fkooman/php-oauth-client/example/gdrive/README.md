# Introduction
This is a sample application to view your files in Google Drive. It uses OAuth 
2.0 to talk to the Google API.

# Installation
This example application uses [Composer](http://www.getcomposer.org) for its
dependency management. You can install the dependencies like this:

    $ php composer.phar install

# Configuration
You need to get yourself a `client_secrets.json` file from the 
[Google API Console](https://code.google.com/apis/console/). You can download 
one once you register the application.

**WARNING**
This sample application expects `client_secrets.json` in the root of the 
project directory, so it will also be accessible through the web browser by 
just requesting this file! In real deployments you will want to move this file 
somewhere else.

My `client_secrets.json` looks like this (after formatting):

    {
        "web": {
            "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
            "auth_uri": "https://accounts.google.com/o/oauth2/auth",
            "client_email": "624925808472@developer.gserviceaccount.com",
            "client_id": "624925808472.apps.googleusercontent.com",
            "client_secret": "HERE_USED_TO_BE_MY_SECRET",
            "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/624925808472@developer.gserviceaccount.com",
            "javascript_origins": [
                "https://fkooman.pagekite.me"
            ],
            "redirect_uris": [
                "https://fkooman.pagekite.me/php-drive-client/callback.php"
            ],
            "token_uri": "https://accounts.google.com/o/oauth2/token"
        }
    }
    
This contains all the information the sample clients needs to find the OAuth 
2.0 server and request an access token. When you register a client it is 
important to set the `redirect_uris` correctly. This has to point to the 
`callback.php` script on your server.

The `callback.php` script will redirect back to `index.php` after the access 
token was obtained. You need to modify the URL in the `callback.php` script to 
point to the location where your `index.php` is located.
