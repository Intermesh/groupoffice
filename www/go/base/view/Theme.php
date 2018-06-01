<?php


namespace GO\Base\View;


class Theme{
	/**
	 * Get the name of the theme that is selected by the user.
	 * 
	 * @return StringHelper
	 */
	public function getName(){
		$theme = \GO::config()->allow_themes && \GO::user() ? \GO::user()->theme : \GO::config()->theme;
		
		if(!file_exists(\GO::view()->getPath().'themes/'.$theme.'/Layout.php')){
			return 'Paper';
		}  else {
			return $theme;
		}
	}
	
	/**
	 * Get the full path to the main theme folder with trailing slash.
	 * 
	 * @return StringHelper
	 */
	public function getPath(){
		return \GO::view()->getPath().'themes/'.$this->getName().'/';
	}
	
	/**
	 * Get the full path to the main theme folder with trailing slash.
	 * 
	 * @return StringHelper
	 */
	public function getUrl(){
		return \GO::view()->getUrl().'themes/'.$this->getName().'/';
	}
	
	
}
