<?php

/*
 * Copyright 2012 Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace JMS\I18nRoutingBundle\Tests\Functional;

require_once __DIR__.'/../bootstrap.php';

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

abstract class AbstractKernel extends Kernel
{
    private $config;

    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \JMS\I18nRoutingBundle\Tests\Functional\TestBundle\TestBundle(),
            new \JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
        );
    }

    abstract function registerContainerConfiguration(LoaderInterface $loader);

    public function getProjectDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/JMSI18nRoutingBundle/cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/JMSI18nRoutingBundle/logs';
    }

    public function serialize()
    {
        return serialize(array($this->config));
    }

    public function unserialize($str)
    {
        call_user_func_array(array($this, '__construct'), unserialize($str));
    }

}
