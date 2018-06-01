<?php
namespace GO\Base\View;

use GO;

class FileView extends AbstractView{
	
	public $layout='ajax';
	
	public function render($viewName, $data=array()){
		
		$this->headers();		
		
		$viewPath = GO::config()->root_path.'views/'.GO::viewName().'/';
		
		if(!($file = $this->findViewFile($viewName))){
			$file = $viewPath.'/Default.php';						
		}
		
		
		
		$layoutFile = $viewPath.'layout/'.$this->layout.'.php';
		$masterPage = file_exists($layoutFile);
		
		if($masterPage){
			ob_start();
			ob_implicit_flush(false);
			
			extract($data);
			
			require($file);
			
			$content = ob_get_clean();			
			
			ob_start();
			ob_implicit_flush(false);
			require($layoutFile);
			
			$fullPage = ob_get_clean();
			
			GO::scripts()->render($fullPage);
			
			echo $fullPage;
		}else
		{
			require($file);
		}
	}
	
	public function findViewFile($viewName){
		
		$viewPath = GO::config()->root_path.'views/'.GO::viewName().'/';
		
		$controller = GO::router()->getController();		
		
		
		$module = $controller ? $controller->getModule() : false;
		
		if(!$module){
			$file = $viewPath.$viewName.'.php';
		}else
		{
			$file = $module->path.'views/'.GO::viewName().'/'.$viewName.'.php';
		}
		
		if(file_exists($file)){
			return $file;
		}elseif(($file = $viewPath.$viewName.'.php') && file_exists($file))
		{
			return $file;
		}else
		{			
			return false;
		}
		
	}
	
}
