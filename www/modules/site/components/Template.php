<?php

namespace GO\Site\Components;

use GO;

class Template{
	
	/**
	 * Get the path to the template folder
	 * 
	 * @return string
	 */
	public function getPath(){		
		if(empty(\Site::model()->module))
			return false;
		
		return \GO::config()->root_path . 'modules/' . \Site::model()->module . '/views/site/';	
	}
	
	/**
	 * Get URL to template folder. This is a static alias defined in the apache
	 * config
	 * 
	 * @return string
	 */
	public function getUrl(){
		$this->_checkLink();
		return \Site::assetManager()->getBaseUrl().'/template/';
	}
	
	private function _checkLink() {

		
		$folder = new \GO\Base\Fs\Folder(\Site::assetManager()->getBasePath());
				

			$templateFolder = $folder->createChild('template', false);
			

			$mtime = GO::config()->get_setting('site_template_publish_date_'.\Site::model()->id);
			
			if($mtime != GO::config()->mtime || !$templateFolder->exists()){
				$templateFolder->delete();
				
				$sourceTemplateFolder = new \GO\Base\Fs\Folder($this->getPath().'assets');
				
				if($sourceTemplateFolder->copy($folder, 'template')){
					GO::config()->save_setting('site_template_publish_date_'.\Site::model()->id, GO::config()->mtime);
				}
			}

	}
}
