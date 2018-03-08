<?php

namespace GO\Site\Widget;

use \GO\Site\Components\Widget;

class Breadcrumb extends Widget {

	/**
	 *
	 * @var GO\Site\Model\Content 
	 */
	public $content;

	public function render() {
		$html = '<ol class="breadcrumb">';
		
		$crumbs = array($this->content);
		$slugContent = $this->content;
		while ($parent = $slugContent->parent) {
			$crumbs[] = $parent;

			$slugContent = $parent;
		}

		$crumbs = array_reverse($crumbs);

		foreach ($crumbs as $crumb) {
			if ($crumb->id == $this->content->id) {
				$html .= '<li class="active">' . $crumb->title . '</li>';
			} else {
				$html .= '<li><a href="' . $crumb->getUrl() . '">' . $crumb->title . '</a></li>';
			}
		}

		$html .= '</ol>';
		
		return $html;
	}

}
