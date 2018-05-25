<?php
namespace GO\Site\Tag;

use Site;

class Thumb implements TagInterface{	
	static function render($params, $tag, \GO\Site\Model\Content $content){
		
		$html = '';
		
		if(empty($params['path'])){
			return "Error: path attribute must be set in img tag!";
		}
		
		//Change Tickets.png into public/site/1/files/Tickets.png
		
		$folder = new \GO\Base\Fs\Folder(Site::model()->getPublicPath());
		
		$fullRelPath = $folder->stripFileStoragePath().'/files/'.$params['path'];
//		var_dump($p);
		
		$thumbParams = $params;
		unset($thumbParams['path'], 
						$thumbParams['lightbox'],
						$thumbParams['alt'], 
						$thumbParams['class'],
						$thumbParams['style'], 
						$thumbParams['astyle'], 
						$thumbParams['caption'],
						$thumbParams['aclass']);
		
		if(!isset($thumbParams['lw']) && !isset($thumbParams['w']))
			$thumbParams['lw']=300;
		
		if(!isset($thumbParams['ph']) && !isset($thumbParams['ph']))
			$thumbParams['ph']=300;
		
		$thumb = Site::thumb($fullRelPath, $thumbParams);
		
		
		if(!isset($params['caption'])){
			$file = new \GO\Base\Fs\File($fullRelPath);
			
			$params['caption'] = $file->nameWithoutExtension();
		}
		
		if(!isset($params['alt'])){
			$params['alt']=isset($params['caption']) ? $params['caption'] :  basename($tag['params']['path']);
		}
		
		$html .= '<img src="' . $thumb . '" alt="' . $params['alt'] . '"';
		
	
		
		$html .= 'class="thumb-img"';
		
		
	
		
		$html .= ' />';
		
		
		if(!isset($params['lightbox']))
		{
			$params['lightbox']="thumb";
		}
		
		if(!empty($params['lightbox'])){
			$a = '<a';
			
			if(isset($params['caption'])){
				$a .= ' title="'.$params['caption'].'"';
			}

			if(!isset($params['aclass'])){
				$params['aclass']='thumb-a';
			}
			
			$a .= ' class="'.$params['aclass'].'"';

			if(isset($params['astyle'])){
				$a .= ' style="'.$params['astyle'].'"';
			}
			
			$a .= ' data-lightbox="'.$params['lightbox'].'" href="'.\Site::file($params['path'], false).'">'.$html.'</a>'; // Create an url to the original image
			
			$html= $a;
		}
		
		if(isset($params['caption'])){
			$html .= '<div class="thumb-caption">'.$params['caption'].'</div>';
		}
		
		
		if(!isset($params['class'])){
			$params['class']='thumb-wrap';
		}
		
		$wrap = '<div class="'.$params['class'].'"';
		
		if(isset($params['style'])){
			$wrap .= 'style="'.$params['style'].'"';
		}
		
		$wrap .= '>';
		
		
		$html = $wrap.$html.'</div>';
		
		return $html;
	}
}
