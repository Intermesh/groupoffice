<?php
//chdir(\GO::config()->root_path.'go/vendor/XML_WBXML/WBXML/');
//require_once(\GO::config()->root_path.'go/vendor/XML_WBXML/WBXML/Decoder.php');

namespace GO\Base\Wbxml;


class Convertor{
	
	private $_wbxmlFile;
	private $_xmlFile;
	
	public function __construct(){
		
		$tmpFolder = \GO::config()->getTempFolder();
		
		$this->_wbxmlFile=$tmpFolder->path().'/'.uniqid(time()).'.wbxml';
		$this->_xmlFile=$tmpFolder->path().'/'.uniqid(time()).'.xml';
	}
	
	public function decode($wbxml){
	

		//file_put_contents did not work with nokia phones because the
		//line ends got mixed up somehow.
		$fp = fopen($this->_wbxmlFile, 'w+');
		fwrite($fp, $wbxml);
		fclose($fp);
		//convert temp file

		if(\GO\Base\Util\Common::isWindows())
		{
			$cmd = \GO::config()->cmd_wbxml2xml.' -o '.$this->_xmlFile.' '.$this->_wbxmlFile;
		}else
		{
			$cmd = \GO::config()->cmd_wbxml2xml.' -o '.$this->_xmlFile.' '.$this->_wbxmlFile.' 2>/dev/null';
		}
		exec($cmd);

		if(!file_exists($this->_xmlFile))
			throw new \Exception('wbxml2xml conversion failed');

		//read xml
		$xml = trim(file_get_contents($this->_xmlFile));

		//remove temp files
		unlink($this->_wbxmlFile);
		unlink($this->_xmlFile);
		return $xml;
	}
	
	public function encode($xml, $output=false){

		file_put_contents($this->_xmlFile, $xml);

		if(\GO\Base\Util\Common::isWindows())
		{
			$cmd = \GO::config()->cmd_xml2wbxml.' -o '.$this->_wbxmlFile.' '.$this->_xmlFile;
		}else
		{
			$cmd = \GO::config()->cmd_xml2wbxml.' -o '.$this->_wbxmlFile.' '.$this->_xmlFile.' 2>/dev/null';
		}
		exec($cmd);

		if(!file_exists($this->_wbxmlFile))
			throw new \Exception('xml2wbxml conversion failed');
		
		if($output){
			readfile($this->_wbxmlFile);
		}else
		{
			$wbxml = trim(file_get_contents($this->_wbxmlFile));
		}	

		//remove temp files
		unlink($this->_wbxmlFile);
		unlink($this->_xmlFile);
		
		return $output ? true : $wbxml;
	}
	
	public function output($xml){
		return $this->encode($xml, true);
	}
}
