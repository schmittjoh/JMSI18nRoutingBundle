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

use JMS\I18nRoutingBundle\Exception\NotAcceptableLanguageException;

use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * I18n Router implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class I18nRouter extends Router
{
    private $hostMap = array();
    private $i18nLoaderId;
    private $container;
    private $defaultLocale;
    private $redirectToHost = true;
    private $localeResolver;

    /**
     * Constructor.
     *
     * The only purpose of this is to make the container available in the sub-class
     * since it is declared private in the parent class.
     *
     * The parameters are not listed explicitly here because they are different for
     * Symfony 2.0 and 2.1. If we did list them, it would make this class incompatible
     * with one of both versions.
     */
    public function __construct()
    {
        call_user_func_array(array('Symfony\Bundle\FrameworkBundle\Routing\Router', '__construct'), func_get_args());
        $this->container = func_get_arg(0);
    }

    public function setLocaleResolver(LocaleResolverInterface $resolver)
    {
        $this->localeResolver = $resolver;
    }

    /**
     * Whether the user should be redirected to a different host if the
     * matching route is not belonging to the current domain.
     *
     * @param Boolean $bool
     */
    public function setRedirectToHost($bool)
    {
        $this->redirectToHost = (Boolean) $bool;
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
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
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
            $referenceType = self::NETWORK_PATH === $referenceType ? self::NETWORK_PATH : self::ABSOLUTE_URL;
        }
        $needsHost = self::NETWORK_PATH === $referenceType || self::ABSOLUTE_URL === $referenceType;

        $generator = $this->getGenerator();

        // if an absolute or network URL is requested, we set the correct host
        if ($needsHost && $this->hostMap) {
            $currentHost = $this->context->getHost();
            $this->context->setHost($this->hostMap[$locale]);
        }

        try {
            $url = $generator->generate($locale.I18nLoader::ROUTING_PREFIX.$name, $parameters, $referenceType);

            if ($needsHost && $this->hostMap) {
                $this->context->setHost($currentHost);
            }

            return $url;
        } catch (RouteNotFoundException $ex) {
            if ($needsHost && $this->hostMap) {
                $this->context->setHost($currentHost);
            }

            // fallback to default behavior
        }

        // use the default behavior if no localized route exists
        return $generator->generate($name, $parameters, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function match($url)
    {
        return $this->matchI18n(parent::match($url), $url);
    }

    public function getRouteCollection()
    {
        $collection = parent::getRouteCollection();

        return $this->container->get($this->i18nLoaderId)->load($collection);
    }

    public function getOriginalRouteCollection()
    {
        return parent::getRouteCollection();
    }

    /**
     * To make compatible with Symfony <2.4
     */
    public function matchRequest(Request $request)
    {
        $matcher = $this->getMatcher();
        $pathInfo = $request->getPathInfo();
        if (!$matcher instanceof RequestMatcherInterface) {
            // fallback to the default UrlMatcherInterface
            return $this->matchI18n($matcher->match($pathInfo), $pathInfo);
        }

        return $this->matchI18n($matcher->matchRequest($request), $pathInfo);
    }

    private function matchI18n(array $params, $url)
    {
        if (false === $params) {
            return false;
        }

        $request = $this->getRequest();

        if (isset($params['_locales'])) {
            if (false !== $pos = strpos($params['_route'], I18nLoader::ROUTING_PREFIX)) {
                $params['_route'] = substr($params['_route'], $pos + strlen(I18nLoader::ROUTING_PREFIX));
            }

            if (!($currentLocale = $this->context->getParameter('_locale'))
                    && null !== $request) {
                $currentLocale = $this->localeResolver->resolveLocale(
                    $request, $params['_locales']
                );

                // If the locale resolver was not able to determine a locale, then all efforts to
                // make an informed decision have failed. Just display something as a last resort.
                if (!$currentLocale) {
                    $currentLocale = reset($params['_locales']);
                }
            }

            if (!in_array($currentLocale, $params['_locales'], true)) {
                // TODO: We might want to allow the user to be redirected to the route for the given locale if
                //       it exists regardless of whether it would be on another domain, or the same domain.
                //       Below we assume that we do not want to redirect always.

                // if the available locales are on a different host, throw a ResourceNotFoundException
                if ($this->hostMap) {
                    // generate host maps
                    $hostMap = $this->hostMap;
                    $availableHosts = array_map(function($locale) use ($hostMap) {
                        return $hostMap[$locale];
                    }, $params['_locales']);

                    $differentHost = true;
                    foreach ($availableHosts as $host) {
                        if ($this->hostMap[$currentLocale] === $host) {
                            $differentHost = false;
                            break;
                        }
                    }

                    if ($differentHost) {
                        throw new ResourceNotFoundException(sprintf('The route "%s" is not available on the current host "%s", but only on these hosts "%s".',
                            $params['_route'], $this->hostMap[$currentLocale], implode(', ', $availableHosts)));
                    }
                }

                // no host map, or same host means that the given locale is not supported for this route
                throw new NotAcceptableLanguageException($currentLocale, $params['_locales']);
            }

            unset($params['_locales']);
            $params['_locale'] = $currentLocale;
        } else if (isset($params['_locale']) && 0 < $pos = strpos($params['_route'], I18nLoader::ROUTING_PREFIX)) {
            $params['_route'] = substr($params['_route'], $pos + strlen(I18nLoader::ROUTING_PREFIX));
        }

        // check if the matched route belongs to a different locale on another host
        if (isset($params['_locale'])
                && isset($this->hostMap[$params['_locale']])
                && $this->context->getHost() !== $host = $this->hostMap[$params['_locale']]) {
            if (!$this->redirectToHost) {
                throw new ResourceNotFoundException(sprintf(
                    'Resource corresponding to pattern "%s" not found for locale "%s".', $url, $this->getContext()->getParameter('_locale')));
            }

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
        }

        // if we have no locale set on the route, we try to set one according to the localeResolver
        // if we don't do this all _internal routes will have the default locale on first request
        if (!isset($params['_locale'])
                && null !== $request
                && $locale = $this->localeResolver->resolveLocale(
                        $request,
                        $this->container->getParameter('jms_i18n_routing.locales'))) {
            $params['_locale'] = $locale;
        }

        return $params;
    }

    /**
     * @return Request|null
     */
    private function getRequest()
    {
        $request = null;
        if ($this->container->has('request_stack')) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
        } elseif (method_exists($this->container, 'isScopeActive') && $this->container->isScopeActive('request')) {
            $request = $this->container->get('request');
        }

        return $request;
    }
}
