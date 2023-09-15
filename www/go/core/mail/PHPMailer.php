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

	public $AllowEmpty = true;

	protected $exceptions = true;

	public function preSend()
	{
		if (
			'smtp' === $this->Mailer
			|| ('mail' === $this->Mailer && (\PHP_VERSION_ID >= 80000 || stripos(PHP_OS, 'WIN') === 0))
		) {
			//SMTP mandates RFC-compliant line endings
			//and it's also used with mail() on Windows
			static::setLE(self::CRLF);
		} else {
			//Maintain backward compatibility with legacy Linux command line mailers
			static::setLE(PHP_EOL);
		}
		//Check for buggy PHP versions that add a header with an incorrect line break
		if (
			'mail' === $this->Mailer
			&& ((\PHP_VERSION_ID >= 70000 && \PHP_VERSION_ID < 70017)
				|| (\PHP_VERSION_ID >= 70100 && \PHP_VERSION_ID < 70103))
			&& ini_get('mail.add_x_header') === '1'
			&& stripos(PHP_OS, 'WIN') === 0
		) {
			trigger_error($this->lang('buggy_php'), E_USER_WARNING);
		}

		try {
			$this->error_count = 0; //Reset errors
			$this->mailHeader = '';

			//Dequeue recipient and Reply-To addresses with IDN
			foreach (array_merge($this->RecipientsQueue, $this->ReplyToQueue) as $params) {
				$params[1] = $this->punyencodeAddress($params[1]);
				call_user_func_array([$this, 'addAnAddress'], $params);
			}
//			if (count($this->to) + count($this->cc) + count($this->bcc) < 1) {
//				throw new Exception($this->lang('provide_address'), self::STOP_CRITICAL);
//			}

			//Validate From, Sender, and ConfirmReadingTo addresses
			foreach (['From', 'Sender', 'ConfirmReadingTo'] as $address_kind) {
				$this->{$address_kind} = trim($this->{$address_kind});
				if (empty($this->{$address_kind})) {
					continue;
				}
				$this->{$address_kind} = $this->punyencodeAddress($this->{$address_kind});
				if (!static::validateAddress($this->{$address_kind})) {
					$error_message = sprintf(
						'%s (%s): %s',
						$this->lang('invalid_address'),
						$address_kind,
						$this->{$address_kind}
					);
					$this->setError($error_message);
					$this->edebug($error_message);
					if ($this->exceptions) {
						throw new Exception($error_message);
					}

					return false;
				}
			}

			//Set whether the message is multipart/alternative
			if ($this->alternativeExists()) {
				$this->ContentType = static::CONTENT_TYPE_MULTIPART_ALTERNATIVE;
			}

			$this->setMessageType();
			//Refuse to send an empty message unless we are specifically allowing it
			if (!$this->AllowEmpty && empty($this->Body)) {
				throw new Exception($this->lang('empty_message'), self::STOP_CRITICAL);
			}

			//Trim subject consistently
			$this->Subject = trim($this->Subject);
			//Create body before headers in case body makes changes to headers (e.g. altering transfer encoding)
			$this->MIMEHeader = '';
			$this->MIMEBody = $this->createBody();
			//createBody may have added some headers, so retain them
			$tempheaders = $this->MIMEHeader;
			$this->MIMEHeader = $this->createHeader();
			$this->MIMEHeader .= $tempheaders;

			//To capture the complete message when using mail(), create
			//an extra header list which createHeader() doesn't fold in
			if ('mail' === $this->Mailer) {
				if (count($this->to) > 0) {
					$this->mailHeader .= $this->addrAppend('To', $this->to);
				} else {
					$this->mailHeader .= $this->headerLine('To', 'undisclosed-recipients:;');
				}
				$this->mailHeader .= $this->headerLine(
					'Subject',
					$this->encodeHeader($this->secureHeader($this->Subject))
				);
			}

			//Sign with DKIM if enabled
			if (
				!empty($this->DKIM_domain)
				&& !empty($this->DKIM_selector)
				&& (!empty($this->DKIM_private_string)
					|| (!empty($this->DKIM_private)
						&& static::isPermittedPath($this->DKIM_private)
						&& file_exists($this->DKIM_private)
					)
				)
			) {
				$header_dkim = $this->DKIM_Add(
					$this->MIMEHeader . $this->mailHeader,
					$this->encodeHeader($this->secureHeader($this->Subject)),
					$this->MIMEBody
				);
				$this->MIMEHeader = static::stripTrailingWSP($this->MIMEHeader) . static::$LE .
					static::normalizeBreaks($header_dkim) . static::$LE;
			}

			return true;
		} catch (Exception $exc) {
			$this->setError($exc->getMessage());
			if ($this->exceptions) {
				throw $exc;
			}

			return false;
		}
	}

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

	/**
	 * Get the message MIME type headers.
	 *
	 * @return string
	 */
	public function getMailMIME()
	{
		//override this for the "Remove attachments" feature. It will attach inline texts and
		//we need the content type of the message to be "multipart/mixed". The only way PHPMailer will
		// set this is by settings message type to "inline_attach" temporarily.
		$org = $this->message_type;
		if($this->inlineTextExists()) {
			$this->message_type = 'inline_attach';
		}
		$result = parent::getMailMIME();
		$this->message_type = $org;
		return $result;
	}


	private function inlineTextExists(): bool
	{
		foreach ($this->attachment as $attachment) {
			// Check for empty content type and ID as well so we set multipart/mixed to display inline text
			if ('inline' === $attachment[6] && empty($attachment[7]) && str_contains($attachment[4], 'text')) {
				return true;
			}
		}

		return false;
	}

}