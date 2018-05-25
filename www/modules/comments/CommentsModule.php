<?php

namespace GO\Comments;


class CommentsModule extends \GO\Base\Module{
	public function autoInstall() {
		return true;
	}
	
	public static function submitSettings($settingsController, &$params, &$response, $user) {
		
		\GO::config()->save_setting('comments_enable_read_more', isset($params['comments_enable_read_more']) ? $params['comments_enable_read_more'] : '0', \GO::user()->id);
		\GO::config()->save_setting('comments_disable_orig_contact', isset($params['comments_disable_orig_contact']) ? $params['comments_disable_orig_contact'] : '0', \GO::user()->id);
		\GO::config()->save_setting('comments_disable_orig_company', isset($params['comments_disable_orig_company']) ? $params['comments_disable_orig_company'] : '0', \GO::user()->id);
		
		return parent::submitSettings($settingsController, $params, $response, $user);
	}
	
	public static function loadReadMore(){
		
		$readMore = \GO::config()->get_setting("comments_enable_read_more",\GO::user()->id);
		
		if($readMore === false)
			return 1; // By default (when the setting is not set) return 1;
		else
			return $readMore;
	}
	
	public static function commentsRequired(){
		return isset(\GO::config()->comments_category_required)?\GO::config()->comments_category_required:false;
	} 
	
	
	public static function disableOriginalCommentsCompany(){
		
		if(!empty(\GO::config()->comments_disable_original_company)){
			return 1;
		}
		
		$disComp = \GO::config()->get_setting("comments_disable_orig_company",\GO::user()->id);
		if($disComp === false)
			return 0; // By default (when the setting is not set) return 1;
		else
			return $disComp;
	} 
	
	public static function disableOriginalCommentsContact(){
		
		if(!empty(\GO::config()->comments_disable_original_contact)){
			return 1;
		}
		
		$disCont = \GO::config()->get_setting("comments_disable_orig_contact",\GO::user()->id);
		if($disCont === false)
			return 0; // By default (when the setting is not set) return 1;
		else
			return $disCont;
	} 
	
	
	public static function loadSettings($settingsController, &$params, &$response, $user) {
		
		$response['data']['comments_enable_read_more'] = self::loadReadMore();
		$response['data']['comments_disable_orig_contact'] = self::disableOriginalCommentsContact();
		$response['data']['comments_disable_orig_company'] = self::disableOriginalCommentsCompany();
		
		return parent::loadSettings($settingsController, $params, $response, $user);
	}
	
}
