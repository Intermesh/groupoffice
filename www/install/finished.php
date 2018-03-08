<?php
$logout=true;
require('header.php');



printHead()
?>
<h1>Installation finished!</h1>
<p>
	Please make sure '<?php echo \GO::config()->get_config_file(); ?>' is not writable anymore now.<br />
</p>
<div class="cmd">
	$ chmod 644 <?php echo \GO::config()->get_config_file(); ?>
</div>
<p>
	If you don't have shell access then you should download <?php echo \GO::config()->get_config_file(); ?>, then delete it
	from the server and upload it back to the server. This way you change the ownership to your account.
	<br />
	<br />
	Don't use the administrator account for regular use! Only use it for administrative tasks.
	<br />
	Read this to get started with <?php echo \GO::config()->product_name; ?>: <a href="http://www.group-office.com/wiki/Getting_started" target="_blank">http://www.group-office.com/wiki/Getting_started</a>
<ul>
	<li>Navigate to the menu: Administrator menu -&gt; Modules and remove the modules you do not wish to use.</li>
	<li>Navigate to the menu: Administrator menu -&gt; User groups and create user groups.</li>
	<li>Navigate to the menu: Administrator menu -&gt; Users users to add new users.</li>
</ul>
<br />
You can also configure external authentication servers such as an LDAP,IMAP or POP-3 server.
Read more about it here: <a target="_blank" href="http://www.group-office.com/wiki/IMAP_or_LDAP_authentication">http://www.group-office.com/wiki/IMAP_or_LDAP_authentication</a>
<br />
<br />
For troubleshooting please visit <a target="_blank" href="http://www.group-office.com/wiki/Troubleshooting">http://www.group-office.com/wiki/Troubleshooting</a><br />
If that doesn't help post on the <a target="_blank" href="http://www.group-office.com/forum/">forums</a>.<br />
</p>

<div align="right">
<input type="button" value="Launch <?php echo \GO::config()->product_name; ?>!" onclick="javascript:window.location='<?php echo \GO::config()->host; ?>';" />
</div>

<?php

printFoot();
?>