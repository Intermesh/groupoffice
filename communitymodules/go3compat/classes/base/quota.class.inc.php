<?php
class quota {
	
	var $usage=0;
	var $quota=0;
	
	function __construct(){
		global $GO_CONFIG;
		
		$this->quota=$GLOBALS['GO_CONFIG']->quota;
		$this->usage = intval($GLOBALS['GO_CONFIG']->get_setting('file_storage_usage'));
		
	}
	
	function get(){
		return $this->usage;		
	}
	
	function set($usage){
		global $GO_CONFIG;
		$this->usage=$usage;
		return $GLOBALS['GO_CONFIG']->save_setting('file_storage_usage', $usage);		
	}
	
	function reset(){
		global $GO_CONFIG;
		
		$this->usage = ceil(File::get_directory_size($GLOBALS['GO_CONFIG']->file_storage_path));
		return $GLOBALS['GO_CONFIG']->save_setting('file_storage_usage', $this->usage);
	}
	
	function check($usage)
	{
		return $this->quota==0 || $this->usage+$usage<=$this->quota;
	}

	function add_file($filepath){
		$size = filesize($filepath)/1024;
		return $this->add($size);
	}

	/**
	 *
	 * @param int $usage in kilobytes
	 * @return true if allowed
	 */
	function add($usage)
	{
		global $lang;
		if($this->quota>0)
		{
			$usage = $usage>0?ceil($usage) : floor($usage);
			if(!$this->check($usage)){
					return false;
			}
			$this->usage+=$usage;
			$this->set($this->usage);
		}
		return true;
	}	
	
	function delete($path)
	{
		if($this->quota>0)
		{
			if(is_dir($path))
			{
				$size = File::get_directory_size($path);
				
			}else
			{
				$size = filesize($path)/1024;
			}			
			$this->add(-$size);
		}
	}
}
?>