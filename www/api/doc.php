<?php
use go\core\App;
use go\core\jmap\Router;
use go\core\jmap\State;
use go\core\orm\EntityType;
use go\core\orm\PropertySchema;
use go\core\util\StringUtil;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\Summery;

require("../vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

$parser = new PhpdocParser(PhpDocumentor::tags()->with([new Summery()]));

$mods = [];

/**
 * Escape text for HTML
 */
function e(?string $text): string {
	return htmlspecialchars($text ?? "");
}

/**
 * Description text as HTML with links for URLs and e-mail addresses
 */
function description(?string $text): string {
	return StringUtil::textToHtml($text ?? "");
}

/**
 * Short type notation of a JSON schema as HTML. For example "string | string[]". References to other types link to
 * their table, which has the id "$anchorPrefix.$name".
 */
function schemaType(array $schema, string $anchorPrefix = ''): string {
	if (isset($schema['$ref'])) {
		$name = substr($schema['$ref'], strlen('#/$defs/'));
		return "<a href='#" . e($anchorPrefix . '.' . $name) . "'>" . e($name) . "</a>";
	}
	if (isset($schema['enum'])) {
		return e(implode(' | ', array_map('json_encode', $schema['enum'])));
	}
	if (isset($schema['anyOf'])) {
		return implode(' | ', array_map(fn($s) => schemaType($s, $anchorPrefix), $schema['anyOf']));
	}
	if (isset($schema['x-phpType'])) {
		return e($schema['x-phpType']);
	}
	$type = $schema['type'] ?? 'mixed';
	$types = (array) $type;
	$nullable = in_array('null', $types, true) && count($types) > 1;
	$types = array_values(array_diff($types, $nullable ? ['null'] : []));

	if ($types === ['array']) {
		$items = schemaType($schema['items'] ?? [], $anchorPrefix);
		$str = (str_contains($items, '|') ? '(' . $items . ')' : $items) . '[]';
	} else if ($types === ['object'] && isset($schema['additionalProperties']) && is_array($schema['additionalProperties'])) {
		$str = '{id: ' . schemaType($schema['additionalProperties'], $anchorPrefix) . '}';
	} else if ($types === ['object'] && !empty((array) ($schema['properties'] ?? []))) {
		$props = [];
		foreach ($schema['properties'] as $name => $prop) {
			$props[] = e($name) . (in_array($name, $schema['required'] ?? []) ? '' : '?') . ': ' . schemaType($prop, $anchorPrefix);
		}
		$str = '{' . implode(', ', $props) . '}';
	} else {
		$str = e(implode(' | ', $types));
		if (isset($schema['format'])) {
			$str .= ' (' . e($schema['format']) . ')';
		}
	}

	return $nullable ? $str . ' | null' : $str;
}

/**
 * Table with the properties of an object schema
 */
function propertiesTable(array $schema, string $anchorPrefix): string {
	$html = "<table><tr><th>Property</th><th>Type</th><th>Read only</th><th>Description</th></tr>";
	foreach ($schema['properties'] as $name => $prop) {
		$description = $prop['description'] ?? '';
		if (isset($prop['maxLength'])) {
			$description = trim($description . ' Max length ' . $prop['maxLength'] . '.');
		}
		$html .= "<tr><td>" . e($name) . "</td>" .
			"<td>" . schemaType($prop, $anchorPrefix) . "</td>" .
			"<td>" . (!empty($prop['readOnly']) ? 'yes' : '') . "</td>" .
			"<td>" . description($description) . "</td></tr>";
	}
	return $html . "</table>";
}

$types = EntityType::findAll();
usort($types, function($a, $b) {
	return strcmp($a->getName(), $b->getName());
});
foreach($types as $type) {

	$mod = $type->getModule()->getTitle();

	$cls = $type->getClassName();
	if(!is_a($cls, \go\core\jmap\Entity::class, true)) {
		continue;
	}

	$entityName = $type->getName();

	$html = "<h3 id='" . e($entityName) . "'>" . e($entityName) . "</h3>";

	$meta = $parser->parse((new ReflectionClass($cls))->getDocComment());

	if(isset($meta['description'])) {
		$html .= "<p>" . description($meta['description']) . "</p>";
	}

	$html .= "<h4>Methods</h4><table><tr><th>Method</th><th>Description</th></tr>";
	foreach(Router::getEntityMethods($type) as $method => $description) {
		$html .= "<tr><td>" . e($entityName . '/' . $method) . "</td><td>" . description($description) . "</td></tr>";
	}
	$html .= "</table>";

	// Custom fields are installation specific and this page is public, so they are left out.
	$schema = PropertySchema::build($cls);
	$rootName = substr($schema['$ref'], strlen('#/$defs/'));

	$html .= "<h4>Properties</h4>" . propertiesTable($schema['$defs'][$rootName], $entityName);

	foreach($schema['$defs'] as $defName => $def) {
		if($defName === $rootName) {
			continue;
		}
		$html .= "<h4 id='" . e($entityName . '.' . $defName) . "'>" . e($defName) . "</h4>" . propertiesTable($def, $entityName);
	}

	$customFieldNames = method_exists($cls, 'getCustomFieldModels') ? array_keys($cls::getCustomFieldModels()) : [];
	$filterProperties = (array) $cls::getFilters()->toJsonSchema(false)['$defs']['FilterCondition']['properties'];

	$html .= "<h4>Filters</h4><table><tr><th>Filter</th><th>Type</th><th>Value</th><th>Description</th><th>Default</th></tr>";
	foreach($filterProperties as $name => $filterSchema) {
		if(in_array($name, $customFieldNames, true)) {
			continue;
		}
		$html .= "<tr><td>" . e($name) . "</td>" .
			"<td>" . e($filterSchema['x-filterType']) . "</td>" .
			"<td>" . schemaType($filterSchema) . "</td>" .
			"<td>" . description($filterSchema['description'] ?? null) . "</td>" .
			"<td>" . (array_key_exists('default', $filterSchema) ? e(json_encode($filterSchema['default'])) : "") . "</td></tr>";
	}
	$html .= "</table>";

	$mods[$mod][$entityName] = $html;
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
		<li><a href="#properties">Methods and properties</a></li>
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
	More examples and information about getting an API bearer token can be found in the <a href="https://groupoffice.readthedocs.io/en/latest/developer/jmap.html">online GroupOffice documentation</a>.
</p>

<h2 id="properties">Methods and properties</h2>

<p>
	Each entity lists the methods it supports, like Note/get, and its properties. Properties marked "read only" are set by
	the server and ignored in Foo/set. Required properties are not listed: Foo/set returns an "invalidProperties" error
	with the properties that are missing or invalid.
</p>

<ul>
	<li>IDs are strings.</li>
	<li>"date-time" is ISO 8601 in UTC, for example "2025-01-31T09:00:00+00:00".</li>
	<li>"local-date-time" has no time zone, for example "2025-01-31T09:00:00".</li>
	<li>"date" has no time, for example "2025-01-31".</li>
	<li>Types like Address link to their own table below the entity. {id: Type} is an object with the ID as key.</li>
	<li>"customFields" holds the custom field values with the database name of the field as key.</li>
</ul>

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
	echo "<h2>" . e($modName) . "</h2>";
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
