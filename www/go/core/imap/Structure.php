<?php

namespace go\core\imap;

use Closure;
use go\core\data\Model;
use function str_split;

/**
 * Message body structure
 * 
 * Reads the structure and turns it into SinglePart and MultiPart objects
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Structure extends Model {

	/**
	 * The IMAP message it belongs to
	 * 
	 * @var Message 
	 */
	private $message;
	
	/**
	 * The parts of the structure.
	 * 
	 * Parts can have sub parts too.
	 * 
	 * @var Part[] 
	 */
	private $parts;
	
	public function __construct(Message $message, $structureString) {
		
//var_dump($structureString);
		$this->message = $message;	
		$startpos = strpos($structureString, "BODYSTRUCTURE");
		$endpos = strpos($structureString, ' BODY[');
		if(!$endpos){			
			$cutString = substr($structureString, $startpos + 14);
		}else
		{
			$length = $endpos-$startpos+13;
			$cutString = substr($structureString, $startpos + 14, $length);
		}
		$struct = $this->parseStructure($cutString);
		

		if(is_array($struct[0])){
			$this->parts[] = new MultiPart($message,"", $struct);
		}else
		{
			$this->parts[] = new SinglePart($message, "1", $struct);
		}
		
	}
	
	/**
	 * The parts of the structure.
	 * 
	 * Parts can have sub parts too.
	 * 
	 * @return Part[]
	 */
	public function getParts() {
		return $this->parts;
	}
	
	public function toArray(array $properties = null): array
	{
		$arr=[];
		foreach($this->parts as $part){
			$arr[] = $part->toArray();
		}
		
		return $arr;
	}
	

	private function parseStructure($structStr) {

		$structStr = trim($structStr);
		
		//cut off the outer parenthesis
		$structStr = substr($structStr, 1, -1);

		//makes parsing easier
		$structStr = str_replace(')(', ') (', $structStr);
		
		//I encountered ""Textual alternative to HTML part""
		//so replace these double quotes
		$structStr = preg_replace('/ ""(\w)/', ' "$1', $structStr);
		$structStr = preg_replace('/(\w)"" /', '$1" ', $structStr);

		$array = str_split($structStr, 1);

		$tokens = [];

		$inQuotes = false;

		$subLevel = 0;

		$buffer = '';

		for ($i = 0, $c = count($array); $i < $c; $i++) {

			$char = $array[$i];

			switch ($char) {
				case '"':					
					if (!$inQuotes) {
						$inQuotes = true;
					} else {
						$inQuotes = false;
					}					
					
					if($subLevel != 0) {
						$buffer .= $char;
					}					
					break;

				case '(':
					if(!$inQuotes){
						$subLevel++;
					}
					$buffer .= $char;
					break;

				case ')':
					if(!$inQuotes){
						$subLevel--;
					}
					$buffer .= $char;

					break;

				case ' ':
					if ($subLevel == 0 && !$inQuotes) {
						$tokens[] = $buffer;
						$buffer = "";
					} else {
						$buffer .= $char;
					}
					break;

				default:
					$buffer .= $char;
					break;
			}
		}

		$tokens[] = $buffer;
		$buffer = "";
	
		
		for ($i = 0, $c = count($tokens); $i < $c; $i++) {
			if (substr($tokens[$i], 0, 1) == '(') {
				$tokens[$i] = $this->parseStructure($tokens[$i]);
			}
		}

		return $tokens;
	}

	
	
	/**
	 * Check if the message has an alternative html body
	 * 
	 * @param array $parts Used internally for recursion
	 * @return boolean
	 */
	public function hasAlternativeBody($parts = null){		
		
		if(!isset($parts)){
			$parts = $this->parts;
		}	

		foreach($parts as $part){
			if($part instanceof SinglePart){
				return false;
			}else
			{
				if($part->type == 'alternative'){
					return true;
				}else
				{
					return $this->hasAlternativeBody($part->parts);
				}
			}
		}	
	}
	
	/**
	 * Get part by number
	 * 
	 * @param string $partNumber
	 * @return Part|boolean
	 */
	public function getPart($partNumber){
		$parts = $this->findParts(['partNumber' => $partNumber]);
		
		if(!isset($parts[0])){
			return false;
		}  else {
			return $parts[0];
		}
	}
	
	/**
	 * Find parts by type
	 * 
	 * @param array $props Key value of part properties that must match
	 * @param array $parts
	 * @return SinglePart[]
	 */
	public function findParts(array $props, $parts = null) {	
		
		$results  = [];
		
		if(!isset($parts)){
			$parts = $this->parts;		
		}
		
		foreach($parts as $part){
			
			$match = true;
			foreach($props as $name => $value){
				
//				echo $part->$name.' != '.$value."\n";
				
				if(!isset($part->$name) || $part->$name != $value){
					$match = false;
					break;
				}
			}
			
			
			if($match){
//				echo 'ja';
				$results[] = $part;
			}
			
			if($part instanceof MultiPart){
				$results = array_merge($results, $this->findParts($props, $part->parts));				
			}
		}
		return $results;
	}
	
	
	/**
	 * Find parts by type
	 * 
	 * @param Closure $fn Function that is called with the part
	 * @param array $parts
	 * @return SinglePart[]
	 */
	public function findPartsBy(Closure $fn, $parts = null) {	
		
		$results  = [];
		
		if(!isset($parts)){
			$parts = $this->parts;		
		}
		
		foreach($parts as $part){
			
			$match = $fn($part);		
			
			
			if($match){
				$results[] = $part;
			}
			
			if($part instanceof MultiPart){
				$results = array_merge($results, $this->findPartsBy($fn, $part->parts));				
			}
		}
		return $results;
	}

}
