<?php
if(isset($_POST['createModelForm']))
{
	// Create the model
	
	$className = $_POST['modelName'];

	generateLangKeys($className);
	
}
else
{
?>

<form method="POST" id="createModelForm" target="">
	Model Name: <input type="text" name="modelName" id="modelName" /><br />
	<input type="submit" name="createModelForm" id="createModelForm" value="Create_Model" />
</form>

<?php
}


function generateLangKeys($className){
	require('../../www/GO.php');
	
	$model = new $className();
	
	$name = explode('\\',$className);
	$modelName = strtolower(end($name));
	$columns = $model->getColumns();
	
	echo '<pre>';
	echo '// Language for model: '.$className;
	echo '<br />';
	foreach($columns as $column=>$value) {
		echo htmlspecialchars('$l["'.$modelName.ucfirst($column).'"] = "";');
		echo '<br />';
	}

	echo '</pre>';
}
?>