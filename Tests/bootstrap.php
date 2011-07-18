<?php

// this file searches for the autoload file of your project, and includes it
$dir = __DIR__;
$lastDir = null;
while (($dir = dirname($dir)) && $dir !== $lastDir) {
	$lastDir = $dir;

	if (file_exists($file = $dir.'/app/bootstrap.php.cache')) {
		require_once $file;
		return;
	}
}

throw new RuntimeException('Could not locate the project\'s bootstrap.php.cache. If your bundle is not inside a project, you need to replace this bootstrap file.');