<?php

namespace JMS\I18nRoutingBundle\Translation;

use Symfony\Component\Routing\RouterInterface;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\I18nRoutingBundle\Router\I18nLoader;
use JMS\TranslationBundle\Translation\ExtractorInterface;

class RouteTranslationExtractor implements ExtractorInterface
{
    private $router;
    private $i18nLoader;
    private $domain = 'routes';

    public function __construct(RouterInterface $router, I18nLoader $loader)
    {
        $this->router = $router;
        $this->i18nLoader = $loader;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function extract()
    {
        $catalogue = new MessageCatalogue();

        foreach ($this->i18nLoader->extract($this->router->getRouteCollection()) as $name => $route) {
            $message = new Message($name, $this->domain);
            $message->setDesc($route->getPattern());
            $catalogue->add($message);
        }

        return $catalogue;
    }
}