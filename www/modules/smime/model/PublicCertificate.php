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
 * @version $Id: example.php 7607 20120101Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */


namespace GO\Smime\Model;

use GO\Base\Db\FindCriteria;
use GO\Base\Db\FindParams;
use GO\Base\Fs\File;
use GO\Base\Util\HttpClient;
use GO\Base\Util\StringHelper;

/**
 * The PublicCertificate model
 *
 * @property int $user_id
 * @property string $email
 * @property string $cert
 */
class PublicCertificate extends \GO\Base\Db\ActiveRecord {

	private $valid;
	private $ocsp;
	private $ocspMsg;

	public function tableName() {
		return 'smi_certs';
	}

	static function import($certData, $emails) {

		if(strpos($certData, "-BEGIN CERTIFICATE-") === false) {
			throw new \Exception(go()->t("The certificate must be in PEM format", "legacy", "smime"));
		}

		$success = true;
		foreach($emails as $email) {
			$findParams = FindParams::newInstance()->single();
			$findParams->getCriteria()
				->addCondition('email', $email)
				->addCondition('user_id', \GO::user()->id);

			$cert = self::model()->find($findParams);
			if (!$cert) {
				$cert = new self();
				$cert->email = $email;
				$cert->user_id = \GO::user()->id;
			}
			$cert->cert = $certData;
			$success = $cert->save() && $success;
		}
		return $success;
	}

	static function fromEmail($accountId, $mailbox, $uid) {
		$account = \GO\Email\Model\Account::model()->findByPk($accountId);
		$imapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($account, $mailbox, $uid);

		$inputFile = \GO\Base\Fs\File::tempFile();
		if (!$imapMessage->saveToFile($inputFile->path())) {
			throw new \Exception("Could not fetch message from IMAP server");
		}

		$date = date('Y-m-d H:i:s', $imapMessage->udate);
		$cert = (new Smime($account->id))->latestCert($date);

		$data = $inputFile->getContents();
		if (strpos($data, "enveloped-data") || strpos($data, 'Encrypted Message')) {
			$inputFile = $cert->setPassword(\GO::session()->values['smime']['passwords'][$_REQUEST['account_id']])->decryptFile($inputFile);
			if(is_string($inputFile)) { // needs to be a File if decrypted succesfull
				throw new \Exception($inputFile);
			}
		}

		$pubCertFile = \GO\Base\Fs\File::tempFile();
		$valid = openssl_pkcs7_verify($inputFile->path(), null, $pubCertFile->path(), Smime::rootCertificates());
		$inputFile->delete();

		if (!$valid) {
			$err = '';
			while ($msg = openssl_error_string())
				$err .= $msg . "\n";
			throw new \Exception($err);
		}
		if (!$pubCertFile->exists()) {
			throw new \Exception('Certificate appears to be valid but could not get certificate from signature. SSL Error: ' . openssl_error_string());
		}
		$certData = $pubCertFile->getContents();

		if (empty($certData)){
			throw new \Exception('Certificate appears to be valid but could not get certificate from signature.');
		}

		$arr = openssl_x509_parse($certData);

		if(strpos($certData, "-BEGIN CERTIFICATE-") === false) {
			throw new \Exception(go()->t("The certificate must be in PEM format", "legacy", "smime"));
		}

		$emails = Smime::readEmails($arr);
		$success = true;
		foreach($emails as $email) {
			// save in DB
			$cert = self::model()->find(FindParams::newInstance()->criteria(FindCriteria::newInstance()
				->addCondition('email', $email)
				->addCondition('user_id', \GO::user()->id)
			)->single());
			if (!$cert) {
				$cert = new self();
				$cert->email = $email;
				$cert->user_id = \GO::user()->id;
			}
			$cert->cert = $certData;
			$success = $cert->save() && $success;
		}

		try {
			$cert->ocsp = $cert->checkOCSP($pubCertFile, $arr);
			$cert->ocspMsg = $cert->ocsp ? "OK" : \GO::t("The certificate has been revoked!", "smime");
		}catch(\Exception $e) {
			$cert->ocspMsg = $e->getMessage();
		}
		$pubCertFile->delete();

		// return latest
		$cert->valid = $valid;
		return $cert;

	}

	/**
	 * Return cert data in readable format
	 */
	public function parse() {
		$arr = openssl_x509_parse($this->cert);

		$issuer = [];
		foreach ($arr['issuer'] as $skey => $svalue) {
			if (is_array($svalue)) {
				foreach ($svalue as $sv) {
					$issuer[$skey] = $sv;
				}
			} else {
				$issuer[$skey] = $svalue;
			}
		}

		$result = [
			'emails' => Smime::readEmails($arr),
			'name' => $arr['name'],
			'hash' => $arr['hash'],
			'serialNumber' => $arr['serialNumber'],
			'version' => $arr['version'],
			'issuer' => $issuer,
			'validFrom' => date('Y-m-d H:i:s',$arr['validFrom_time_t']),
			'validTo' => date('Y-m-d H:i:s',$arr['validTo_time_t']),
			'type' => $arr['signatureTypeSN']
		];
		if(isset($this->valid)) {
			$result['valid'] = $this->valid;
		}
		if($this->ocsp) {
			$result['ocsp'] = $this->ocsp;
		}
		if($this->ocspMsg) {
			$result['ocspMsg'] = $this->ocspMsg;
		}
		return $result;
	}

	/**
	 * Online Certificate Status Protocol
	 * checks is the certificate is still valid or revoked
	 * @param $publicCertFile
	 * @param $arr array the readed cert data
	 * @return false|string
	 * @throws \Exception
	 */
	private function checkOCSP($publicCertFile, $arr) {

		if(!isset($arr['extensions']['authorityInfoAccess'])) {
			throw new \Exception( "No OCSP information found in certificate");
		}

		preg_match_all('/^.*URI:(.*)$/m', $arr['extensions']['authorityInfoAccess'], $matches, PREG_SET_ORDER);

		foreach($matches as $match) {
			if(stristr($match[0], 'issuer')) {
				$issuerURI = $match[1];
			}
			if(stristr($match[0], 'ocsp')) {
				$ocspURI = $match[1];
			}
		}

		if(!isset($ocspURI)) {
			throw new \Exception("No OCSP URI found : " . var_export($matches, true));
		}

		if(!isset($issuerURI)) {
			return "No issuer URI found";
		}

		//Get OSCP uri and Issuer uri

		//openssl x509 -in signer.pem -text
		//Authority Information Access:
		//                CA Issuers - URI:http://secure.globalsign.com/cacert/gspersonalsign1sha2g3ocsp.crt
		//                OCSP - URI:http://ocsp2.globalsign.com/gspersonalsign1sha2g3

		$issuerPemFile = false;
		if(!empty(\GO::config()->smime_root_cert_location)) {
			$issuerPemFile = $this->getIssuerPemFileFromLocal($publicCertFile);
		}

		if(!$issuerPemFile) {
			$issuerPemFile = $this->getIssuerPemFileFromURI($issuerURI);
		}

		//Do OCSP

		$cmd = "openssl ocsp -issuer ". escapeshellarg($issuerPemFile->path()) ." -cert " . escapeshellarg($publicCertFile->path())." -url ". escapeshellarg($ocspURI) ." -CAfile ". escapeshellarg($issuerPemFile->path());
		go()->debug("Running: $cmd");
		exec ($cmd, $output,$ret);

		if($ret != 0) {
			throw new \Exception( "OSCP request failed");
		}

		//Response:
		//OSCP:	/tmp/groupoffice/1/15795372755e25d37b05b00: good
		//This Update: Jan 20 16:21:15 2020 GMT
		//Next Update: Jan 24 16:21:15 2020 GMT
		return stristr($output[0], 'good');
	}

	private function getIssuerPemFileFromLocal(File $cert) {
		exec ("openssl x509 -noout -in " . escapeshellarg($cert->path()) ." -issuer_hash", $output,$ret);
		if($ret != 0) {
			throw new \Exception( "OSCP request failed");
		}
		$hash = $output[0];

		$issuerPemFile = new File(\GO::config()->smime_root_cert_location . '/' . $hash . '.0');


		if(!$issuerPemFile->exists()) {
			\GO::debug("Local cert " . $issuerPemFile->path() ." does not exist.");
			return false;
		}

		\GO::debug("Checking local cert " . $issuerPemFile->path());

		return $issuerPemFile;
	}

	private function getIssuerPemFileFromURI($issuerURI) {

		$issuerDerFile = File::tempFile('issuer.der');
		$issuerPemFile = File::tempFile('issuer.pem');
		$c = new HttpClient();
		if(!$c->downloadFile($issuerURI, $issuerDerFile))
		{
			throw new \Exception( "Failed to download issuer certificate");
		}

		//Convert DER to pem
		//openssl x509 -inform DER -in issuer.crt -out issuer.pem

		$cmd = "openssl x509 -inform DER -in ". escapeshellarg($issuerDerFile->path()) ." -out " .escapeshellarg($issuerPemFile->path());
		go()->debug("Running: $cmd");
		exec($cmd, $output, $ret);

		if($ret != 0) {
			throw new \Exception( "Failed to convert issuer certificate");
		}

		if($ret != 0) {
			throw new \Exception( "OSCP request failed");
		}

		return $issuerPemFile;
	}

}
