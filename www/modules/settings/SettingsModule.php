<?php

namespace GO\Settings;


class SettingsModule extends \GO\Base\Module{
	public static function initListeners() {
		
		$c = new \GO\Core\Controller\AuthController();
		$c->addListener('inlinescripts', 'GO\Settings\SettingsModule', 'inlinescripts');
		
		return parent::initListeners();
	}
	
	public static function inlinescripts(){

		
		
		$t = \GO::config()->get_setting('login_screen_text_enabled');
		if(!empty($t)){
			$login_screen_text = \GO::config()->get_setting('login_screen_text');
			$login_screen_text_title = \GO::config()->get_setting('login_screen_text_title');
			
			echo 'GO.mainLayout.on("login", function(mainLayout){mainLayout.msg("'.\GO\Base\Util\StringHelper::escape_javascript ($login_screen_text_title).'", "'.\GO\Base\Util\StringHelper::escape_javascript ($login_screen_text).'", 3600, 600);});';
					
		}

	}
	
	public function adminModule() {
		return true;
	}
}
