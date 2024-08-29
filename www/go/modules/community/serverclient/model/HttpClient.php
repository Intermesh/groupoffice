<?php
/**
 * @todo: fully refactor into new framework, so with go/core/http/Client
 */
namespace go\modules\community\serverclient\model;

class HttpClient extends \GO\Base\Util\HttpClient
{
	/**
	 * @inheritDoc
	 */
	public function request($url, $params = array())
	{
		if (empty(\GO::config()->serverclient_server_url)) {
			\GO::config()->serverclient_server_url = \GO::config()->full_url;
		}

		$url = \GO::config()->serverclient_server_url . '?r=' . $url;

		if (empty(\GO::config()->serverclient_token)) {
			throw new \Exception("Could not connect to mailserver. Please set a strong password in /etc/groupoffice/globalconfig.inc.php.\n\nPlease remove serverclient_username and serverclient_password.\n\nPlease add:\n\n \$config['serverclient_token']='aStrongPasswordOfYourChoice';");
		}

		$params['serverclient_token'] = \GO::config()->serverclient_token;

		return parent::request($url, $params);
	}
}
