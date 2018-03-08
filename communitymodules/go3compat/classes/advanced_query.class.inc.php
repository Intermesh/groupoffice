<?php
class advanced_query extends db{
	function add_search_query($sql) {
		$sql['id'] = $this->nextid('go_saved_search_queries');
		return $this->insert_row('go_saved_search_queries',$sql);
	}

	function get_search_queries($user_id) {
		$this->query("SELECT * FROM go_saved_search_queries WHERE user_id='".intval($user_id)."' ");
		return $this->num_rows();
	}

	function get_search_query($id) {
		$this->query("SELECT * FROM go_saved_search_queries WHERE id='".$this->escape($id)."'");
		return $this->next_record();
	}

	function delete_search_query($sql_id) {
		return $this->query("DELETE FROM go_saved_search_queries WHERE id='".$this->escape($sql_id)."'");
	}

	public function parse_advanced_query($query, $link_type=0) {

		// DATE SEARCH 1
		preg_match_all("/unix_timestamp[(]'([0-9]+)[-]([0-9]+)[-]([0-9]+)'[)]/",$query,$matched_tags,PREG_SET_ORDER);

		foreach($matched_tags as $tag) {
			try {
				$time = Date::to_unixtime($tag[1].'-'.$tag[2].'-'.$tag[3]);

				if (empty($time))
					throw new Exception('Exception!');
			} catch (Exception $e) {
				throw new Exception('Error with date string: '.$tag[0]);
			}
			$query = str_replace($tag[0],'\''.$time.'\'',$query);
		}

		preg_match_all("/[`)]{1}[\s]*(NOT INCLUDES)[\s]*'/",$query,$matched_tags,PREG_SET_ORDER);
		foreach($matched_tags as $tag) {
			$tag_new = str_replace($tag[1],'!=',$tag[0]);
			$query = str_replace($tag[0],$tag_new,$query);
		}

		preg_match_all("/[`)]{1}[\s]*(INCLUDES)[\s]*'/",$query,$matched_tags,PREG_SET_ORDER);
		foreach($matched_tags as $tag) {
			$tag_new = str_replace($tag[1],'=',$tag[0]);
			$query = str_replace($tag[0],$tag_new,$query);
		}

		preg_match_all("/[`)]{1}[\s]*(AT MOST)[\s]*'/",$query,$matched_tags,PREG_SET_ORDER);
		foreach($matched_tags as $tag) {
			$tag_new = str_replace($tag[1],'<=',$tag[0]);
			$query = str_replace($tag[0],$tag_new,$query);
		}

		preg_match_all("/[`)]{1}[\s]*(AT LEAST)[\s]*'/",$query,$matched_tags,PREG_SET_ORDER);
		foreach($matched_tags as $tag) {
			$tag_new = str_replace($tag[1],'>=',$tag[0]);
			$query = str_replace($tag[0],$tag_new,$query);
		}

		// DATE SEARCH 2
//		preg_match_all("/[(][\s]*'([0-9]+)-([0-9]+)-([0-9]+)'/",$query,$matched_tags,PREG_SET_ORDER);
//
//		foreach($matched_tags as $tag) {
//			try {
//				$time = mktime(0,0,0,$tag[2],$tag[3],$tag[1]);
//				if (empty($time))
//					throw new Exception('Exception!');
//			} catch (Exception $e) {
//				throw new Exception('Error with date string: '.$tag[0]);
//			}
//			$query = str_replace('('.$tag[0],$time,$query);
//		}

		if($link_type)
			$query = $this->parse_custom_fields($query, $link_type);

		preg_match_all("/'([0-9]+):(.+)'/i",$query,$matched_tags, PREG_SET_ORDER);
		foreach($matched_tags as $tag) {
			try {
				$query = str_replace($tag[0],$tag[1],$query);
			} catch (Exception $e) {
				throw new Exception('Incorrect user/contact field: '.$tag[0]);
			}
		}

		return ' '.$query;
	}

	public function parse_custom_fields($query, $link_type){
		global $GO_MODULES;
		if(isset($GLOBALS['GO_MODULES']->modules['customfields'])){
			require_once($GLOBALS['GO_MODULES']->modules['customfields']['class_path'].'customfields.class.inc.php');
			$cf = new customfields();

			preg_match_all('/`cf:([^:]*):([^`]*)`/i',$query,$matched_tags, PREG_SET_ORDER);
			//go_debug($matched_tags);
			foreach($matched_tags as $tag) {
					//$field=

					//$buffer = str_replace('cf:','',$tag[0]);
					//$category_pos = strpos($buffer,':');
				try {
					$category = $cf->get_category_by_name($link_type, $tag[1]);
					$field = $cf->get_field_by_name($category['id'], $tag[2]);
				} catch (Exception $e) {
					throw new Exception('Customfield not recognized: '.$tag[0]);
				}

				$query = str_replace($tag[0],'col_'.$field['id'],$query);
			}
		}

		return $query;
	}

}