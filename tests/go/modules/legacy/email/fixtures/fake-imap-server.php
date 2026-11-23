<?php
/**
 * A minimal IMAP server for tests. It answers every command with OK and writes the commands it receives to a log file.
 *
 * Usage: php fake-imap-server.php <logfile>
 * Prints the port it listens on and stops after 30 seconds or when stdin is closed.
 */
$log = fopen($argv[1], 'a');
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
if (!$server) {
	fwrite(STDERR, $errstr);
	exit(1);
}
$name = stream_socket_get_name($server, false);
echo substr($name, strrpos($name, ':') + 1) . "\n";
fflush(STDOUT);
stream_set_blocking(STDIN, false);

$clients = [];
$end = time() + 30;
while (time() < $end) {
	$read = array_merge([$server, STDIN], $clients);
	$write = $except = null;
	if (!stream_select($read, $write, $except, 1)) {
		continue;
	}
	foreach ($read as $stream) {
		if ($stream === STDIN) {
			if (feof(STDIN)) {
				exit(0);
			}
			fgets(STDIN);
		} else if ($stream === $server) {
			$client = stream_socket_accept($server);
			fwrite($client, "* OK fake IMAP ready\r\n");
			$clients[] = $client;
		} else {
			$line = fgets($stream);
			if ($line === false) {
				fclose($stream);
				$clients = array_values(array_filter($clients, fn($c) => $c !== $stream));
				continue;
			}
			fwrite($log, $line);
			fflush($log);
			$parts = explode(' ', trim($line));
			$tag = $parts[0];
			$cmd = strtoupper($parts[1] ?? '');
			if ($cmd === 'UID') {
				$cmd .= ' ' . strtoupper($parts[2] ?? '');
			}
			switch ($cmd) {
				case 'CAPABILITY':
					fwrite($stream, "* CAPABILITY IMAP4rev1 SORT\r\n$tag OK done\r\n");
					break;
				case 'SELECT':
				case 'EXAMINE':
					fwrite($stream, "* 0 EXISTS\r\n* 0 RECENT\r\n* OK [UIDVALIDITY 1] ok\r\n* OK [UIDNEXT 1] ok\r\n$tag OK [READ-WRITE] done\r\n");
					break;
				case 'LOGOUT':
					fwrite($stream, "* BYE\r\n$tag OK done\r\n");
					break;
				default:
					fwrite($stream, "$tag OK done\r\n");
			}
		}
	}
}
