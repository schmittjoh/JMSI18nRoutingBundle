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

/**
 * Changes the Router implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class SetRouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setAlias('router', 'jms_i18n_routing.router');
        $container->getAlias('router')->setPublic(true);

        $translatorDef = $container->findDefinition('translator');
        if ('%translator.identity.class%' === $translatorDef->getClass()) {
            throw new \RuntimeException('The JMSI18nRoutingBundle requires Symfony2\'s translator to be enabled. Please make sure to un-comment the respective section in the framework config.');
        }
    }
}
