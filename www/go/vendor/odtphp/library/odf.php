<?php

require 'zip/PclZipProxy.php';
require 'zip/PhpZipProxy.php';
require 'Segment.php';

class OdfException extends \Exception {

}

/**
 * Templating class for odt file
 * You need PHP 5.2 at least
 * You need Zip Extension or PclZip library
 * Encoding : ISO-8859-1
 * Last commit by $Author: neveldo $
 * Date - $Date: 2009-06-17 11:11:57 +0200 (mer., 17 juin 2009) $
 * SVN Revision - $Rev: 42 $
 * Id : $Id: odf.php 42 2009-06-17 09:11:57Z neveldo $
 *
 * @copyright  GPL License 2008 - Julien Pauli - Cyril PIERRE de GEYER - Anaska (http://www.anaska.com)
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version 1.3
 */
class Odf {

	protected $config = array(
			'ZIP_PROXY' => 'PclZipProxy',
			'DELIMITER_LEFT' => '{',
			'DELIMITER_RIGHT' => '}',
			'PATH_TO_TMP' => null
	);
	protected $file;
	protected $contentXml;
	protected $stylesXml;

	protected $tmpfile;

	protected $images = array();
	protected $vars = array();
	protected $segments = array();

	const PIXEL_TO_CM = 0.026458333;

	/**
	 * Class constructor
	 *
	 * @param StringHelper $filename the name of the odt file
	 * @throws OdfException
	 */
	public function __construct($filename, $config = array()) {
		if (!is_array($config)) {
			throw new OdfException('Configuration data must be provided as array');
		}
		
		$this->config['PATH_TO_TMP'] = GO::config()->tmpdir;
		
		foreach ($config as $configKey => $configValue) {
			//if (array_key_exists($configKey, $this->config)) {
				$this->config[$configKey] = $configValue;
			//}
		}
		if (!class_exists($this->config['ZIP_PROXY'])) {
			throw new OdfException($this->config['ZIP_PROXY'] . ' class not found - check your php settings');
		}
		$zipHandler = $this->config['ZIP_PROXY'];
		$this->file = new $zipHandler();
		if ($this->file->open($filename) !== true) {
			throw new OdfException("Error while Opening the file '$filename' - Check your odt file");
		}
		if (($this->contentXml = $this->file->getFromName('content.xml')) === false) {
			throw new OdfException("Nothing to parse - check that the content.xml file is correctly formed");
		}

		if (($this->stylesXml = $this->file->getFromName('styles.xml')) === false) {
			throw new OdfException("Nothing to parse - check that the styles.xml file is correctly formed");
		}
		
		$this->contentXml = str_replace('<text:s/>', ' ', $this->contentXml);
		$this->stylesXml = str_replace('<text:s/>', ' ', $this->stylesXml);

		$this->file->close();

		$tmp = tempnam($this->config['PATH_TO_TMP'], md5(uniqid()));
		copy($filename, $tmp);
		$this->tmpfile = $tmp;

		$this->contentXml = $this->_moveRowSegments($this->contentXml);
		$this->stylesXml = $this->_moveRowSegments($this->stylesXml);

		$this->contentXml = $this->_fix_segments($this->contentXml);
		$this->stylesXml = $this->_fix_segments($this->stylesXml);
	}

	/**
	 * Assing a template variable
	 *
	 * @param StringHelper $key name of the variable within the template
	 * @param StringHelper $value replacement value
	 * @param bool $encode if true, special XML characters are encoded
	 * @throws OdfException
	 * @return odf
	 */
	public function setVars($key, $value, $encode = true) {
			$value = $encode ? htmlspecialchars($value ?? "", ENT_COMPAT, 'UTF-8') : $value;
			
			//\GO::debug('ODF var: '.$key.'=>'.$value);
			
			$this->vars[$key] = str_replace("\n", "<text:line-break/>", $value);
			return $this;
	}

	/**
	 * Assign a template variable as a picture
	 *
	 * @param StringHelper $key name of the variable within the template
	 * @param StringHelper $value path to the picture
	 * @throws OdfException
	 * @return odf
	 */
	public function setImage($key, $value) {
		$filename = strtok(strrchr($value, '/'), '/.');
		$file = substr(strrchr($value, '/'), 1);
		$size = @getimagesize($value);
		if ($size === false) {
			throw new OdfException("Invalid image");
		}
		list ($width, $height) = $size;
		$width *= self::PIXEL_TO_CM;
		$height *= self::PIXEL_TO_CM;
		$xml = <<<IMG
<draw:frame draw:style-name="fr1" draw:name="$filename" text:anchor-type="char" svg:width="{$width}cm" svg:height="{$height}cm" draw:z-index="3"><draw:image xlink:href="Pictures/$file" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>
IMG;
		$this->images[$value] = $file;
		$this->setVars($key, $xml, false);
		return $this;
	}

	/**
	 * Move segment tags for lines of tables
	 * Called automatically within the constructor
	 *
	 * @return void
	 */
	private function _moveRowSegments($xml) {
		// Search all possible rows in the document
		$reg1 = "#<table:table-row[^>]*>(.*)</table:table-row>#smU";
		preg_match_all($reg1, $xml, $matches);
		for ($i = 0, $size = count($matches[0]); $i < $size; $i++) {
			// Check if the current row contains a segment row.*
			$reg2 = '#\[!--\sBEGIN\s(row.[\S]*)\s--\](.*)\[!--\sEND\s\\1\s--\]#sm';
			if (preg_match($reg2, $matches[0][$i], $matches2)) {
				$balise = str_replace('row.', '', $matches2[1]);
				// Move segment tags around the row
				$replace = array(
						'[!-- BEGIN ' . $matches2[1] . ' --]' => '',
						'[!-- END ' . $matches2[1] . ' --]' => '',
						'<table:table-row' => '[!-- BEGIN ' . $balise . ' --]<table:table-row',
						'</table:table-row>' => '</table:table-row>[!-- END ' . $balise . ' --]'
				);
				$replacedXML = str_replace(array_keys($replace), array_values($replace), $matches[0][$i]);
				$xml = str_replace($matches[0][$i], $replacedXML, $xml);
			}
		}

		return $xml;
	}

	/**
	 * Merge template variables
	 * Called automatically for a save
	 *
	 * @return void
	 */
	private function _parse() {
		//  $this->contentXml = str_replace(array_keys($this->vars), array_values($this->vars), $this->contentXml);
		$this->contentXml = preg_replace_callback('/{([^}]*)}/U', array($this, "replacetag"), $this->contentXml);
		$this->stylesXml = preg_replace_callback('/{([^}]*)}/U', array($this, "replacetag"), $this->stylesXml);

		//clean up unprocessed tags
		$this->stylesXml=preg_replace('/{([^}]*)}/U',"",$this->stylesXml);
		$this->contentXml=preg_replace('/{([^}]*)}/U',"",$this->contentXml);
	}

	private function _fix_segments($xml) {

		$reg = '@\[!--\sBEGIN\s[^\]]*--\]@smU';
		$xml = preg_replace_callback($reg, "self::_fix_segments_callback", $xml);
		$reg = '@\[!--\sEND\s[^\]]*--\]@smU';
		$xml = preg_replace_callback($reg, "self::_fix_segments_callback", $xml);
		return $xml;
	}

	public static function _fix_segments_callback($tag) {
		//Sometimes people change styles within a {autodata} tag.
		//Then there are XML tags inside the GO template tag.
		//We place them outside the tag.
		$tag = stripslashes($tag[0]);
		preg_match_all('/<[^>]*>/', $tag, $matches);
		$garbage_tags = implode('', $matches[0]);

		$tag = strip_tags($tag);
		return $tag . $garbage_tags;
	}

	public function replacetag($tag) {
		
		$tag = stripslashes($tag[1]);
		$orig_tag = $tag;

		//Sometimes people change styles within a {autodata} tag.
		//Then there are XML tags inside the GO template tag.
		//We place them outside the tag.
		//go_debug($tag);
		preg_match_all('/<[^>]*>/', $tag, $matches);
		$garbage_tags = implode('', $matches[0]);

		$tag = strip_tags($tag);
		$arr = explode('|', $tag);

		$math = false;
		$ops = array('/', '*', '+', '-');
		foreach ($ops as $op) {
			if (strpos($arr[0], $op)) {
				$math = true;
				break;
			}
		}

		if (!$math) {
			if (!isset($this->vars[$arr[0]])) {
				return '{' . $orig_tag . '}';
			} else {
				$v = $this->vars[$arr[0]];
			}
		} else {
			$v = $arr[0];
			foreach ($this->vars as $key => $value) {
				$v = str_replace($key, $value, $v);
			}

			\GO::config()->debug_display_errors = false;
			@eval("\$result_string=" . $v . ";");
			\GO::config()->debug_display_errors = true;

			$v = isset($result_string) ? $result_string : 'invalid math expression!';
		}

//		if (isset($arr[1])) {
//			$args = explode(':', $arr[1]);
//
//			//first value = function name
//			$func = array_shift($args);
//
//			//add value as first argument
//			array_unshift($args, $v);
//
//			$v = call_user_func_array(array('odf_renderers', $func), $args);
//		}
		return $garbage_tags . $v;
	}

	/**
	 * Add the merged segment to the document
	 *
	 * @param Segment $segment
	 * @throws OdfException
	 * @return odf
	 */
	public function mergeSegment(Segment $segment) {
		if (!array_key_exists($segment->getName(), $this->segments)) {
			throw new OdfException($segment->getName() . 'cannot be parsed, has it been set yet ?');
		}
		$string = $segment->getName();
		// $reg = '@<text:p[^>]*>\[!--\sBEGIN\s' . $string . '\s--\](.*)\[!--.+END\s' . $string . '\s--\]<\/text:p>@smU';
		$reg = '@\[!--\sBEGIN\s' . $string . '\s--\](.*)\[!--.+END\s' . $string . '\s--\]@smU';

		$this->contentXml = preg_replace($reg, $segment->getXmlParsed(), $this->contentXml, -1, $count);
		$this->stylesXml = preg_replace($reg, $segment->getXmlParsed(), $this->stylesXml, -1, $count);

		return $this;
	}

	/**
	 * Display all the current template variables
	 *
	 * @return StringHelper
	 */
	public function printVars() {
		return print_r('<pre>' . print_r($this->vars, true) . '</pre>', true);
	}

	/**
	 * Display the XML content of the file from odt document
	 * as it is at the moment
	 *
	 * @return StringHelper
	 */
	public function __toString() {
		return $this->contentXml;
	}

	/**
	 * Display loop segments declared with setSegment()
	 *
	 * @return StringHelper
	 */
	public function printDeclaredSegments() {
		return '<pre>' . print_r(implode(' ', array_keys($this->segments)), true) . '</pre>';
	}

	/**
	 * Declare a segment in order to use it in a loop
	 *
	 * @param StringHelper $segment
	 * @throws OdfException
	 * @return Segment
	 */
	public function setSegment($segment) {
		if (array_key_exists($segment, $this->segments)) {
			return $this->segments[$segment];
		}
		// $reg = "#\[!--\sBEGIN\s$segment\s--\]<\/text:p>(.*)<text:p\s.*>\[!--\sEND\s$segment\s--\]#sm";
		$reg = "#\[!--\sBEGIN\s$segment\s--\](.*)\[!--\sEND\s$segment\s--\]#sm";
		if (preg_match($reg, html_entity_decode($this->contentXml), $m) == 0) {
			throw new OdfException("'$segment' segment not found in the document");
		}
		$this->segments[$segment] = new Segment($segment, $m[1], $this);
		return $this->segments[$segment];
	}

	/**
	 * Save the odt file on the disk
	 *
	 * @param StringHelper $file name of the desired file
	 * @throws OdfException
	 * @return void
	 */
	public function saveToDisk($file = null) {
		if ($file !== null && is_string($file)) {
			if (file_exists($file) && !(is_file($file) && is_writable($file))) {
				throw new OdfException('Permission denied : can\'t create ' . $file);
			}
			$this->_save();
			copy($this->tmpfile, $file);
		} else {
			$this->_save();
		}
	}

	/**
	 * Internal save
	 *
	 * @throws OdfException
	 * @return void
	 */
	private function _save() {
		$this->file->open($this->tmpfile);
		$this->_parse();
		if (!$this->file->addFromString('content.xml', $this->contentXml)) {
			throw new OdfException('Error during file export');
		}
		if (!$this->file->addFromString('styles.xml', $this->stylesXml)) {
			throw new OdfException('Error during file export');
		}
		foreach ($this->images as $imageKey => $imageValue) {
			$this->file->addFile($imageKey, 'Pictures/' . $imageValue);
		}
		$this->file->close(); // seems to bug on windows CLI sometimes
	}

	/**
	 * Export the file as attached file by HTTP
	 *
	 * @param StringHelper $name (optionnal)
	 * @throws OdfException
	 * @return void
	 */
	public function exportAsAttachedFile($name="") {
		$this->_save();
		if (headers_sent($filename, $linenum)) {
			throw new OdfException("headers already sent ($filename at $linenum)");
		}

		if ($name == "") {
			$name = md5(uniqid()) . ".odt";
		}

		header('Content-type: application/vnd.oasis.opendocument.text');
		header('Content-Disposition: attachment; filename="' . $name . '"');
		readfile($this->tmpfile);
	}

	/**
	 * Returns a variable of configuration
	 *
	 * @return StringHelper The requested variable of configuration
	 */
	public function getConfig($configKey) {
		if (array_key_exists($configKey, $this->config)) {
			return $this->config[$configKey];
		}
		return false;
	}

	/**
	 * Returns the temporary working file
	 *
	 * @return StringHelper le chemin vers le fichier temporaire de travail
	 */
	public function gettmpfile() {
		return $this->tmpfile;
	}

	/**
	 * Delete the temporary file when the object is destroyed
	 */
	public function __destruct() {
		if (file_exists($this->tmpfile)) {
			unlink($this->tmpfile);
		}
	}

}

//class odf_renderers {
//
//	function number($v, $decimals=2) {
//		return \GO\Base\Util\Number::localize($v, $decimals);
//	}
//
//	function from_unixtime($v, $with_time=true) {
//		return \GO\Base\Util\Date::get_timestamp($v, $with_time);
//	}
//
//	function from_unixdate($v) {
//		return \GO\Base\Util\Date::get_timestamp($v, false);
//	}
//
//}

?>
