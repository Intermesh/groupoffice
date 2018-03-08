<?php
require('header.php');
if($_SERVER['REQUEST_METHOD']=='POST'){
	if(\GO\Base\Html\Error::checkRequired())
		redirect("configFile.php");
}


printHead();

?>
<h1>License terms</h1>
<p>The following license applies to this product:</p>
<div class="cmd">
<?php
echo \GO\Base\Util\StringHelper::text_to_html(file_get_contents('../LICENSE.TXT'));
?>
</div>

<?php
\GO\Base\Html\Checkbox::render(array(
		'required'=>true,
		'name'=>'agree',
		'value'=>1,
		'label'=>'I agree to the terms of the above license.'
		));

continueButton();
printFoot();