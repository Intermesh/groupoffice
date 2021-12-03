<?php

namespace GO\Email\Controller;

use go\core\jmap\EntityController;
use go\core\model;

class EmailTemplate extends EntityController {
	
	/**
	 * The class name of the entity this controller is for.
	 * 
	 * @return string
	 */
	protected function entityClass(): string
	{
		return \GO\Email\Model\EmailTemplate::class;
	}
	
	/**
	 * Return body and subject parsed
	 * 
	 * @param string $id
	 * @param array $entities
	 */
	public function parse($params) {
		
		$template = \GO\Email\Model\EmailTemplate::findById($params['id']);
		
		$parser = $this->getParser($params['entities'] ?? []);
		
		$body = $parser->parse($template->body);
		$subject = $parser->parse($template->subject);
		
		\go\core\jmap\Response::get()->addResponse(['body' => $body, 'subject' => $body]);
	}
	
	/**
	 * 
	 * @param array $entities Key = name in template and value is entity {name: "Contact", id: 1}
	 * @return \go\core\TemplateParser
	 */
	private function getParser($entities) {
		$parser = new \go\core\TemplateParser();
		
		foreach($entities as $name => $entity) {
			$type = \go\core\orm\EntityType::findByName($entity['name']);
			$cls = $type->getClassName();			
			
			$entity = $cls::findById($entity['id']);
			$parser->addModel($name, $entity);			
		}
		
		return $parser;		
	}
	
	
	/**
	 * Handles the Foo entity's Foo/query command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/query
	 */
	public function query($params) {
		return $this->defaultQuery($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/get command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/get
	 */
	public function get($params) {
		return $this->defaultGet($params);
	}
	
	/**
	 * Handles the Foo entity's Foo/set command
	 * 
	 * @see https://jmap.io/spec-core.html#/set
	 * @param array $params
	 */
	public function set($params) {
		return $this->defaultSet($params);
	}
	
	
	/**
	 * Handles the Foo entity's Foo/changes command
	 * 
	 * @param array $params
	 * @see https://jmap.io/spec-core.html#/changes
	 */
	public function changes($params) {
		return $this->defaultChanges($params);
	}
}
