<?php

namespace go\modules\community\syncfusion;

use go\core;
use go\core\App;
use go\core\model\Group;
use go\core\webclient\CSP;
use go\modules\community\syncfusion\model\Settings;

class Module extends core\Module
{
	public function getStatus(): string
	{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return 'Michal Charvat <info@michalcharvat.cz>';
	}

	public function autoInstall(): bool
	{
		return false;
	}

	public function getDependencies(): array
	{
		return ['legacy/files'];
	}

	/**
	 * @return \go\modules\community\syncfusion\model\Settings|null
	 */
	public function getSettings()
	{
		return Settings::get();
	}

	/**
	 * @return void
	 */
	public function defineListeners()
	{
		App::on(App::EVENT_HEAD, static::class, 'onHead');
		CSP::on(CSP::EVENT_CREATE, static::class, 'onCsp');
	}

	public static function onHead(): void
	{
		$settings = Settings::get();

		// Register license key if library is already loaded (e.g. by another module)
		if (!empty($settings->licenseKey)) {
			$escaped = json_encode($settings->licenseKey, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
			echo '<script>if(typeof ej !== "undefined") { ej.base.registerLicense(' . $escaped . '); }</script>' . "\n";
		}
	}

	public static function onCsp(CSP $csp): void
	{
		$settings = Settings::get();

		// Editor locales are fetched from jsdelivr regardless of library source
		$csp->add('connect-src', 'https://cdn.jsdelivr.net');

		if ($settings->librarySource === 'cdn' && !empty($settings->cdnUrl)) {
			$parsed = parse_url($settings->cdnUrl);
			if (isset($parsed['scheme'], $parsed['host'])) {
				$origin = $parsed['scheme'] . '://' . $parsed['host'];
				if (isset($parsed['port'])) {
					$origin .= ':' . $parsed['port'];
				}
				$csp
					->add('script-src', $origin)
					->add('style-src', $origin)
					->add('connect-src', $origin);
			}
		}
	}

	protected function beforeInstall(\go\core\model\Module $model): bool
	{
		$model->permissions[Group::ID_INTERNAL] = (new \go\core\model\Permission($model))
			->setRights(['mayRead' => true]);

		return parent::beforeInstall($model);
	}
}
