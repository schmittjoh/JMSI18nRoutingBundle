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

namespace JMS\I18nRoutingBundle\Router;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use JMS\I18nRoutingBundle\Util\RouteExtractor;
use Symfony\Component\Config\Loader\LoaderResolver;

/**
 * This loader expands all routes which are eligible for i18n.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class I18nLoader
{
    const ROUTING_PREFIX = '__RG__';

    private $translator;
    private $translationDomain = 'routes';
    private $locales;
    private $cacheDir;
    private $defaultLocale;
    private $strategy;
    private $routeExclusionStrategy;

    public function __construct(TranslatorInterface $translator, RouteExclusionStrategyInterface $routeExclusionStrategy, array $locales, $defaultLocale, $strategy, $cacheDir)
    {
        $this->routeExclusionStrategy = $routeExclusionStrategy;
        $this->translator = $translator;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
        $this->strategy = $strategy;
        $this->cacheDir = $cacheDir;
    }

    public function setTranslationDomain($domain)
    {
        $this->translationDomain = $domain;
    }

    public function load(RouteCollection $collection)
    {
        $i18nCollection = new RouteCollection();
        $this->addResources($i18nCollection, $collection);

        foreach ($collection->all() as $name => $route) {
            if ($this->routeExclusionStrategy->shouldExcludeRoute($name, $route)) {
                $i18nCollection->add($name, $route);
                continue;
            }

            foreach ($this->getI18nPatterns($name, $route) as $pattern => $locales) {
                // If this pattern is used for more than one locale, we need to keep the original route.
                // We still add individual routes for each locale afterwards for faster generation.
                if (count($locales) > 1) {
                    $catchMultipleRoute = clone $route;
                    $catchMultipleRoute->setPattern($pattern);
                    $catchMultipleRoute->setDefault('_locales', $locales);
                    $i18nCollection->add(implode('_', $locales).I18nLoader::ROUTING_PREFIX.$name, $catchMultipleRoute);
                }

                foreach ($locales as $locale) {
                    $localeRoute = clone $route;
                    $localeRoute->setPattern($pattern);
                    $localeRoute->setDefault('_locale', $locale);
                    $i18nCollection->add($locale.I18nLoader::ROUTING_PREFIX.$name, $localeRoute);
                }
            }
        }

        return $i18nCollection;
    }

    private function getI18nPatterns($routeName, Route $route)
    {
        $patterns = array();
        foreach ($route->getOption('i18n_locales') ?: $this->locales as $locale) {
            // if no translation exists, we use the current pattern
            if ($routeName === $i18nPattern = $this->translator->trans($routeName, array(), $this->translationDomain, $locale)) {
                $i18nPattern = $route->getPattern();
            }

            // prefix with locale if requested
            if (I18nRouter::STRATEGY_PREFIX === $this->strategy
                    || (I18nRouter::STRATEGY_PREFIX_EXCEPT_DEFAULT === $this->strategy && $this->defaultLocale !== $locale)) {
                $i18nPattern = '/'.$locale.$i18nPattern;
            }

            $patterns[$i18nPattern][] = $locale;
        }

        return $patterns;
    }

    private function addResources(RouteCollection $i18nCollection, RouteCollection $collection)
    {
        // add translation resources
        foreach ($this->locales as $locale) {
            if (file_exists($metadata = $this->cacheDir.'/translations/catalogue.'.$locale.'.php.meta')) {
                foreach (unserialize(file_get_contents($metadata)) as $resource) {
                    $i18nCollection->addResource($resource);
                }
            }
        }

        // add route resources
        foreach ($collection->getResources() as $resource) {
            $i18nCollection->addResource($resource);
        }
    }
}
