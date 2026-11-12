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

/**
 * Short type notation of a JSON schema for the docs table. For example "string|string[]".
 */
function schemaType(array $schema): string {
	if (isset($schema['enum'])) {
		return implode(' | ', array_map('json_encode', $schema['enum']));
	}
	if (isset($schema['anyOf'])) {
		return implode(' | ', array_map('schemaType', $schema['anyOf']));
	}
	$type = $schema['type'] ?? 'mixed';
	if ($type === 'array') {
		$items = schemaType($schema['items'] ?? []);
		return (str_contains($items, '|') ? '(' . $items . ')' : $items) . '[]';
	}
	if ($type === 'object' && isset($schema['properties'])) {
		$props = [];
		foreach ($schema['properties'] as $name => $prop) {
			$props[] = $name . (in_array($name, $schema['required'] ?? []) ? '' : '?') . ': ' . schemaType($prop);
		}
		return '{' . implode(', ', $props) . '}';
	}
	return is_array($type) ? implode(' | ', $type) : $type;
}

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
			$html .= "<tr><td>" . $name . "</td><td>".($property['type'] ?? "")."</td><td>".($property['description'] ?? "")."</td></tr>";
		}

		$html .= "</table>";

		// Custom fields are installation specific and this page is public, so leave them out.
		$customFieldNames = method_exists($cls, 'getCustomFieldModels') ? array_keys($cls::getCustomFieldModels()) : [];
		$filterProperties = (array) $cls::getFilters()->toJsonSchema(false)['$defs']['FilterCondition']['properties'];

		$html .= "<h4>Filters</h4><table><tr><th>Filter</th><th>Type</th><th>Value</th><th>Description</th><th>Default</th></tr>";
		foreach($filterProperties as $name => $schema) {
			if(in_array($name, $customFieldNames, true)) {
				continue;
			}
			$html .= "<tr><td>" . htmlspecialchars($name) . "</td>" .
				"<td>" . htmlspecialchars($schema['x-filterType']) . "</td>" .
				"<td>" . htmlspecialchars(schemaType($schema)) . "</td>" .
				"<td>" . htmlspecialchars($schema['description'] ?? "") . "</td>" .
				"<td>" . (array_key_exists('default', $schema) ? htmlspecialchars(json_encode($schema['default'])) : "") . "</td></tr>";
		}
		$html .= "</table>";

		$mods[$mod][$type->getName()] = $html;
	}
}

uksort($mods, function($a, $b) {return strcmp($a, $b);})
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GroupOffice JMAP API</title>
<style>
	* {
		box-sizing: border-box;
	}

	html, body {
		height: 100%;
		margin: 0;
	}

	body {
		font: 16px/24px Helvetica, sans-serif;
		display: flex;
	}

	nav {
		width: 280px;
		flex-shrink: 0;
		overflow-y: auto;
		border-right: 1px solid #ccc;
		background-color: #f7f7f7;
		padding: 16px;
	}

	nav input {
		width: 100%;
		padding: 4px 8px;
		margin-bottom: 8px;
		font: inherit;
	}

	nav h4 {
		margin: 16px 0 4px;
	}

	nav ul {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	nav a {
		display: block;
		padding: 0 8px;
		color: inherit;
		text-decoration: none;
		border-radius: 4px;
	}

	nav a:hover, nav a.active {
		background-color: #e2e2e2;
	}

	main {
		flex: 1;
		min-width: 0;
		overflow-y: auto;
		padding: 16px 32px;
	}

	table {
		border-collapse: collapse;
		margin-bottom: 16px;
	}

	td, th {
		border: 1px solid black;
		padding: 4px;
		text-align: left;
		vertical-align: top;
	}

	code {
		display: block;
		background-color: #f1f1f1;
		font-family: "Courier New", Courier, monospace;
		padding: 8px;
		white-space: pre;
		overflow-x: auto;
	}

	@media (max-width: 700px) {
		body {
			display: block;
		}

		nav {
			width: auto;
			max-height: 40vh;
			border-right: none;
			border-bottom: 1px solid #ccc;
		}

		main {
			padding: 16px;
		}
	}
</style>
</head>
<body>

<nav>
	<input type="search" id="search" placeholder="Search entities" aria-label="Search entities">
	<ul>
		<li><a href="#introduction">Introduction</a></li>
		<li><a href="#filters">Filters</a></li>
	</ul>
	<?php foreach($mods as $modName => $entities): ?>
	<div class="module">
		<h4><?= htmlspecialchars($modName) ?></h4>
		<ul>
			<?php foreach($entities as $entityName => $html): ?>
			<li><a href="#<?= htmlspecialchars($entityName) ?>"><?= htmlspecialchars($entityName) ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endforeach; ?>
</nav>

<main>

<h1 id="introduction">GroupOffice JMAP API</h1>

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

<h2 id="filters">Filters</h2>

<p>
	The "filter" argument of Foo/query is either a filter condition object with the filters listed for each entity, or a
	filter operator that combines conditions with AND, OR or NOT. Operators can be nested.
	Custom fields can be filtered on by their database name.
</p>

<p>The value syntax depends on the filter type shown in the filter tables:</p>

<table>
	<tr><th>Type</th><th>Value syntax</th></tr>
	<?php foreach(\go\core\orm\Filters::getTypeSyntax() as $filterType => $syntax): ?>
	<tr><td><?= htmlspecialchars($filterType) ?></td><td><?= htmlspecialchars($syntax) ?></td></tr>
	<?php endforeach; ?>
	<tr><td>generic</td><td>See the value type and description of the filter.</td></tr>
</table>

<code>["Note/query", {
  "filter": {
    "operator": "AND",
    "conditions": [
      {"text": "invoice", "createdAt": "-1 month..now"},
      {"operator": "NOT", "conditions": [{"createdBy": "{{me}}"}]}
    ]
  }
}, "r1"]
</code>

<?php
foreach($mods as $modName => $entities) {
	echo "<h2>" . htmlspecialchars($modName) . "</h2>";
	foreach($entities as $entityName => $html) {
		echo $html;
	}
}
?>

</main>

<script>
	// Filter the entity menu
	const search = document.getElementById('search');
	search.addEventListener('input', () => {
		const q = search.value.trim().toLowerCase();
		document.querySelectorAll('nav .module').forEach(module => {
			let visible = 0;
			module.querySelectorAll('li').forEach(li => {
				const match = !q || li.textContent.toLowerCase().includes(q);
				li.hidden = !match;
				if (match) visible++;
			});
			module.hidden = visible === 0;
		});
	});

	// Highlight the menu item of the selected entity
	function markActive() {
		document.querySelectorAll('nav a').forEach(a => a.classList.toggle('active', a.getAttribute('href') === location.hash));
	}
	window.addEventListener('hashchange', markActive);
	markActive();
</script>

</body>
</html>
