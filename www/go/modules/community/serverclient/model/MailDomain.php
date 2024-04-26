<?php

namespace go\modules\community\serverclient\model;

use Exception;
use GO;
use go\core\http\Client;
use go\core\model\User;
use go\core\orm\Entity;
use GO\Email\Model\Account;
use go\modules\community\maildomains\model\Domain;
use go\modules\community\maildomains\model\Mailbox;

class MailDomain
{

	private $http;
	private $password;

	public function __construct(string $password)
	{
		$this->password = $password;
		$this->http = new Client();
	}

	/**
	 * @param string $url
	 * @return string
	 * @throws Exception
	 */
	private function getLegacyBaseUrl(string $url)
	{
		if (empty(GO::config()->serverclient_server_url)) {
			GO::config()->serverclient_server_url = go()->getSettings()->URL;
		}

		if (empty(GO::config()->serverclient_token)) {
			throw new Exception("Could not connect to mailserver. Please set a strong password in /etc/groupoffice/globalconfig.inc.php.\n\nPlease remove serverclient_username and serverclient_password.\n\nPlease add:\n\n \$config['serverclient_token']='aStrongPasswordOfYourChoice';");
		}

		$url = GO::config()->serverclient_server_url . '?r=' . $url . '&serverclient_token=' . GO::config()->serverclient_token;

		return $url;
	}

	/**
	 * Base URL for the current JMAP API call
	 *
	 * This is just your regular link to jmap.php, but the main difference is that the GO servers may differ
	 *
	 * @return string
	 * @throws Exception
	 */
	private function getBaseUrl(): string
	{
		if (empty(go()->getConfig()['serverclient_server_url'])) {
			go()->getConfig()['serverclient_server_url'] = go()->getSettings()->URL;
		}
		if (empty(go()->getConfig()['serverclient_token'])) {
			throw new Exception("Could not connect to mailserver. Please set a strong password in /etc/groupoffice/globalconfig.inc.php.\n\nPlease remove serverclient_username and serverclient_password.\n\nPlease add:\n\n \$config['serverclient_token']='aStrongPasswordOfYourChoice';");
		}
		return go()->getConfig()['serverclient_server_url'] . "/api/jmap.php";
	}

	/**
	 * Create a user mailbox
	 *
	 * @param Entity $user
	 * @param string $domain
	 * @return void
	 * @throws Exception
	 */
	public function addMailbox(Entity $user, string $domain)
	{
		//strip domain from username if it's present.
		$username = str_replace('@' . $domain, '', $user->username);
		$alias = strpos($user->email, '@' . $domain) ? $user->email : '';

		if (!go()->getModule('community', 'maildomains')) {
			$this->legacyAddMailbox(array(
				'name' => $user->displayName,
				'username' => $username,
				'alias' => $alias,
				'password' => $this->password,
				'password2' => $this->password,
				'domain' => $domain
			));
			$d = Domain::find()->where(['domain' => $domain])->single();
			$data[] =
				['MailBox/set',
					[
						'create' => [
							'active' => true,
							'fts' => false,
							'name' => $user->displayName,
							'username' => $username,
							'password' => $this->password,
							'domainId' => $d->id,
							'quota' => $d->defaultQuota
						]
					],
					'clientCallId-1'
				];
			if (strlen($alias)) {
				$data[] = [
					["MailAlias/set",
						"create" => [
							'active' => true,
							'address' => $user->email,
							'domainId' => $d->id,
							'goto' => $user->email
						]
					],
					'clientCallId-2'
				];
			}
			$responses = $this->jmapCall($data);
			return;
		}
	}

	/**
	 * Add a mailbox to the deprecated postfixadmin module
	 *
	 * This method is here for backwards compatibility purposes. Please remove after the old module
	 * has been fully replaced.
	 *
	 * @param array $params
	 * @return void
	 * @throws Exception
	 */
	private function legacyAddMailbox(array $params)
	{
		$url = $this->getLegacyBaseUrl("postfixadmin/mailbox/submit");
		$response = $this->http->post($url, $params);

		if ($response['status'] != 200) {
			throw new Exception("Unexpected HTTP status " . $response['status'] . " from " . $url);
		}
		$result = json_decode($response['body']);

		if (!$result) {
			throw new Exception("Could not create mailbox on postfixadmin module. " . $response['body']);
		}
		if (!$result->success) {
			throw new Exception("Could not create mailbox on postfixadmin module. " . $result->feedback);
		}
	}


	/**
	 * Update a password for an existing user mailbox
	 *
	 * @param User $user
	 * @param string $domain
	 * @return void
	 * @throws GO\Base\Exception\AccessDenied
	 * @todo: refactor to new maildomains JMAP module
	 */
	public function setMailboxPassword(User $user, string $domain)
	{
		$username = explode('@', $user->username)[0];
		$username .= '@' . $domain;

		if (!go()->getModule("community", "maildomains")) {
			$this->legacySetMailboxPassword($username);
		}


		if (!GO::modules()->isInstalled('email')) {
			return;
		}

		$mb = Mailbox::find(['id'])->where(['username' => $username])->single();

		$data = ['MailBox/set',
			[
				'update' => [
					$mb->id => [
						'username' => $username,
						'password' => $this->password
					]
				]
			],
			'clientCallId-1'
		];
		$response = $this->jmapCall($data);

		$stmt = Account::model()->findByAttributes(['username' => $username]);

		while ($account = $stmt->fetch()) {
			$account->password = $this->password;
			$account->save(true);
		}
	}

	private function legacySetMailboxPassword(string $username): void
	{
		$url = $this->getLegacyBaseUrl("postfixadmin/mailbox/setPassword");

		$response = $this->http->post($url, array(
			"username" => $username,
			"password" => $this->password,
		));

		if ($response['status'] != 200) {
			throw new Exception("Unexpected HTTP status " . $response['status'] . " from " . $url);
		}

		$result = json_decode($response['body']);

		if (!$result) {
			throw new Exception("Could not create mailbox on postfixadmin module. " . $response['body']);
		}

		if (!$result->success) {
			throw new Exception("Could not set mailbox password on postfixadmin module. " . $result->feedback);
		}
	}

	/**
	 * Get mail usage in bytes
	 * @param array $domains
	 * @return int
	 * @throws Exception
	 */
	public function getUsage(array $domains)
	{
		if (!go()->getModule("community", "maildomains")) {
			return $this->legacyGetUsage($domains);
		}
		$i = 0;
		foreach (Domain::find()->where(['domain' => $domains])->all() as $d) {
			foreach ($d->mailboxes as $mb) {
				$i += $mb->usage;
			}
		}
		return $i;
	}


	private function legacyGetUsage(array $domains): int
	{
		$url = $this->getLegacyBaseUrl("postfixadmin/domain/getUsage");
		$response = $this->http->post($url, [
			'domains' => json_encode($domains)
		]);

		if ($response['status'] != 200) {
			throw new Exception("Unexpected HTTP status " . $response['status'] . " from " . $url);
		}

		$result = json_decode($response['body']);

		if (!$result->success) {
			throw new Exception("Could not set mailbox password on postfixadmin module. " . $result->feedback);
		}

		return (int)$result->usage * 1024;
	}

	/**
	 * Add an email account
	 *
	 * @param Entity $user
	 * @param string $domain
	 * @return void
	 * @throws GO\Base\Exception\AccessDenied
	 */
	public function addAccount(Entity $user, string $domain)
	{
		if (!GO::modules()->isInstalled('email')) {
			return;
		}

		$account = new Account();
		$account->user_id = $user->id;
		$account->mbroot = GO::config()->serverclient_mbroot;
		$account->imap_encryption = '';

		if (!empty(GO::config()->serverclient_use_ssl)) {
			$account->imap_encryption = 'ssl';
		} elseif (!empty(GO::config()->serverclient_use_tls)) {
			$account->imap_encryption = 'tls';
		}

		$account->imap_allow_self_signed = GO::config()->serverclient_novalidate_cert ?? true;
		$account->host = GO::config()->serverclient_host ?? "localhost";
		$account->port = GO::config()->serverclient_port ?? 143;
		$account->force_smtp_login = GO::config()->serverclient_force_smtp_login ?? false;

		$username = explode('@', $user->username) [0];
		$username .= '@' . $domain;

		$account->username = $username;

		$account->password = $this->password;
		$account->smtp_host = GO::config()->serverclient_smtp_host ?? 'localhost';
		$account->smtp_port = GO::config()->serverclient_smtp_port ?? 25;
		$account->smtp_encryption = GO::config()->serverclient_smtp_encryption;
		$account->smtp_username = GO::config()->serverclient_smtp_username;
		$account->smtp_password = GO::config()->serverclient_smtp_password;
		$account->save();

		$alias = strpos($user->email, '@' . $domain) ? $user->email : $account->username;

		if (!strpos($alias, '@')) {
			$alias .= '@' . $domain;
		}

		$account->addAlias($alias, $user->displayName);
	}

	/**
	 * Perform a JMAP call through CURL
	 *
	 * @param array $data
	 * @return mixed
	 * @throws Exception
	 */
	private function jmapCall(array $data)
	{
		$dataStr = json_encode($data);

		$apiUrl = $this->getBaseUrl();

		$apiKey = go()->getConfig()['serverclient_api_token']; // For development purposes

		// Make POST request with curl
		$ch = curl_init($apiUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/json; charset=utf-8",
				"Authorization: Bearer " . $apiKey,
				"Content-Length: " . strlen($dataStr),
//			"COOKIE: XDEBUG_SESSION=PHPSTORM" // Uncomment to use XDebug to debug the API call
			)
		);

		$result = curl_exec($ch);

		if (!$result) {
			throw new Exception("Failed to send request!" . curl_error($ch));
		}

		$responses = json_decode($result, true);
		$error = null;
		//check for API error. More details on https://jmap.io
		if (isset($responses[0][1][0]) && $responses[0][1][0] == "error") {
			$error = "Error: " . $responses[0][1][1]['message'];
		} else if (!empty($responses[0][1]['notCreated'])) {
			$error = "Error: " . var_export($responses[0][1]['notCreated']['contact-1']['validationErrors'], true);
		} else if (empty($responses[0][1]['created'])) {
			$error = "Error: " . var_export($responses, true);
		}

		if ($error) {
			throw new Exception($error);
		}

		return $responses;
	}

}