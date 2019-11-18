<?php

$pdo = new \PDO("mysql:host=db;dbname=groupoffice-master", "root", "groupoffice");
$pdo->exec("SET NAMES utf8");
$stmt = $pdo->query("SELECT id,username,displayName FROM core_user LIMIT 0,30");

$users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

echo json_encode($users);