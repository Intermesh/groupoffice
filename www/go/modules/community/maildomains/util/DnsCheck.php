<?php

namespace go\modules\community\maildomains\util;

use go\core\util\ArrayObject;
use go\modules\community\maildomains\model\Domain;
use SPFLib\{Checker,Decoder};
use SPFLib\Check\Environment;
use SPFLib\Check\Result;


final class DnsCheck
{
	private Domain $domainEntity;
	
	private string $domainName;
	private string $ipAddress;

	private bool $rawOutput;

	/**
	 * @param Domain $domain
	 * @param string $ipAddress
	 * @param bool|null $rawOutput
	 */
	public function __construct(Domain $domain, string $ipAddress, ?bool $rawOutput=false)
	{
		$this->domainEntity = $domain;
		$this->domainName = $domain->domain;
		$this->ipAddress = $ipAddress;
		$this->rawOutput = $rawOutput;
	}

	/**
	 * @param string $dnsTXT
	 * @return array
	 */
	private function parseRecord(string $dnsTXT) : array
	{
		$record = [];
		$parts = explode(';', $dnsTXT);
		$parts = array_map('trim', $parts);
		foreach($parts as $p) {
			$keyValue = explode('=', $p);
			if(count($keyValue) > 1) {
				$key = array_shift($keyValue);
				$record[$key] = implode('=', $keyValue);
			}
		}
		return $record;
	}

	public function getSpf()
	{
		require_once __DIR__ . '/../vendor/autoload.php';
		$decoder = new Decoder();
		try {
			$record = $decoder->getRecordFromDomain($this->domainName);
		} catch(\SPFLib\Exception $e) {
			return null;
		}
		$s = (string) $record;
		return $s;
	}
	/**
	 * Check the SPF record.
	 *
	 * If there's none, null is returned. Otherwise a boolean value will indicate whether a record was valid
	 *
	 * @return bool|null
	 * @throws \SPFLib\Exception\InvalidIPAddressException
	 */
	public function checkSPF(): ?bool
	{
		require_once __DIR__ . '/../vendor/autoload.php';

		$environment = new Environment($this->ipAddress, $this->domainName);

		$checker = new Checker();
		$checkResult = $checker->check($environment);

		if($checkResult->getCode() == Result::CODE_NONE) {
			return null;
		}

		return $checkResult->getCode() == "pass";
	}

	/**
	 * Check for the existence of a DMARC record
	 *
	 * If there's none, null is returned. Otherwise, it is being parsed and returned as as array
	 *
	 * @return array|null|string
	 */
	public function checkDMARC(): null|array|string
	{
		$records = dns_get_record("_dmarc." . $this->domainName, DNS_TXT);
		if(empty($records) || empty($records[0]['txt'])) {
			return null;
		}
		return $this->rawOutput ? $records[0]['txt'] : $this->parseRecord($records[0]['txt']);
	}

	/**
	 * Check for the existence of a DKIM record
	 *
	 * If there's none, null is returned. Otherwise, it is being parsed and returned as as array
	 *
	 * @param string[] $selectors
	 * @return array|null|string
	 */
	public function checkDKIM(): null|array|string
	{
		$r = [];
		if (!isset($this->domainEntity->dkim)) {
			return $r;
		}
		foreach(array_keys($this->domainEntity->dkim) as $selector) {
			$records = dns_get_record($selector . "._domainkey." . $this->domainName, DNS_TXT);

			if (empty($records) || empty($records[0]['txt'])) {
				$r[$selector] =  null;
			} else {
				$r[$selector] = $this->rawOutput ? $records[0]['txt'] : $this->parseRecord($records[0]['txt']);
			}
		}

		 return $r;
	}

	/**
	 * Return an array of known MX records
	 *
	 * @return array
	 */
	public function getMX() : array
	{
		$records = dns_get_record( $this->domainName, DNS_MX);

		$mxs = [];
		foreach($records as $record) {
			$mxs[] = $record['target'];
		}

		return $mxs;
	}


	/**
	 * Check whether the MX records match the given IP address
	 *
	 * @param array $targets
	 * @param string $mxIP
	 * @return bool
	 */
	public function checkMX(array $targets, string $mxIP): bool
	{
		foreach($targets as $t) {
			$ip = gethostbyname($t);
			if($ip == $mxIP) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Return an array of all the checks!
	 *
	 * @return ArrayObject
	 */
	public function checkAll() : ArrayObject
	{
		$record =  [
			'mailDomain' => $this->domainName,
			'mxIP' => $this->ipAddress,
			'spf' => $this->getSpf(),
			'spfStatus' => $this->checkSPF(),
			'dmarc' => $this->checkDMARC(),
			'dkim' => $this->checkDKIM(),
			'mxTargets' => $this->getMX(),
		];

		$record['mx'] = $this->checkMX($record['mxTargets'], $this->ipAddress);

		$record['allPassed'] = $record['spfStatus'] && $record['dmarc'] && $record['dkim'] && $record['mx'];

		return new ArrayObject($record);
	}

}