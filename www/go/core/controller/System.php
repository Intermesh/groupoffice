<?php

namespace go\core\controller;

use Exception;
use GO;
use go\core\cli\controller\System as CliSystemCtrl;
use go\core\Controller;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\fs\Blob;
use go\core\http\Client;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\Response;
use go\core\model;
use go\core\util\Changelog;
use go\core\util\DateTime;
use go\core\util\JSON;

class System extends Controller {
	/**
	 * Create demo data
	 *
	 * @apiPermission Only administrators.
	 */
	public function demo() {

		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}

		go()->getEnvironment()->setMaxExecutionTime(240);

		ob_start();
		$cliCtrl = new CliSystemCtrl();
		$cliCtrl->demo();
		ob_end_clean();

		return ['success' => true];
	}

	/**
	 * Blob method for CLI programs
	 *
	 * It aborts the regular JMAP output and outputs the file instead!
	 *
	 * @apiPermission Only administrators.
	 * @param $params
	 * @return void
	 * @throws Forbidden
	 */
	public function blob($params) {
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}

		$blob = Blob::findById($params['id']);

		$blob->getFile()->output();
		exit();
	}

	/**
	 * The changes of the releases after a version, for the "What's new" window that administrators get after an upgrade
	 *
	 * @apiPermission Only administrators.
	 * @apiReadOnly
	 * @param array{since?: string} $params "since" is the version the administrator saw last
	 * @return array{version: string, releases: array{version: string, date: string, changes: string[]}[]}
	 * @throws Forbidden
	 * @throws InvalidArguments
	 */
	public function changelog(array $params = []): array
	{
		if(!go()->getAuthState()->isAdmin()) {
			throw new Forbidden();
		}

		if(!isset($params['since']) || !is_string($params['since']) || !preg_match('/^\d+(\.\d+)+$/', $params['since'])) {
			throw new InvalidArguments("Parameter 'since' must be a version like 26.0.1");
		}

		$version = go()->getVersion();
		$changelog = Changelog::load();

		return [
			'version' => $version,
			'releases' => $changelog ? $changelog->between($params['since'], $version) : []
		];
	}

	/**
	 * Information for the About window
	 *
	 * Returns the version and, when the "checkForUpdates" config option is on and a newer release exists, the latest
	 * version. Administrators also get the number of users and the disk usage of this instance.
	 *
	 * @apiPermission Module access: every user may see the version. The usage is only returned to administrators.
	 * @apiReadOnly
	 * @return array{version: string, latestVersion: ?string, usage?: array}
	 */
	public function about(): array
	{
		$version = go()->getVersion();
		$latestVersion = go()->getConfig()['checkForUpdates'] ? $this->getLatestVersion() : null;

		$response = [
			'version' => $version,
			'latestVersion' => $latestVersion && version_compare($version, $latestVersion) < 0 ? $latestVersion : null
		];

		if(!go()->getAuthState()->isAdmin()) {
			return $response;
		}

		$settings = go()->getSettings();
		$diskUsageJob = model\CronJobSchedule::findByName("CalculateDiskUsage");
		$quota = (int) go()->getConfig()['quota'];

		$response['usage'] = [
			'users' => (int) go()->getDbConnection()->selectSingleValue('count(*)')->from('core_user')->where('enabled', '=', 1)->single(),
			'fileStorage' => $settings->fileStorageUsage,
			'database' => $settings->databaseUsage,
			'mailbox' => $settings->mailboxUsage,
			// The quota in the config is in kilobytes
			'quota' => $quota ? $quota * 1024 : null,
			'calculatedAt' => $diskUsageJob?->lastRunAt?->format(DateTime::FORMAT_API)
		];

		return $response;
	}

	/**
	 * The version of the newest GroupOffice release on GitHub, cached for a day
	 *
	 * @return string|null Null when it couldn't be checked
	 */
	private function getLatestVersion(): ?string
	{
		$cached = go()->getCache()->get("latestVersion");
		if($cached !== null) {
			return $cached ?: null;
		}

		$latestVersion = null;
		try {
			$client = new Client();
			$client->setOption(CURLOPT_TIMEOUT, 5);
			$client->setHeader("Accept", "application/vnd.github+json");
			$client->setHeader("X-GitHub-Api-Version", "2022-11-28");
			$response = $client->get("https://api.github.com/repos/intermesh/groupoffice/releases");

			if($response['status'] == 200) {
				foreach (JSON::decode($response['body']) as $release) {
					if($release->prerelease) {
						continue;
					}
					$version = ltrim($release->tag_name, 'v');
					if (!isset($latestVersion) || version_compare($latestVersion, $version) < 0) {
						$latestVersion = $version;
					}
				}
			}
		} catch(\Throwable $e) {
			ErrorHandler::logException($e);
		}

		// Also cache a failed check, so an installation without internet access doesn't try on every call
		go()->getCache()->set("latestVersion", $latestVersion ?? "", true, 86400);

		return $latestVersion;
	}

	/**
	 * Write an error of the client to the server error log
	 *
	 * @apiPermission Module access: only writes the given message to the server error log. Nothing is returned or changed.
	 */
	public function logClientError($params) {
		if(!isset($params['message'])) {
			throw new InvalidArguments("Required parameter 'message' is missing");
		}
		ErrorHandler::log("CLIENT: " . $params['message']);

		return ['success'=>true];
	}
}