<?php
namespace go\core\mail;


use GO\Base\Mail\MimeDecode;
use go\core\util\StringUtil;
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
	// The php validator method will not validate icloud rsvp addresses
	// e.g. "2_haytgnjxge3dsnjuhaytgnjxgh3b6mqy3inkhor6edr7cmefu6w7s2fptx4azi7iyoxpyp7lrquoi@imip.me.com"
	public static $validator = 'html5';
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
		if (empty($this->smimeExtraCertsFile)) {
			$sign = @openssl_pkcs7_sign(
				$file,
				$signed,
				$this->smimeCertificate,
				[$this->smimePrivateKey, $this->smimePassword],
				$this->smimeHeaders()
			);
		} else {
			$sign = @openssl_pkcs7_sign(
				$file,
				$signed,
				$this->smimeCertificate,
				[$this->smimePrivateKey, $this->smimePassword],
				$this->smimeHeaders(),
				PKCS7_DETACHED,
				$this->smimeExtraCertsFile
			);
		}

		unlink($file);
		if ($sign) {
			$body = file_get_contents($signed);
			unlink($signed);

			$body = StringUtil::normalizeCrlf($body, static::$LE);

			//The message returned by openssl contains both headers and body, so need to split them up
			$parts = explode(static::$LE . static::$LE, $body, 2);

			$this->MIMEHeader = $parts[0]. static::$LE;
			$this->MIMEBody = $parts[1];
		} else {
			if(file_exists($signed)) {
				unlink($signed);
			}
			throw new Exception($this->lang('signing') . openssl_error_string());
		}
	}


	private function smimeHeaders() {

		$unfold = preg_replace("/".static::$LE."(\s+)/", '$1', $this->MIMEHeader);
		$lines = explode(static::$LE, trim($unfold));

		$headers = [];

		foreach($lines as $line) {
			$name = substr($line, 0, $pos = strpos($line, ':'));
			$headers[$name] = trim(substr($line, $pos + 1));
		}

		unset($headers['Content-Transfer-Encoding'], $headers['Content-Type'], $headers['MIME-Version']);

		return $headers;
	}


	private function doSmimeEncrypt() {
		$file = tempnam(sys_get_temp_dir(), 'srcencrypt');
		$encrypted = tempnam(sys_get_temp_dir(), 'srcencrypt');
		file_put_contents($file, $this->MIMEHeader . static::$LE . static::$LE . $this->MIMEBody);

		$encrypt = openssl_pkcs7_encrypt(
			$file,
			$encrypted,
			$this->smimeEncryptRecipientCertificates,
			$this->smimeHeaders(),
			0,
			OPENSSL_CIPHER_AES_256_CBC);

		unlink($file);
		if ($encrypt) {
			$body = file_get_contents($encrypted);
			unlink($encrypted);

			$body = StringUtil::normalizeCrlf($body, static::$LE);

			//The message returned by openssl contains both headers and body, so need to split them up
			$parts = explode(static::$LE.static::$LE, $body, 2);
			//fix header name bug in php
			$this->MIMEHeader = str_replace('application/x-pkcs7','application/pkcs7', $parts[0]);
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

	public function smimeSign(string $certificate, string $privateKey, string $password, string|null $extraCertsFile = null) {
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

	/**
	 * Returns the whole MIME message.
	 * Includes complete headers and body.
	 * Only valid post preSend().
	 *
	 * @see PHPMailer::preSend()
	 *
	 * @return string
	 */
	public function getSentMIMEMessage()
	{
		$header = $this->MIMEHeader;

		// PHPMailer leaves BCC out of headers when using SMTP. We want this header for our sent items
		// source. So we append it here.
		if (
			(
				'sendmail' !== $this->Mailer && 'qmail' !== $this->Mailer && 'mail' !== $this->Mailer
			)
			&& count($this->bcc) > 0
		) {
			$header .= $this->addrAppend('Bcc', $this->bcc);
		}

		return static::stripTrailingWSP($header . $this->mailHeader) .
			static::$LE . static::$LE . $this->MIMEBody;
	}


	/**
	 * Abort sending of the message if one recipient fails.
	 *
	 * @var bool
	 */
	public $abortOnRecipientError = true;


	/**
	 * Send mail via SMTP.
	 * Returns false if there is a bad MAIL FROM, RCPT, or DATA input.
	 *
	 * @see PHPMailer::setSMTPInstance() to use a different class.
	 *
	 * @uses \PHPMailer\PHPMailer\SMTP
	 *
	 * @param string $header The message headers
	 * @param string $body   The message body
	 *
	 * @throws Exception
	 *
	 * @return bool
	 */
	protected function smtpSend($header, $body): bool
	{
		$header = static::stripTrailingWSP($header) . static::$LE . static::$LE;
		$bad_rcpt = [];
		if (!$this->smtpConnect($this->SMTPOptions)) {
			throw new Exception($this->lang('smtp_connect_failed'), self::STOP_CRITICAL);
		}
		//Sender already validated in preSend()
		if ('' === $this->Sender) {
			$smtp_from = $this->From;
		} else {
			$smtp_from = $this->Sender;
		}
		if (!$this->smtp->mail($smtp_from)) {
			$this->setError($this->lang('from_failed') . $smtp_from . ' : ' . implode(',', $this->smtp->getError()));
			throw new Exception($this->ErrorInfo, self::STOP_CRITICAL);
		}

		$callbacks = [];
		//Attempt to send to all recipients
		foreach ([$this->to, $this->cc, $this->bcc] as $togroup) {
			foreach ($togroup as $to) {
				if (!$this->smtp->recipient($to[0], $this->dsn)) {
					$error = $this->smtp->getError();
					$bad_rcpt[] = ['to' => $to[0], 'error' => $error['detail']];
					$isSent = false;
				} else {
					$isSent = true;
				}

				$callbacks[] = ['issent' => $isSent, 'to' => $to[0], 'name' => $to[1]];
			}
		}

		if($this->abortOnRecipientError) {
			$shouldSendData = count($bad_rcpt) === 0;
		} else {
			$shouldSendData = count($this->all_recipients) > count($bad_rcpt);
		}
			//Only send the DATA command if we have viable recipients
		if ($shouldSendData && !$this->smtp->data($header . $body)) {
			throw new Exception($this->lang('data_not_accepted'), self::STOP_CRITICAL);
		}

		$smtp_transaction_id = $this->smtp->getLastTransactionID();

		if ($this->SMTPKeepAlive) {
			$this->smtp->reset();
		} else {
			$this->smtp->quit();
			$this->smtp->close();
		}

		foreach ($callbacks as $cb) {
			$this->doCallback(
				$cb['issent'],
				[[$cb['to'], $cb['name']]],
				[],
				[],
				$this->Subject,
				$body,
				$this->From,
				['smtp_transaction_id' => $smtp_transaction_id]
			);
		}

		//Create error message for any bad addresses
		if (count($bad_rcpt) > 0) {
			$errstr = '';
			foreach ($bad_rcpt as $bad) {
				$errstr .= $bad['to'] . ': ' . $bad['error'];
			}
			throw new Exception($this->lang('recipients_failed') . $errstr, self::STOP_CONTINUE);
		}

		return true;
	}

	/**
	 * Send raw mime message
	 *
	 * @param string $mime
	 * @return bool
	 * @throws Exception
	 */
	public function sendMime(string $mime) : bool {
		$splitter = static::$LE . static::$LE;
		$headerEndPos = strpos($mime, $splitter);
		if($headerEndPos === false) {
			throw new \Exception("Can't find headers");
		}

		$this->MIMEHeader = substr($mime, 0, $headerEndPos);
		$this->MIMEBody = trim(substr($mime, $headerEndPos));


		$decoder = new MimeDecode($mime);
		$structure = $decoder->decode(array(
			'include_bodies'=>false,
			'decode_headers'=>true,
			'decode_bodies'=>false,
		));

		if(!$structure)
			throw new \Exception("Could not decode mime data");


		$to = isset($structure->headers['to']) && strpos($structure->headers['to'],'undisclosed')===false ? $structure->headers['to'] : '';
		$cc = isset($structure->headers['cc']) && strpos($structure->headers['cc'],'undisclosed')===false ? $structure->headers['cc'] : '';
		$bcc = isset($structure->headers['bcc']) && strpos($structure->headers['bcc'],'undisclosed')===false ? $structure->headers['bcc'] : '';

		//workaround activesync problem where 'mailto:' is included in the mail address.
		$to = str_replace('mailto:','', $to);
		$cc = str_replace('mailto:','', $cc);
		$bcc = str_replace('mailto:','', $bcc);

		$toList = new AddressList($to);
		foreach($toList->toArray() as $a) {
			$this->addAddress($a->getEmail(), $a->getName());
		}

		$bccList = new AddressList($bcc);
		foreach($bccList->toArray() as $a) {
			$this->addBCC($a->getEmail(), $a->getName());
		}
		$ccList = new AddressList($cc);
		foreach($ccList->toArray() as $a) {
			$this->addCC($a->getEmail(), $a->getName());
		}


		$this->Subject = $structure->headers['subject'] ?? "";


		if(isset($structure->headers['from'])) {

			$fromList = new AddressList(str_replace('mailto:','',$structure->headers['from']));
			if(isset($fromList[0])){
				$from = $fromList[0];
				try {
					$this->From = $from->getEmail();
					$this->FromName = $from->getName();

				} catch(Exception $e)  {
					\GO::debug('Failed to add from address: '.$e);
				}
			}
		}

		unset($mime);

		try {
			$this->error_count = 0; //Reset errors
			$this->mailHeader = '';

			return $this->postSend();
		} catch (Exception $exc) {
			$this->mailHeader = '';
			$this->setError($exc->getMessage());
			if ($this->exceptions) {
				throw $exc;
			}

			return false;
		}
	}

}