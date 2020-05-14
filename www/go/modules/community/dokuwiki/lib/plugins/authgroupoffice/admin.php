<?php
class admin_plugin_authgroupoffice extends admin_plugin_acl {
	
	function getMenuText($language) {
		return 'Extended ACL Management with Group-Office Groups';
	}
	
	 /**
     * Get current ACL settings as multidim array
     *
     * @author WilmarVB <info@intermesh.nl>
     */
	function handle(){
		parent::handle();
		$this->usersgroups = $this->_loadGOGroups();
	}

	private function _loadGOGroups() {
		$groups = $this->usersgroups;
		$groupNames= array();
		$allGroupNames = array();
		foreach($groups as $line){
			$line = trim(preg_replace('/#.*$/','',$line)); //ignore comments
			if(!$line) continue;

			$acl = preg_split('/[ \t]+/',$line);
			//0 is pagename, 1 is user, 2 is acl

			$acl[1] = rawurldecode($acl[1]);
			$acl_config[$acl[0]][$acl[1]] = $acl[2];

			// store non-special users and groups for later selection dialog
			$ug = $acl[1];
			$allGroupNames[] = $ug;
			if (substr($ug,0,1)=='@') $ug = substr($ug,1);
			$groupNames[] = $ug;
		}
		
		$goGroupsStmt = \GO\Base\Model\Group::model()->find();
		
		foreach ($goGroupsStmt as $goGroupModel) {
			if (!in_array($goGroupModel->name,$groupNames)) {
				$groupNames[] = $goGroupModel->name;
				$allGroupNames[] = '@'.$goGroupModel->name;
			}
		}
		
		return $allGroupNames;
	}
		
  function _html_detail(){
			global $ID;

			echo '<form action="'.wl('admin','page=authgroupoffice').'" method="post" accept-charset="utf-8"><div class="no">'.NL;

			echo '<div id="acl__user">';
			echo $this->getLang('acl_perms').' ';
			$inl =  $this->_html_select();
			echo '<input type="text" name="acl_w" class="edit" value="'.(($inl)?'':hsc(ltrim($this->who,'@'))).'" />'.NL;
			echo '<input type="submit" value="'.$this->getLang('btn_select').'" class="button" />'.NL;
			echo '</div>'.NL;

			echo '<div id="acl__info">';
			$this->_html_info();
			echo '</div>';

			echo '<input type="hidden" name="ns" value="'.hsc($this->ns).'" />'.NL;
			echo '<input type="hidden" name="id" value="'.hsc($ID).'" />'.NL;
			echo '<input type="hidden" name="do" value="admin" />'.NL;
			echo '<input type="hidden" name="page" value="acl" />'.NL;
			echo '<input type="hidden" name="sectok" value="'.getSecurityToken().'" />'.NL;
			echo '</div></form>'.NL;
	}
	
}
?>

