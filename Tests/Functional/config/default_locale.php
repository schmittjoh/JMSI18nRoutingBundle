<?php

use Symfony\Component\HttpKernel\Kernel;

if (version_compare(Kernel::VERSION, '2.1.0-DEV', '<')) {
    $config = array('session' => array('default_locale' => 'en'));
} else {
    $config = array('default_locale' => 'en');
}

$container->loadFromExtension('framework', $config);