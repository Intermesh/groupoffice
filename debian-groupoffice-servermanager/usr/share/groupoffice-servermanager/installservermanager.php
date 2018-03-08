#!/usr/bin/php
<?php
define('GO_CONFIG_FILE', '/etc/groupoffice/config.php');
require(GO_CONFIG_FILE);
require($config['root_path'].'GO.php');

\GO::setIgnoreAclPermissions();


try{
        if(!\GO::modules()->isInstalled('servermanager')){
                $module = new \GO\Base\Model\Module();
								$module->id = 'servermanager';
								$module->save();
        }
}
catch(Exception $e){
        echo 'ERROR: '.$e->getMessage();
}

