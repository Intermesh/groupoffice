<?php

namespace GO\Site\Tag;

use GO\Site\Model\Content;

class Link implements TagInterface {

	static function render($params, $tag, \GO\Site\Model\Content $content) {

		$html = '<a';

		if (empty($params['slug']) && empty($params['path'])) {
			return "Error: slug or path must be set in link tag!";
		}

		if(isset($params['slug'])){
			$params['slug']=explode('#', $params['slug']);


			$model = Content::model()->findBySlug($params['slug'][0], $content->site_id);

			if(!$model){
				return "Broken link to slug: '".$params['slug'][0]."'";
			}

			$params['href'] = $model->getUrl();

			if (isset($params['slug'][1]))
				$params['href'].= '#' . $params['slug'][1];
		
		}else
		{
			$params['href'] = \Site::file($params['path'], false);
		}

		unset($params['anchor'], $params['slug'], $params['path']);


		foreach ($params as $key => $value) {
			$html .= ' ' . $key . '="' . $value . '"';
		}

		$html .= '>' . $tag['innerText'] . '</a>';

		return $html;
	}

}
