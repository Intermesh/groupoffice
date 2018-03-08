<?php

namespace GO\Customcss;

class CustomcssModule extends \GO\Base\Module {

	public static function initListeners() {

		$c = new \GO\Core\Controller\AuthController();
		$c->addListener('head', 'GO\Customcss\CustomcssModule', 'head');

		return parent::initListeners();
	}

	public static function head() {
		if (file_exists(\GO::config()->file_storage_path . 'customcss/style.css'))
			echo '<style>' . file_get_contents(\GO::config()->file_storage_path . 'customcss/style.css') . '</style>' . "\n";


		if (file_exists(\GO::config()->file_storage_path . 'customcss/javascript.js'))
			echo '<script type="text/javascript">' . file_get_contents(\GO::config()->file_storage_path . 'customcss/javascript.js') . '</script>' . "\n";
	}

	public function adminModule() {
		return true;
	}

}
