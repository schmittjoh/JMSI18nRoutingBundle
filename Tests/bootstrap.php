<?php

spl_autoload_register(function($class) {
    if (0 !== strpos($class, 'JMS\I18nRoutingBundle')) {
        return;
    }
    
    if (is_file($path = dirname(__DIR__).str_replace('\\', '/', substr($class, strlen('JMS\I18nRoutingBundle'))).'.php')) {
        require_once $path;
    }
});

if (isset($_SERVER['autoload_file'])) {
    require_once $_SERVER['autoload_file'];
    
    return;
}

// this file searches for the autoload file of your project, and includes it
$dir = __DIR__;
$lastDir = null;
while (($dir = dirname($dir)) && $dir !== $lastDir) {
    $lastDir = $dir;

    if (file_exists($file = $dir.'/app/autoload.php')) {
        require_once $file;
        return;
    }
}

throw new RuntimeException('Could not locate the project\'s autoload.php. If your bundle is not inside a project, you need to replace this bootstrap file.');