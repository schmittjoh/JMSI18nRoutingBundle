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

namespace JMS\I18nRoutingBundle\Router;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use JMS\I18nRoutingBundle\Util\RouteExtractor;
use Symfony\Component\Config\Loader\LoaderResolver;

class I18nLoader
{
    private $translator;
    private $locales;
    private $catalogue;
    private $cacheDir;
    private $defaultLocale;
    private $strategy;

    public function __construct(TranslatorInterface $translator, array $locales, $defaultLocale, $catalogue, $strategy, $cacheDir)
    {
        $this->translator = $translator;
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
        $this->catalogue = $catalogue;
        $this->strategy = $strategy;
        $this->cacheDir = $cacheDir;
    }

    public function load(RouteCollection $collection)
    {
        $i18nCollection = new RouteCollection();

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

        foreach ($collection->all() as $name => $route) {
            if ($this->isNotTranslatable($name, $route)) {
                $i18nCollection->add($name, $route);
                continue;
            }

            $keepOriginal = false;
            $translations = new RouteCollection();
            $patterns = array();
            foreach ($route->getOption('i18n_locales') ?: $this->locales as $locale) {
                $i18nRoute = clone $route;

                // if no translation exists, we use the current pattern
                if ($name === $i18nPattern = $this->translator->trans($name, array(), $this->catalogue, $locale)) {
                    $i18nPattern = $route->getPattern();
                }

                // prefix with locale if requested
                if (I18nRouter::STRATEGY_PREFIX === $this->strategy
                    || (I18nRouter::STRATEGY_PREFIX_EXCEPT_DEFAULT === $this->strategy && $this->defaultLocale !== $locale)) {
                    $i18nPattern = '/'.$locale.$i18nPattern;
                }

                if (isset($patterns[$i18nPattern])) {
                    $keepOriginal = true;
                }
                $patterns[$i18nPattern] = true;

                $i18nRoute->setPattern($i18nPattern);
                $i18nRoute->setDefault('_locale', $locale);
                $translations->add($locale.'_'.$name, $i18nRoute);
            }

            if ($keepOriginal || $route->getOption('i18n_keep')) {
                $i18nCollection->add($name, $route);
            }

            $i18nCollection->addCollection($translations);
        }

        return $i18nCollection;
    }

    public function extract(RouteCollection $collection)
    {
        $nonI18nRoutes = array();
        foreach ($collection->all() as $k => $v) {
            if (0 === strpos($k, $this->defaultLocale.'_') && null === $collection->get(substr($k, 3))) {
                if (I18nRouter::STRATEGY_PREFIX === $this->strategy) {
                    $v = clone $v;
                    $v->setPattern(substr($v->getPattern(), 3));
                }

                $nonI18nRoutes[substr($k, 3)] = $v;
                continue;
            }

            if ($this->isNotTranslatable($k, $v)) {
                continue;
            }

            $nonI18nRoutes[$k] = $v;
        }

        return $nonI18nRoutes;
    }

    private function isNotTranslatable($name, Route $route)
    {
        return false === $route->getOption('i18n')
               || preg_match('/^(?:_|[a-z]{2}_)/', $name) > 0
        ;
    }
}