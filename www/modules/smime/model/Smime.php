<?php
/**
 * Group-Office
 *
 * Copyright Intermesh BV.
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @package GO.modules.smime.model
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart mdhart@intermesh.nl
 */

namespace GO\Smime\Model;

use GO\Base\Db\FindCriteria;
use GO\Base\Db\FindParams;
use GO\Base\Fs\Base;
use GO\Base\Fs\File;
use GO\Base\Util\HttpClient;
use \GO\Base\Util\StringHelper;

class Smime {


	private $key;
	private $accountId;

	public $name,
	$email,
	$version,
	$oscp;

	public function __construct($accountId) {
		//fetch account for permission check.
		$account = \GO\Email\Model\Account::model()->findByPk($accountId);
		$this->accountId = $account->id;
//		$this->key = Certificate::model()->findByPk($account->id); // could me multiple
	}

	/**
	 * Certificate for this account with longest valid date
	 * @params string $date when the decrypted text was sent in mysql format
	 * @return false|Certificate
	 */
	public function latestCert($date = null) {
		$criteria = FindCriteria::newInstance()->addCondition('account_id', $this->accountId);
		if(!empty($date)) {
			$criteria
				->addCondition('valid_until', $date, ' >')
				->addCondition('valid_since', $date, '<=');
		}
		$latest = Certificate::model()->find(FindParams::newInstance()
			->select('*')
			->criteria($criteria)
			->order(['valid_until','id'], ['DESC','DESC']));
		// todo: may need to fetch again when cert is revoked??
		return $latest->fetch();
	}

	/**
	 * @param $accountId int the EmailAccount id
	 * @param $certData string the PKS12 file data
	 * @param $passphrase string for the certificate
	 * @return bool when ok
	 * @throws \Exception
	 */
	public function import($certData, $passphrase) {
		if(empty($passphrase)) {//password may not be empty.
			throw new \Exception(\GO::t("Your SMIME key has no password. This is prohibited for security reasons!", "smime"));
		}
		openssl_pkcs12_read($certData, $certs, $passphrase);
		if (!empty($certs))
			throw new \Exception(\GO::t("Your SMIME key password matches your Group-Office password. This is prohibited for security reasons!", "smime"));

		$cert = Certificate::model()->findByPk($this->accountId);
		if (!$cert) {
			$cert = new Certificate();
			$cert->account_id = $this->accountId;
			$cert->always_sign = !empty($params['always_sign']); // move to different table
		}

		return true;
	}

	static function rootCertificates() {
		$certs = array();
		if (isset(\GO::config()->smime_root_cert_location) && file_exists(\GO::config()->smime_root_cert_location))
			$certs[] = \GO::config()->smime_root_cert_location;

		return $certs;
	}

	/**
	 * @param $arr array data readed from cenrtificate
	 * @return array list of plain email addresses
	 */
	static function readEmails($arr) {
		$emails = $arr['extensions']['subjectAltName'] ?? $arr['subject']['emailAddress'];
		$senderEmails = explode(',', $emails);

		$emails = [];
		foreach($senderEmails as $emailRaw) {
			if($email = strtolower(StringHelper::get_email_from_string($emailRaw))) {
				$emails[] = $email;
			}
		}
		return $emails;
	}

}