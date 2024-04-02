<?php

require("vendor/autoload.php");

$mailDomains = isset($argv[1]) ? array_map("trim", explode(",", $argv[1])) : ["intermesh.nl"];
$mxIP = $argv[2] ?? '149.210.243.200';


class MailDomainChecker {

	private string $mailDomain;
	public function __construct(string $mailDomain)
	{

		$this->mailDomain = $mailDomain;
	}

	public function checkSPF(string $mxIP): ?bool
	{
		$environment = new \SPFLib\Check\Environment($mxIP, $this->mailDomain);

		$checker = new \SPFLib\Checker();
		$checkResult = $checker->check($environment);

		if($checkResult->getCode() == \SPFLib\Check\Result::CODE_NONE) {
			return null;
		}

		return $checkResult->getCode() == "pass";
	}

	private function parseRecord(string $dnsTXT) : array {
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

	public function checkDMARC(): ?array
	{
		$records = dns_get_record("_dmarc." . $this->mailDomain, DNS_TXT);
		if(empty($records) || empty($records[0]['txt'])) {
			return null;
		}
		return $this->parseRecord($records[0]['txt']);
	}

	public function checkDKIM(string $selector = "mail._domainkey"): ?array
	{
		$records = dns_get_record($selector."." . $this->mailDomain, DNS_TXT);

		if(empty($records) || empty($records[0]['txt'])) {
			return null;
		}
		return $this->parseRecord($records[0]['txt']);
	}

	public function getMX() : array
	{
		$records = dns_get_record( $this->mailDomain, DNS_MX);

		$mxs = [];
		foreach($records as $record) {
			$mxs[] = $record['target'];
		}

		return $mxs;
	}


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



	public function checkAll(string $mxIP) : array {
		$record =  [
			'mailDomain' => $this->mailDomain,
			'mxIP' => $mxIP,
			'spf' => $this->checkSPF($mxIP),
			'dmarc' => $this->checkDMARC(),
			'dkim' => $this->checkDKIM(),
			'mxTargets' => $this->getMX(),
		];

		$record['mx'] = $this->checkMX($record['mxTargets'], $mxIP);

		$record['allPassed'] = $record['spf'] && $record['dmarc'] && $record['dkim'] && $record['mx'];

		return $record;
	}
}

$records = [];
foreach($mailDomains as $mailDomain) {
	$checker = new MailDomainChecker($mailDomain);

	$records[] = $checker->checkAll($mxIP);
}

echo json_encode($records, JSON_PRETTY_PRINT);
echo "\n";



