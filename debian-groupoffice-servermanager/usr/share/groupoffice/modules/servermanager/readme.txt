To use this module with automatic invoicing capability 4 values need to added
to the config.php file:

The location where groupoffice can be found
  $config["servermanager_billing_host"]="http://mygroupoffice.url/";

The username the servermanager is loggin in to the external system to add an order
  $config["servermanager_billing_user"]="admin";

The corrisponding password if the above username
  $config["servermanager_billing_pass"]="123456";

The order bookid the order should be placed in. (defaults to 2)
  $config["servermanager_billing_bookid"]=2;


To test the reporting/billing cronjob you can execute the following command on the commandline:
  sudo php /path/to/groupoffice/groupofficecli.php -r=servermanager/installation/report

Note that the AutomaticInvoice object keep score of when to send the next invoice edit this timestamp to resend an invoice
