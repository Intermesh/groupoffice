<?php


namespace GO\Manualsite;

use GO;
use GO\Base\Module;
use GO\Site\Model\Site;

class ManualsiteModule extends Module {

	public function adminModule() {
		return false;
	}
	
	public function depends() {
		return array('site');
	}

	public function install() {
		
		if(GO::modules()->isInstalled('site')){
			$alreadyExists = Site::model()->findSingleByAttribute('module','adminmanual');
			
			if(!$alreadyExists){
				
				$siteProperties = array(
					'name'=>"Manual",
					'user_id'=>1,
					'domain'=>'*',
					'module'=>'manualsite',
					'ssl'=>'0',
					'mod_rewrite'=>'0',
					'mod_rewrite_base_path'=>'/',
					'base_path'=>'',
					'language'=>'en'
				);
				
				$defaultSite = new Site();
				$defaultSite->setAttributes($siteProperties);
				$defaultSite->save();
				
				
				$home = new GO\Site\Model\Content();
				$home->site_id=$defaultSite->id;
				$home->title="Home";
				$home->slug="";
				$home->template="/manualsite/home";
				$home->save();
				
				
				$chapter = new GO\Site\Model\Content();
				$chapter->parent_id=$home->id;
				$chapter->site_id=$home->id;
				$chapter->title="Example chapter";
				$chapter->content="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
				$chapter->slug="example-chapter";
				$chapter->template="/manualsite/content";
				$chapter->save();
				
				$sub = new GO\Site\Model\Content();
				$sub->parent_id=$chapter->id;
				$sub->site_id=$defaultSite->id;
				$sub->title="Sub 1";
				$sub->content="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
				$sub->slug="example-chapter";
				$sub->template="/manualsite/content";
				$sub->save();
				
				
				$sub = new GO\Site\Model\Content();
				$sub->parent_id=$chapter->id;
				$sub->site_id=$defaultSite->id;
				$sub->title="Sub 2";
				$sub->content="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
				$sub->slug="example-chapter";
				$sub->template="/manualsite/content";
				$sub->save();
				
				
				
				
			}
			
			$category = \GO\Customfields\Model\Category::model()->createIfNotExists("GO\Site\Model\Site", "Extra");
			\GO\Customfields\Model\Field::model()->createIfNotExists($category->id, "Google tracking code");
		}
		
		return parent::install();
	}
	
}
