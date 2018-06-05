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
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.util.charset
 */

/**
 * Charset mapping taken from:
 * 
 * ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/THAI.TXT 
 *
 * @package GO.base.util.charset
 * @version $Id: File.class.inc.php 7607 2011-06-15 09:17:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl> 
 */


namespace GO\Base\Util\Charset;


class Xmac {

	public static $map = array(


			"80" => array(0x00AB), // LEFT-POINTING DOUBLE ANGLE QUOTATION MARK
			"81" => array(0x00BB), // RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK
			"82" => array(0x2026), // HORIZONTAL ELLIPSIS
			"83" => array(0x0E48, 0xF875), // THAI CHARACTER MAI EK low left position
			"84" => array(0x0E49, 0xF875), // THAI CHARACTER MAI THO low left position
			"85" => array(0x0E4A, 0xF875), // THAI CHARACTER MAI TRI low left position
			"86" => array(0x0E4B, 0xF875), // THAI CHARACTER MAI CHATTAWA low left position
			"87" => array(0x0E4C, 0xF875), // THAI CHARACTER THANTHAKHAT low left position
			"88" => array(0x0E48, 0xF873), // THAI CHARACTER MAI EK low position
			"89" => array(0x0E49, 0xF873), // THAI CHARACTER MAI THO low position
			"8A" => array(0x0E4A, 0xF873), // THAI CHARACTER MAI TRI low position
			"8B" => array(0x0E4B, 0xF873), // THAI CHARACTER MAI CHATTAWA low position
			"8C" => array(0x0E4C, 0xF873), // THAI CHARACTER THANTHAKHAT low position
			"8D" => array(0x201C), // LEFT DOUBLE QUOTATION MARK
			"8E" => array(0x201D), // RIGHT DOUBLE QUOTATION MARK
			"8F" => array(0x0E4D, 0xF874), // THAI CHARACTER NIKHAHIT left position

			"91" => array(0x2022), // BULLET
			"92" => array(0x0E31, 0xF874), // THAI CHARACTER MAI HAN-AKAT left position
			"93" => array(0x0E47, 0xF874), // THAI CHARACTER MAITAIKHU left position
			"94" => array(0x0E34, 0xF874), // THAI CHARACTER SARA I left position
			"95" => array(0x0E35, 0xF874), // THAI CHARACTER SARA II left position
			"96" => array(0x0E36, 0xF874), // THAI CHARACTER SARA UE left position
			"97" => array(0x0E37, 0xF874), // THAI CHARACTER SARA UEE left position
			"98" => array(0x0E48, 0xF874), // THAI CHARACTER MAI EK left position
			"99" => array(0x0E49, 0xF874), // THAI CHARACTER MAI THO left position
			"9A" => array(0x0E4A, 0xF874), // THAI CHARACTER MAI TRI left position
			"9B" => array(0x0E4B, 0xF874), // THAI CHARACTER MAI CHATTAWA left position
			"9C" => array(0x0E4C, 0xF874), // THAI CHARACTER THANTHAKHAT left position
			"9D" => array(0x2018), // LEFT SINGLE QUOTATION MARK
			"9E" => array(0x2019), // RIGHT SINGLE QUOTATION MARK
#
			"A0" => array(0x00A0), // NO-BREAK SPACE
			"A1" => array(0x0E01), // THAI CHARACTER KO KAI
			"A2" => array(0x0E02), // THAI CHARACTER KHO KHAI
			"A3" => array(0x0E03), // THAI CHARACTER KHO KHUAT
			"A4" => array(0x0E04), // THAI CHARACTER KHO KHWAI
			"A5" => array(0x0E05), // THAI CHARACTER KHO KHON
			"A6" => array(0x0E06), // THAI CHARACTER KHO RAKHANG
			"A7" => array(0x0E07), // THAI CHARACTER NGO NGU
			"A8" => array(0x0E08), // THAI CHARACTER CHO CHAN
			"A9" => array(0x0E09), // THAI CHARACTER CHO CHING
			"AA" => array(0x0E0A), // THAI CHARACTER CHO CHANG
			"AB" => array(0x0E0B), // THAI CHARACTER SO SO
			"AC" => array(0x0E0C), // THAI CHARACTER CHO CHOE
			"AD" => array(0x0E0D), // THAI CHARACTER YO YING
			"AE" => array(0x0E0E), // THAI CHARACTER DO CHADA
			"AF" => array(0x0E0F), // THAI CHARACTER TO PATAK
			"B0" => array(0x0E10), // THAI CHARACTER THO THAN
			"B1" => array(0x0E11), // THAI CHARACTER THO NANGMONTHO
			"B2" => array(0x0E12), // THAI CHARACTER THO PHUTHAO
			"B3" => array(0x0E13), // THAI CHARACTER NO NEN
			"B4" => array(0x0E14), // THAI CHARACTER DO DEK
			"B5" => array(0x0E15), // THAI CHARACTER TO TAO
			"B6" => array(0x0E16), // THAI CHARACTER THO THUNG
			"B7" => array(0x0E17), // THAI CHARACTER THO THAHAN
			"B8" => array(0x0E18), // THAI CHARACTER THO THONG
			"B9" => array(0x0E19), // THAI CHARACTER NO NU
			"BA" => array(0x0E1A), // THAI CHARACTER BO BAIMAI
			"BB" => array(0x0E1B), // THAI CHARACTER PO PLA
			"BC" => array(0x0E1C), // THAI CHARACTER PHO PHUNG
			"BD" => array(0x0E1D), // THAI CHARACTER FO FA
			"BE" => array(0x0E1E), // THAI CHARACTER PHO PHAN
			"BF" => array(0x0E1F), // THAI CHARACTER FO FAN
			"C0" => array(0x0E20), // THAI CHARACTER PHO SAMPHAO
			"C1" => array(0x0E21), // THAI CHARACTER MO MA
			"C2" => array(0x0E22), // THAI CHARACTER YO YAK
			"C3" => array(0x0E23), // THAI CHARACTER RO RUA
			"C4" => array(0x0E24), // THAI CHARACTER RU
			"C5" => array(0x0E25), // THAI CHARACTER LO LING
			"C6" => array(0x0E26), // THAI CHARACTER LU
			"C7" => array(0x0E27), // THAI CHARACTER WO WAEN
			"C8" => array(0x0E28), // THAI CHARACTER SO SALA
			"C9" => array(0x0E29), // THAI CHARACTER SO RUSI
			"CA" => array(0x0E2A), // THAI CHARACTER SO SUA
			"CB" => array(0x0E2B), // THAI CHARACTER HO HIP
			"CC" => array(0x0E2C), // THAI CHARACTER LO CHULA
			"CD" => array(0x0E2D), // THAI CHARACTER O ANG
			"CE" => array(0x0E2E), // THAI CHARACTER HO NOKHUK
			"CF" => array(0x0E2F), // THAI CHARACTER PAIYANNOI
			"D0" => array(0x0E30), // THAI CHARACTER SARA A
			"D1" => array(0x0E31), // THAI CHARACTER MAI HAN-AKAT
			"D2" => array(0x0E32), // THAI CHARACTER SARA AA
			"D3" => array(0x0E33), // THAI CHARACTER SARA AM
			"D4" => array(0x0E34), // THAI CHARACTER SARA I
			"D5" => array(0x0E35), // THAI CHARACTER SARA II
			"D6" => array(0x0E36), // THAI CHARACTER SARA UE
			"D7" => array(0x0E37), // THAI CHARACTER SARA UEE
			"D8" => array(0x0E38), // THAI CHARACTER SARA U
			"D9" => array(0x0E39), // THAI CHARACTER SARA UU
			"DA" => array(0x0E3A), // THAI CHARACTER PHINTHU
			"DB" => array(0x2060), // WORD JOINER # for Unicode 3.2 and later
			"DC" => array(0x200B), // ZERO WIDTH SPACE
			"DD" => array(0x2013), // EN DASH
			"DE" => array(0x2014), // EM DASH
			"DF" => array(0x0E3F), // THAI CURRENCY SYMBOL BAHT
			"E0" => array(0x0E40), // THAI CHARACTER SARA E
			"E1" => array(0x0E41), // THAI CHARACTER SARA AE
			"E2" => array(0x0E42), // THAI CHARACTER SARA O
			"E3" => array(0x0E43), // THAI CHARACTER SARA AI MAIMUAN
			"E4" => array(0x0E44), // THAI CHARACTER SARA AI MAIMALAI
			"E5" => array(0x0E45), // THAI CHARACTER LAKKHANGYAO
			"E6" => array(0x0E46), // THAI CHARACTER MAIYAMOK
			"E7" => array(0x0E47), // THAI CHARACTER MAITAIKHU
			"E8" => array(0x0E48), // THAI CHARACTER MAI EK
			"E9" => array(0x0E49), // THAI CHARACTER MAI THO
			"EA" => array(0x0E4A), // THAI CHARACTER MAI TRI
			"EB" => array(0x0E4B), // THAI CHARACTER MAI CHATTAWA
			"EC" => array(0x0E4C), // THAI CHARACTER THANTHAKHAT
			"ED" => array(0x0E4D), // THAI CHARACTER NIKHAHIT
			"EE" => array(0x2122), // TRADE MARK SIGN
			"EF" => array(0x0E4F), // THAI CHARACTER FONGMAN
			"F0" => array(0x0E50), // THAI DIGIT ZERO
			"F1" => array(0x0E51), // THAI DIGIT ONE
			"F2" => array(0x0E52), // THAI DIGIT TWO
			"F3" => array(0x0E53), // THAI DIGIT THREE
			"F4" => array(0x0E54), // THAI DIGIT FOUR
			"F5" => array(0x0E55), // THAI DIGIT FIVE
			"F6" => array(0x0E56), // THAI DIGIT SIX
			"F7" => array(0x0E57), // THAI DIGIT SEVEN
			"F8" => array(0x0E58), // THAI DIGIT EIGHT
			"F9" => array(0x0E59), // THAI DIGIT NINE
			"FA" => array(0x00AE), // REGISTERED SIGN
			"FB" => array(0x00A9), // COPYRIGHT SIGN
	);

	public static function uniord($c) {
		$ord0 = ord($c{0});
		if ($ord0 >= 0 && $ord0 <= 127)
			return $ord0;
		$ord1 = ord($c{1});
		if ($ord0 >= 192 && $ord0 <= 223)
			return ($ord0 - 192) * 64 + ($ord1 - 128);
		$ord2 = ord($c{2});
		if ($ord0 >= 224 && $ord0 <= 239)
			return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
		$ord3 = ord($c{3});
		if ($ord0 >= 240 && $ord0 <= 247)
			return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);
		return false;
	}

	public static function toUtf8($string, $charset) {

		if(!\GO\Base\Util\StringHelper::is8bit($string,$charset))
			return $string;
		
//		$searches = array();
//		$replaces = array();
//		foreach (self::$map as $key => $values) {
////			if($key!='C1')
////				continue;
//			
//			$replace = '';
//			foreach ($values as $val)
//				$replace.=self::unicodeToUtf8($val);
//			
//			 $searches[] = chr(hexdec($key));
//			//echo hexdec($key).' ';
//			$replaces[] = $replace;
//		}
//		return str_replace($searches, $replaces, $string);

		$out = '';
		$len = strlen($string);
		for ($i = 0; $i < $len; $i++) {
			$hex = strtoupper(dechex(ord($string[$i])));
			if (isset(self::$map[$hex])) {
				foreach (self::$map[$hex] as $unicodeHex)
					$out .= self::unicodeToUtf8($unicodeHex);
			} else {
				$out .= $string[$i];
			}
		}
		return $out;
	}

	public static function unicodeToUtf8($num) {
		if ($num <= 0x7F)
			return chr($num);
		if ($num <= 0x7FF)
			return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
		if ($num <= 0xFFFF)
			return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
		if ($num <= 0x1FFFFF)
			return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
		return '';
	}

}
