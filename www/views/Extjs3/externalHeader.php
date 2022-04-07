<?php
		

$theme = \GO::user() ? \GO::user()->theme : \GO::config()->theme;

$file = __DIR__ . '/themes/' . $theme . '/pageHeader.php';

if(!file_exists($file)) {
	$file = __DIR__ . '/themes/Paper/pageHeader.php';
}

require $file;
?>
<section>
    <div class="card">
