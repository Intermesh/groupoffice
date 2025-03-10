<?php
use go\core\App;
use go\core\jmap\Response;
use go\core\jmap\Router;
use go\core\jmap\Request;
use go\core\jmap\State;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\Summery;

require("../vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

$entities = [];

$parser = new PhpdocParser(PhpDocumentor::tags()->with([new Summery()]));

$toc = [];

$html = '';

$mods = [];

$types = \go\core\orm\EntityType::findAll();
usort($types, function($a, $b) {
	return strcmp($a->getName(), $b->getName());
});
foreach($types as $type) {

	$mod = $type->getModule()->getTitle();

	$cls = $type->getClassName();
	if(is_a($cls, \go\core\jmap\Entity::class, true)) {

		if(!isset($mods[$mod])) {
			$mods[$mod] = [];
		}

		$toc[] = $type->getName();

		$html = "<h3 id='".$type->getName()."'>" . $type->getName() . "</h3>";

		$meta = $parser->parse((new ReflectionClass($cls))->getDocComment());

		if(isset($meta['description'])) {
			$html .= "<p>" . $meta['description'] . "</p>";
		}

//		$entities[] = [
//			'name' => $type->getName(),
//			'properties' => $cls::buildApiProperties(true)
//		];

		$html .= "<table><tr><th>Property</th><th>Type</th><th>Description</th></tr>";
		$properties = $cls::buildApiProperties(true);
		uksort($properties, function($a, $b) {
			return strcmp($a, $b);
		});
		foreach($properties as $name => $property) {
			$html .= "<tr><td>" . $name . "</td><td>".($property['type'] ?? "")."</td><td>".($property['description'] ?? "-?-")."</td></tr>";
		}

		$html .= "</table>";

		$mods[$mod][$type->getName()] = $html;
	}
}

uksort($mods, function($a, $b) {return strcmp($a, $b);})
?>

<style>
	body {
			padding: 16px;
      font: 16px/24px Helvetica, serif;
  }

	table {
			border-collapse: collapse;
			td,th {
					border: 1px solid black;
					padding: 4px;
					align: left;
			}
	}
	code {
			display:block;
			background-color: #f1f1f1;
      font-family: "Courier New", Courier, serif;
			padding: 8px;
			white-space: pre;

	}
</style>

<h1>JMAP API</h1>

<h2>API endpoints</h2>
<table>
	<tr><td>Endpoint:</td><td><?= go()->getAuthState()->getApiUrl() ?></td></tr>
	<tr><td>Upload:</td><td><?= go()->getAuthState()->getUploadUrl()  ?></td></tr>
	<tr><td>Download:</td><td><?= go()->getAuthState()->getDownloadUrl("\$BLOBID"); ?></td></tr>
</table>

	<p>
		All entities listed below usually implement the standard methods as described in the <a href="https://jmap.io/spec-core.html#standard-methods-and-naming-convention">core JMAP spec</a>.
	</p>

<h3>Example request:</h3>

<code>curl --location '<?= go()->getAuthState()->getApiUrl() ?>' \
 --header 'Content-Type: application/json' \
 --header 'Authorization: Bearer $YOUR_API_TOKEN' \
 --data '[
 ["User/query", {"limit": 10}, "r1"],
 ["User/get", {"properties":[], "#ids": {"resultOf": "r1", "path": "/ids"}}, "r2"]
 ]'
</code>

<p>
	More examples and information about getting an API bearer token can be found in the <a href="https://groupoffice.readthedocs.io/en/latest/developer/jmap.html">online Group-Office documentation</a>.
</p>

<h2>Available entities</h2>

<?php


foreach($mods as $modName => $entities) {

	echo "<h4>".$modName."</h4>";
	foreach($entities as $entityName => $html) {
//
//		echo "<ul>";
		echo "<li><a href='#$entityName'>" . $entityName . "</a></li>";
		echo "</ul>";
	}

}


foreach($mods as $modName => $entities) {

	echo "<h2>".$modName."</h2>";
	foreach($entities as $entityName => $html) {
		echo $html;
	}

}

//echo "<pre>";
//
//echo json_encode($entities, JSON_PRETTY_PRINT);