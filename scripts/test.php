<?php

$start = new DateTime("2023-03-20 10:15");
$end = new DateTime("2023-05-01 09:12");

$diff = $start->diff($end);

var_dump($diff);