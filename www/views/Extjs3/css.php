<?php
require(__DIR__ . "/../../vendor/autoload.php");

\go\core\App::get();
header('Content-Type: text/css');    
//header('Content-Encoding: gzip');
		
$webclient = new \go\core\webclient\Extjs3();
readfile($webclient->getCSSFile()->getPath());


if(GO()->getSettings()->primaryColor) {
?>
:root {
    --c-primary: <?= '#'.GO()->getSettings()->primaryColor; ?>;
    --c-primary-tp: <?= GO()->getSettings()->getPrimaryColorTransparent(); ?>;
}
<?php
  if(GO()->getSettings()->logoId) {
    //blob id is not used by script but added only for caching.
    echo ".go-app-logo, #go-logo {background-image: url(" . GO()->getSettings()->URL . "api/logo.php?blob=" . GO()->getSettings()->logoId . ")}";
  }
}
