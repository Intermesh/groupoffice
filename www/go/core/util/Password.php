<?php

namespace go\core\util;

final class Password
{
	/**
	 * Generate a random password on the server side.
	 *
	 * Normally used for temporary password resets, e.g. for mail migrations. The default password length is as per the
	 * system settings. Optionally, you can check whether a password generator was leaked by using the output in
	 * conjunction with the Pwned->hasBeenPwned method. This is part of the optional pwned module.
	 *
	 * @param int|null $length
	 * @param string|null $charactersAllow
	 * @param string|null $charactersDisallow
	 * @return string
	 * @throws \Random\RandomException
	 * @see \go\modules\community\maildomains\convert\Spreadsheet:: exportPassword
	 *
	 */
	public static function generateRandom(?int $length = 0, ?string $charactersAllow = 'A-Z,a-z,0-9', ?string $charactersDisallow = 'i,o'): string
	{
		if ($length === 0) {
			$length = go()->getSettings()->passwordMinLength;
		}

		$arAllowed = self::paramToArray($charactersAllow);
		$arDisallowed = self::paramToArray($charactersDisallow);;

		// Generate array of allowed characters by removing disallowed characters from array.
		// TODO: Refactor into an array_filter because this is ugly. >:-(
		$arChars = array_values(array_diff($arAllowed, $arDisallowed));

		$password = '';

		while (strlen($password) < $length) {
			$character = random_int(0, count($arChars) - 1);
			// Characters are not allowed to repeat
			if (substr_count($password, $arChars[$character]) == 0) {
				$password .= $arChars[$character];
			}
		}

		return $password;
	}

	/**
	 * Slice and dice a parameter string into an array of characters
	 *
	 * @param string $str
	 * @return array
	 */
	private static function paramToArray(string $str): array
	{
		$ret = [];
		$arr = explode(",", $str);
		for ($i = 0; $i < count($arr); $i++) {
			if (substr_count($arr[$i], '-') > 0) {
				$arRange = explode('-', $arr[$i]);
				for ($j = ord($arRange[0]); $j <= ord($arRange[1]); $j++) {
					$ret[] = chr($j);
				}
			} else {
				$ret[] = $arr[$i];
			}
		}
		return $ret;
	}
}