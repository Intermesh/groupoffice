</div>
</section>
<?php

$theme = \GO::user() ? \GO::user()->theme : \GO::config()->theme;

$file = __DIR__ . '/themes/' . $theme . '/pageFooter.php';

if(!file_exists($file)) {
	$file = __DIR__ . '/themes/Paper/pageFooter.php';
}


require $file;