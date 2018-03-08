<?php
$folder = \GO\Files\Model\Folder::model()->findByPath("log", true);
$folder->setNewAcl();
$folder->readonly=0;
$folder->save();
