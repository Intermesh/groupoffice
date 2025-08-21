<?php


namespace GO\Base\View;


class Theme{

	private $name = 'Paper';
	/**
	 * Get the name of the theme that is selected by the user.
	 * 
	 * @return string
	 */
	public function getName(){
		if(!isset($this->name)) {
			$this->name = \GO::config()->allow_themes && \GO::user() ? \GO::user()->theme : \GO::config()->theme;
			
			if(!file_exists(\GO::view()->getPath().'themes/'.$this->name.'/Layout.php')){
				$this->name = 'Paper';
			}  
		}
		return $this->name;
	}
	
	/**
	 * Get the full path to the main theme folder with trailing slash.
	 * 
	 * @return string
	 */
	public function getPath(){
		return \GO::view()->getPath().'themes/'.$this->getName().'/';
	}
	
	/**
	 * Get the full path to the main theme folder with trailing slash.
	 * 
	 * @return string
	 */
	public function getUrl(){
		return \GO::view()->getUrl().'themes/'.$this->getName().'/';
	}
	
	
}
