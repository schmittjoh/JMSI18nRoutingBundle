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

namespace JMS\I18nRoutingBundle;

use JMS\I18nRoutingBundle\DependencyInjection\JMSI18nRoutingExtension;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use JMS\I18nRoutingBundle\DependencyInjection\Compiler\SetRouterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * JMSI18nRoutingBundle.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class JMSI18nRoutingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SetRouterPass());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new JMSI18nRoutingExtension();
    }
}
