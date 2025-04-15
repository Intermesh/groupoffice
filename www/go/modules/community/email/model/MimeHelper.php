<?php
/**
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\email\model;


class MimeHelper
{
	/** @var int The maximum line length allowed by RFC 2822 section 2.1.1. */
	const MAX_LINE_LENGTH = 998;
	const LINE_LENGTH = 76;
	const EOL = "\r\n";
	const DATE_FORMAT = 'D, j M Y H:i:s O';

	const PROD = 'GroupOffice {VERSION}';

	private $mail;
	public $date;
	/** @var string the encoded MIME */
	public $encoded;
	public $hasAttachments = false;
	public $uid;

	public function __construct(Email $mail) {
		$this->mail = $mail;
		$d = $mail->date();
		$this->date = !isset($d) ? date(self::DATE_FORMAT) : date(self::DATE_FORMAT, $d);
	}

	/**
	 * To RFC882 string
	 */
	static function encode(Email $mail)
	{
		$d = $mail->date();
		$headerStr = self::encodeHeaders((object)[
			'From' => $mail->getFrom(),
			'To' => $mail->getTo(),
			'Cc' => $mail->getCc(),
			'MessageID' => $mail->messageId[0],
			'Subject' => $mail->subject,
			'Date' => !isset($d) ? date(self::DATE_FORMAT) : date(self::DATE_FORMAT, $d),
			'ContentType' => $mail->getBodyStructure()->type
		]);
		$body = new EmailBodyPart($mail->getBodyStructure());
		return $headerStr . self::encodeBody($body);
	}

	private static function headerLine($name, $value)
	{
		return $name . ': ' . $value . self::EOL;
	}
	/**
	 * Create address format headers.
	 *
	 * @param string $type
	 * @param array  $addr An array of recipients,
	 * @return string
	 */
	private static function headerAddr($type, $addrs)
	{
		$addresses = [];
		foreach ($addrs as $addr) {
			$addresses[] = empty($addr['name']) ? self::secureHeader($addr['email']) :
				self::encodeHeader(self::secureHeader($addr['name']), 'phrase').' <'.self::secureHeader($addr['email']).'>';
		}

		return self::headerLine($type, implode(', ', $addresses));
	}

	private static function encodeHeaders($headers) {
		$result = self::headerAddr('From', $headers->From);

		// To be created automatically by mail()
		if (!empty($headers->To)) {
			$result .= self::headerAddr('To', $headers->To);
		} elseif (empty($headers->Cc)) {
			$result .= self::headerLine('To', 'undisclosed-recipients:;');
		}

		if (!empty($headers->Cc)) {
			$result .= self::headerAddr('Cc', $headers->Cc);
		}
		if (!empty($headers->ReplyTo)) {
			$result .= self::headerAddr('Reply-To', $headers->ReplyTo);
		}

		$result .= self::headerLine('Subject', self::encodeHeader(self::secureHeader($headers->Subject)));
		$result .= self::headerLine('Date', $headers->Date);

		// in reply to

		// references

		// Only allow a custom message ID if it conforms to RFC 5322 section 3.6.4
		// https://tools.ietf.org/html/rfc5322#section-3.6.4
		if (isset($headers->MessageID[0])) {
			$result .= self::headerLine('Message-ID', $headers->MessageID[0]);
		} else {
			throw new \Exception('Need proper MessageID to encode header '. $headers->MessageID[0]);
			//$lastMessageID = sprintf('<%s@%s>', self::uniqueid, server()->hostname());
		}

		if (isset($headers->Priority)) {
			$result .= self::headerLine('X-Priority', $headers->Priority);
		}
		$result .= self::headerLine('X-Mailer', str_replace('{VERSION}', go()->getVersion(),self::PROD));


		if (isset($headers->ConfirmReadingTo)) {
			$result .= self::headerLine('Disposition-Notification-To', '<' . $headers->ConfirmReadingTo . '>');
		}

		// Add custom headers
		if (isset($headers->CustomHeaders)) {
			foreach ($headers->CustomHeaders as $header) {
				$result .= self::headerLine(trim($header[0]), self::encodeHeader(trim($header[1])));
			}
		}
		//if (!self::sign_key_file) {
		$result .= self::headerLine('MIME-Version', '1.0');
		//	$result .= $this->headerLine('Content-Type', $headers->ContentType);
		//$result .= $this->getMailMIME();
		//}

		return $result;
	}
	/**
	 * Strip newlines to prevent header injection.
	 */
	private static function secureHeader($str) {
		return trim(str_replace(["\r", "\n"], '', $str));
	}

	private static function encodeHeader($text, $position = 'text') {
		$matchcount = 0;
		switch (strtolower($position)) {
			case 'phrase':
				if (!preg_match('/[\200-\377]/', $text)) {
					// Can't use addslashes as we don't know the value of magic_quotes_sybase
					$encoded = addcslashes($text, "\0..\37\177\\\"");
					if (($text === $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $text)) {
						return $encoded;
					}

					return "\"$encoded\"";
				}
				$matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $text, $matches);
				break;
			case 'comment':
				$matchcount = preg_match_all('/[()"]/', $text, $matches);
			//fallthrough
			case 'text':
			default:
				$matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $text, $matches);
				break;
		}

		$charset = self::has8bitChars($text) ? 'iso-8859-1' : 'us-ascii'; // or 'utf-8'? (5,8 or 10 characters overheat)
		$overhead = 8 + strlen($charset);

		$maxlen = static::MAX_LINE_LENGTH - $overhead;

		if ($matchcount > strlen($text) / 3) {
			// More than 1/3 of the content needs encoding, use B-encode.
			$encoding = 'B';
		} elseif ($matchcount > 0) {
			// Less than 1/3 of the content needs encoding, use Q-encode.
			$encoding = 'Q';
		} elseif (strlen($text) > $maxlen) {
			// No encoding needed, but value exceeds max line length, use Q-encode to prevent corruption.
			$encoding = 'Q';
		} else {
			// No reformatting needed
			return $text;
		}

		// all the above is to decide $encoding = Q or B
		return \mb_encode_mimeheader($text, \mb_language(), $encoding);
	}

	private static function has8bitChars($text) {
		return (bool)preg_match('/[\x80-\xFF]/', $text);
	}

	public static function hasLineLongerThanMax($str)
	{
		return (bool) preg_match('/^(.{' . (self::MAX_LINE_LENGTH + strlen(self::EOL)) . ',})/m', $str);
	}

	public static function encodeBody(EmailBodyPart $part)
	{
		$body = '';

		// if ($this->sign_key_file) {
		// 	$body .= $this->getMailMIME() . static::$LE;
		// }

		$encoding = $part->encoding();
		if($part->isInline()) {
			$content = $this->mail->getBodyValues()->{$part->partId}->value;
			if ($encoding === '8bit' && !self::has8bitChars($content)) { // Can we do a 7-bit downgrade?
				$encoding  = '7bit';
				$part->charset = 'us-ascii'; // ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
			}
			// If lines are too long, and we're not already using an encoding that will shorten them,
			// change to quoted-printable transfer encoding for this body part
			if ($encoding !== 'base64' && static::hasLineLongerThanMax($content)) {
				$encoding = 'quoted-printable';
			}
		} elseif($part->blobId) {
			if(!$part->isA('text')) {
				$encoding = 'base64';
			}
			$this->hasAttachments = true;
			$file = server()->blob()->fetch($part->blobId);
			$content = stream_get_contents($file->content()); // todo: stream reader
		} elseif(!$part->isMultipart()) {
			throw new \Exception('part is missing content '. $part->partId);
		}

		$body .= $part->buildHeaders($encoding) .self::EOL;
		if($part->isMultipart()) {
			// only visible to clients that cant parse MIME structures
			$body .= 'This is a multi-part message in MIME format.' .self::EOL;
			foreach($part->subParts as $subpart) {
				$body .= self::EOL.'--'.$part->boundary() .self::EOL;
				$body .= $this->encodeBody($subpart) .self::EOL;
			}
			$body .= '--'.$part->boundary() .'--' .self::EOL;
		} else {
			//$body .= \iconv_mime_encode()
			$body .= self::encodeString($content, $encoding);
		}

		// if ($this->sign_key_file) {
		// 	try {
		// 			if (!defined('PKCS7_TEXT')) {
		// 				throw new Exception($this->lang('extension_missing') . 'openssl');
		// 			}

		// 			$file = tempnam(sys_get_temp_dir(), 'srcsign');
		// 			$signed = tempnam(sys_get_temp_dir(), 'mailsign');
		// 			file_put_contents($file, $body);

		// 			// Workaround for PHP bug https://bugs.php.net/bug.php?id=69197
		// 			if (empty($this->sign_extracerts_file)) {
		// 				$sign = @openssl_pkcs7_sign(
		// 					$file,
		// 					$signed,
		// 					'file://' . realpath($this->sign_cert_file),
		// 					['file://' . realpath($this->sign_key_file), $this->sign_key_pass],
		// 					[]
		// 				);
		// 			} else {
		// 				$sign = @openssl_pkcs7_sign(
		// 					$file,
		// 					$signed,
		// 					'file://' . realpath($this->sign_cert_file),
		// 					['file://' . realpath($this->sign_key_file), $this->sign_key_pass],
		// 					[],
		// 					PKCS7_DETACHED,
		// 					$this->sign_extracerts_file
		// 				);
		// 			}

		// 			@unlink($file);
		// 			if ($sign) {
		// 				$body = file_get_contents($signed);
		// 				@unlink($signed);
		// 				//The message returned by openssl contains both headers and body, so need to split them up
		// 				$parts = explode("\n\n", $body, 2);
		// 				$this->MIMEHeader .= $parts[0] . static::$LE . static::$LE;
		// 				$body = $parts[1];
		// 			} else {
		// 				@unlink($signed);
		// 				throw new Exception($this->lang('signing') . openssl_error_string());
		// 			}
		// 	} catch (Exception $exc) {
		// 			$body = '';
		// 			if ($this->exceptions) {
		// 				throw $exc;
		// 			}
		// 	}
		// }

		return $body;
	}

	/**
	 * Encode a string in requested format.
	 * Returns an empty string on failure.
	 *
	 * @param string $str      The text to encode
	 * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
	 *
	 * @throws \Exception
	 *
	 * @return string
	 */
	public static function encodeString($str, $encoding = 'base64') // todo: the streaming thing in BodyPart getStreamContent
	{
		switch ($encoding) {
			case 'base64':
				return chunk_split(base64_encode($str), self::LINE_LENGTH,self::EOL);
			case '7bit':
			case '8bit': return static::normalizeBreaks($str);
			case 'binary': return $str;
			case 'quoted-printable': return static::normalizeBreaks(quoted_printable_encode($str));
			default:
				throw new \Exception('encoding' . $encoding);
		}
	}

	public static function normalizeBreaks($text)
	{
		$text = str_replace(["\r\n", "\r"], "\n", $text);
		return str_replace("\n", self::EOL, $text);
	}
}