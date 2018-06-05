<?php


namespace GO\Site\Widget;

use \GO\Site\Components\Widget;

class TOC extends Widget {
	
	/**
	 *
	 * @var GO\Site\Model\Content 
	 */
	public $content;
	
	public $maxLevels=3;
	
	
	public $ulTemplate = '<ul class="nav nav-pills nav-stacked">';
	
	public $linkTemplate = '<a href="#{baseslug}" class="">{chapter} {title}</a>';
	
	public function render() {
		return $this->printLi($this->content);
	}
	
	
	public function printLi(\GO\Site\Model\Content $content, $level=0, $prefix=''){

		if ($this->maxLevels == $level)
			return;
		
		$html = '';

		if ($level == 0) {
			$html .= $this->ulTemplate;
		}

		$count = 0;

		foreach ($content->children as $child) {

			$count++;			

			$html .= '<li>';
			
			$attr = $child->getAttributes();
			
			$attr['baseslug']=$child->getBaseslug();
			$attr['parentslug']=$child->getParentslug();
			
			$attr['url']=$child->getUrl();
			$attr['chapter']=$prefix . $count;
			
			$item = $this->linkTemplate;
			foreach($attr as $key=>$value){
				$item = str_replace('{'.$key.'}', $value, $item);
			}
			
			$html .= $item;

			$html .= $this->printLi($child, $level + 1, $attr['chapter'] . '.');

			$html .= '</li>';
		}

		if ($level == 0) {
			$html .= '</ul>';
		}
		return $html;
	}

}
