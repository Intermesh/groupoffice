<?php
$GO_SCRIPTS_JS .='GO.email.defaultSmtpHost="'.\GO::config()->smtp_server.'";
GO.email.useHtmlMarkup=';

$use_plain_text_markup = \GO::config()->get_setting('email_use_plain_text_markup', \GO::user()->id);
if(!empty($use_plain_text_markup))
	$GO_SCRIPTS_JS .= 'false;';
else
	$GO_SCRIPTS_JS .= 'true;';

$GO_SCRIPTS_JS .= 'GO.email.skipUnknownRecipients=';
$skip_unknown_recipients = \GO::config()->get_setting('email_skip_unknown_recipients', \GO::user()->id);
if(empty($skip_unknown_recipients))
	$GO_SCRIPTS_JS .= 'false;';
else
	$GO_SCRIPTS_JS .= 'true;';

$GO_SCRIPTS_JS .= 'GO.email.alwaysRequestNotification=';
$always_request_notification = \GO::config()->get_setting('email_always_request_notification', \GO::user()->id);
if(empty($always_request_notification))
	$GO_SCRIPTS_JS .= 'false;';
else
	$GO_SCRIPTS_JS .= 'true;';

$GO_SCRIPTS_JS .= 'GO.email.alwaysRespondToNotifications=';
$always_respond_to_notifications = \GO::config()->get_setting('email_always_respond_to_notifications', \GO::user()->id);
if(empty($always_respond_to_notifications))
	$GO_SCRIPTS_JS .= 'false;';
else
	$GO_SCRIPTS_JS .= 'true;';

if (\GO::modules()->isInstalled('sieve')) {
$GO_SCRIPTS_JS .= 'GO.email.sievePortValue=';
	if (!empty(\GO::config()->sieve_port))
		$GO_SCRIPTS_JS .= \GO::config()->sieve_port . ';';
	else
		$GO_SCRIPTS_JS .= '4190;';

	$GO_SCRIPTS_JS .= 'GO.email.sieveUseTlsValue=';
	if (isset(\GO::config()->sieve_usetls))
		$GO_SCRIPTS_JS .= !empty(\GO::config()->sieve_usetls) ? 'true;' : 'false;';
	else
		$GO_SCRIPTS_JS .= 'true;';
}

$font_size = \GO::config()->get_setting('email_font_size', \GO::user()->id);
if(empty($font_size))
	$GO_SCRIPTS_JS .= 'GO.email.fontSize="14px";';
else
	$GO_SCRIPTS_JS .= 'GO.email.fontSize="'.$font_size.'";';

$GO_SCRIPTS_JS .= 'GO.email.permissionLevels={delegated:15};';

if(isset($_GET['mail_to']))
{
	//$qs=strtolower(str_replace('mailto:','mail_to=', $_GET['mail_to']));
	//$qs=str_replace('?subject','&subject', $qs);

        $qs=strtolower(str_replace('mailto:','', urldecode($_SERVER['QUERY_STRING'])));
        $qs=str_replace('?subject','&subject', $qs);
	
	parse_str($qs, $vars);
	//var_dump($vars);
	
	$vars['to']=isset($vars['mail_to']) ? $vars['mail_to'] : '';
	unset($vars['mail_to']);
		
	if(!isset($vars['subject']))
		$vars['subject']='';
		
	if(!isset($vars['body']))
		$vars['body']='';
	
	$js = json_encode($vars);
	?>
	<script type="text/javascript">
	GO.mainLayout.onReady(function(){
		GO.email.showComposer({
			values: <?php echo $js; ?>
		});
	});
	</script>
	<?php
}


$email_show_from = GO::config()->get_setting('email_show_from', GO::user()->id,1);
$email_show_cc = GO::config()->get_setting('email_show_cc', GO::user()->id,1);
$email_show_bcc = GO::config()->get_setting('email_show_bcc', GO::user()->id,0);


$GO_SCRIPTS_JS .='GO.email.showCCfield='.$email_show_cc.';'
		. 'GO.email.showBCCfield='.$email_show_bcc.';'
		. 'GO.email.showFromField='.$email_show_from.';';

$GO_SCRIPTS_JS .= "GO.email.disableAliases=";

if(\GO::config()->email_disable_aliases)
	$GO_SCRIPTS_JS .= 'true;';
else
	$GO_SCRIPTS_JS .= 'false;';
