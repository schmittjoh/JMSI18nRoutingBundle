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

namespace JMS\I18nRoutingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Make sure the Twig extension could get a request object.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class SetRequestPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $twigExtDev = $container->getDefinition('jms_i18n_routing.twig_extension');
        // If SF=>2.4
        if (null !== $container->hasDefinition('request_stack')) {
            $twigExtDev->addMethodCall('setRequestStack', array(new Reference('request_stack')));
        } else {
            $twigExtDev->addMethodCall('setContainer', array(new Reference('service_container')));
        }
    }
}
