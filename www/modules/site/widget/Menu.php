<?php


namespace GO\Site\Widget;


class Menu extends \GO\Site\Components\Widget {
	
	public $template = "<ul>\n{items}</ul>\n";
	public $itemTemplate = "<li><a href='{url}'{target}>{item}</a>{items}</li>\n";
	public $headerTemplate = "<h2>{menutitle}</h2>\n";
	public $id = "";
	public $showLabel = true;
	public $htmlOptions = array();
	
	private $_menuModel = false;
	
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->_menuModel = \GO\Site\Model\Menu::model()->findSingleByAttributes(array('site_id'=>\Site::model()->id,'menu_slug'=>$this->id));	
	}
	
	public function render(){
		if($this->_findMenuModel()){
			return $this->_renderMenuModel();
		}
	}
	
	private function _renderMenuModel(){
		
		$html = '';
		
		//Render menu header when showLabel is true
		if($this->showLabel)
			$html .= str_replace('{menutitle}', $this->_menuModel->label, $this->headerTemplate);

		$html .= $this->_renderChildren($this->_menuModel);
		
		return $html;
	}
	
	private function _renderChildren($parent, $level=0){

		$itemHtml = '';
		// Render normal menu structure children
		foreach($parent->children as $child){
			
			$target = !empty($child->target)?' target="'.$child->target.'"':'';
			
			if($child->display_children && !empty($child->content_id))
				$childHtml = $this->_renderContentChildren($child,$level++);
			else
				$childHtml = $this->_renderChildren($child,$level++);
			
			$itemHtml .= strtr($this->itemTemplate, array(
					'{item}'=>$child->label,
					'{url}'=>$child->getUrl(),
					'{target}'=>$target,
					'{items}'=>$childHtml
			));
		}

		$html = '';
		if(!empty($itemHtml)){
		
			$tpl = $this->template;
			// If this is the root UL
			if($parent instanceof \GO\Site\Model\Menu){

				$options = $this->htmlOptions;
				array_walk($options, create_function('&$i,$k','$i=" $k=\"$i\"";'));
				$options = implode($options,"");
				
				$tpl = preg_replace('/>/', $options.'>', $tpl, 1);
			} 
			
			$html = strtr($tpl, array(
				'{items}'=>$itemHtml
			));
		}
		
		return $html;
	}
		
	private function _renderContentChildren($parent, $level=0){
		
		$itemHtml = '';
		$target = !empty($parent->target)?' target="'.$parent->target.'"':'';
		$parentContentItem = \GO\Site\Model\Content::model()->findByPk($parent->content_id);
		
		foreach($parentContentItem->children as $child){
			$itemHtml .= strtr($this->itemTemplate, array(
					'{item}'=>$child->title,
					'{url}'=>$child->getUrl(),
					'{target}'=>$target,
					'{items}'=>''
			));
		}
		
		$html = '';
		if(!empty($itemHtml)){
		
			$tpl = $this->template;
			// If this is the root UL
			if($parent instanceof \GO\Site\Model\Menu){

				$options = $this->htmlOptions;
				array_walk($options, create_function('&$i,$k','$i=" $k=\"$i\"";'));
				$options = implode($options,"");
				
				$tpl = preg_replace('/>/', $options.'>', $tpl, 1);
			} 
			
			$html = strtr($tpl, array(
				'{items}'=>$itemHtml
			));
		}
		
		return $html;
	}
	
	private function _findMenuModel(){
		if(!$this->_menuModel){
			echo "Geen menu gevonden!";
			return false;
		} else{
			return true;
		}
	}
}
