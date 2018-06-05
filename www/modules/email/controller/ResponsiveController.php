<?php
namespace GO\Email\Controller;

use GO\Base\Controller\AbstractController;

class ResponsiveController extends AbstractController{
	
	protected $layout='html';


	public function actionLoad(){
		$this->render('load');
	}
	
}
