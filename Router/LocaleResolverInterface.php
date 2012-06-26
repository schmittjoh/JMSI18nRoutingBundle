<?php

namespace JMS\I18nRoutingBundle\Router;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for Locale Resolvers.
 * 
 * A resolver implementation is triggered only if we match a route that is
 * available for multiple locales.
 * 
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface LocaleResolverInterface
{
    /**
     * Resolves the locale in case a route is available for multiple locales.
     * 
     * @param array $availableLocales
     * 
     * @return string|null may return null if no suitable locale is found, may also
     *                        return a locale which is not available for the matched route
     */
    function resolveLocale(Request $request, array $availableLocales);

    /**
     * Returns the locale used in this request based on a previous call to resolveLocale method
     *
     * @return string|null may return null if no suitable locale is found, may also
     *                        return a locale which is not available for the matched route
     */
    function getCurrentLocale();
}