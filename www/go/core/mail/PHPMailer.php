<?php
namespace go\core\mail;


use PHPMailer\PHPMailer\Exception;

/**
 * PHPMailer extension
 *
 * Extends PHPMailer with S/MIME signing and encryption
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Intermesh BV
 */
class PHPMailer extends \PHPMailer\PHPMailer\PHPMailer {
	/**
	 * @var string
	 */
	private $smimeCertificate;
	/**
	 * @var string
	 */
	private $smimePrivateKey;
	/**
	 * @var array
	 */
	private $smimeEncryptRecipientCertificates;

	/**
	 * @var string|null
	 */
	private $smimeExtraCertsFile;

	private $smimePassword;


	public function postSend()
	{
		if($this->isSmimeSinged()) {
			$this->doSmimeSign();
		}

		if($this->isSmimeEncrypted()) {
			$this->doSmimeEncrypt();
		}
		return parent::postSend();
	}

	private function doSmimeSign() {
		$file = tempnam(sys_get_temp_dir(), 'srcsign');
		$signed = tempnam(sys_get_temp_dir(), 'mailsign');
		file_put_contents($file, $this->MIMEHeader . static::$LE . static::$LE . $this->MIMEBody);

		//Workaround for PHP bug https://bugs.php.net/bug.php?id=69197
		if (empty($this->sign_extracerts_file)) {
			$sign = @openssl_pkcs7_sign(
				$file,
				$signed,
				$this->smimeCertificate,
				[$this->smimePrivateKey, $this->smimePassword],
				[]
			);
		} else {
			$sign = @openssl_pkcs7_sign(
				$file,
				$signed,
				$this->smimeCertificate,
				[$this->smimePrivateKey, $this->smimePassword],
				[],
				PKCS7_DETACHED,
				$this->smimeExtraCertsFile
			);
		}

		unlink($file);
		if ($sign) {
			$body = file_get_contents($signed);
			unlink($signed);
			//The message returned by openssl contains both headers and body, so need to split them up
			$parts = explode("\n\n", $body, 2);

			preg_match("/Content-Type:.*/", $parts[0], $matches);

			$this->MIMEHeader = preg_replace("/Content-Type:(.*)/", $matches[0], $this->MIMEHeader);
			$this->MIMEBody = $parts[1];
		} else {
			if(file_exists($signed)) {
				unlink($signed);
			}
			throw new Exception($this->lang('signing') . openssl_error_string());
		}
	}


	private function doSmimeEncrypt() {
		$file = tempnam(sys_get_temp_dir(), 'srcencrypt');
		$encrypted = tempnam(sys_get_temp_dir(), 'srcencrypt');
		file_put_contents($file, $this->MIMEHeader . static::$LE . static::$LE . $this->MIMEBody);


		$encrypt = openssl_pkcs7_encrypt($file, $encrypted, $this->smimeEncryptRecipientCertificates, [],0, OPENSSL_CIPHER_AES_256_CBC);

		unlink($file);
		if ($encrypt) {
			$body = file_get_contents($encrypted);
			unlink($encrypted);
			//The message returned by openssl contains both headers and body, so need to split them up
			$parts = explode("\n\n", $body, 2);


			preg_match("/Content-Type:.*/", $parts[0], $matches);
			//fix header name bug in php
			$matches[0] = str_replace('application/x-pkcs7','application/pkcs7', $matches[0]);
			$this->MIMEHeader = preg_replace("/Content-Type:(.*)/", $matches[0], $this->MIMEHeader);
			$this->MIMEHeader = preg_replace("/Content-Transfer-Encoding:(.*)/", "Content-Transfer-Encoding: base64", $this->MIMEHeader);

			$this->MIMEBody = $parts[1];
		} else {
			if(file_exists($encrypted)) {
				unlink($encrypted);
			}
			throw new Exception($this->lang('signing') . openssl_error_string());
		}
	}

	/**
	 * Sign the message
	 *
	 * @param string $certificate The X.509 certificate used to digitally sign input_filename.
	 * @param string $privateKey the private key corresponding to certificate.
	 * @return void
	 */

	public function smimeSign(string $certificate, string $privateKey, string $password, string $extraCertsFile = null) {
		$this->smimeCertificate = $certificate;
		$this->smimePrivateKey = $privateKey;
		$this->smimeExtraCertsFile = $extraCertsFile;
		$this->smimePassword = $password;
	}

	/**
	 * Check if message is signed
	 *
	 * @return bool
	 */
	public function isSmimeSinged() : bool {
		return isset($this->smimeCertificate);
	}

	/**
	 * Get the X.509 certificate used to digitally sign
	 * @return string|null
	 */
	public function getSmimeCertificate() : ?string {
		return $this->smimeCertificate;
	}

	/**
	 * The private key corresponding to {@see getSmimeCertificate()}.
	 * @return string|null
	 */
	public function getSmimePrivateKey() : ?string {
		return $this->smimePrivateKey;
	}

	/**
	 * Encrypt the message
	 *
	 * @param array $recipientCertificates An array of X.509 certificates of the recipients.
	 * @return void
	 */
	public function smimeEncrypt(array $recipientCertificates) {
		$this->smimeEncryptRecipientCertificates = $recipientCertificates;
	}

	/**
	 * Check if the mail is encrypted
	 *
	 * @return bool
	 */
	public function isSmimeEncrypted() : bool {
		return isset($this->smimeEncryptRecipientCertificates);
	}

	/**
	 * X.509 certificates of the recipients.
	 *
	 * @return array
	 */
	public function getSmimeEncryptRecipientCertificates() : array {
		return $this->smimeEncryptRecipientCertificates;
	}


	public function getSMTPInstance()
	{
		return new PHPMailerSMTP();
	}
}