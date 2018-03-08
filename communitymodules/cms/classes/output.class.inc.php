<?php
require_once($GLOBALS['GO_CONFIG']->class_path.'TagParser.class.inc.php');

class cms_output extends cms {

	var $site;
	var $file_id;
	var $folder_id;
	var $config;
	var $head='';

	var $safe_regex = "/[^\pL0-9]/i";

	function __construct() {
		parent::__construct();

		if(isset($_REQUEST['site_id']))
			$_SESSION['site_id']=$_REQUEST['site_id'];
	}


	function create_href_by_file($file, $querystring=''){
		
		if($this->site['enable_rewrite']=='1') {
			

			$path = $this->build_path($file['folder_id'], true, $this->site['root_folder_id']);
			$path = substr($path,-1)=='/' ? $path : $path.'/';
			$url = $this->site['rewrite_base'].$path.$this->special_encode($file['name']);
			if(!empty($querystring)){
				$url .= '?'.$querystring;
			}
		}else {
			$url = $GLOBALS['GO_MODULES']->modules['cms']['url'].'run.php?file_id='.$file['id'];
			if(!empty($querystring)){
				$url .= '&amp;'.$querystring;
			}
		}

		return $url;
	}
	function create_href_by_folder_id($folder_id, $querystring=''){
		if($this->site['enable_rewrite']=='1') {

			$path = $this->build_path($folder_id, true, $this->site['root_folder_id']);

			$url = $this->site['rewrite_base'].$path;
			if(!empty($querystring)){
				$url .= '?'.$querystring;
			}
		}else {
			$url = $GLOBALS['GO_MODULES']->modules['cms']['url'].'run.php?file_id='.$file_id;
			if(!empty($querystring)){
				$url .= '&amp;'.$querystring;
			}
		}
		return $url;
	}
	function create_href_by_path($path, $querystring=''){

		if($this->site['enable_rewrite']=='1') {
			$url = $this->site['rewrite_base'].$path;
			if(!empty($querystring)){
				$url .= '?'.$querystring;
			}
		}else {
			$url = $GLOBALS['GO_MODULES']->modules['cms']['url'].'run.php?site_id='.$this->site['id'].'&path='.$path;
			if(!empty($querystring)){
				$url .= '&amp;'.$querystring;
			}
		}
		return $url;
	}

	/*
	 * A page must call load_site, set_by_id or set_by_path to inititalize
	*/

	function load_site() {
		$this->site=$this->get_site($_SESSION['site_id']);
		$this->load_config();
	}

	function load_config() {
		global $GO_MODULES, $GO_LANGUAGE;

		$conf = $GO_MODULES->modules['cms']['path'].'templates/'.$this->site['template'].'/config.php';
		if(file_exists($conf)) {
			require($conf);

			$this->config = $config;
		}

		//disable rewriting if we are not in the configured domain
		if(strpos($_SERVER['HTTP_HOST'], $this->site['domain'])===false){
			$this->site['enable_rewrite']='0';
		}

		$GO_LANGUAGE->set_language($this->site['language']);

		$this->get_feeds($this->site['id']);
		while($feed = $this->next_record()){
			$this->head .= '<link rel="alternate" type="application/rss+xml" title="'.$feed['name'].'" href="'.$GO_MODULES->modules['cms']['full_url'].'feed.php?folder_id='.$feed['id'].'">'."\r\n";
		}
		

	}

	function set_by_id($file_id=0, $folder_id=0) {
		global $GO_MODULES;
		//$this->folder['id']=$folder_id;
		//$this->file['id']=$file_id;

		if(empty($file_id)) {
			$this->find_file($folder_id);
		}else {
			$this->file = $this->get_file($file_id);
		}

		$folder_id=$this->file['folder_id'];

		$this->folder=$this->get_folder($folder_id);

		$this->site=$this->get_site($this->folder['site_id']);
		$_SESSION['site_id']=$this->site['id'];

		//still no file?
		if(!$this->file) {
			$this->file['content']='No file found';
		}else {
			$this->folder['path']=$this->build_path($this->file['folder_id'], true, $this->site['root_folder_id']);
			$this->file['path']=$this->folder['path'].'/'.$this->file['name'];
			$this->file['level']=count(explode('/', $this->file['path']))-1;
		}

		$this->load_config();
	}

	function authenticate() {

	}

	function set_by_path($site_id, $path) {

		if(empty($site_id)) {
			$this->site = $this->get_site_by_domain($_SERVER['HTTP_HOST'], true);
		}else {
			$this->site = $this->get_site($site_id);
		}

		if(!$this->site) {
			die('Invalid site requested or the CMS is not configured correctly');
		}

		$_SESSION['site_id']=$this->site['id'];

		$item = $this->resolve_url($path,$this->site['root_folder_id']);

		// Just create a file in the CMS called "404" in root folder "status" and
		// that file will be used in 404 errors.
		if (!$item) {
			header("HTTP/1.0 404 Not Found");
			header("Status: 404 Not Found");		
			$item = $this->resolve_url('status/404',$this->site['root_folder_id']);
		}
		
		if(!$item) {

			echo '<h1>404 Not found</h1>';
			echo '<p>Sorry the page you requested was not found on this server.</p>';

			echo '<a href="'.$this->create_href_by_path("").'">Go to the website</a>';
			
			exit();

		}else {
			if(isset($item['parent_id'])) {
				$this->folder=$item;
				$this->find_file($this->folder['id']);
				if($this->file) {

					$this->folder=$this->get_folder($this->file['folder_id']);
					$this->folder['path']=$this->build_path($this->file['folder_id'], true, $this->site['root_folder_id']);
					$this->file['path']=$this->folder['path'].'/'.$this->file['name'];
					$this->file['level']=count(explode('/', $this->file['path']))-1;
				}
			}else {
				$this->file=$item;
				$this->file['option_values']=$this->get_template_values($this->file['option_values']);
				$this->file['path']=$path;
				$this->folder=$this->get_folder($item['folder_id']);
			}
		}

		if($this->file && (empty($this->folder) || $this->file['folder_id']!=$this->folder['id'])) {
			$this->folder=$this->get_folder($this->file['folder_id']);
			$this->folder['path']=$this->build_path($this->file['folder_id'], true, $this->site['root_folder_id']);
			$this->file['path']=$this->folder['path'].$this->file['name'];
		}

		if(isset($this->file['path'])) {
			$this->folder['path']=dirname($this->file['path']);
			$this->file['level']=count(explode('/', $this->file['path']))-1;
		}

		$this->load_config();
	}

	function replace_urls($content) {
		global $GO_MODULES;

		if($this->site['enable_rewrite']=='1') {
			//we do rewriting
			return str_replace('/{site_url}?site_id='.$this->site['id'].'&amp;path=', $this->site['rewrite_base'], $content);
		}else {
			//we use the ugly URL
			return str_replace('/{site_url}?', $GO_MODULES->modules['cms']['url'].'run.php?', $content);
		}
	}

	/**
	 * Returns an array of the files and folders inside the folder with folder_id
	 * the user with user_id has access to. The folders/files in the array are ordered
	 * according to the priority of the folder/file in it.
	 * @param int $folder_id
	 * @param int $user_id
	 * @param bool $only_visible
	 * @param bool $reverse
	 * @return Array An array of folders and/or files the user has access to.
	 */
	function get_authorized_items($folder_id, $user_id, $only_visible=false, $reverse=false, $filter_year=false, $filter_category_id=false) {
		$items = array();
			$folders = $this->get_authorized_folders($folder_id, $user_id, $only_visible);
			foreach($folders as $folder) {
				$priority=$folder['priority'];
				while(isset($items[$priority]))
					$priority++;

				$items[$priority] = $folder;
				$items[$priority]['fstype']='folder';
			}
		$files = $this->get_authorized_files($folder_id, $user_id, $only_visible,array(),0,$filter_year,$filter_category_id);
		foreach($files as $file) {
			$priority=$file['priority'];
			while(isset($items[$priority]))
				$priority++;

			$items[$priority] = $file;
			$items[$priority]['fstype']='file';
		}
		if($reverse) {
			krsort($items);
		}else {
			ksort($items);
		}
		return $items;
	}

	function get_authorized_folders($folder_id, $user_id, $only_visible=false) {
		global $GO_SECURITY;
		$folders=array();
		if($only_visible) {
			$this->get_visible_folders($folder_id);
		}else {
			$this->get_folders($folder_id,'priority', 'ASC');
		}
		while($this->next_record()) {
			if($this->f('acl')==0 || $GO_SECURITY->has_permission($user_id, $this->f('acl'))) {
				$folders[]=$this->record;
			}
		}
		return $folders;
	}

	function get_authorized_files($folder_id, $user_id, $only_visible=false, $categories=array(),$site_id=0, $filter_year=false, $filter_category_id=0) {
		global $GO_SECURITY;

		$files=array();

		$this->get_files($folder_id,'priority','ASC',0,0,$only_visible, $categories,$site_id,$filter_year,$filter_category_id);
		while($this->next_record()) {
			if($this->f('acl')==0 || $GO_SECURITY->has_permission($user_id, $this->f('acl'))) {
				$files[]=$this->record;
			}
		}
		return $files;
	}

	function find_file($folder_id, $nested=false) {
		global $GO_SECURITY;

		if($folder_id==0) {
			$folder_id=$this->site['root_folder_id'];
		}
		
		$items = $this->get_authorized_items($folder_id, $GO_SECURITY->user_id,true);

		foreach($items as $item) {
			if($item['fstype']=='file') {
				//$this->folder=$this->get_folder($item['folder_id']);
				//var_dump($item);
				$this->file=$item;
				$this->file['option_values']=$this->get_template_values($this->file['option_values']);
				return $this->file['id'];
			}else {
				$file = $this->find_file($item['id'],true);
				if($file)
					return $file;
			}
		}


		if(!$nested){
			//if we reach this and we are not nested then we didn't find a file below the tree from the requested start point.
			//We'll go up the tree now.
			$folder = $this->get_folder($folder_id);
			if($folder && $folder['parent_id']>0) {
				//pass go_down_tree as false so it won't go in an endless loop between two empty folders
				return $this->find_file($folder['parent_id'], false);
			}
		}
		return false;
	}

	function get_active_levels() {

		$levels=array();

		$folder_id=$this->folder['id'];

		if(empty($folder_id))
			return array();

		do {
			$levels[]=$folder_id;
			$folder = $this->get_folder($folder_id);
			$folder_id=$folder['parent_id'];
		}while($folder['parent_id']>0);

		return array_reverse($levels);
	}



	/**
	 * Prints items (files and folders) in various different ways:
	 *
	 * - A menu
	 * - A list of files with content
	 *
	 * The parameters explained:
	 *
	 * root_path: For example "News". The list will start with items found in the "News" folder
	 * root_folder_id: The same as root_path but faster because the id is used directly so the path doesn't need to be resolved.
	 * level: Can be used instead of root_path or root_folder_id. If level is set then it will always display this folder level.
	 * 	In the example below level 0 will display Home and News and level 1 would display the news items if the news folder is active.
	 *
	 * expand_levels: The maximum number of folder levels that will be expanded. Each level will be put in a new div eg.
	 * 	<div class="items items_0">
	 * 		<a class="items items_0" href="#">Home</a>
	 * 		<a class="items items_0" href="#">News</a>
	 *		<div class="items items_1">
	 * 			<a class="items items_1" href="#>News item 1</a>
	 * 			<a class="items items_1" href="#>News item 2</a>
	 * 		</div>
	 * 	</div>
	 * 	News items will be expanded when clicked at the News folder is expand_levels is set to 1 at least.
	 *
	 * class: The class name that will be used. If class is set to items (default). The all items will get the classname:
	 * 	items items_0. Where 0 is the level of the menu.
	 *
	 * max_items: The maximum number of items to process. If 0 or undefined it will process them all.
	 *
	 * item_template: Optional smarty template to process on each item. The item will have the following vars:
	 * 	$item: All the item fields from the cms_folders or cms_files tables and a href property.
	 *  $content: The html that is created by this function if you didn't use a smarty template
	 *
	 * active_template: Overide the item_template if an item is active.
	 *
	 * @param array $params
	 * @param object $smarty
	 * @param int $current_level
	 * @param int $folder_id
	 * @return String HTML
	 */

	function print_items($params, &$smarty, $current_level=0, $folder_id=0, $path=null, $parentitem=false) {
		global $GO_CONFIG, $GO_SECURITY, $GO_MODULES;
		//var_dump($this->site);
		$root_path = isset($params['root_path']) ? $params['root_path'] : '';
		$root_folder_id = isset($params['root_folder_id']) ? $params['root_folder_id'] : $this->site['root_folder_id'];
		$expand_levels = isset($params['expand_levels']) ? $params['expand_levels'] : 0;
		$expand_all =  !empty($params['expand_all']);
		$class = isset($params['class']) ? $params['class'] : 'items';
		$level = isset($params['level']) ? $params['level'] : 0;
		$item_template = isset($params['item_template']) ? $params['item_template'] : '';
		$active_item_template = isset($params['active_item_template']) ? $params['active_item_template'] : $item_template;
		$max_items=isset($params['max_items']) ? $params['max_items'] : 0;
		$wrap_div=isset($params['wrap_div']) && (empty($params['wrap_div']) || $params['wrap_div']=='false') ? false : true;
		$paging_id = isset($params['paging_id']) ? $params['paging_id'] : false;
		$reverse = !empty($params['reverse']);
		$level_template  = isset($params['level_template'])?  $params['level_template'] : '';
		$start  = isset($params['start'])?  $params['start'] : 0;
		$random = !empty($params['random']);
		$no_folder_links = !empty($params['no_folder_links']);
		$search = !empty($params['search']);
		$sort_time = !empty($params['sort_time']) ? $params['sort_time'] : '';
		$filter_by_get_year = !empty($params['filter_by_get_year']) ? $_GET['filter_year'] : false;
		$filter_by_get_category_id = !empty($params['filter_by_get_category_id']) ? $_GET['filter_category_id'] : false;
		$all_subfolder_content = !empty($params['all_subfolder_content']);

		/*
		 * lastfile is used to record the previous and next file of the currently viewed file
		*/
		if($current_level==0) {
			$this->lastfile=false;
			$this->record_next_file=false;
		}


		if(!empty($root_path)) {
			if(!isset($path)) {
				$path = $root_path;
			}
			$folder =  $this->resolve_url($root_path, $this->site['root_folder_id']);
			if(!$folder) {
				return 'Couldn\'t resolve path: '.$root_path;
			}else {
				$root_folder_id=$folder['id'];
			}
		}

		$html = '';


		if($folder_id==0) {
			if(!empty($level)) {
				$levels=$this->get_active_levels();

				if(!isset($levels[$level])) {
					return '';
				}else {
					$folder_id=$levels[$level];
				}

			}else {
				$folder_id = $root_folder_id;
			}
		}

		if(empty($folder_id)) {
			return '';
		}

		//When we start with a level or root_folder_id we don't
		//know the current path yet. If rewrte is enabled we need to know
		//the path for mod_rewrite to work.
		if(!isset($path)){// && $this->site['enable_rewrite']=='1') {
			$path = $this->build_path($folder_id, true, $this->site['root_folder_id']);
		}

		if($search) {
			$items = $this->search_files($root_folder_id, $_REQUEST['query']);
		}else {
			$items = isset($params['items']) ? $params['items'] : $this->get_authorized_items($folder_id, $GO_SECURITY->user_id, true, $reverse, $filter_by_get_year, $filter_by_get_category_id);
		}

		$total = count($items);

		// sort by sort_time

		if (strtolower($sort_time)=='asc') {
			if(!function_exists('cmp_sort_time_asc')){
				function cmp_sort_time_asc($a,$b) {
					if ($a['sort_time']==$b['sort_time'])
						return 0;
					return $a['sort_time']<$b['sort_time'] ? -1 : 1;
				}
			}
		} elseif (strtolower($sort_time)=='desc') {
			if(!function_exists('cmp_sort_time_desc')){
				function cmp_sort_time_desc($a,$b) {
					if ($a['sort_time']==$b['sort_time'])
						return 0;
					return $a['sort_time']>$b['sort_time'] ? -1 : 1;
				}
			}
		}

		if (strtolower($sort_time)=='asc' || strtolower($sort_time)=='desc') {
			usort($items,'cmp_sort_time_'.strtolower($sort_time));
		}

		if($random) {
			shuffle($items);
		} elseif($paging_id && $total > $max_items) {
			$_SESSION['GO_SESSION']['cms']['paging_'.$paging_id]=isset($_SESSION['GO_SESSION']['cms']['paging_'.$paging_id]) ? $_SESSION['GO_SESSION']['cms']['paging_'.$paging_id] : $start;
			if(isset($_REQUEST[$paging_id])) {
				$start = $_SESSION['GO_SESSION']['cms']['paging_'.$paging_id]= $_REQUEST[$paging_id];
			}else {
				$start=$_SESSION['GO_SESSION']['cms']['paging_'.$paging_id];
			}
			for($i=0;$i<$start;$i++) {
				array_shift($items);
			}

			$pages = ceil($total/$max_items);

			$previous_start = $start - $max_items;
			$next_start = $start+$max_items;

			$pagination_html = '<div class="'.$class.' pagination">';

			$request_uri = preg_replace('/&'.$paging_id.'=.+&?/', '', $_SERVER['REQUEST_URI']);

			if($start>0) {
				$pagination['firstpage_href']=$request_uri.'&'.$paging_id.'=0';
				$pagination['previous_href']=$request_uri.'&'.$paging_id.'='.$previous_start;
			}

			$start_link = ($start-((10/2)*$max_items));
			$end_link = ($start+((10/2)*$max_items));

			if ($start_link < 0) {
				$end_link = $end_link - $start_link;
				$start_link=0;
			}
			if ($end_link > $total) {
				$end_link = $total;
			}


			$pagination['start']=$start;
			$pagination['page_hrefs']=array();
			for ($i=$start_link;$i<$end_link;$i+=$max_items) {
				$page = ($i/$max_items)+1;
				$pagination['page_hrefs'][]=array(
								'page'=>$page,
								'href'=>$request_uri.'&'.$paging_id.'='.$i,
								'active'=>$start==$i? 'active' : 'inactive');
			}

			if ($end_link < $total) {
				$pagination_html .= '...&nbsp;';
			}

			$last_page = floor($total/$max_items)*$max_items;
			if($total>$next_start) {
				$pagination['lastpage_href']=$request_uri.'&'.$paging_id.'='.$last_page;
				$pagination['next_href']=$request_uri.'&'.$paging_id.'='.$next_start;
			}

			$smarty->assign($paging_id, $pagination);
		}else {
			for($i=0;$i<$start;$i++) {
				array_shift($items);
			}
		}

		
		
		$count = count($items);

		$smarty2 = new cms_smarty();

		$uneven=true;

		$smarty->assign('item_count', $count);

		if($count) {
			$smarty2->assign('item_count', $count);
			$smarty2->assign('item_percentage', round(100/$count,1));

			if($wrap_div)
				$html .= '<div id="'.$class.'_'.$folder_id.'" class="'.$class.' '.$class.'_'.$current_level.'">';

			$counter=$active_index=0;
			while ($item = array_shift($items)) {

				$item['index']=$counter;
				$item['first']=$counter==0;
				$item['last']=$counter==$count-1;
				$item['safename']=preg_replace($this->safe_regex, '', $item['name']);
				$item['name']=htmlspecialchars($item['name']);
				$item['level']=$current_level;

				$current_item_template = $item_template;

				$last_was_in_path = !empty($is_in_path);

				$item_html = '';
				if ($item['fstype']=='file') {

					$name = File::strip_extension($item['name']);
					$title = $item['title'] == '' ? $name : $item['title'];
					$item_html .= '<a title="'.$title.'" class="'.$class.' '.$class.'_'.$current_level;

					if($this->file['id']==$item['id']) {
						$is_in_path=true;
						$item_html .= ' selected';
						$current_item_template = $active_item_template;

						$smarty->assign('previous_file', $this->lastfile);
						$this->record_next_file=true;
					}else {
						$is_in_path=false;
					}


					$filepath = $search ? $this->build_path($item['folder_id'], true, $this->site['root_folder_id']) : $path;
					if(!empty($filepath)){
						$filepath .= '/';
					}
					$filepath .=$this->special_encode($item['name']);
					$item['href']=$this->create_href_by_path($filepath);

					$item_html .= '" href="'.$item['href'].'">'.$name.'</a>';

				} else {

					$is_in_path = $this->is_in_path($item['id'],$this->folder['id']);

					$item_html .= $no_folder_links ? '<div' : '<a title="'.$item['name'].'"';
					$item_html .= ' class="'.$class.' '.$class.'_'.$current_level;

					//if($this->folder['id']==$item['id'])
					if($is_in_path) {
						$item_html .= ' selected';
						$current_item_template = $active_item_template;
					}
					
					$filepath = $path;
					if(!empty($filepath)){
						$filepath.='/';
					}
					$filepath.=$this->special_encode($item['name']);

					$item['href']=$this->create_href_by_path($filepath);


					if($no_folder_links)
						$item_html .= '">'.$item['name'].'</div>';
					else
						$item_html .= '" href="'.$item['href'].'">'.$item['name'].'</a>';

				}
				if($is_in_path)
					$active_index=$counter;

				if(!empty($current_item_template)) {
					if(!empty($item['option_values']))
						$item['option_values']=$this->get_template_values($item['option_values']);

					$smarty2->assign('parentitem', $parentitem);
					$smarty2->assign('item', $item);
					$smarty2->assign('content', $item_html);
					$smarty2->assign('level', $current_level);
					$smarty2->assign('is_in_path', $is_in_path);
					$smarty2->assign('last_was_in_path', $last_was_in_path);

					$smarty2->assign('even', $uneven ? 'uneven' : 'even');

					$folder = $this->get_folder($folder_id);
					$smarty2->assign('folder', $folder);

					$html .= $smarty2->fetch($current_item_template);
				}else {
					$html .= $item_html;
				}

				if($item['fstype']=='folder' && ($all_subfolder_content || ($current_level < $expand_levels && ($is_in_path || $expand_all)))) {
					$href_path = empty($path) ? '' : $path.'/';
					$html .= $this->print_items($params, $smarty, $current_level+1,$item['id'],$href_path.urlencode($item['name']), $item);
				}

				/**
				 * Record the previous and next file if there is an active file
				 */
				if($item['fstype']=='file') {
					$this->lastfile=$item;
					if(!$is_in_path && $this->record_next_file) {
						$smarty->assign('next_file', $item);
						$this->record_next_file=false;
					}
				}

				$counter++;

				if($max_items>0 && $max_items==$counter) {
					break;
				}

				$uneven=!$uneven;
			}
			if($wrap_div)
				$html .= '</div>';

			if(!empty($level_template)) {
				$smarty2->assign('parentitem', $parentitem);
				$smarty2->assign('level', $current_level);
				$smarty2->assign('count', $counter);
				$smarty2->assign('active_index', $active_index);
				$smarty2->assign('content', $html);

				$html = $smarty2->fetch($level_template);
			}

		}

		return $html;
	}
	
	function print_category_items($params, &$smarty) {
		global $GO_CONFIG, $GO_SECURITY, $GO_MODULES;
		$category_path = !empty($params['category_path']) ? $params['category_path'] : '';
//		$category_names = !empty($params['category_names']) ? explode(',',$params['category_names']) : array();
		$category_ids = !empty($params['category_ids']) ? explode(',',$params['category_ids']) : array();
		$random = !empty($params['random']);
		
		if ($cat_id = $this->get_category_id_by_path($category_path)) {
			$params['items'] = $this->get_authorized_files(0, $GO_SECURITY->user_id, true, array($cat_id),$this->site['id']);
		}
//		else if (is_array($category_names)) {
//			for ($i=0; $i<count($category_names); $i++) {
//				$category_names[$i] = trim($category_names[$i]);
//			}
//			$params['items'] = $this->get_authorized_files(0, $GO_SECURITY->user_id, true, $category_names,$this->site['id']);
//		} 
		else if (is_array($category_ids)) {
			for ($i=0; $i<count($category_ids); $i++) {
				$category_ids[$i] = trim($category_ids[$i]);
			}
			$params['items'] = $this->get_authorized_files(0, $GO_SECURITY->user_id, true, $category_ids,$this->site['id']);
		}

		if($random)
			shuffle($params['items']);
		
		foreach ($params['items'] as $k=>$item) {
			//When we start with a level or root_folder_id we don't
			//know the current path yet. If rewrte is enabled we need to know
			//the path for mod_rewrite to work.
			$path = $this->build_path($item['folder_id'], true, $this->site['root_folder_id']);

			$filepath = $search ? $this->build_path($item['folder_id'], true, $this->site['root_folder_id']) : $path;
			if(!empty($filepath)){
				$filepath .= '/';
			}
			$filepath .=$this->special_encode($item['name']);
			$params['items'][$k]['href']=$this->create_href_by_path($filepath);
		}
		
		return $this->items2html($params, $smarty);
	}
	
	function print_child_categories($params, &$smarty) {
//		global $GO_CONFIG, $GO_SECURITY, $GO_MODULES;
		$category_path = !empty($params['category_path']) ? $params['category_path'] : '';
//		$category_name = !empty($params['category_name']) ? $params['category_name'] : '';
		$category_id = !empty($params['category_id']) ? intval($params['category_id']) : '';
		$sort_by = !empty($params['sort_by']) ? $params['sort_by'] : false;
		$sort_order = !empty($params['sort_order']) ? $params['sort_order'] : false;
		$random = !empty($params['random']);
		
//		if ($category_name=='Root') {
//			$category_id = 0;
//			unset($category_name);
//		}
		
		if ($category_id = $this->get_category_id_by_path($category_path)) {
			$params['items'] = $this->get_child_categories($category_id,$this->site['id'],!empty($params['return_first_item']),$sort_by,$sort_order);
		}
//		else if (!empty($category_name)) {
//			$category_name = trim($category_name);
//			
//			//todo get_category_by_name
//			
//			$category_id = $this->get_category_by_name($category_name);
//						
//		} 
		$params['items'] = $this->get_child_categories($category_id,$this->site['id'],!empty($params['return_first_item']),$sort_by,$sort_order);

		if($random)
			shuffle($params['items']);
		
		return $this->items2html($params, $smarty);
	}
		
	function print_years($params, &$smarty) {
		global $GO_CONFIG, $GO_SECURITY, $GO_MODULES;
		
		$root_path = isset($params['root_path']) ? $params['root_path'] : '';
		$random = !empty($params['random']);
				
		$folder =  $this->resolve_url($root_path, $this->site['root_folder_id']);
		if(!$folder) {
			return 'Couldn\'t resolve path: '.$root_path;
		} else {
			$root_folder_id=$folder['id'];
		}
		
		$params['items'] = $this->get_item_years($root_folder_id);
		
		if($random)
			shuffle($params['items']);
		
		return $this->items2html($params, $smarty);
	}
	
	public function get_category_id_by_path($path_string,$root_category_id=0) {
		if (substr($path_string,0,1)=='/')
			$path_string = substr($path_string,1);
		if (substr($path_string,-1,1)=='/')
			$path_string = substr($path_string,0,strlen($path_string)-1);
		
		$path_arr = explode('/',$path_string);
		$head_category_name = array_shift($path_arr);
		$cat_id = $this->get_category_id($head_category_name,$root_category_id);
		if (is_numeric($cat_id)) {
			if (empty($path_arr))
				return $cat_id;
			else
				return $this->get_category_id_by_path(implode('/',$path_arr),$cat_id);
		} else {
			return false;
		}
	}
	
	private function items2html ($params, &$smarty) {
		$html = !empty($params['html']) ? $params['html'] : '';
		$items = !empty($params['items']) ? $params['items'] : array();
		$class = isset($params['class']) ? $params['class'] : 'items';
		$item_template = isset($params['item_template']) ? $params['item_template'] : '';
		$active_item_template = isset($params['active_item_template']) ? $params['active_item_template'] : $item_template;
		$max_items=isset($params['max_items']) ? $params['max_items'] : 0;
		$wrap_div=isset($params['wrap_div']) && (empty($params['wrap_div']) || $params['wrap_div']=='false') ? false : true;
		$level_template  = isset($params['level_template'])?  $params['level_template'] : '';
	
		$count = count($items);
		$smarty2 = new cms_smarty();
		$uneven=true;

		$smarty->assign('item_count', $count);

		if($count) {
			
			$smarty2->assign('item_count', $count);
			$smarty2->assign('item_percentage', round(100/$count,1));

			if($wrap_div)
				$html .= '<div id="'.$class.'" class="'.$class.'">';
			
			$counter=$active_index=0;
			while ($item = array_shift($items)) {

				$item['index']=$counter;
				$item['safename']=preg_replace($this->safe_regex, '', $item['name']);
				$item['name']=htmlspecialchars($item['name']);
				$item['level']=$current_level;

				$current_item_template = $item_template;

				$last_was_in_path = !empty($is_in_path);

				$item_html = '';
				if ($item['fstype']=='file') {

					$name = File::strip_extension($item['name']);
					$title = $item['title'] == '' ? $name : $item['title'];
					$item_html .= '<a title="'.$title.'" class="'.$class.' '.$class.'_'.$current_level;

					if($this->file['id']==$item['id']) {
						$is_in_path=true;
						$item_html .= ' selected';
						$current_item_template = $active_item_template;

						$smarty->assign('previous_file', $this->lastfile);
						$this->record_next_file=true;
					}else {
						$is_in_path=false;
					}


					$filepath = $search ? $this->build_path($item['folder_id'], true, $this->site['root_folder_id']) : $path;
					if(!empty($filepath)){
						$filepath .= '/';
					}
					$filepath .=$this->special_encode($item['name']);
					$item['href']=$this->create_href_by_path($filepath);

					$item_html .= '" href="'.$item['href'].'">'.$name.'</a>';

				}
				
				if($is_in_path)
					$active_index=$counter;

				if(!empty($current_item_template)) {
					if(!empty($item['option_values']))
						$item['option_values']=$this->get_template_values($item['option_values']);

					$smarty2->assign('parentitem', $parentitem);
					$smarty2->assign('item', $item);
					$smarty2->assign('content', $item_html);
					$smarty2->assign('level', $current_level);
					$smarty2->assign('is_in_path', $is_in_path);
					$smarty2->assign('last_was_in_path', $last_was_in_path);

					$smarty2->assign('even', $uneven ? 'uneven' : 'even');

					$folder = $this->get_folder($folder_id);
					$smarty2->assign('folder', $folder);

					$html .= $smarty2->fetch($current_item_template);
				}else {
					$html .= $item_html;
				}

				/**
				 * Record the previous and next file if there is an active file
				 */
				if($item['fstype']=='file') {
					$this->lastfile=$item;
					if(!$is_in_path && $this->record_next_file) {
						$smarty->assign('next_file', $item);
						$this->record_next_file=false;
					}
				}

				$counter++;

				if($max_items>0 && $max_items==$counter) {
					break;
				}

				$uneven=!$uneven;
			}
			
			if($wrap_div)
				$html .= '</div>';
			
			if(!empty($level_template)) {
				$smarty2->assign('parentitem', $parentitem);
				$smarty2->assign('level', $current_level);
				$smarty2->assign('count', $counter);
				$smarty2->assign('active_index', $active_index);
				$smarty2->assign('content', $html);

				$html = $smarty2->fetch($level_template);
			}
		}
		return $html;
	}
		
}