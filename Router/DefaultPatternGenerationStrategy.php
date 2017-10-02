<?php

namespace JMS\I18nRoutingBundle\Router;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\LoggingTranslator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Routing\Route;

/**
 * The default strategy supports 3 different scenarios, and makes use of the
 * Symfony2 Translator Component.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class DefaultPatternGenerationStrategy implements PatternGenerationStrategyInterface
{
    const STRATEGY_PREFIX = 'prefix';
    const STRATEGY_PREFIX_EXCEPT_DEFAULT = 'prefix_except_default';
    const STRATEGY_CUSTOM = 'custom';

    private $strategy;
    private $translator;
    private $translationDomain;
    private $locales;
    private $cacheDir;
    private $defaultLocale;

    public function __construct($strategy, TranslatorInterface $translator, array $locales, $cacheDir, $translationDomain = 'routes', $defaultLocale = 'en')
    {
        $this->strategy = $strategy;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->locales = $locales;
        $this->cacheDir = $cacheDir;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritDoc}
     */
    public function generateI18nPatterns($routeName, Route $route)
    {
        $patterns = array();
        foreach ($route->getOption('i18n_locales') ?: $this->locales as $locale) {
            // Check if translation exists in the translation catalogue to avoid errors being logged by 
            // the new LoggingTranslator of Symfony 2.6. However, the LoggingTranslator did not implement
            // the interface until Symfony 2.6.5, so an extra check is needed.
            if ($this->translator instanceof TranslatorBagInterface || $this->translator instanceof LoggingTranslator) {
                // Check if route is translated.
                if (!$this->translator->getCatalogue($locale)->has($routeName, $this->translationDomain)) {
                    // No translation found.
                    $i18nPattern = $route->getPath();
                } else {
                    // Get translation.
                    $i18nPattern = $this->translator->trans($routeName, array(), $this->translationDomain, $locale);
                }
            } else {
                // if no translation exists, we use the current pattern
                if ($routeName === $i18nPattern = $this->translator->trans($routeName, array(), $this->translationDomain, $locale)) {
                    $i18nPattern = $route->getPath();
                }
            }

            // prefix with locale if requested
            if (self::STRATEGY_PREFIX === $this->strategy
                || (self::STRATEGY_PREFIX_EXCEPT_DEFAULT === $this->strategy && $this->defaultLocale !== $locale)) {
                $i18nPattern = '/'.$locale.$i18nPattern;
                if (null !== $route->getOption('i18n_prefix')) {
                    $i18nPattern = $route->getOption('i18n_prefix').$i18nPattern;
                }
            }

            $patterns[$i18nPattern][] = $locale;
        }

        return $patterns;
    }

    /**
     * {@inheritDoc}
     */
    public function addResources(RouteCollection $i18nCollection)
    {
        foreach ($this->locales as $locale) {
            if (file_exists($metadata = $this->cacheDir.'/translations/catalogue.'.$locale.'.php.meta')) {
                foreach (unserialize(file_get_contents($metadata)) as $resource) {
                    $i18nCollection->addResource($resource);
                }
            }
        }
    }
}
