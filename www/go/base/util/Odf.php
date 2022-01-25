<?php
namespace GO\Base\Util;

require(\GO::config()->root_path.'go/vendor/odtphp/library/odf.php');


class Odf extends \Odf{

	/**
	 * @var string
	 */
	private $_filename;

	public function __construct($filename, $config = array()) {
		
		$this->_filename=\GO\Base\Fs\File::utf8Basename($filename);

		parent::__construct($filename, $config);
	}

	public function getFilename(){
		return $this->_filename;
	}

}
