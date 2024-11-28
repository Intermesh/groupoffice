<?php
$client = go\core\webclient\Extjs3::get();
$client->loadExt ??= false;
$client->bodyCls = 'go-page';
$client->loadGoui ??=  false;
require($client->getBasePath().'/views/Layout.php');?>

<header>
    <div class="go-app-logo"></div>
</header>

<section>