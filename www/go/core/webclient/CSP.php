<?php
namespace go\core\webclient;

use go\core\jmap\Request;
use go\core\jmap\Response;
use go\core\SingletonTrait;

/**
 * Content Security Policy for webclients
 * 
 * You can extend this with {@see App::EVENT_HEAD}:
 * 
 * go()->on(App::EVENT_HEAD, Example::class, 'onHead');
 * 
 * public static function onHead() {
 *  CSP::get()->add(...)
 * }
 */
class CSP {

  use SingletonTrait;

  private $data = [];

  public function __construct()
  {
    $this->add("default-src", Request::get()->getHost())
    ->add("default-src", "'self'")
    ->add("default-src", "about:")
    ->add("font-src", "'self'")
    ->add('font-src', Request::get()->getHost())
    ->add('font-src', "data:")
    ->add("script-src", Request::get()->getHost())
    // ->add("script-src", "'nonce-" . Response::get()->getCspNonce() . "'")
    ->add("script-src", "'unsafe-eval'")
    ->add("script-src", "'self'")
    ->add("script-src", "'unsafe-inline'") //TODO replace all onclick="" in the code and remove this line
    ->add('img-src', Request::get()->getHost())
    ->add('img-src', "'self'")
    ->add('img-src', "about:")
    ->add('img-src', "data:")
    ->add('img-src', "http:")
    ->add('img-src', "https:")
    ->add('style-src', "'self'")
    ->add('style-src', "'unsafe-inline'")
    ->add("frame-src", "'self'")
    ->add('frame-src', 'https:')
    ->add('frame-src', 'http:')
    ->add('frame-src', "groupoffice:")
    ->add('frame-src', "groupoffices:")
	    ->add('frame-ancestors', "self");
  }

  public function add($directive, $value) {
    $this->data[$directive][] = $value;
    return $this;
  }

  public function __toString() {
    $str = "";
    foreach($this->data as $directive => $value) {
      $str .= $directive . ' ' .implode(' ', $value) .';';
    }
    return $str;
  }
}