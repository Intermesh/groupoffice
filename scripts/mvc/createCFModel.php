<?php
if(isset($_POST['createCFModelForm']))
{
	// Create the model
	
	$extendclassName = $_POST['modelExtendName'];
	$moduleName = $_POST['moduleName'];
	
	// TODO: Check for module exists
	// TODO: Check for extended model exists
		
	generateCFModel($extendclassName, $moduleName);
	
}
else
{
?>

<form method="POST" id="createModelForm" target="">
	Module Name: <input type="text" name="moduleName" id="moduleName" /><br />
	Model Name: <input type="text" name="modelExtendName" id="modelName" /><br />
	<input type="submit" name="createCFModelForm" id="createCFModelForm" value="Create_CF_Model" />
</form>

<?php
}

function generateCFModel($extendclassName, $moduleName){
	$date = date('Y-m-d H:i:s');
	$className = 'GO_'.$moduleName.'_Model_'.$extendclassName.'CustomFieldsRecord';
	$extendclassName = 'GO_'.$moduleName.'_Model_'.$extendclassName;

	echo '<pre>'.htmlspecialchars('<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
 
 * @version $Id: '.$className.'.php 7607 '.$date.'Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 * @package GO.modules.'.$moduleName.' 
	 
/**
 * The CustomField Model for the GO_'.$moduleName.'_Model_'.$extendclassName.'
 *
 * @package GO.modules.'.$moduleName.'
 * @version $Id: '.$className.'.php 7607 '.$date.'Z <<USERNAME>> $
 * @copyright Copyright Intermesh BV.
 * @author <<FIRST_NAME>> <<LAST_NAME>> <<EMAIL>>@intermesh.nl
 */	
 
class '.$className.' extends \GO\Customfields\Model\AbstractCustomFieldsRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return GO_'.$moduleName.'_Model_CustomFieldsRecord 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function extendsModel(){
		return "'.$extendclassName.'";
	}
}
	').'</pre>';
}
?>