<?php


namespace GO\Base\Vobject;

class Iterator implements \Iterator {

	private $file;
	private $type;

	private $fp;
	
	private $current=false;
	
	private $key = 0;
	
	private $header=false;
	
	public function __construct(\GO\Base\Fs\File $file, $type = "VEVENT") {
		$this->file = $file;
		$this->type = $type;		
		
//		$this->rewind();		
	}
	
	private function getNextData(){		
		$buffer="";
		$found = false;
		$count=0;
		
		$buildHeader = empty($this->header);
		
		while($line = fgets($this->fp)){	
			$count++;
			
			if($buildHeader && trim($line) != "BEGIN:".$this->type){
				$this->header .= $line;				
			}else
			{			
				
				$buildHeader = false;
				
				$buffer .= $line;			
				if(trim($line)=="END:".$this->type){		
					$found=true;
					break;
				}
			}
			
			if($count==50000){
				//var_dump($buffer);
				throw new \Exception("Reached 50000 lines for one event. Aborting!");
			}
		}		
		
		
		//fclose($fp);
		
		return $found && !empty($buffer) ? $this->header.$buffer."END:VCALENDAR" : false;
	}

	#[\ReturnTypeWillChange]
	public function rewind() {
//		\GO::debug("rewind");
		if(is_resource($this->fp))
			fclose($this->fp);
		
		$this->current=false;
		$this->key=-1;
		$this->header=false;
		
		//if(!is_resource($this->fp))
		$this->fp = fopen($this->file->path(), "r");
		
		$this->next();
	}

	#[\ReturnTypeWillChange]
	public function current() {
//		\GO::debug("current");
		return $this->current;
	}

	#[\ReturnTypeWillChange]
	public function key() {
//		\GO::debug("key");
		return $this->key;
	}



	#[\ReturnTypeWillChange]
	public function next() {
//		\GO::debug("next");
		$data = $this->getNextData();
		
//		\GO::debug($data);
		
		$this->current=false;
				
		if(empty($data))			
			return false;
		
		$vcal = \GO\Base\VObject\Reader::read($data);
			
		$vevents = $vcal->select($this->type);
		
//		\GO::debug("Found: ".count($vevents));
		$vevent = array_shift($vevents);
		if($vevent){
//			\GO::debug("Found event");
			$this->current=$vevent;
			$this->key++;
			return $this->current;
		}else{
			return false;
		}
		
	}

	#[\ReturnTypeWillChange]
	public function valid() {
//		\GO::debug("valid");
		$ret = $this->current!=false;
//		\GO::debug($ret);
		return $ret;
	}

}
