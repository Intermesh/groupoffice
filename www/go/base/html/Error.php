<?php


namespace GO\Base\Html;


class Error extends Input {
	public static function getError($inputName='form') {
		$error = parent::getError($inputName);
		unset(\GO::session()->values['formErrors'][$inputName]);
		return $error;
	}
	
	public static function printErrors(){
		foreach(\GO::session()->values['formErrors'] as $inputName=>$error){
			echo self::getError($inputName).'<br />';
		}
	}
	
	public static function setError($message,$inputName='form') {
		return parent::setError($inputName, $message);
	}
	
	public static function checkRequired(){		
		if(isset($_POST['required'])){
			foreach($_POST['required'] as $inputName){
				if ($pos = strpos($inputName, '[')) {
					$key1 = substr($inputName, 0, $pos);
					$key2 = substr($inputName, $pos + 1, -1);
					$v=isset($_POST[$key1][$key2]) ? trim($_POST[$key1][$key2]) : '';						
				}else
				{					
					$v=isset($_POST[$inputName]) ? trim($_POST[$inputName]) : '';						
				}
				if($v=='')
					parent::setError($inputName, 'This field is required');
			}
		}
		
		return !parent::hasErrors();
	}
	
	public static function checkEmailInput($params) {
		if ( isset($params['email_check']) && isset($params['email'])
				&& strcmp($params['email_check'],$params['email']) !== 0
			) {
			parent::setError('email_check','Email address is not the same');
		}
	}
	
	public static function validateModel($model,$attrmapping=false){
		
//		if(\GO\Base\Util\Http::isPostRequest()){
			
//			if(!empty($attrmapping)){
//				foreach($attrmapping as $attr=>$replaceattr){
//					$model->$replaceattr = $_POST[$attr];
//				}
//			}
			$errors=array();
			if (!$model->validate())
				$errors = $model->getValidationErrors();
				
			if($model->customfieldsRecord && !$model->customfieldsRecord->validate())
				$errors = array_merge($errors, $model->customfieldsRecord->getValidationErrors());
			
			if(count($errors)){
				foreach ($errors as $attribute => $message) {
					
					$formAttribute = isset($attrmapping[$attribute]) ? $attrmapping[$attribute] : $attribute;
					
					Input::setError($formAttribute, $message); // replace is needed because of a mix up with order model and company model
				}
				Error::setError(\GO::t("There were errors in the form. Correct them and try again."));
				return false;
			}else
			{
				return true;
			}
		}
//	}
}
