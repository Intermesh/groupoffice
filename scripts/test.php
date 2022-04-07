<?php
chdir(__DIR__);
require ('../www/GO.php');

GO::session()->runAsRoot();


$file = \GO\Files\Model\File::model()->findByPk(204921);
$file->cacheSearchRecord();

