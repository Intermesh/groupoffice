<?php

namespace GO\Site\Components;


class Config{

	private $_configOptions = array();
	
	public function __construct(\GO\Site\Model\Site $siteModel) {

		$file = new \GO\Base\Fs\File($siteModel->getSiteModule()->moduleManager->path().'siteconfig.php');
		if($file->exists()){
			require ($file->path());
		}	
		if(isset($siteconfig))
			$this->_configOptions = $siteconfig;
	}
	
	public function __get($name) {
		
		if(array_key_exists($name, $this->_configOptions))
			return $this->_configOptions[$name];
		else
			return null;
	}
	
	public function getDefaultTemplate(){
		if($this->defaultTemplate)
			return $this->defaultTemplate;
		
		if($this->templates){
			
			$templates = array_keys($this->templates);
			return array_shift($templates);
		}
		
		return false;
	}
}
