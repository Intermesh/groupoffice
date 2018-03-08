<?php

namespace GO\Addressbook;


class Utils{
	
	public static function getIndexChar($string) {
		$char = '';
		if (!empty($string)) {
			if (function_exists('mb_substr')) {
				$char = strtoupper(mb_substr(\GO\Base\Fs\Base::stripInvalidChars($string),0,1,'UTF-8'));
			} else {
				$char = strtoupper(substr(\GO\Base\Fs\Base::stripInvalidChars($string),0,1));
			}
		}

		return $char;
	}
	
}