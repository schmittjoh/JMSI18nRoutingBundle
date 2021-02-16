<?php

declare(strict_types=1);


namespace JMS\I18nRoutingBundle\Tests\Functional;


use JMS\I18nRoutingBundle\Tests\Functional\AbstractKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends AbstractKernel
{
    function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/default.yml');
    }
}
