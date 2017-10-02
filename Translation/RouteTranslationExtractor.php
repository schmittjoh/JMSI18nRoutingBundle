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

namespace JMS\I18nRoutingBundle\Translation;

use JMS\I18nRoutingBundle\Router\I18nRouter;

use JMS\I18nRoutingBundle\Router\RouteExclusionStrategyInterface;

use Symfony\Component\Routing\RouterInterface;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;

class RouteTranslationExtractor implements ExtractorInterface
{
    private $router;
    private $routeExclusionStrategy;
    private $domain = 'routes';

    public function __construct(RouterInterface $router, RouteExclusionStrategyInterface $routeExclusionStrategy)
    {
        $this->router = $router;
        $this->routeExclusionStrategy = $routeExclusionStrategy;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function extract()
    {
        $catalogue = new MessageCatalogue();

        $collection = $this->router instanceof I18nRouter ? $this->router->getOriginalRouteCollection()
            : $this->router->getRouteCollection();

        foreach ($collection->all() as $name => $route) {
            if ($this->routeExclusionStrategy->shouldExcludeRoute($name, $route)) {
                continue;
            }

            $message = new Message($name, $this->domain);
            $message->setDesc($route->getPath());
            $catalogue->add($message);
        }

        return $catalogue;
    }
}