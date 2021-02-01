#!/usr/bin/env php
<?php
require(__DIR__ . '/vendor/autoload.php');
$config = require(__DIR__ . '/config.php');

error_reporting(E_ALL);

function exception_error_handler($severity, $message, $file, $line) {
	if (!(error_reporting() & $severity)) {
		// This error code is not included in error_reporting
		return;
	}
	throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler("exception_error_handler");

function run($cmd) {
	echo "Running " . $cmd . "\n";
	exec($cmd, $output, $return);

	foreach ($output as $line) {
		echo $line . "\n";
	}

	if ($return > 0) {
		throw new \Exception("Command failed with status " . $return);
	}

	return $output;
}

function cd($dir) {
	if (!chdir(realpath($dir))) {
		throw new \Exception("Could not change dir to '" . $dir . "'");
	}
}

class Builder {

	public $branch = "6.4";

	/**
	 *
	 * @var string 63-php-70
	 */
	public $distro;

	/**
	 *
	 * @var string php-70
	 */
	public $variant;

	/**
	 *
	 * @var int
	 */
	private $version;

	/**
	 *
	 * @var string groupoffice-6.3.8-php70
	 */
	private $packageName;

	private $variants = [
		'php-70' => [
			"encoderOptions" => "-56 --allow-reflection-all"
		],
		'php-71' => [
			"encoderOptions" => "-71 --allow-reflection-all"
		]
	];
	private $communityRepos = "https://github.com/Intermesh/groupoffice.git";
	public $repreproDir = __DIR__ . "/deploy/reprepro";
	private $encoder = __DIR__ . "/deploy/ioncube_encoder5_10.2/ioncube_encoder.sh";

	private $encoderOptions = "-56 --allow-reflection-all";
	private $proRepos = "git@git.intermesh.nl:groupoffice/promodules.git";
	private $sourceDir = __DIR__ . "/deploy/source";
	private $encodedDir = __DIR__ . "/deploy/encoded";
	private $buildDir = __DIR__ . "/deploy/build";
	private $proModules = [
		"gota",
		"documenttemplates",
		"savemailas",
		"professional",
		"tickets",
		"scanbox",
		"leavedays",
		"projects2",
		"timeregistration2",
		"hoursapproval2",
		"pr2analyzer",
		"workflow",
		"filesearch",
		"assistant",
        "billing"
	];

	private $github = [
			'PERSONAL_ACCESS_TOKEN' => "secret",
			'USERNAME' => 'intermesh',
			'REPOSITORY' => 'groupoffice'
		];

	private $ioncubePassword = "secret";

	public function __construct($config) {
        $this->github = $config['github'];
        $this->ioncubePassword = $config['ioncubePassword'];

	}

	public function build() {

		$this->pullSource();
		$this->version = explode(".", require(dirname(__DIR__) . "/www/version.php"))[2];

		$this->packageName = "groupoffice-" . $this->branch . "." . $this->version;

//		if($this->branch != "master") {
//			//	$this->tag();
//		}

		foreach($this->variants as $variant => $options) {
			$this->distro = str_replace(".", "", $this->branch)."-".$variant; //63-php-70
			$this->variant = $variant;

			$this->packageName = "groupoffice-" . $this->branch . "." . $this->version . '-' . $variant;

			$this->encoderOptions = $options['encoderOptions'];

			$this->buildDir = __DIR__ . "/deploy/build/" . $this->branch . "/" . $variant;

			run("rm -rf " . $this->buildDir);
			run("mkdir -p " . $this->buildDir);

			$this->encodedDir = __DIR__ . "/deploy/encoded/" . $this->branch . "/". $variant;

			run("rm -rf " . $this->encodedDir);
			run("mkdir -p " . $this->encodedDir);

			$this->buildFromSource();

			if($this->branch == 'master' || $this->branch == '6.5') {
				echo "Skipping SourceForge upload and Debian package because we're building master\n";
			} else
			{
				$this->buildDebianPackage();

                $this->createGithubRelease();

				$this->addToDebianRepository();

				$this->sendTarToSF();

			}
		}
	}

	private function pullSource() {
		run("mkdir -p " . $this->sourceDir);

//		if (!is_dir($this->sourceDir . "/groupoffice")) {
//			cd($this->sourceDir);
//			run("git clone " . $this->communityRepos);
//		}

		cd(dirname(__DIR__));

		$branch = $this->branch == 'master' ? $this->branch : $this->branch . '.x';
//
		run("git fetch");
		run("git checkout " . $branch);
		run("git pull");

		cd($this->sourceDir);

		if (!is_dir($this->sourceDir . "/promodules")) {
			run("git clone " . $this->proRepos);
		}

		cd($this->sourceDir . "/promodules");

		run("git fetch");
		run("git checkout " . $branch);
		run("git pull");


        cd($this->sourceDir);
        if (!is_dir($this->sourceDir . "/business")) {
            run("git clone git@git.intermesh.nl:groupoffice/business.git");
        }

        cd($this->sourceDir . "/business");

        run("git fetch");
        run("git checkout " . $branch);
        run("git pull");
	}

	private function buildFromSource() {

		run("cp -r " . dirname(__DIR__) . "/www/ " . $this->buildDir . "/" . $this->packageName);

		cd($this->buildDir . "/" . $this->packageName);
		run("composer install --no-dev --optimize-autoloader --ignore-platform-reqs");

		cd("views/Extjs3/themes/Paper/src");
		run("sassc style.scss  ../style.css");
		run("sassc style-mobile.scss ../style-mobile.css");

		if(file_exists("htmleditor.scss")) {
			run("sassc htmleditor.scss ../htmleditor.css");
		}

		if(is_dir("../../Dark")) {
			cd("../../Dark/src");

			run("sassc style.scss  ../style.css");
			run("sassc style-mobile.scss ../style-mobile.css");
			if(file_exists('htmleditor.scss')) {

				run("sassc htmleditor.scss ../htmleditor.css");
			}

		}

		$this->encode();

		cd($this->buildDir);
		run("tar czf " . $this->packageName . ".tar.gz " . $this->packageName);
		echo "Created " . $this->packageName . ".tar.gz\n";
	}

	private function encode() {
		foreach ($this->proModules as $module) {
			run($this->encoder ." ". $this->encoderOptions . ' --replace-target --encode "*.inc" --exclude "Site*Controller.php" ' .
				'--copy "vendor/" --with-license groupoffice-pro-' . $this->branch . '-license.txt ' .
				'--passphrase go'. $this->ioncubePassword  . $this->branch . ' ' . $this->sourceDir . '/promodules/' . $module . ' ' .
				'--into ' . $this->buildDir . "/" . $this->packageName . '/modules');
		}

		$documentsModules = ["workflow", "filesearch"];

		foreach ($documentsModules as $module) {
			run($this->encoder ." ". $this->encoderOptions. ' --replace-target --encode "*.inc" ' .
				'--copy "vendor/" --with-license documents-' . $this->branch . '-license.txt ' .
				'--passphrase do'. $this->ioncubePassword . $this->branch . ' ' . $this->sourceDir . '/promodules/' . $module . ' ' .
				'--into ' . $this->buildDir . "/" . $this->packageName . '/modules');
		}


        $module = 'billing';
        run($this->encoder ." ". $this->encoderOptions. ' --replace-target --encode "*.inc" ' .
            '--copy "vendor/" --with-license billing-' . $this->branch . '-license.txt ' .
            '--passphrase bs'. $this->ioncubePassword . $this->branch . ' ' . $this->sourceDir . '/promodules/' . $module . ' ' .
            '--into ' . $this->buildDir . "/" . $this->packageName . '/modules');


		run($this->encoder." ". $this->encoderOptions . ' --replace-target --license-check script ' . $this->sourceDir . '/promodules/professional ' .
			'--into ' . $this->buildDir . "/" . $this->packageName . '/modules');

		run($this->encoder." ". $this->encoderOptions . ' --replace-target --encode "*.inc" ' . $this->sourceDir . '/promodules/tickets/model ' .
			'--into ' . $this->buildDir . "/" . $this->packageName . '/modules/tickets/');
		run($this->encoder ." ". $this->encoderOptions. ' --replace-target --encode "*.inc" ' . $this->sourceDir . '/promodules/tickets/customfields/model ' .
			'--into ' . $this->buildDir . "/" . $this->packageName . '/modules/tickets/customfields');
		run($this->encoder ." ". $this->encoderOptions. ' --replace-target ' . $this->sourceDir . '/promodules/tickets/TicketsModule.php ' .
			'--into ' . $this->buildDir . "/" . $this->packageName . '/modules/tickets/');



		foreach ($this->proModules as $module) {
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


        run($this->encoder ." ". $this->encoderOptions . ' --replace-target --encode "*.inc" ' .
            '--with-license groupoffice-pro-' . $this->branch . '-license.txt ' .
            '--passphrase go' . $this->ioncubePassword   . $this->branch . ' ' . dirname(__DIR__) . '/www/licensechecks/groupoffice-pro.php ' .
            '--into ' . $this->buildDir . "/" . $this->packageName . '/licensechecks');

        //business package
        run($this->encoder ." ". $this->encoderOptions . ' --replace-target --encode "*.inc" ' .
            '--with-license groupoffice-pro-' . $this->branch . '-license.txt ' .
            '--passphrase go' . $this->ioncubePassword . $this->branch . ' ' . $this->sourceDir . '/business ' .
            '--into ' . $this->buildDir . "/" . $this->packageName . '/go/modules');

        $businessDir = new DirectoryIterator($this->sourceDir . '/business');
        foreach($businessDir as $fileinfo) {
            if($fileinfo->isDot() || !$fileinfo->isDir() || $fileinfo->getBasename() == '.git') {
                continue;
            }

            $moduleName = $fileinfo->getBasename();

            echo "Building business/" . $moduleName . "\n";

            $moduleFile = $this->sourceDir . '/business/' . $moduleName . '/Module.php';

            if(strpos(file_get_contents($moduleFile), "requiredLicense") === false) {
                throw new \Exception($moduleFile ." must contain a 'requiredLicense()' function override.");
            }

            run('cp ' . $moduleFile . ' ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/' . $moduleName . '/');
            run('cp ' . $this->sourceDir . '/business/' . $moduleName . '/language/* ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/' . $moduleName . '/language/');
            run('cp -r ' . $this->sourceDir . '/business/' . $moduleName . '/install/* ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/' . $moduleName . '/install/');
        }

        run('rm -rf ' . $this->buildDir . "/" . $this->packageName . '/go/modules/business/.git*');

	}

	private function sendTarToSF() {

		cd($this->buildDir);


		run("scp " . $this->packageName . ".tar.gz mschering@frs.sourceforge.net:/home/frs/project/group-office/$this->branch/");
	}

	private $githubRelease;


	private function createGithubRelease() {


		$client = new \Github\Client();
		$client->authenticate($this->github['PERSONAL_ACCESS_TOKEN'], null, \Github\Client::AUTH_ACCESS_TOKEN);

		$tagName = 'v' . $this->branch . "." . $this->version;
		$branch = $this->branch . ".x";

		$r = $client->api('repo')->releases();



		if(!isset($this->githubRelease)) {
			$this->githubRelease = $r->create($this->github['USERNAME'], $this->github['REPOSITORY'], array('tag_name' => $tagName, 'target_commitish' => $branch, 'body' => 'Use the php-70 tar.gz file for PHP 7.0 and the php-71 file for all newer versions of PHP. For installation instructions read: https://groupoffice.readthedocs.io/en/latest/install/install.html'));
		}

		$asset = $r->assets()->create(
			$this->github['USERNAME'],
			$this->github['REPOSITORY'],
			$this->githubRelease['id'],
			$this->packageName . '.tar.gz',
			'application/tar+gzip',
			file_get_contents($this->packageName . '.tar.gz')
		);

	}

//	private function tag() {
//		cd($this->sourceDir . "/promodules");
//
//
//		run("git tag -a v" . $this->branch . "." . $this->version . ' -m "Build v' . $this->branch . "." . $this->version . '"');
//		run("git push --tags");
//
//		cd($this->sourceDir . "/groupoffice");
//		run("git tag -a v" . $this->branch . "." . $this->version . ' -m "Build v' . $this->branch . "." . $this->version . '"');
//		run("git push --tags");
//	}

	private function buildDebianPackage() {

		$tpl = '{package} ({version}-'.$this->variant.') ' . $this->distro . '; urgency=medium

  * Changes can be found in /usr/share/groupoffice/changelog.md

 -- Intermesh BV (Developer key) <info@intermesh.nl>  {date}';

//Mon, 26 May 2010 12:30:00 +0200
		$date = date('D, j M Y H:i:s O');

		$debTarget = $this->buildDir . "/debian";
		run("rm -rf " . $debTarget);
		run("mkdir " . $debTarget);
		run("cp -r " . __DIR__ . "/debian/* " . $debTarget);

		run("mkdir " . $debTarget . "/etc/groupoffice");

		file_put_contents($debTarget . '/debian/changelog', str_replace(
			array('{package}', '{version}', '{date}'), array("groupoffice", $this->branch . '.' . $this->version, $date), $tpl
		));

		run("cp -r " . $this->buildDir . "/" . $this->packageName . " " . $debTarget . "/usr/share/groupoffice");
		cd($debTarget);
		run("debuild --no-lintian -b");
	}

	public function addToDebianRepository() {

		if(!is_dir($this->repreproDir)) {
			run("mkdir -p " . $this->repreproDir . "/conf");
			run("cp ".$this->sourceDir . "/debian-groupoffice/reprepro/distributions ".$this->repreproDir . "/conf");
		}

		run("reprepro -b " . $this->repreproDir . " include " . $this->distro . " " . $this->buildDir . "/groupoffice_" . $this->branch . "." . $this->version . "-". $this->variant. "_amd64.changes");
	}

}


$builder = new Builder($config);

if(isset($argv[1])) {
	$builder->branch = $argv[1];
}

$builder->build();
