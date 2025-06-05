<?php

namespace go\core\util;

use Closure;
use go\core\App;
use go\core\Environment;
use go\core\fs\Folder;
use go\core\model\Module;
use ReflectionClass;
use go\core\fs\File;
use ReflectionException;
use Throwable;

/**
 * Finds classes within Group-Office.
 * 
 * This only finds classes in the new framwwork under "go/*".
 * 
 * Warning: Using this is expensive. Caching the results is recommended.
 * 
 */
class ClassFinder {	
	
	/**
	 * Get all the Group-Office namespaces to search in.
	 * 
	 * @return string[]
	 */
	public static function getDefaultNamespaces(): array
	{
		$ns = go()->getCache()->get("class-finder-default-namespaces");
		
		if($ns === null) {
			$ns = ['go\\core'];		

			/** @var Module[] $modules */
			$modules = Module::find()->where(['enabled' => true]);
			foreach ($modules as $module) {
				if(!isset($module->package) || $module->package == "core" ||  !$module->isAvailable()) {
					continue;
				}
				$namespace = "go\\modules\\" . $module->package . "\\" . $module->name;
				$ns[] = $namespace;
			}
			
			go()->getCache()->set("class-finder-default-namespaces", $ns);
		}
		
		return $ns;
	}

	/**
	 * 
	 * @param boolean $useDefaultNamespaces Load go\\core and all installed module namespaces
	 */
	public function __construct(bool $useDefaultNamespaces = true) {
		if($useDefaultNamespaces) {			
			foreach(self::getDefaultNamespaces() as $namespace){
				$this->addNamespace($namespace);
			}
		}
	}

	private $namespaces = [];

	/**
	 * Add a namespace to search
	 *
	 * @param string $namespace
	 * @param Folder|null $folder If not given it will use the installation root + namespace
	 */
	public function addNamespace(string $namespace, Folder|null $folder = null) {
		if(!isset($folder)) {
			$folder = Environment::get()->getInstallFolder()->getFolder(str_replace('\\', '/', $namespace));
		}		
		$this->namespaces[$namespace] = $folder;
	}

	/**
	 * Find all classes
	 * 
	 * @return string[] Full class name without leading "\" eg. ["IFW\App"]
	 */
	public function find(): array
	{
		$allClasses = [];
		foreach ($this->namespaces as $namespace => $folder) {

			$classesForNs = App::get()->getCache()->get('ns-classes-'.$namespace);

			if(!isset($classesForNs)) {
				$classesForNs = $this->folderToClassNames($folder, $namespace);
				App::get()->getCache()->set('ns-classes-'.$namespace, $classesForNs);
			}

			$allClasses = array_merge($allClasses, $classesForNs);
		}

		sort($allClasses);
		
		return $allClasses;
	}

	/**
	 * Find class names that are sub classes of the given class or implement this as an interface
	 *
	 * @template T
	 * @param class-string<T> $name Parent class name or interface eg. go\core\orm\Record::class
	 * @return class-string<T>[]
	 */
	public function findByParent(string $name): array
	{
		return $this->findBy(function($className) use ($name) {
			try {
				$reflection = new ReflectionClass($className);
				return !$reflection->isTrait()  && !$reflection->isInterface() && !$reflection->isAbstract() && ($reflection->isSubclassOf($name) || in_array($name, $reflection->getInterfaceNames()));
			} catch(\Error $e) { // class not found?
				return false;
			}

		});
	}
	
	/**
	 * Find classes that use a given trait
	 *
	 * @template T
	 * @param class-string<T> $name Name of the trait eg. go\core\db\Searchable::class
	 * @return class-string<T>[] Full class name eg. ["go\core\App"]
	 */
	public function findByTrait(string $name): array
	{
		 return $this->findBy(function($className) use($name){
			 try {
				return in_array($name, static::classUsesDeep($className));
			 } catch(\Error $e) { // class not found?
				 return false;
			 }
		});
	}


	private static function classUsesDeep(string $class, bool $autoload = true): array
	{

		$traits = [];

		do {
			$traits = array_merge(class_uses($class, $autoload), $traits);

		} while($class = get_parent_class($class));

		foreach ($traits as $trait => $same) {
			$traits = array_merge(class_uses($trait, $autoload), $traits);
		}

		return array_unique($traits);

	}

	/**
	 * Find class names by a closure function
	 *
	 * If you return true in the closure function it will be included in the results.
	 * The closure function is called with the class name
	 *
	 * @param Closure $fn
	 * @return string[]
	 */
	public function findBy(Closure $fn): array
	{
		$classes = $this->find();

		$r = [];
		foreach ($classes as $class) {
			if ($fn($class)) {
				$r[] = $class;
			}
		}

		return $r;
	}

	public static function canBeDecoded(File $file): bool
	{

		if(go()->getEnvironment()->hasIoncube()) {
			return true;
		} else{
			return !static::fileIsEncoded($file);
		}
//		//check for pro license on business package
//		if(!$this->fileIsEncoded($file) {
//			return true;
//		}
//
//		if(!)
//		{
//			return false;
//		}
//
//		$parts = explode("\\", $namespace);
//
//		$moduleCls= "go\\modules\\". $parts[2]."\\".$parts[3]."\\Module";
//		try {
//			return !class_exists($moduleCls) || $moduleCls::get()->isLicensed();
//		}
//		catch(\Throwable $e) {
//			go()->debug("Class '$moduleCls' couldn't be loaded: " . $e->getMessage());
//			return false;
//		}

	}

	public static function fileIsEncoded(File $file): bool
	{
		//Check if file is encoded
		$data = $file->getContents(0, 200);
		return strpos($data, 'sg_load') !== false;
	}

	private function folderToClassNames(Folder $folder, string $namespace): array
	{
		$classes = [];
		foreach ($folder->getFiles() as $file) {

			if ($file->getExtension() == 'php') {

				$name = $file->getNameWithoutExtension();
				if(preg_match('/^[^A-Z]/', $name)) {
					//skip filenames that do not start with an upper case char
					continue;
				}

				if(!static::canBeDecoded($file)) {
					continue;
				}

				$className = $namespace . '\\'. $name;

				try {
					if (!class_exists($className)) {
						continue;
					}
				}
				catch(Throwable $e) {
					go()->debug("Class '$className' couldn't be loaded: " . $e->getMessage());
					continue;
				}

				$classes[] = $className;
			}
		}

		foreach ($folder->getFolders() as $folder) {
			if($folder->getName() !== 'vendor' && substr($folder->getName(), 0, 1) != ".") {
				$classes = array_merge($classes, $this->folderToClassNames($folder, $namespace . '\\' . $folder->getName()));
			}
		}

		return $classes;
	}

}

