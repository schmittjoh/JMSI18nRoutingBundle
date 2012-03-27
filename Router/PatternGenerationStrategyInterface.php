<?php

namespace JMS\I18nRoutingBundle\Router;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Implementations are responsible for generating the i18n patterns
 * for a given route.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface PatternGenerationStrategyInterface
{
    /**
     * Returns the i18n patterns for a given route.
     *
     * @param string $routeName
     * @param Route $route
     *
     * @return array<string, array<string>> an array mapping the pattern to an array of locales
     */
    function generateI18nPatterns($routeName, Route $route);

    /**
     * You may add possible resources to the i18n collection.
     *
     * This may for example be translation resources.
     *
     * @param RouteCollection $i18nRouteCollection
     */
    function addResources(RouteCollection $i18nRouteCollection);
}