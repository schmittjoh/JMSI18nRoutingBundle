<?php

/*
 * Copyright 2011 Johannes M. Schmitt <schmittjoh@gmail.com>
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

namespace JMS\I18nRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * DI Extension.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class JMSI18nRoutingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config')));
        $loader->load('services.xml');

        $container
            ->getDefinition('jms_i18n_routing.router')
            ->addMethodCall('setDefaultLocale', array($config['default_locale']))
        ;

        $container
            ->getDefinition('jms_i18n_routing.loader')
            ->replaceArgument(1, $config['locales'])
            ->replaceArgument(2, $config['catalogue'])
        ;

        $this->addClassesToCompile(array(
            $container->getDefinition('jms_i18n_routing.router')->getClass(),
        ));

        if ($config['hosts']) {
            $container
                ->getDefinition('jms_i18n_routing.router')
                ->addMethodCall('setHostMap', array($config['hosts']))
            ;

            $container
                ->getDefinition('jms_i18n_routing.locale_changing_listener')
                ->setPublic(true)
                // this must run after the session listener which has a priority of 128 atm
                ->addTag('kernel.event_listener', array('event' => 'kernel.request', 'priority' => 120))
                ->addArgument(array_flip($config['hosts']))
            ;

            $this->addClassesToCompile(array(
                $container->getDefinition('jms_i18n_routing.locale_changing_listener')->getClass(),
            ));
        }
    }

    public function getAlias()
    {
        return 'jms_i18n_routing';
    }
}