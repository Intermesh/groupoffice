<?php


namespace GO\Favorites;


class FavoritesModule extends \GO\Base\Module{
	
	public function package() {
		return self::PACKAGE_COMMUNITY;
	}
	
	public function autoInstall() {
		return false;
	}
	
	public function author() {
		return 'Wesley Smits';
	}
		
	public function authorEmail() {
		return 'wsmits@intermesh.nl';
	}		
}
