<?php
class contact_customfield_type extends default_customfield_type {
	function format_for_display($field, &$record, $fields) {
		//global $GO_MODULES;



		if(!empty($record[$field['dataname']])) {

			if(defined('EXPORTING')){
				$record[$field['dataname']]=$this->get_name($record[$field['dataname']]);
			}  else {
				$record[$field['dataname']]='<a href="#" onclick=\'GO.linkHandlers[2].call(this,'.
				$this->get_id($record[$field['dataname']]).');\' title="'.$this->get_name($record[$field['dataname']]).'">'.
					$this->get_name($record[$field['dataname']]).'</a>';
			}

			
		}
	}

	private function get_id($cf) {
		$pos = strpos($cf,':');
		return substr($cf,0,$pos);
	}

	private function get_name($cf) {
		$pos = strpos($cf,':');
		return htmlspecialchars(substr($cf,$pos+1), ENT_COMPAT,'UTF-8');
	}
}