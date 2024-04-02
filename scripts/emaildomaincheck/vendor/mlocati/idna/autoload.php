<?php

spl_autoload_register(
    function ($class) {
        if (strpos($class, 'MLocati\\IDNA\\') !== 0) {
            return;
        }
        $file = __DIR__.DIRECTORY_SEPARATOR.'src'.str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen('MLocati\\IDNA'))).'.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
);
