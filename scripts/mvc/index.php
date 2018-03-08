<?php
//define('GO_CONFIG_FILE', '/etc/groupoffice/go61.intermesh.dev/config.php');
?>

<html>
	<head>
		<title></title>
	</head>
	<body>
		<div id="header"></div>
		<div id="menu">
			<ul>
				<li><a href="?action=createModel">Create a model</a></li>
				<li><a href="?action=createCFModel">Create a customfield model</a></li>
				<li><a href="?action=createController">Create a Controller</a></li>
				<li><a href="?action=getLangKeys">Get the language keys for a model</a></li>
				<li><a href="?action=createYuml">Get the uml diagram from a model</a></li>
			</ul>
		</div>
		<div id="content">
			<?php
			
			if(isset($_GET['action'])){
				switch($_GET['action']){
					case 'createModel':
						include "createModel.php";
						break;
					case 'createCFModel':
						include "createCFModel.php";
						break;
					case 'createController':
						include "createController.php";
						break;
					case 'getLangKeys':
						include "getModelLanguageKeys.php";
						break;
					case 'createYuml':
						include "createYuml.php";
						break;
					default:
						echo "Select a task from the menu!";
						break;
				}
			}
			else
			{
				echo "Select a task from the menu!";
			}
			
			?>
		</div>
		<div id="footer"></div>
	</body>
</html>