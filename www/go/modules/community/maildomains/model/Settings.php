<?php
namespace go\modules\community\maildomains\model;
use go\core;


class Settings extends core\Settings {
		
	/**
	 * Mail hostname to use for SPF and MX check.
	 *
	 * If not set the web hostname is used.
	 */
	protected ?string $overrideMailHost = null;

	public function getMailHost() : string {
		return $this->overrideMailHost ?? core\jmap\Request::get()->getHost();
	}

	public function setMailHost(string $host): void
	{
		if($host != core\jmap\Request::get()->getHost()) {
			$this->overrideMailHost = $host;
		} else {
			$this->overrideMailHost = null;
		}
	}

}
