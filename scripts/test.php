<?php
require ('../www/GO.php');
$folder = 'Folder name with "quotes"';
$test1 = mb_convert_encoding($folder, "UTF7-IMAP", "UTF-8");
var_dump($test1);

$imap = new \GO\Base\Mail\Imap();
$test2 = $imap->utf7_encode($folder);
var_dump($test2);