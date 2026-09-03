<?php

namespace go\modules\community\webpush\model;

class WebPush {

	const TTL = 2419200;
	const BatchSize = 1000;
	const Concurrency = 100;
	const ContentType = 'application/octet-stream';

	private $queue = [];
	private string $subject;
	private string $publicKey;
	private string $privateKey;

	public function __construct() {
		$settings = Settings::get();
		$this->subject = 'mailto:' . go()->getSettings()->systemEmail;
		$this->publicKey = $settings->vapidPublicKey;
		$this->privateKey = $settings->vapidPrivateKey;
	}

	public function queue(PushSubscription $subscription, string $payload, int $ttl = self::TTL): self
	{
		$this->queue[] = ['subscription' => $subscription, 'payload' => $payload, 'ttl' => $ttl];
		return $this;
	}

	public function flush(): array
	{
		if (empty($this->queue)) {
			return [];
		}

		$results = [];
		foreach (array_chunk($this->queue, self::BatchSize) as $batch) {
			$results[] = $this->sendBatch($batch);
		}

		$this->queue = [];
		return array_merge(...$results);
	}

	private function sendBatch(array $batch): array
	{
		$handles = [];
		foreach ($batch as $item) {
			$handles[] = $this->buildHandle($item['subscription'], $item['payload'], $item['ttl']);
		}

		$results = [];
		$mh = curl_multi_init();
		$active = [];
		$pending = $handles;

		// Seed initial concurrent handles
		$window = array_splice($pending, 0, self::Concurrency);
		foreach ($window as $ch) {
			curl_multi_add_handle($mh, $ch);
			$active[(int)$ch] = $ch;
		}

		do {
			curl_multi_exec($mh, $running);
			curl_multi_select($mh);

			while ($done = curl_multi_info_read($mh)) {
				$ch = $done['handle'];
				$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$endpoint = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
				$results[] = [
					'success' => $status >= 200 && $status < 300,
					'status' => $status,
					'endpoint' => $endpoint,
				];
				go()->debug($results);
				curl_multi_remove_handle($mh, $ch);
				curl_close($ch);
				unset($active[(int)$ch]);

				// Slide in next pending handle
				if (!empty($pending)) {
					$next = array_shift($pending);
					curl_multi_add_handle($mh, $next);
					$active[(int)$next] = $next;
				}
			}
		} while (!empty($active));

		curl_multi_close($mh);
		return $results;
	}


	private function buildHandle(PushSubscription $subscription, string $payload, int $ttl): \CurlHandle
	{
		$encrypted = Encryption::encrypt($payload, $subscription->publicKey(), $subscription->authToken());
		$audience = parse_url($subscription->url, PHP_URL_SCHEME) . '://' . parse_url($subscription->url, PHP_URL_HOST);

		$headers = array_merge($encrypted['headers'], [
			'Authorization: vapid t='.JWT::create($audience, $this->subject, base64_decode($this->privateKey)).', k='.$this->publicKey,
			'TTL: '.$ttl,
		]);
		go()->debug($headers);

		$ch = curl_init($subscription->url);
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $encrypted['body'],
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
		]);

		return $ch;
	}
}