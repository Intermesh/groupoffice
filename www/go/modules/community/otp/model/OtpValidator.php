<?php

namespace go\modules\community\otp\model;

class OtpValidator {


	public int $codeLength = 6;

	/**
	 * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now.
	 *
	 * @param string $secret
	 * @param string $code
	 * @param int $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
	 * @param int|null $currentTimeSlice time slice if we want use other that time()
	 *
	 * @return bool
	 */
	public function verify(string $code, string $secret, int $discrepancy = 1, ?int $currentTimeSlice = null): bool
	{
		//replace spaces
		$code = preg_replace('/\s+/', '', $code);

		if ($currentTimeSlice === null) {
			$currentTimeSlice = floor(time() / 30);
		}

		if (strlen($code) != $this->codeLength) {
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

	private function internalGetCode(string $secret, float|null $timeSlice = null): string
	{
		if ($timeSlice === null) {
			$timeSlice = floor(time() / 30);
		}

		$secretkey = $this->base32Decode($secret);

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
	 * A timing safe equals comparison
	 * more info here: http://blog.ircmaxell.com/2014/11/its-all-about-time.html.
	 *
	 * @param string $safeString The internal (safe) value to be checked
	 * @param string $userString The user submitted (unsafe) value
	 *
	 * @return bool True if the two strings are identical
	 */
	private function timingSafeEquals(string $safeString, string $userString): bool
	{
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


	/**
	 * Helper class to decode base32.
	 *
	 * @param $secret
	 *
	 * @return bool|string
	 */
	protected function base32Decode(string $secret): bool|string
	{
		if (empty($secret)) {
			return '';
		}

		$base32chars = $this->getBase32LookupTable();
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
				$binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) === 48) ? $y : '';
			}
		}

		return $binaryString;
	}


	/**
	 * Get array with all 32 characters for decoding from/encoding to base32.
	 *
	 * @return array
	 */
	protected function getBase32LookupTable(): array
	{
		return array(
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
			'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
			'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
			'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
			'=', // padding char
		);
	}
}