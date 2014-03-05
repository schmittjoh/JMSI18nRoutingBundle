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

namespace JMS\I18nRoutingBundle\EventListener;

use JMS\I18nRoutingBundle\Router\LocaleResolverInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Chooses the default locale.
 *
 * This listener chooses the default locale to use on the first request of a
 * user to the application.
 *
 * This listener is only active if the strategy is "prefix".
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class LocaleChoosingListener
{
    private $defaultLocale;
    private $locales;
    private $localeResolver;
    private $router;

    public function __construct($defaultLocale, array $locales, LocaleResolverInterface $localeResolver, RouterInterface $router)
    {
        $this->defaultLocale  = $defaultLocale;
        $this->locales        = $locales;
        $this->localeResolver = $localeResolver;
        $this->router         = $router;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $ex = $event->getException();
        if (!$ex instanceof NotFoundHttpException || !$ex->getPrevious() instanceof ResourceNotFoundException) {
            return;
        }

        $request = $event->getRequest();
        $path = rtrim($request->getPathInfo(), '/');
        if (! $this->pathIsLandingUri($path)) {
            return;
        }

        $locale = $this->localeResolver->resolveLocale($request, $this->locales) ? : $this->defaultLocale;
        $request->setLocale($locale);

        $params = $request->query->all();
        unset($params['hl']);

        $event->setResponse(
            new RedirectResponse($request->getBaseUrl() . $path . '/' . $locale . '/' . ($params ? '?' . http_build_query(
                        $params
                    ) : ''))
        );
    }

    private function pathIsLandingUri($path)
    {
        if ('' === $path) {
            return true;
        }

        foreach ($this->router->getRouteCollection()->getIterator() as $route) {
            if ($path === $route->getOption('i18n_prefix')) {
                return true;
            }
        }

        return false;
    }
}
