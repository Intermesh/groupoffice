</div>
</section>
<?php

$theme = \GO::user() ? \GO::user()->theme : \GO::config()->theme;


require 'themes/' . $theme . '/pageFooter.php';