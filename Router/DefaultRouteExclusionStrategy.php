<?php

namespace JMS\I18nRoutingBundle\Router;

use Symfony\Component\Routing\Route;

/**
 * The default route exclusion strategy.
 *
 * This strategy ignores all routes if at least one of the following is true:
 *
 *     - the route name starts with an underscore
 *     - the option "i18n" is set to false
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class DefaultRouteExclusionStrategy implements RouteExclusionStrategyInterface
{
    public function shouldExcludeRoute($routeName, Route $route)
    {
        if ('_' === $routeName[0]) {
            return true;
        }

        if (false === $route->getOption('i18n')) {
            return true;
        }

        return false;
    }
}