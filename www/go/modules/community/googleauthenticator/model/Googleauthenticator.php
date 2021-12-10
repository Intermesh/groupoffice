<?php

namespace go\modules\community\googleauthenticator\model;

use Exception;
use go\core\orm\Mapping;
use go\core\fs\File;
use go\core\orm\Property;

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'QRcode.php';

class Googleauthenticator extends Property {
		
	public $userId;
	protected $secret;
	public $createdAt;
	
	private $verify = false;
	private $requestSecret = false;

	/**
	 * We only publish the secret when it was just created
	 * @var bool 
	 */
	private $publish = false;
	
	protected $codeLength = 6;
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable("googleauth_secret");
	}
	
	public function getSecret() {
		return $this->secret;
	}
	
	public function setRequestSecret($value){
		$this->requestSecret = $value;
	}
	
	public function setVerify($code){
		$this->verify = $code;
	}
	
	public function setSecret($secret) {
		$this->secret = $secret;
	}
		
	protected function internalValidate() {
		
		// When saving the new secret, the code needs to be verified first
		if(!empty($this->verify) && !$this->verifyCode($this->verify)){
			$this->setValidationError('verify', \go\core\validate\ErrorCode::INVALID_INPUT, "The verify code is not correct.");
		}
		
		return parent::internalValidate();
	}
	
	protected function internalSave(): bool
	{
		
		if(empty($this->secret)) {
			$this->secret = $this->createSecret();
		}
		
		// Don't actually save when only the secret is requested
		if($this->requestSecret){
			return true;
		}
				
		return parent::internalSave();
	}
	
	/**
	 * Create new secret.
	 * 16 characters, randomly chosen from the allowed base32 characters.
	 *
	 * @param int $secretLength
	 *
	 * @return string
	 */
	private function createSecret($secretLength = 16) {
		$this->publish = true;
		$validChars = $this->_getBase32LookupTable();

		// Valid secret lengths are 80 to 640 bits
		if ($secretLength < 16 || $secretLength > 128) {
			throw new Exception('Bad secret length');
		}
		$secret = '';
		$rnd = false;
		if (function_exists('random_bytes')) {
			$rnd = random_bytes($secretLength);
		} elseif (function_exists('openssl_random_pseudo_bytes')) {
			$rnd = openssl_random_pseudo_bytes($secretLength, $cryptoStrong);
			if (!$cryptoStrong) {
				$rnd = false;
			}
		}
		if ($rnd !== false) {
			for ($i = 0; $i < $secretLength; ++$i) {
				$secret .= $validChars[ord($rnd[$i]) & 31];
			}
		} else {
			throw new Exception('No source of secure random');
		}

		return $secret;
	}
	
	public function getIsEnabled() {
		return isset($this->secret)&& isset($this->createdAt);
	}

	/**
	 * Calculate the code, with given secret and point in time.
	 *
	 * @param string   $secret
	 * @param int|null $timeSlice
	 *
	 * @return string
	 */
	public function getCode($secret, $timeSlice = null) {
		
		if(!$this->publish) {
			return null;
		}
		
		return $this->internalGetCode($secret, $timeSlice);
		
	}
	
	private function internalGetCode($secret, $timeSlice = null) {
		if ($timeSlice === null) {
			$timeSlice = floor(time() / 30);
		}

		$secretkey = $this->_base32Decode($secret);

		// Pack time into binary string
		$time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
		// Hash it with users secret key
		$hm = hash_hmac('SHA1', $time, $secretkey, true);
		// Use last nipple of result as index/offset
		$offset = ord(substr($hm, -1)) & 0x0F;
		// grab 4 bytes of the result
		$hashpart = substr($hm, $offset, 4);

		// Unpak binary value
		$value = unpack('N', $hashpart);
		$value = $value[1];
		// Only 32 bits
		$value = $value & 0x7FFFFFFF;

		$modulo = pow(10, $this->codeLength);

		return str_pad($value % $modulo, $this->codeLength, '0', STR_PAD_LEFT);
	}

	/**
	 * Get QR-Code URL for image, from google charts.
	 *
	 * @param string $name
	 * @param string $secret
	 * @param string $title
	 * @param array  $params
	 *
	 * @return string
	 */
//	public function getQrUrl($name=null, $secret=null, $title = null, $params = array()) {
//		
//		if(!$this->publish) {
//			return null;
//		}
//		
//		$name = empty($name)?App::get()->getSettings()->title:$name;
//		$secret = empty($secret)?$this->secret:$secret;
//		
//		$width = !empty($params['width']) && (int) $params['width'] > 0 ? (int) $params['width'] : 200;
//		$height = !empty($params['height']) && (int) $params['height'] > 0 ? (int) $params['height'] : 200;
//		$level = !empty($params['level']) && array_search($params['level'], array('L', 'M', 'text', 'H')) !== false ? $params['level'] : 'M';
//
//		$urlencoded = urlencode('otpauth://totp/' . rawurlencode($name) . '?secret=' . $secret . '');
//		if (isset($title)) {
//			$urlencoded .= urlencode('&issuer=' . urlencode($title));
//		}
//
//		return 'https://chart.googleapis.com/chart?chs=' . $width . 'x' . $height . '&chld=' . $level . '|0&cht=qr&chl=' . $urlencoded . '';
//	}
	
	/**
	 * Get the blob id of the QR code image
	 * 
	 * @param string $name
	 * @param string $secret
	 * @param string $title
	 * @param array $params
	 * 
	 * @return boolean/string
	 */
	public function getQrBlobId($name=null, $secret=null, $title = null, $params = array()) {
		
		if(!$this->publish) {
			return null;
		}
		
		$name = empty($name) ? File::stripInvalidChars(go()->getSettings()->title) : $name;
		$secret = empty($secret)?$this->secret:$secret;

		$level = QR_ECLEVEL_M;

		if(!empty($params['level']) && array_search($params['level'], array('L', 'M', 'Q', 'H')) !== false){
			switch($params['level']){
				case 'L':
					$level = QR_ECLEVEL_L;
					break;
				case 'Q':
					$level = QR_ECLEVEL_Q;
					break;
				case 'H':
					$level = QR_ECLEVEL_H;
					break;
				case 'M':
				default:
					$level = QR_ECLEVEL_M;
					break;
			}
		}
		
		$otpUrl = 'otpauth://totp/' . rawurlencode($name) . '?secret=' . $secret . '';
		if (isset($title)) {
			$otpUrl .= '&issuer='.urlencode($title);
		}
		
		$tmpFile = \go\core\fs\File::tempFile($name);
		
		\go\core\util\QRcode::png($otpUrl, $tmpFile->getPath(),$level,8);
				
		$qrBlob = \go\core\fs\Blob::fromTmp($tmpFile);
		$qrBlob->setValues(array(
				'name'=>$name,
				'modified'=>time(),
				'type'=>'image/png'
		));
		
		if(!$qrBlob->save()){
			return false;
		}
		
		return $qrBlob->id;
	}

	/**
	 * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now.
	 *
	 * @param string   $secret
	 * @param string   $code
	 * @param int      $discrepancy      This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
	 * @param int|null $currentTimeSlice time slice if we want use other that time()
	 *
	 * @return bool
	 */
	public function verifyCode($code, $secret=null, $discrepancy = 1, $currentTimeSlice = null) {

		//replace spaces
		$code = preg_replace('/\s+/', '', $code);
		
		$secret = empty($secret)?$this->secret:$secret;
		
		if ($currentTimeSlice === null) {
			$currentTimeSlice = floor(time() / 30);
		}

		if (strlen($code) != 6) {
			return false;
		}

		for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
			$calculatedCode = $this->internalGetCode($secret, $currentTimeSlice + $i);
			if ($this->timingSafeEquals($calculatedCode, $code)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Set the code length, should be >=6.
	 *
	 * @param int $length
	 *
	 * @return GoogleAuthenticator
	 */
	public function setCodeLength($length) {
		$this->codeLength = $length;

		return $this;
	}

	/**
	 * Helper class to decode base32.
	 *
	 * @param $secret
	 *
	 * @return bool|string
	 */
	protected function _base32Decode($secret) {
		if (empty($secret)) {
			return '';
		}

		$base32chars = $this->_getBase32LookupTable();
		$base32charsFlipped = array_flip($base32chars);

		$paddingCharCount = substr_count($secret, $base32chars[32]);
		$allowedValues = array(6, 4, 3, 1, 0);
		if (!in_array($paddingCharCount, $allowedValues)) {
			return false;
		}
		for ($i = 0; $i < 4; ++$i) {
			if ($paddingCharCount == $allowedValues[$i] &&
							substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) {
				return false;
			}
		}
		$secret = str_replace('=', '', $secret);
		$secret = str_split($secret);
		$binaryString = '';
		for ($i = 0; $i < count($secret); $i = $i + 8) {
			$x = '';
			if (!in_array($secret[$i], $base32chars)) {
				return false;
			}
			for ($j = 0; $j < 8; ++$j) {
				$x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
			}
			$eightBits = str_split($x, 8);
			for ($z = 0; $z < count($eightBits); ++$z) {
				$binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
			}
		}

		return $binaryString;
	}

	/**
	 * Get array with all 32 characters for decoding from/encoding to base32.
	 *
	 * @return array
	 */
	protected function _getBase32LookupTable() {
		return array(
				'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
				'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
				'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
				'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
				'=', // padding char
		);
	}

	/**
	 * A timing safe equals comparison
	 * more info here: http://blog.ircmaxell.com/2014/11/its-all-about-time.html.
	 *
	 * @param string $safeString The internal (safe) value to be checked
	 * @param string $userString The user submitted (unsafe) value
	 *
	 * @return bool True if the two strings are identical
	 */
	private function timingSafeEquals($safeString, $userString) {
		if (function_exists('hash_equals')) {
			return hash_equals($safeString, $userString);
		}
		$safeLen = strlen($safeString);
		$userLen = strlen($userString);

		if ($userLen != $safeLen) {
			return false;
		}

		$result = 0;

		for ($i = 0; $i < $userLen; ++$i) {
			$result |= (ord($safeString[$i]) ^ ord($userString[$i]));
		}

		// They are only identical strings if $result is exactly 0...
		return $result === 0;
	}
}
