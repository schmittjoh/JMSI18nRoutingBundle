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

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * I18n Router implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class I18nRouter extends Router
{
    const STRATEGY_PREFIX = 'prefix';
    const STRATEGY_PREFIX_EXCEPT_DEFAULT = 'prefix_except_default';
    const STRATEGY_CUSTOM = 'custom';

    private $hostMap = array();
    private $i18nLoaderId;
    private $container;
    private $defaultLocale;

    public function __construct(ContainerInterface $container, $resource, array $options = array(), RequestContext $context = null, array $defaults = array())
    {
        parent::__construct($container, $resource, $options, $context, $defaults);

        $this->container = $container;
    }

    /**
     * Sets the host map to use.
     *
     * @param array $hostMap a map of locales to hosts
     */
    public function setHostMap(array $hostMap)
    {
        $this->hostMap = $hostMap;
    }

    public function setI18nLoaderId($id)
    {
        $this->i18nLoaderId = $id;
    }

    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param  string  $name       The name of the route
     * @param  array   $parameters An array of parameters
     * @param  Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        // determine the most suitable locale to use for route generation
        $currentLocale = $this->context->getParameter('_locale');
        if (isset($parameters['_locale'])) {
            $locale = $parameters['_locale'];
        } else if ($currentLocale) {
            $locale = $currentLocale;
        } else {
            $locale = $this->defaultLocale;
        }

        // if the locale is changed, and we have a host map, then we need to
        // generate an absolute URL
        if ($currentLocale && $currentLocale !== $locale && $this->hostMap) {
            $absolute = true;
        }

        $generator = $this->getGenerator();

        // skip internal routes
        if ('_' !== $name[0]) {
            // if an absolute URL is requested, we set the correct host
            if ($absolute && $this->hostMap) {
                $currentHost = $this->context->getHost();
                $this->context->setHost($this->hostMap[$locale]);
            }

            try {
                $url = $generator->generate($locale.'_'.$name, $parameters, $absolute);

                if ($absolute && $this->hostMap) {
                    $this->context->setHost($currentHost);
                }

                return $url;
            } catch (RouteNotFoundException $ex) {
                if ($absolute && $this->hostMap) {
                    $this->context->setHost($currentHost);
                }

                // fallback to default behavior
            }
        }

        // use the default behavior if no localized route exists
        return $generator->generate($name, $parameters, $absolute);
    }

    /**
     * Tries to match a URL with a set of routes.
     *
     * Returns false if no route matches the URL.
     *
     * @param  string $url URL to be parsed
     *
     * @return array|false An array of parameters or false if no route matches
     */
    public function match($url)
    {
        $params = $this->getMatcher()->match($url);

        // check if a host change or RouteNotFoundException is required
        if (false !== $params && isset($params['_locale'])
            && isset($this->hostMap[$params['_locale']])
            && $this->context->getHost() !== $host = $this->hostMap[$params['_locale']]) {

            $matchedLocale = $params['_locale'];
            // Check whether the matched route corresponds to the active locale
            // by generating the route matching $params, but for the active locale
            $generated = null;
            try {
                $generated = $this->convertParamsAndGenerate($params);
                $params['_locale'] = $this->context->getParameter('_locale');
            }
            catch (RouteNotFoundException $ex) {
            }

            $params['_route'] = substr($params['_route'], strlen($matchedLocale) + 1);

            // If the generated url failed, or the new url is different: corresponding locales do not share the same route
            if (null === $generated || $generated !== $url) {
                if (true === $this->container->getParameter('jms_i18n_routing.redirect_to_host')) {
                    return array(
                        '_controller' => 'JMS\I18nRoutingBundle\Controller\RedirectController::redirectAction',
                        'path'        => $url,
                        'host'        => $host,
                        'permanent'   => true,
                        'scheme'      => $this->context->getScheme(),
                        'httpPort'    => $this->context->getHttpPort(),
                        'httpsPort'   => $this->context->getHttpsPort(),
                        '_route'      => $params['_route'],
                    );
                } else {
                    throw new ResourceNotFoundException(
                        sprintf('Resource corresponding pattern %s not found for locale \'%s \'', $url, $this->getContext()->getParameter('_locale'))
                    );
                }
            }
        }

        if (isset($params['_locale']) && 0 === strpos($params['_route'], $params['_locale'].'_')) {
            $params['_route'] = substr($params['_route'], strlen($params['_locale']) + 1);
        }

        return $params;
    }

    public function getRouteCollection()
    {
        $collection = parent::getRouteCollection();

        return $this->container->get($this->i18nLoaderId)->load($collection);
    }

    /**
     * Generates the route matching $params, but for the active locale
     *
     * @param array $params The params required for generating a route
     *
     * @return string The route corresponding $params for the active locale
     */
    private function convertParamsAndGenerate($params) {

        $route = $params['_route'];
        if (isset($params['_locale']) && 0 === strpos($params['_route'], $params['_locale'])) {

            // Remove locale + '_'
            $route = substr($params['_route'], strlen($params['_locale']) + 1);
        }

        unset($params['_route']);
        unset($params['_locale']);

        return $this->generate($route, $params);
    }
}
