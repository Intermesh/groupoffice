<?php
namespace go\core\imap;

use go\core\imap\Message;
use go\core\imap\Part;
use go\core\util\StringUtil;
use Html2Text\Html2Text;


/**
 * SinglePart class
 * 
 * A single part can be the html body or an attachment part
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class SinglePart extends Part{
	
	public function __construct(Message $message, $partNumber, array $struct) {
		
		
		
		$this->message = $message;
		
		$this->partNumber = $partNumber;
		        //("application" "pdf" NIL NIL NIL "base64" 7682 NIL ("attachment" ("filename" "order_F0000_2015_0036_8704.pdf")) NIL NIL) "mixed" ("boundary" "=_87037aab8b97daa841cac2efbdbd71d1") NIL NIL NIL)"
		$atts = [
				'type', //0 
				'subtype', //1
				'params', //2
				'id', //3
				'description', //4 
				'encoding', //5
				'size', //6
				'lines', //7 Only if type = 'text'
				'md5', //8
				'disposition', //9			
				'language', //10
				'location' //11
				];
		
		$c = count($struct);
		if($c>12) {
			$c = 12;
		}
		
//		if($c > 12) {
//			throw new \Exception("Wrong count ".var_export($struct, true));
//		}
		
		
		for($i = 0; $i < $c; $i++) {
			
			switch($atts[$i]) {
				
				case 'size':
					$struct[$i] = intval($struct[$i]);
					
					//lines is only present if type = text
					if($this->type !== 'text') {
						array_splice($atts, 7, 1);						
						$c--;
					}
					
					break;
				case 'type':
				case 'subtype':
				case 'encoding':
				//case 'disposition':
					$struct[$i] = strtolower($struct[$i]);
				break;
			
				
			}
			
			if($atts[$i] == 'subtype' && $struct[$i]=='rfc822'){
				//in this case the sub structure is also passed. We ignore this.
				/*
				 * [
                    "message",
                    "rfc822",
                    "NIL",
                    "NIL",
                    "NIL",
                    "7bit",
                    "1518",
                    [
                        "Thu, 03 Jan 2013 11:19:39 +0100",
                        "Complete your Group-Office trial installation",
                        [
                            [
                                "Group-Office",
                                "NIL",
                                "mschering",
                                "intermesh.nl"
                            ]
                        ],
                        [
                            [
                                "Group-Office",
                                "NIL",
                                "mschering",
                                "intermesh.nl"
                            ]
                        ],
                        [
                            [
                                "Group-Office",
                                "NIL",
                                "mschering",
                                "intermesh.nl"
                            ]
                        ],
                        [
                            [
                                "demo demo",
                                "NIL",
                                "demo",
                                "demo.com"
                            ]
                        ],
                        "NIL",
                        "NIL",
                        "NIL",
                        "<1357208379.50e55b3b449b4@server.trials.group-office.com>"
                    ],
                    [
                        "text",
                        "plain",
                        [
                            "charset",
                            "utf-8"
                        ],
                        "NIL",
                        "NIL",
                        "quoted-printable",
                        "729",
                        "28",
                        "NIL",
                        "NIL",
                        "NIL",
                        "NIL"
                    ],
                    "44",
                    "NIL",
                    "NIL",
                    "NIL",
                    "NIL"
                ]
				 */
				$c = 6;				
			}
			
			//echo $atts[$i].' = '.$this->_parseValue($struct[$i])."\n";
			
			$this->{$atts[$i]} = $this->_parseValue($struct[$i]);
		}
		
		if(isset($this->id)) {
			$this->id = trim($this->id, '<> ');
		}		
	}
	
	
	
//	public function getUrl(){
//		
//	}
	
	
	private function _parseValue($v){
		if(is_array($v)){

			$value = [];
			for($n = 0, $c2 = count($v); $n < $c2; $n++){
				
				$key = $v[$n++];
				
				if(is_array($key)){
					//throw new Exception("Something wrong with the structure.".var_export($key, true). " parts: ".var_export($v, true));
					$value = array_merge($value, $this->_parseValue($key));
				}else
				{				
					$value[strtolower($key)] = isset($v[$n]) ? $this->_parseValue($v[$n]) : null;					
				}
			}
			
			return $value;

		}  else {			
			return $v != 'NIL' ? $v : null;
		}
	}
	
	/**
	 * Get's the data decoded
	 * 
	 * @param string
	 */
	public function getDataDecoded(){
		switch(strtolower($this->encoding)){
			case 'base64':
					return base64_decode($this->getData());
				
			case 'quoted-printable':
					return quoted_printable_decode($this->getData());
			default:
				
				return $this->getData();
		}
	}
	
	
	public function toHtml() {
		
		if($this->type == 'text') {
			$body = $this->getDataDecoded();
			$body = StringUtil::cleanUtf8($body, isset($this->params['charset']) ? $this->params['charset'] : null);
			$body =  StringUtil::normalizeCrlf($body, "\n");
			
			if ($this->subtype == 'plain') {				
				$body = StringUtil::textToHtml($body);
			}else
			{
				$body = StringUtil::sanitizeHtml($body);
				$body = StringUtil::convertLinks($body);
			}
		}else if($this->type=='image')
		{
			$body = '<img src="cid:'.$this->id.'" />';
		}else
		{
			return false;
		}
		return '<div class="part part-'.$this->partNumber.'">'.$body.'</div>';	
	}
	
	
	public function toText() {
		if($this->type == 'text') {
			$body = $this->getDataDecoded();
			$body = StringUtil::cleanUtf8($body, isset($this->params['charset']) ? $this->params['charset'] : null);
			$body =  StringUtil::normalizeCrlf($body, "\n");
			
			if ($this->subtype == 'plain') {				
				return $body;
			}else
			{
		
				$html = new Html2Text($body);			
				return $html->getText();
				
			}
		}else
		{
			return false;
		}
	}

	
	
	public function toArray(array $properties = null): array
	{
		return parent::toArray($properties);
	}
	
	
}
