<?php
if(isset($_POST['createYumlForm']))
{
	$className = $_POST['modelName'];
	generateYuml($className);
}
else
{
?>

<form method="POST" id="createYumlForm" target="">
	Model Name: <input type="text" name="modelName" id="modelName" /><br />
	<input type="submit" name="createYumlForm" id="createYumlForm" value="Create_Yuml" />
</form>

<?php
}

function generateYuml($className,$withRelations=false){
	require('../../www/GO.php');
	
	$yumlURL = "http://yuml.me/diagram/scruffy/class/";
	
	$modelCode = array();

	$model = \GO::getModel($className);
	
	$modelCode[$model->className()] = getModelCodeBlock($model);

	if($withRelations){
		$relations = $model->relations();
		if(!empty($relations)){
			foreach($relations as $relationName=>$relation){

				$rmodel = \GO::getModel($relation['model']);
				$modelCode[$rmodel->className()] = getModelCodeBlock($rmodel);

	//			switch($relation['type']){
	//				case \GO\Base\Db\ActiveRecord::HAS_MANY:
	//					break;
	//				case \GO\Base\Db\ActiveRecord::BELONGS_TO:
	//					break;
	//				case \GO\Base\Db\ActiveRecord::HAS_ONE:
	//					break;
	//				case \GO\Base\Db\ActiveRecord::MANY_MANY:
	//					break;
	//			}
			}
		}
	}
	
	$output = '';
	foreach($modelCode as $modelName=>$code){
		$output .= $code;
	}
	echo '<pre>'.$output.'</pre>';
	//echo '<img src="'.$yumlURL.$output.'"/>';
	
	
}
	
function getModelCodeBlock(\GO\Base\Db\ActiveRecord $model, $includeArMethods=false){
	$mcoutput='';
	$mcoutput = '['.$model->className().'|';

	$columns = $model->getColumns();

	foreach($columns as $columnName=>$column){
		$mcoutput .= $columnName.';';
	}
	
	$modelMethods = get_class_methods($model->className());
	$arMethods = get_class_methods('GO\Base\Db\ActiveRecord');
	
	if($includeArMethods)
		$methods = $modelMethods;
	else	
		$methods = array_diff($modelMethods,$arMethods);
	
	if($methods){
		$mcoutput .= '|';
		foreach($methods as $method){
			$mcoutput .= $method.'();';
		}
	}
	
	$mcoutput .=']';
	//$mcoutput .= ','; // For image
	$mcoutput .= '<br />'; // A linebreak after each model object
	return $mcoutput;
}

?>