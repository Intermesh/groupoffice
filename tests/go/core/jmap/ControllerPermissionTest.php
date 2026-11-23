<?php

namespace go\core\jmap;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

/**
 * Every public method of a JMAP controller can be called by any user with access to the module. Each one must check
 * permissions, or say why that's not needed.
 *
 * The controllers are found on disk, so that modules which can't be installed in the tests without a license are
 * checked too. A method passes when its body, or the body of a method of the same class that it calls, contains a
 * known permission check, or when its "@apiPermission" starts with {@see MethodSchema::PERMISSION_MODULE_ACCESS} or
 * {@see MethodSchema::PERMISSION_CHECKED_IN}.
 *
 * Custom methods (not the standard JMAP methods) must also document the permission with "@apiPermission".
 */
class ControllerPermissionTest extends TestCase
{
	/**
	 * Calls and statements that check permissions
	 */
	private const GUARDS = [
		'\$this->(findEntity|getEntity|default[A-Z]\w*)\(',
		'->(hasPermissionLevel|checkPermissionLevel|requirePermissionLevel|getPermissionLevel)\(',
		'->isAdmin\(\)',
		'applyAclToQuery\(',
		'Acl::applyToQuery\(',
		'[\'"]permissionLevel[\'"]',
		'new\s+\\\\?([\w\\\\]*\\\\)?(Forbidden|AccessDenied)\b',
		'->rights->',
		'getUserRights\(',
	];

	/**
	 * The standard methods. They don't need "@apiPermission" because their permissions are documented once.
	 */
	private const STANDARD = ['get', 'query', 'set', 'changes', 'export', 'import', 'exportcolumns', 'importcsvmapping', 'merge'];

	/**
	 * Find the controller classes in the "controller" folders under www/go
	 *
	 * @return array{0: string[], 1: string[]} Class names and the files that could not be loaded
	 */
	private function findControllers(): array
	{
		$root = go()->getEnvironment()->getInstallPath() . '/go';
		$classes = [];
		$errors = [];

		$it = new \RecursiveIteratorIterator(new \RecursiveCallbackFilterIterator(
			new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
			function (\SplFileInfo $file) {
				return !in_array($file->getFilename(), ['vendor', 'node_modules', 'cli', 'views', 'language', 'install'], true);
			}
		));

		foreach ($it as $file) {
			if ($file->getExtension() !== 'php' || basename($file->getPath()) !== 'controller') {
				continue;
			}
			$source = file_get_contents($file->getPathname());
			if (!preg_match('/^namespace\s+([^;]+);/m', $source, $ns) || !preg_match('/^(?:abstract\s+|final\s+)?class\s+(\w+)/m', $source, $cls)) {
				continue;
			}
			$class = $ns[1] . '\\' . $cls[1];
			try {
				if (Router::isRoutableController($class) && !(new ReflectionClass($class))->isAbstract()) {
					$classes[] = $class;
				}
			} catch (Throwable $e) {
				$errors[] = $file->getPathname() . ': ' . $e->getMessage();
			}
		}

		sort($classes);
		return [$classes, $errors];
	}

	/**
	 * The public methods that the router can call
	 *
	 * @return ReflectionMethod[]
	 */
	private function routableMethods(string $class): array
	{
		$methods = [];
		foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
			if ($method->isStatic() || str_starts_with($method->getName(), '__') || $method->getDeclaringClass()->getName() === \go\core\Controller::class) {
				continue;
			}
			$methods[] = $method;
		}
		return $methods;
	}

	private function body(ReflectionMethod $method): string
	{
		$file = $method->getFileName();
		if (!$file) {
			return '';
		}
		$lines = file($file);
		$source = implode('', array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));

		// Without comments, so commented-out checks don't count
		$code = '';
		foreach (token_get_all('<?php ' . $source) as $token) {
			if (is_array($token)) {
				if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG], true)) {
					continue;
				}
				$code .= $token[1];
			} else {
				$code .= $token;
			}
		}
		return $code;
	}

	private function hasGuard(string $body): bool
	{
		foreach (self::GUARDS as $guard) {
			if (preg_match('/' . $guard . '/', $body)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * A permission check in the method, or in a method of the same class that it calls
	 */
	private function isGuarded(ReflectionMethod $method): bool
	{
		$body = $this->body($method);
		if ($this->hasGuard($body)) {
			return true;
		}

		$class = $method->getDeclaringClass();
		preg_match_all('/\$this->(\w+)\(/', $body, $calls);
		foreach (array_unique($calls[1]) as $call) {
			if ($call === 'checkParams' || !$class->hasMethod($call)) {
				continue;
			}
			if ($this->hasGuard($this->body($class->getMethod($call)))) {
				return true;
			}
		}
		return false;
	}

	private function isExempt(ReflectionMethod $method): bool
	{
		$permission = MethodSchema::permission($method->getDocComment());
		return isset($permission) && (str_starts_with($permission, MethodSchema::PERMISSION_MODULE_ACCESS) || str_starts_with($permission, MethodSchema::PERMISSION_CHECKED_IN));
	}

	public function testControllersCanBeLoaded(): void
	{
		[, $errors] = $this->findControllers();
		$this->assertEquals([], $errors);
	}

	public function testControllersAreFound(): void
	{
		[$classes] = $this->findControllers();
		// Controllers of modules that aren't installed in the tests must be found too
		$this->assertContains(\go\modules\business\contracts\controller\Contract::class, $classes);
		$this->assertContains(\go\core\controller\FieldSet::class, $classes);
	}

	/**
	 * Every method checks permissions, or says why that's not needed
	 */
	public function testMethodsCheckPermissions(): void
	{
		[$classes] = $this->findControllers();
		$unguarded = [];
		foreach ($classes as $class) {
			foreach ($this->routableMethods($class) as $method) {
				if (!$this->isGuarded($method) && !$this->isExempt($method)) {
					$unguarded[] = $class . '::' . $method->getName();
				}
			}
		}

		$this->assertEquals([], $unguarded, "Check permissions in these methods. When access to the module is enough, document that with \"@apiPermission " . MethodSchema::PERMISSION_MODULE_ACCESS . " <reason>\", or with \"@apiPermission " . MethodSchema::PERMISSION_CHECKED_IN . "<Class::method()>: <permission>\" when it's checked elsewhere.");
	}

	/**
	 * Every custom method documents its permission
	 */
	public function testCustomMethodsDocumentPermission(): void
	{
		[$classes] = $this->findControllers();
		$undocumented = [];
		foreach ($classes as $class) {
			foreach ($this->routableMethods($class) as $method) {
				$doc = $method->getDocComment();
				if (in_array(strtolower($method->getName()), self::STANDARD, true) || ($doc && str_contains($doc, '@noapi'))) {
					continue;
				}
				if (MethodSchema::permission($doc) === null) {
					$undocumented[] = $class . '::' . $method->getName();
				}
			}
		}

		$this->assertEquals([], $undocumented, "Document the permission of these methods with @apiPermission");
	}
}
