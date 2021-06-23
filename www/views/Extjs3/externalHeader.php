<?php
		

$theme = \GO::user() ? \GO::user()->theme : \GO::config()->theme;
		

require 'themes/' . $theme . '/pageHeader.php';
?>
<section>
    <div class="card">
