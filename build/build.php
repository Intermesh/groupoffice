#!/usr/bin/env php7.4
<?php

use Github\Client;

require(__DIR__ . '/vendor/autoload.php');
$config = require(__DIR__ . '/config.php');

error_reporting(E_ALL);

function exception_error_handler($severity, $message, $file, $line)
{
	if (!(error_reporting() & $severity)) {
		// This error code is not included in error_reporting
		return;
	}
	throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler("exception_error_handler");

function run(string $cmd): array
{
	echo "Running " . $cmd . "\n";
	exec($cmd, $output, $return);

	foreach ($output as $line) {
		echo $line . "\n";
	}

	if ($return > 0) {
		throw new Exception("Command failed with status " . $return);
	}

	return $output;
}

function cd(string $dir) : void
{
	$dir = realpath($dir);

	echo "\ncd $dir\n\n";
	if (!chdir($dir)) {
		throw new Exception("Could not change dir to '" . $dir . "'");
	}
}

class Builder
{
	public $test = false;

	private $majorVersion = "25.2";

	private $gitBranch = 'develop';

	/**
	 *
	 * @var string sixsix, sixseven etc or testing
	 */
	public $distro = "testing";


	public $repreproDir = __DIR__ . "/deploy/reprepro";
	/**
	 *
	 * @var int
	 */
	private $minorVersion;
	/**
	 *
	 * @var string groupoffice-6.3.8-php-70
	 */
	private $packageName;


	private $encoder = '/usr/local/share/sourceguardian/bin/sourceguardian --phpversion 8.1+';

	private $encoderOptions = null;
	private $proRepos = "git@git.intermesh.nl:groupoffice/promodules.git";
	private $sourceDir = __DIR__ . "/deploy/source";
	private $encodedDir = __DIR__ . "/deploy/encoded";
	private $buildDir = __DIR__ . "/deploy/build";
	private $proModules = ["gota", "documenttemplates", "savemailas", "professional", "tickets", "scanbox", "leavedays", "projects2", "timeregistration2", "hoursapproval2", "pr2analyzer", "workflow", "filesearch", "assistant", "billing"];

	private $github = ['PERSONAL_ACCESS_TOKEN' => "secret", 'USERNAME' => 'intermesh', 'REPOSITORY' => 'groupoffice'];

	private $githubRelease;

	public function __construct($config)
	{
		$this->github = $config['github'];
	}

	public function build()
	{

		//dummy sign just to enter password at start
		run("gpg --clear-sign -o- /dev/null");

		$this->pullSource();
		$this->minorVersion = explode(".", require(dirname(__DIR__) . "/www/version.php"))[2];


		$this->packageName = "groupoffice-" . $this->majorVersion . "." . $this->minorVersion;


		$this->buildDir = __DIR__ . "/deploy/build/" . $this->majorVersion . "/" . $this->distro;

		run("rm -rf " . $this->buildDir);
		run("mkdir -p " . $this->buildDir);

		$this->encodedDir = __DIR__ . "/deploy/encoded/" . $this->majorVersion . "/" . $this->distro;

		run("rm -rf " . $this->encodedDir);
		run("mkdir -p " . $this->encodedDir);

		$this->buildFromSource();


		$this->buildDebianPackage();

		if (!$this->test) {
			$this->createGithubRelease();
			$this->addToDebianRepository();
			$this->sendTarToSF();
		}
	}

	private function pullSource()
	{
		run("mkdir -p " . $this->sourceDir);

		cd(dirname(__DIR__));

		run("git fetch");

		run("git pull");
        run("git submodule update --init");

		cd($this->sourceDir);

		if (!is_dir($this->sourceDir . "/promodules")) {
			run("git clone " . $this->proRepos . " -b " . $this->gitBranch);
		}

		cd($this->sourceDir . "/promodules");

		run("git fetch");
		run("git checkout " . $this->gitBranch);
		run("git pull");

		cd($this->sourceDir);
		if (!is_dir($this->sourceDir . "/business")) {
			run("git clone git@git.intermesh.nl:groupoffice/business.git -b " . $this->gitBranch);
		}

		cd($this->sourceDir . "/business");

		run("git fetch");
		run("git checkout " . $this->gitBranch);
		run("git pull");
	}

	private function buildFromSource()
	{
		run("cp -r " . dirname(__DIR__) . "/www/ " . $this->buildDir . "/" . $this->packageName);

		cd($this->buildDir . "/" . $this->packageName);

		$this->encode();

		$this->buildNodeCore();
		$this->buildNodeModules();
        $this->cleanupNodeCore();

        cd($this->buildDir . "/" . $this->packageName);

        putenv("COMPOSER_ALLOW_SUPERUSER=1");
		run("composer install --no-dev --optimize-autoloader --ignore-platform-reqs");

		$sassFiles = run("find views/Extjs3 go/modules modules \( -name style.scss -o -name style-mobile.scss -o -name htmleditor.scss \) -not -path '*/goui/*'");

		foreach ($sassFiles as $sassFile) {
			run("sass --no-source-map $sassFile " . dirname(dirname($sassFile)) . '/' . str_replace('scss', 'css', basename($sassFile)));
		}


		// remove sensitive files OWASP WSTG - WSTG-INFO-05
		run("rm composer.json composer.lock vendor/composer/installed.json");

		cd($this->buildDir);

		run("tar czf " . $this->packageName . ".tar.gz " . $this->packageName);
		echo "Created " . $this->buildDir . '/' . $this->packageName . ".tar.gz\n";
	}

	private function buildNodeCore()
	{
		cd($this->buildDir . "/" . $this->packageName);
		cd("views/goui/goui");
		run("npm ci");

        cd("../groupoffice-core");
		run("npm ci");

        cd("../");
        run("npm ci");
        run("npm run build");

	}

    private function cleanupNodeCore() {
        cd($this->buildDir . "/" . $this->packageName);
        cd("views/goui");
        run("npm prune --omit=dev");

        cd("groupoffice-core");
        run("npm prune --omit=dev");

        cd("../goui");
        run("npm prune --omit=dev");
    }


	private function buildNodeModules()
	{

		cd($this->buildDir . "/" . $this->packageName);

		//$packageFiles = array_reverse(run("find views/goui -name package.json -not -path '*/node_modules/*'"));

		$packageFiles = run("find go/modules -name package.json -not -path '*/node_modules/*'");

		foreach ($packageFiles as $packageFile) {
			$nodeDir = dirname($packageFile);
			cd($nodeDir);
			run("npm ci");
			run("pwd");
			run("npm run build");
			run("npm prune --omit=dev");
			cd($this->buildDir . "/" . $this->packageName);
		}
	}

	private function runEncoder(string $sourcePath, string $targetPath, array $excludes = []) : void
	{
        cd($this->sourceDir. dirname($sourcePath));

        $cmd = $this->encoder . ' -r ';

        if(!empty($excludes)) {
	        $cmd .= "-c " . implode("-c ", array_map("escapeshellarg", $excludes));
        }

        $cmd .= ' -f "*.php" -o ' . $this->buildDir . "/" . $this->packageName . $targetPath . " ".basename($sourcePath)."/*";

        echo "Running: " . $cmd . "\n";

		run($cmd);
	}

	private function encode()
	{
		foreach ($this->proModules as $module) {
			$this->runEncoder('/promodules/' . $module, '/modules', ["vendor/*"]);
		}

		$this->runEncoder('/promodules/tickets/model', '/modules/tickets/');
		$this->runEncoder('/promodules/tickets/customfields/model', '/modules/tickets/customfields/');
		$this->runEncoder('/promodules/tickets/customfields/model', '/modules/tickets/customfields/');


		foreach ($this->proModules as $module) {
			// keep lang and module install stuff open source
			if (is_dir($this->sourceDir . '/promodules/' . $module . '/language')) {
				run('cp ' . $this->sourceDir . '/promodules/' . $module . '/language/* ' . $this->buildDir . "/" . $this->packageName . '/modules/' . $module . '/language/');
			}

			if (is_dir($this->sourceDir . '/promodules/' . $module . '/install')) {
				run('cp -r ' . $this->sourceDir . '/promodules/' . $module . '/install/* ' . $this->buildDir . "/" . $this->packageName . '/modules/' . $module . '/install/');
			}

			if (file_exists($this->sourceDir . '/promodules/' . $module . '/' . ucfirst($module) . 'Module.php')) {
				run('cp ' . $this->sourceDir . '/promodules/' . $module . '/' . ucfirst($module) . 'Module.php ' . $this->buildDir . "/" . $this->packageName . '/modules/' . $module . '/');
			}
		}

		run('cp ' . $this->sourceDir . '/promodules/professional/Module.php ' . $this->buildDir . "/" . $this->packageName . '/modules/professional');
		run('cp ' . $this->sourceDir . '/promodules/projects2/report/* ' . $this->buildDir . "/" . $this->packageName . '/modules/projects2/report/');
		run('cp ' . $this->sourceDir . '/promodules/billing/Pdf.php ' . $this->buildDir . "/" . $this->packageName . '/modules/billing/Pdf.php');


		//business package
		$this->runEncoder('/business', '/go/modules', ["*/vendor/*"]);

		$businessDir = new DirectoryIterator($this->sourceDir . '/business');
		foreach ($businessDir as $fileinfo) {
			if ($fileinfo->isDot() || !$fileinfo->isDir() || $fileinfo->getBasename() == '.git') {
				continue;
			}

			$moduleName = $fileinfo->getBasename();

			echo "Building business/" . $moduleName . "\n";

			$moduleFile = $this->sourceDir . '/business/' . $moduleName . '/Module.php';

			if (file_exists($moduleFile) && strpos(file_get_contents($moduleFile), "requiredLicense") === false) {
				throw new Exception($moduleFile . " must contain a 'requiredLicense()' function override.");
			}

			if (is_dir($this->sourceDir . '/business/' . $moduleName . '/language')) {
				run('cp ' . $this->sourceDir . '/business/' . $moduleName . '/language/* ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/' . $moduleName . '/language/');
			}

			if (is_dir($this->sourceDir . '/business/' . $moduleName . '/install')) {
				run('cp -r ' . $this->sourceDir . '/business/' . $moduleName . '/install/* ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/' . $moduleName . '/install/');
			}

			if (file_exists($moduleFile)) {
				run('cp ' . $moduleFile . ' ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/' . $moduleName . '/');
			}
		}

		run('rm -rf ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/.git*');


		run('rm -rf ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/kanban');
		run('rm -rf ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/projects3');


		//needs to be open source as it's used by Module files
		run('cp ' . $this->sourceDir . '/business/finance/model/PaymentProviderInterface.php ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/finance/model/PaymentProviderInterface.php');
        run('cp ' . $this->sourceDir . '/business/finance/model/RecurringPaymentProviderInterface.php ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/finance/model/RecurringPaymentProviderInterface.php');


	}

	private function sendTarToSF()
	{

		echo "Creating SourceForge.net release....\n\n";

		cd($this->buildDir);


		run("scp " . $this->packageName . ".tar.gz mschering@frs.sourceforge.net:/home/frs/project/group-office/$this->majorVersion/");
	}

	private function createGithubRelease()
	{

		echo "Creating GitHub release....\n\n";

		cd($this->buildDir);

		$client = new Client();
		$client->authenticate($this->github['PERSONAL_ACCESS_TOKEN'], null, Client::AUTH_ACCESS_TOKEN);

		$tagName = 'v' . $this->majorVersion . "." . $this->minorVersion;

		$r = $client->api('repo')->releases();

		if (!isset($this->githubRelease)) {
			$this->githubRelease = $r->create($this->github['USERNAME'], $this->github['REPOSITORY'], array('tag_name' => $tagName, 'name' => $tagName, 'prerelease' => $this->distro == "testing", 'target_commitish' => $this->gitBranch, 'body' => 'Use the ' . $this->packageName . '.tar.gz file for installations. It contains all the code, libraries and compiled code. For installation instructions read: https://groupoffice.readthedocs.io/en/latest/install/install.html'));
		}

		$asset = $r->assets()->create($this->github['USERNAME'], $this->github['REPOSITORY'], $this->githubRelease['id'], $this->packageName . '.tar.gz', 'application/tar+gzip', file_get_contents($this->packageName . '.tar.gz'));

	}

	private function buildDebianPackage()
	{

		$tpl = '{package} ({version}-' . $this->distro . ') ' . $this->distro . '; urgency=medium

  * Changes can be found in /usr/share/groupoffice/changelog.md

 -- Intermesh BV (Developer key) <info@intermesh.nl>  {date}';

//Mon, 26 May 2010 12:30:00 +0200
		$date = date('D, j M Y H:i:s O');

		$debTarget = $this->buildDir . "/debian";
		run("rm -rf " . $debTarget);
		run("mkdir " . $debTarget);
		run("cp -r " . __DIR__ . "/debian/* " . $debTarget);

		file_put_contents($debTarget . '/debian/changelog', str_replace(array('{package}', '{version}', '{date}'), array("groupoffice", $this->majorVersion . '.' . $this->minorVersion, $date), $tpl));

		run("cp -r " . $this->buildDir . "/" . $this->packageName . "/* " . $debTarget . "/usr/share/groupoffice");
		cd($debTarget);
		run("debuild --no-lintian -b");
	}

	public function addToDebianRepository()
	{
		if (!is_dir($this->repreproDir)) {
			run("mkdir -p " . $this->repreproDir . "/conf");
			run("cp " . $this->sourceDir . "/debian-groupoffice/reprepro/distributions " . $this->repreproDir . "/conf");
		}

		run("reprepro -b " . $this->repreproDir . " include " . $this->distro . " " . $this->buildDir . "/groupoffice_" . $this->majorVersion . "." . $this->minorVersion . "-" . $this->distro . "_amd64.changes");
	}
}


$builder = new Builder($config);


if (isset($argv[1]) && $argv[1] == "test") {
	$builder->test = true;
}

$builder->build();

