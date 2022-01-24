<?php

namespace go\core\util;

/**
 * Class IniFile.
 * 
 * // demo create ini file
$a = new IniFile('file.ini');
$a->create($data);
$a->add([
    'music' => ['rap' => true, 'rock' => false]
]);
$a->rm([
    'jus' => ['pomme' => '1,5L']
]);
$a->update([
    'fruit' => ['orange' => '200g'] // 100g to 200g
]);
$a->write();

echo '<pre>'.file_get_contents('file.ini').'</pre>';

/* output file.ini
[fruit]
orange = "200g"
fraise = "10g"

[legume]
haricot = "20g"
oignon = "100g"

[jus]
orange = "1L"
pamplemousse = "0,5L"

[music]
rap = 1
rock = 0
*/

class IniFile {

	private $data = [];

	
	public function readFile($path) {
		$this->data = parse_ini_file($path, true, INI_SCANNER_TYPED);
		
		if (false === $this->data) {
			throw new \Exception(sprintf('Unable to parse file ini : %s', $path));
		}
	}
	
	/**
	 * Get the parsed data
	 * 
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * method for change value in the ini file.
	 *
	 * @param array $value
	 */
	public function update(array $value) {
		$this->data = array_replace_recursive($this->data, $value);
	}

	/**
	 * method for create ini file.
	 *
	 * @param array $data
	 */
	public function readData(array $data) {
		$this->data = $data;
	}

	/**
	 * method for erase ini file.
	 */
	public function erase() {
		$this->data = [];
	}

	/**
	 * method for add new value in the ini file.
	 *
	 * @param array $value
	 */
	public function add(array $value) {
		$this->data = array_merge_recursive($this->data, $value);
	}

	/**
	 * method for remove some values in the ini file.
	 *
	 * @param array $value
	 */
	public function rm(array $value) {
		$this->data = self::arrayDiffRecursive($this->data, $value);
	}

	/**
	 * method for write data in the ini file.
	 *
	 * @return bool true for a succes
	 */	
	
	public function write($path) {		
		return file_put_contents($path, (string) $this);		
	}
	
	
	public function __toString() {
		$data_array = $this->data;
		$file_content = null;
		foreach ($data_array as $key_1 => $groupe) {
			$file_content .= "\n[" . $key_1 . "]\n";
			foreach ($groupe as $key_2 => $value_2) {
				if (is_array($value_2)) {
					foreach ($value_2 as $key_3 => $value_3) {
						$file_content .= $key_2 . '[' . $key_3 . '] = ' . self::encode($value_3) . "\n";
					}
				} else {
					$file_content .= $key_2 . ' = ' . self::encode($value_2) . "\n";
				}
			}
		}
		return preg_replace('#^\n#', '', $file_content);
	}

	/**
	 * method for encode type for ini file.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	private static function encode($value) {
		
		if(is_bool($value)) {
			return $value ? "true" : "false";
		} elseif ($value == '0' || $value === false) {
			return 0;
		} elseif ($value == '') {
			return '""';
		}
		if (is_numeric($value)) {
			$value = $value * 1;
			if (is_int($value)) {
				return (int) $value;
			} elseif (is_float($value)) {
				return (float) $value;
			}
		} // @codeCoverageIgnore
		return '"' . $value . '"';
	}

	/**
	 * Computes the difference of 2 arrays recursively
	 * source : http://php.net/manual/en/function.array-diff.php#91756.
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	private static function arrayDiffRecursive(array $array1, array $array2) {
		$finalArray = [];
		foreach ($array1 as $KeyArray1 => $ValueArray1) {
			if (array_key_exists($KeyArray1, $array2)) {
				if (is_array($ValueArray1)) {
					$arrayDiffRecursive = self::arrayDiffRecursive($ValueArray1, $array2[$KeyArray1]);
					if (count($arrayDiffRecursive)) {
						$finalArray[$KeyArray1] = $arrayDiffRecursive;
					}
				}
			} else {
				$finalArray[$KeyArray1] = $ValueArray1;
			}
		}
		return $finalArray;
	}

}
