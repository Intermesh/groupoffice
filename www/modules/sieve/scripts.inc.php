<?php

if(!isset(\GO::config()->sieve_port))
	\GO::config()->sieve_port=4190;

if(!isset(\GO::config()->sieve_tls))
	\GO::config()->sieve_tls=true;

$GO_SCRIPTS_JS .= '
GO.sieve.sievePort = "'.\GO::config()->sieve_port.'";
GO.sieve.sieveTls = '.(\GO::config()->sieve_tls ? 'true' : 'false').';

';
