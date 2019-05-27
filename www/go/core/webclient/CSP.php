<?php
namespace go\core\webclient;

use go\core\jmap\Request;
use go\core\jmap\Response;

/*
 groupoffices: groupoffice:"
*/
class CSP {

  private $data = [];

  public function __construct()
  {
    $this->add("default-src", Request::get()->getHost())
    ->add("default-src", "'self'")
    ->add("script-src", Request::get()->getHost())
    // ->add("script-src", "'nonce-" . Response::get()->getCspNonce() . "'")
    ->add("script-src", "'unsafe-eval'")
    ->add("script-src", "'self'")
    ->add("script-src", "'unsafe-inline'") //TODO replace all onclick="" in the code and remove this line
    ->add('img-src', Request::get()->getHost())
    ->add('img-src', "'self'")
    ->add('img-src', "data:")
    ->add('img-src', "http:")
    ->add('img-src', "https:")
    ->add('style-src', "'self'")
    ->add('style-src', "'unsafe-inline'")
    ->add("frame-src", "'self'")
    ->add('frame-src', 'https:')
    ->add('frame-src', 'http:')
    ->add('frame-src', "groupoffice:")
    ->add('frame-src', "groupoffices:");
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