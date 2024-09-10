<?php
use go\core\App;
require("../../../../vendor/autoload.php");
App::get();

$raw = file_get_contents('php://input');
$matches = array();
preg_match('/<EMailAddress>(.*)<\/EMailAddress>/', $raw, $matches);
header('Content-Type: application/xml');

$email = trim($matches[1] ?? "");


if (str_contains($raw, 'autodiscover/outlook/responseschema')) {
    $autodiscoverType = 'imap';
} else {
	$autodiscoverType = 'eas';
}

if($autodiscoverType == 'imap') {

	if(go()->getModule("community", "maildomains")) {
		$imapHost = go\modules\community\maildomains\Module::get()->getSettings()->getMailHost();
	} else {
        if(go()->getModule("legacy", "email") && ($account = \GO\Email\Model\Account::model()->findByEmail($email))) {
           $imapHost = $account->host;
        } else {
           $imapHost = go()->getSettings()->smtpHost;
        }
    }


	?><Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
	<Response xmlns="http://schemas.microsoft.com/exchange/autodiscover/outlook/responseschema/2006a">
		<User>
			<DisplayName>Group-Office</DisplayName>
		</User>
		<Account>
			<AccountType>email</AccountType>
			<Action>settings</Action>
			<Protocol>
				<Type>IMAP</Type>
				<Server><?=$imapHost;?></Server>
				<Port>143</Port>
				<DomainRequired>off</DomainRequired>
				<SPA>off</SPA>
				<SSL>on</SSL>
				<AuthRequired>on</AuthRequired>
				<LoginName><?=$email;?></LoginName>
			</Protocol>
			<Protocol>
				<Type>SMTP</Type>
				<Server><?=$imapHost;?></Server>
				<Port>587</Port>
				<DomainRequired>off</DomainRequired>
				<SPA>off</SPA>
				<SSL>on</SSL>
				<AuthRequired>on</AuthRequired>
                <UsePOPAuth>on</UsePOPAuth>
				<LoginName><?=$email;?></LoginName>
                <SMTPLast>off</SMTPLast>
			</Protocol>
		</Account>
	</Response>
</Autodiscover>
<?php
} else {

    $easUrl = \go\core\jmap\Request::get()->getBaseUrl() . "/Microsoft-Server-ActiveSync";

	$displayName = $email;
	if(go()->getModule("legacy", "email")) {
		$account = \GO\Email\Model\Account::model()->findByEmail($email);
		if($account) {
			$displayName = $account->getDefaultAlias()->name;
		}
	}

    if(\go\core\jmap\Request::get()->getProtocol())

?><Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
    <Response xmlns="http://schemas.microsoft.com/exchange/autodiscover/mobilesync/responseschema/2006">
        <Culture>en:en</Culture>
        <User>
            <DisplayName><?=htmlspecialchars($displayName, ENT_XML1 | ENT_QUOTES, 'UTF-8');?></DisplayName>
            <EMailAddress><?=$email;?></EMailAddress>
        </User>
        <Action>
            <Settings>
                <Server>
                    <Type>MobileSync</Type>
                    <Url><?=$easUrl;?></Url>
                    <Name><?=$easUrl;?></Name>
                </Server>
            </Settings>
        </Action>
    </Response>
</Autodiscover>
<?php
}