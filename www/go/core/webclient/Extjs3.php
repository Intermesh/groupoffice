<?php

namespace go\core\webclient;

use GO;
use go\core\App;
use go\core\Environment;
use go\core\fs\File;
use go\core\jmap\Request;
use go\core\model\Module;
use go\core\SingletonTrait;

class Extjs3 {

	use SingletonTrait;


	
	public function flushCache() {
		return App::get()->getDataFolder()->getFolder('cache/clientscripts')->delete();
	}

	private $cssFile;

	/**
	 * 
	 * @param string $theme
	 * @return File
	 */
	public function getCSSFile($theme = 'Paper') {

		if(isset($this->cssFile)) {
			return $this->cssFile;
		}

		$cacheFile = go()->getDataFolder()->getFile('cache/clientscripts/' . $theme . '/style.css');
		$debug = go()->getDebugger()->enabled && $cacheFile->exists();
		if ($debug || !$cacheFile->exists()) {
			$modules = Module::getInstalled(['id', 'name', 'package']);
			$css = "";
			$modifiedAt = null;
			foreach ($modules as $module) {

				if (isset($module->package)) {

          $folder = $module->module()->getFolder();

          $file = $folder->getFile('views/extjs3/themes/' . $theme . '/style.css');
          if ($file->exists()) {
            $css .= $this->replaceCssUrl($file->getContents(),$file)."\n";

            if($debug && $file->getModifiedAt() > $modifiedAt) {
            	$modifiedAt = $file->getModifiedAt();
            }
          }


					$file = $folder->getFile('views/extjs3/themes/default/style.css');
					if ($file->exists()) {
						$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";

						if($debug && $file->getModifiedAt() > $modifiedAt) {
							$modifiedAt = $file->getModifiedAt();
						}
					}

					$file = $folder->getFile('views/goui/dist/style.css');
					if ($file->exists()) {
						$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";

						if($debug && $file->getModifiedAt() > $modifiedAt) {
							$modifiedAt = $file->getModifiedAt();
						}
					}


				}

				//old path
				$folder = Environment::get()->getInstallFolder()->getFolder('modules/' . $module->name);
				$file = $folder->getFile('themes/Default/style.css');
				if ($file->exists()) {
					$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";
					if($debug && $file->getModifiedAt() > $modifiedAt) {
						$modifiedAt = $file->getModifiedAt();
					}
				}

				$file = $folder->getFile('themes/' . $theme . '/style.css');
				if ($file->exists()) {
					$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";
					if($debug && $file->getModifiedAt() > $modifiedAt) {
						$modifiedAt = $file->getModifiedAt();
					}
				}
			}

			if(!$debug || $modifiedAt > $cacheFile->getModifiedAt()) {
				$cacheFile->putContents($css);
			}
		}
		$this->cssFile = $cacheFile;
		return $cacheFile;
	}
	
	
	private function replaceCssUrl($css, File $file){

//		$css .= "\n\n/*".$file->getPath() ." */\n\n";
		
		$baseurl = str_replace(Environment::get()->getInstallFolder()->getPath() . '/', $this->getRelativeUrl(), $file->getFolder()->getPath()).'/';
		
		$css = preg_replace_callback('/url[\s]*\(([^\)]*)\)/iU', 
			function($matches) use($baseurl) { 
				return 'url('.$baseurl.trim(stripslashes($matches[1]),'\'" ').')';
			}, $css);

		$css = str_replace("sourceMappingURL=", "sourceMappingURL=".$baseurl, $css);

		return $css;
	}
	
	
	/**
	 * 
	 * @return File
	 */
	public function getLanguageJS() {
		
		$iso = \go()->getLanguage()->getIsoCode();
	
		
		$cacheFile = go()->getDataFolder()->getFile('cache/clientscripts/lang_'.$iso.'.js');

		if (!$cacheFile->exists()) {

			$str = "var GO = GO || {};\n";

			$extjsLang = \go()->getLanguage()->t("extjs_lang");
			if ($extjsLang == 'extjs_lang') {
				// We save ISO region codes lower case, Ext requires upper case.
				$arIsoParts = explode('_', $iso);
				if (isset($arIsoParts[1])) {
					$arIsoParts[1] = strtoupper($arIsoParts[1]);
				}
				$extjsLang = implode('_', $arIsoParts);
			}

			$viewRoot = Environment::get()->getInstallFolder()->getFolder('views/Extjs3');

			$extLang = $viewRoot->getFile('javascript/ext-locale/ext-lang-' . $extjsLang . '.js');
			if ($extLang->exists()) {
				$str .= $extLang->getContents();
			}

			require(Environment::get()->getInstallFolder()->getFile('language/languages.php'));

			/** @var array $languages */
			$str .= "GO.Languages=[];\n";

			foreach ($languages as $code => $name) {
				$str .= 'GO.Languages.push(["' . $code . '","' . $name . '"]);' . "\n";
			}

			//Put all lang vars in js		
			$l = \go()->getLanguage()->getAllLanguage();
			$l['iso'] = $iso;

			$str .= 'GO.lang = ' . json_encode($l) . ";\n";
			
//			$str .= "GO.lang.holidaySets = " . json_encode(\GO\Base\Model\Holiday::getAvailableHolidayFiles()) .";\n";
			$str .= "GO.lang.holidaySets = " . json_encode(\go\core\model\Holiday::getHolidaySets()) .";\n";

			$cacheFile->putContents($str);
		}
		
		return $cacheFile;
	}

	private $baseUrl;

	/**
	 * Get URL to webclient
	 *
	 * eg. https://groupoffice.example.com/groupoffice/
	 *
	 * @return string
	 */
	public function getBaseUrl() {

		if(isset($this->baseUrl)) {
			return $this->baseUrl;
		}

		$this->baseUrl = Request::get()->isHttps() ? 'https://' : 'http://';
		$this->baseUrl .= Request::get()->getHost(false) . $this->getRelativeUrl();

		return $this->baseUrl;
	}

	/**
	 * Get relative URL to webclient.
	 *
	 * @return string eg. /groupofice/
	 */
	public function getRelativeUrl() {
		$path = dirname($_SERVER['SCRIPT_NAME']); // /index.php or /install/*.php
		$firstParent = basename($path);
		if($firstParent == 'install' || $firstParent == 'api') {
			$path = dirname($path);
		}

		if($firstParent == 'Extjs3') {
			$path = dirname($path, 2);
		}

		return rtrim($path, '/') . '/';
	}

	public function getBasePath() {
		return go()->getEnvironment()->getInstallPath();
	}

	/**
	 * Get available theme names as array
	 *
	 * @return string[]
	 */
	public function getThemes() {
		$themes = go()->getCache()->get("themes");
		if($themes == null) {
			$themes = [];
			$themeFolders = go()->getEnvironment()->getInstallFolder()->getFolder('views/Extjs3/themes')->getFolders();
			foreach($themeFolders as $themeFolder) {
				$themes[] = $themeFolder->getName();
			}

			go()->getCache()->set("themes", $themes);
		}

		return $themes;
	}

	private $theme = 'Paper';

	public function getTheme() {
		if(!isset($this->theme)) {
			if(go()->getAuthState() && go()->getAuthState()->isAuthenticated()) {
				$this->theme = go()->getAuthState()->getUser(['theme'])->theme;
				if(!file_exists(\GO::view()->getPath().'themes/'.$this->theme.'/Layout.php')){
					$this->theme = 'Paper';
				}
			} else{
				$this->theme = 'Paper';
			}
		}

		return $this->theme;
	}

	public function getThemePath() {
		return $this->getBasePath() . '/views/Extjs3/themes/' . $this->getTheme() . '/';
	}

	public function getThemeUrl() {
		$relativeUrl = $this->getRelativeUrl();
		if(strpos($relativeUrl, "/modules/") > -1) {
			return '/views/Extjs3/themes/' . $this->getTheme() . '/';
		}
		return $relativeUrl . 'views/Extjs3/themes/' . $this->getTheme() . '/';
	}

	public function renderPage($html, $title = null) {
		$themePath = $this->getThemePath();
		require($themePath . 'pageHeader.php');
		echo $html;
		require($themePath . 'pageFooter.php');
	}

	public $loadGoui;
	public $loadExt;

	public $bodyCls = '';

	public $useThemeSettings = true;

	public $gouiStyleSheet = 'groupoffice.css';

	public $density = 140;

	private $gouiScripts;
	private $goScripts = [];
	private $cacheFile = null; // stays null when debugging

	public function loadScripts() {

		if(!go()->getDebugger()->enabled) {
			$this->cacheFile = \go\core\App::get()->getDataFolder()->getFolder('cache/clientscripts')->create()->getFile('all.js');
			$this->gouiScripts = go()->getCache()->get("gouiScripts");
		}

		if($this->loadGoui && !$this->gouiScripts) {
			$this->gouiScripts = $this->loadGoui();
			if(!go()->getDebugger()->enabled)
				go()->getCache()->set("gouiScripts", $this->gouiScripts);
		}
		if($this->loadExt && (!$this->cacheFile || !$this->cacheFile->exists())) {
			$this->goScripts = $this->loadGoExt();
		}
	}

	public function drawScripts() {
		$rootPath = GO::config()->root_path;
		$baseUri = $this->getRelativeUrl();

		// always draw base ExtJs and language file
		if($this->loadExt) {
			echo '<script src="' . $this->getRelativeUrl() . 'views/Extjs3/javascript/ext-base-debug.js?mtime=' . filemtime(GO::config()->root_path . 'views/Extjs3/javascript/ext-base-debug.js') . '"></script>' .
				'<script src="' . $this->getRelativeUrl() . 'views/Extjs3/javascript/ext-all-debug.js?mtime=' . filemtime(GO::config()->root_path . 'views/Extjs3/javascript/ext-all-debug.js') . '"></script>' .
				'<script src="' . $this->getRelativeUrl() . 'views/Extjs3/lang.php?lang=' . \go()->getLanguage()->getIsoCode() . '&v=' . $this->getLanguageJS()->getModifiedAt()->format("U") . '"></script>' .
				'<script>Ext.namespace("GO");' .
				'GO.settings = ' . json_encode($this->clientSettings()) . ';' .
				'GO.language = "' . go()->getLanguage()->getIsoCode() . '";' .
				'GO.calltoTemplate = "' . GO::config()->callto_template . '";' .
				'GO.calltoOpenWindow = ' . (GO::config()->callto_open_window ? "true" : "false") . ';' .
				'window.name="' . GO::getId() . '";' .
				"var BaseHref = '" . $baseUri . "';GO.version='".go()->getVersion()."';";
			if (isset(GO::session()->values['security_token'])) {
				echo 'GO.securityToken="' . GO::session()->values['security_token'] . '";' .
					'Ext.Ajax.extraParams={security_token:"' . GO::session()->values['security_token'] . '"};';
			}
			echo '</script>';
		}
		// draw go Ext scripts
		if(!go()->getDebugger()->enabled) {
			if(!$this->cacheFile->exists()) {
				$minify = new \MatthiasMullie\Minify\JS();
				foreach ($this->goScripts as $script) {
					$minify->add($script);
				}
				$minify->gzip($this->cacheFile->getPath());
			}
			echo '<script type="text/javascript" src="' . GO::view()->getUrl() . 'script.php?v='. $this->cacheFile->getModifiedAt()->format("U") . '"></script>';

		} else {
			foreach ($this->goScripts as $script) {
				echo ($script instanceof File ?
						'<script src="' . str_replace($rootPath, '', $script->getPath())  . '"></script>' :
						'<script>' . $script . '</script>') . "\n";

			}

		}

		// gouiScripts array is loaded from apcu when not debugging
		foreach($this->gouiScripts as $script) {
			echo '<script type="module" src="'.str_replace($rootPath, $baseUri, $script->getPath()). '?v='.go()->getVersion().'"></script>' . "\n";
		}
		if (file_exists(GO::view()->getTheme()->getPath() . 'MainLayout.js')) {
			echo '<script src="' . GO::view()->getTheme()->getUrl() . 'MainLayout.js" type="text/javascript"></script>';
			echo "\n";
		}
		echo '<script>';

		//these parameter are passed by dialog.php. These are used to directly link to
//a dialog.
		if (isset($_REQUEST['f'])) {
			if (substr($_REQUEST['f'], 0, 9) == '{GOCRYPT}')
				$fp = Crypt::decrypt($_REQUEST['f']);
			else
				$fp = json_decode(base64_decode($_REQUEST['f']), true);

			GO::debug("External function parameters:");
			GO::debug($fp);

			echo 'if (GO.' . $fp['m'] .'){GO.mainLayout.on("render", function () {GO. '. $fp['m'] . '.' . $fp['f'] . '.call(this,  '. json_encode($fp['p']) .');});}';
		}


		echo 'Ext.onReady(GO.mainLayout.boot, GO.mainLayout);</script>';
	}

	public function clientSettings(){
		return [
			'max_row_list' => 50,
			'config' => [
				'theme' => GO::config()->theme,
				'product_name' => GO::config()->product_name,
				'host' => GO::config()->host,
				'title' => GO::config()->title,
				'full_url' => GO::config()->full_url,
				'allow_password_change' => GO::config()->allow_password_change,
				'allow_themes' => GO::config()->allow_themes,
				'allow_profile_edit' => GO::config()->allow_profile_edit,
				'max_users' => GO::config()->max_users,
				'debug' => go()->getDebugger()->enabled,
				'max_attachment_size' => GO::config()->max_attachment_size,
				'max_file_size' => GO::config()->max_file_size,
				'help_link' => GO::config()->help_link,
				'support_link' => GO::config()->support_link,
				'report_bug_link' => GO::config()->report_bug_link,
				'nav_page_size' => intval(GO::config()->nav_page_size),
				'logoutWhenInactive' => intval(go()->getSettings()->logoutWhenInactive),
				'tickets_no_email_required' => GO::config()->tickets_no_email_required,
				'default_country' => GO::config()->default_country,
				'checker_interval' => (int) GO::config()->checker_interval,
				'remember_login' => GO::config()->remember_login,
				'encode_callto_link' => GO::config()->encode_callto_link,
				'login_message' => GO::config()->login_message,
				'hideAbout' => \GO::config()->hideAbout,
				'email_allow_body_search' => GO::config()->email_allow_body_search,
				'lostPasswordURL' => go()->getSettings()->lostPasswordURL
			],
			'state_index' => 'go',
			'language' => go()->getLanguage()->getIsoCode(),
			'show_contact_cf_tabs' => [],
			'version' => go()->getVersion()
		];
	}


	private function loadGoui() {
		$scripts = [];
		$load_modules = GO::modules()->getAllModules(true);
		foreach ($load_modules as $module) {
			$bundleFile = new File($module->moduleManager->path(). 'views/goui/dist/Index.js');
			if($bundleFile->exists()) {
				$scripts[] = $bundleFile;
			}
		}
		return $scripts;
	}

	private function readScriptsTxt($path, $prefix = '') {
		if (file_exists($path)) {
			$data = file_get_contents($path);
			$lines = array_map('trim', explode("\n", $data));

			foreach ($lines as $line) {
				if (!empty($line)) {
					$file = new File(GO::config()->root_path. $prefix. $line);
					if($file->exists()) {
						yield $file;
					}
				}
			}
		}
	}

	private function loadGoExt() {
		$scripts = [];
		$scripts[] = new File(GO::config()->root_path . 'views/Extjs3/javascript/namespaces.js');
		//for t() function to auto detect module package and name
		$scripts[] = "go.module='core';go.package='core';";

		foreach($this->readScriptsTxt(GO::config()->root_path . 'views/Extjs3/javascript/scripts.txt') as $s) {
			$scripts[] = $s;
		}

		$load_modules = GO::modules()->getAllModules(true);
		if (!empty($load_modules))
			foreach ($load_modules as $module) {

				$pkg = $module->package ? $module->package : "legacy";
				$scripts[] = 'Ext.ns("'.($module->package ? 'go.modules.' . $module->package . '.' . $module->name : 'GO.' . $module->name).'"); '.
					"go.module = '" . $module->name . "'; ".
					"go.package = '" . $pkg . "'; ".
					"go.Translate.setModule('" . $pkg . "', '" .$module->name . "'); ";

				$prefix = "";
				if ($module->moduleManager instanceof \go\core\Module) {
					$prefix = dirname(str_replace("\\", "/", get_class($module->moduleManager))) . "/views/extjs3/";
					$scriptsFile = $module->moduleManager->path() . 'views/extjs3/scripts.txt';

					//fallback to old dir
					$modulePath = GO::config()->root_path . 'modules/' . $module->moduleManager->getName() . '/';
				} else {
					$scriptsFile = false;
					$modulePath = $module->moduleManager->path();
				}

				if (!$scriptsFile || !file_exists($scriptsFile)) {
					$scriptsFile = $modulePath . 'scripts.txt';
					if (!file_exists($scriptsFile))
						$scriptsFile = $modulePath . 'views/Extjs3/scripts.txt';

				}

				foreach($this->readScriptsTxt($scriptsFile, $prefix) as $s) {
					$scripts[] = $s;
				}
			}
		return $scripts;
	}

}
