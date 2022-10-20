<?php
namespace go\core\util;

use go\core\App;
use go\core\fs\File;

class BackgroundProcess {
	/**
	 * @var string
	 */
	private $cmd;
	/**
	 * @var File
	 */
	private $pidFile;
	/**
	 * @var File
	 */
	private $outputFile;
	/**
	 * @var array
	 */
	private $params;

	/**
	 *
	 * @param string $cmd
	 * @param array $params
	 */
	public function __construct(string $cmd, array $params = [])
	{
		$this->cmd = $cmd;
		$this->params = $params;
		$this->pidFile = File::tempFile("txt");
		$this->outputFile = File::tempFile("txt");
	}

	public function getOutput() {
		return $this->outputFile->getContents();
	}

	public function run(): int
	{

		$cmd = go()->getEnvironment()->getInstallFolder()->getFile("cli.php") ." "
			. escapeshellarg($this->cmd);

		foreach($this->params as $key=>$value) {
			$cmd .= ' --'.$key.'='.escapeshellarg($value);
		}

		$cmd .= " --userId=" . go()->getUserId();

		$cmd .= " -c=" . escapeshellarg(App::findConfigFile());

		go()->debug("BACKGROUND CMD: ". $cmd);

		exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $this->outputFile, $this->pidFile));

		$pid = (int) $this->pidFile->getContents();

		go()->debug("PID: " .$pid);

		$this->pidFile->delete();

		return $pid;
	}

	public function isRunning(int $pid) : bool{
		try{
			$result = shell_exec(sprintf("ps %d", $pid));
			if( count(preg_split("/\n/", $result)) > 2){
				return true;
			}
		}catch(Exception $e){}

		return false;
	}
}