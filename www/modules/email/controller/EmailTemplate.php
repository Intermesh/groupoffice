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
	protected function entityClass() {
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
}
