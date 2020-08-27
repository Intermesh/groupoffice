<?php
namespace GO\Base\View;

use GO;

abstract class AbstractView{
	
	abstract public function render($viewName, $data);
	
	
	/**
	 * Default headers to send. 
	 */
	protected function headers(){
		
		if(headers_sent())
			return;
		
		//iframe hack for file uploads fails with application/json				
		if(!\GO\Base\Util\Http::isAjaxRequest(false) || \GO\Base\Util\Http::isMultipartRequest()){
			header('Content-Type: text/html; charset=UTF-8');
		}else
		{
			header('Content-Type: application/json; charset=UTF-8');
		}		
//
////		header('Content-Type: text/html; charset=UTF-8');
//		header('X-XSS-Protection: 1; mode=block');
//		header('X-Content-Type-Options: nosniff');

		foreach(GO::config()->extra_headers as $header){
			header($header);
		}
	}
}
