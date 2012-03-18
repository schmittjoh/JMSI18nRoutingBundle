<?php

namespace JMS\I18nRoutingBundle\Router;

use Symfony\Component\Routing\Route;

/**
 * Interface for route exclusions.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface RouteExclusionStrategyInterface
{
    /**
     * Implementations determine whether the given route is eligible for i18n.
     *
     * @param string $routeName
     * @param Route $route
     *
     * @return Boolean
     */
    function shouldExcludeRoute($routeName, Route $route);
}