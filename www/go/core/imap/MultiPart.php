<?php

namespace go\core\imap;


/**
 * MultiPart class
 * 
 * A multipart contains a text and html part for example
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class MultiPart extends Part{
	/**
	 * The sub parts of this part
	 * 
	 * @var Part[] 
	 */
	public $parts=[];
	
	
	public function __construct(Message $message, $partNumber, array $struct) {
		
		if(!empty($partNumber)){
			$partNumber .= ".0";
		}
		
		$this->message = $message;
		
		$this->partNumber = $partNumber;
		
		
		while($part = array_shift($struct)){			
			if(is_array($part)){
				
				$partNumber = $this->incrementPartNumber($partNumber);
				
				if(is_array($part[0])){
					$this->parts[] = new MultiPart($message, $partNumber, $part);
				}else
				{
					$this->parts[] = new SinglePart($message, $partNumber, $part);
				}
			}else
			{
				break;
			}
		}
		
		$this->subtype=$part;
		$this->params=array_shift($struct);
	}
	
}
