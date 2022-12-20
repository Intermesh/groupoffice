<?php

namespace go\core\webclient;

use go\core\event\EventEmitterTrait;
use go\core\SingletonTrait;

/**
 * Content Security Policy for browsers
 *
 * You can extend this with an event listener
 *
 * @example
 * ...
 *
 * public function defineListeners() {
 *  parent::defineListeners();
 *  CSP::on(Csp::EVENT_CREATE,  static::class, 'onCreate');
 * }
 *
 * public static function onCreate(CSP $csp) {
 *  $csp->add(...)
 * }
 *
 * ...
 */
class CSP
{

	const EVENT_CREATE = 'create';

	use SingletonTrait;

	use EventEmitterTrait;

	private $data = [];

	protected function __construct()
	{
		$frameAncestors = go()->getConfig()['frameAncestors'];

		if (empty($frameAncestors)) {
			$frameAncestors = "'self'";
		} else {
			$frameAncestors = "'self' " . $frameAncestors;
		}

		$this
			//->add("default-src", Request::get()->getHost())
			->add("default-src", "'self'")
			->add("default-src", "about:")
			->add("font-src", "'self'")
			//->add('font-src', Request::get()->getHost())
			->add('font-src', "data:")
			//->add("script-src", Request::get()->getHost())
			// ->add("script-src", "'nonce-" . Response::get()->getCspNonce() . "'")
			->add("script-src", "'unsafe-eval'")
			->add("script-src", "'self'")
			->add("script-src", "'unsafe-inline'") //TODO replace all onclick="" in the code and remove this line
			//->add('img-src', Request::get()->getHost())
			->add('img-src', "'self'")
			->add('img-src', "about:")
			->add('img-src', "data:")
			->add('img-src', "blob:")
			->add('img-src', "http:")
			->add('img-src', "https:")
			->add('style-src', "'self'")
			->add('style-src', "'unsafe-inline'")
			->add("frame-src", "'self'")
			->add('frame-src', 'https:')
			->add('frame-src', 'http:')
			->add('frame-src', "groupoffice:")
			->add('frame-src', "groupoffices:")
			->add('frame-ancestors', $frameAncestors);

		static::fireEvent(self::EVENT_CREATE, $this);
	}

	/**
	 * Add a directive
	 *
	 * @param string $directive eg. "default-src"
	 * @param string $value eg "https://www.group-office.com"
	 * @return $this
	 */
	public function add($directive, $value)
	{
		$this->data[$directive][] = $value;
		return $this;
	}

	public function __toString()
	{
		$str = "";
		foreach ($this->data as $directive => $value) {
			$str .= $directive . ' ' . implode(' ', $value) . ';';
		}
		return $str;
	}
}