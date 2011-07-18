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

namespace JMS\I18nRoutingBundle\Tests\Router;

use Symfony\Component\Translation\Loader\YamlFileLoader as TranslationLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;
use JMS\I18nRoutingBundle\Router\I18nLoader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use JMS\I18nRoutingBundle\Router\I18nRouter;

class I18nRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $router = $this->getRouter();
        $this->assertEquals('/welcome-on-our-website', $router->generate('welcome'));

        $context = new RequestContext();
        $context->setParameter('_locale', 'en');
        $router->setContext($context);

        $this->assertEquals('/welcome-on-our-website', $router->generate('welcome'));
        $this->assertEquals('/willkommen-auf-unserer-webseite', $router->generate('welcome', array('_locale' => 'de')));
        $this->assertEquals('/welcome-on-our-website', $router->generate('welcome', array('_locale' => 'fr')));

        // test homepage
        $this->assertEquals('/', $router->generate('homepage', array('_locale' => 'en')));
        $this->assertEquals('/', $router->generate('homepage', array('_locale' => 'de')));
    }

    public function testGenerateWithHostMap()
    {
        $router = $this->getRouter();
        $router->setHostMap(array(
            'de' => 'de.host',
            'en' => 'en.host',
            'fr' => 'fr.host',
        ));

        $this->assertEquals('/welcome-on-our-website', $router->generate('welcome'));
        $this->assertEquals('http://en.host/welcome-on-our-website', $router->generate('welcome', array(), true));

        $context = new RequestContext();
        $context->setParameter('_locale', 'en');
        $router->setContext($context);

        $this->assertEquals('/welcome-on-our-website', $router->generate('welcome'));
        $this->assertEquals('http://en.host/welcome-on-our-website', $router->generate('welcome', array(), true));
        $this->assertEquals('http://de.host/willkommen-auf-unserer-webseite', $router->generate('welcome', array('_locale' => 'de')));
        $this->assertEquals('http://de.host/willkommen-auf-unserer-webseite', $router->generate('welcome', array('_locale' => 'de'), true));
    }

    public function testGenerateDoesUseCorrectHostWhenSchemeChanges()
    {
        $router = $this->getRouter();

        $router->setHostMap(array(
            'en' => 'en.test',
            'de' => 'de.test',
        ));

        $context = new RequestContext();
        $context->setScheme('http');
        $context->setParameter('_locale', 'en');
        $router->setContext($context);

        $this->assertEquals('https://en.test/login', $router->generate('login'));
        $this->assertEquals('https://de.test/einloggen', $router->generate('login', array('_locale' => 'de')));
    }

    public function testGenerateDoesNotI18nInternalRoutes()
    {
        $router = $this->getRouter();

        $this->assertEquals('/internal?_locale=de', $router->generate('_internal', array('_locale' => 'de')));
    }

    public function testMatch()
    {
        $router = $this->getRouter();
        $router->setHostMap(array(
            'en' => 'en.test',
            'de' => 'de.test',
            'fr' => 'fr.test',
        ));

        $this->assertEquals(array('_controller' => 'foo', '_route' => 'welcome'), $router->match('/welcome'));

        $context = new RequestContext('', 'GET', 'en.test');
        $context->setParameter('_locale', 'en');
        $router->setContext($context);

        $this->assertEquals(array('_controller' => 'foo', '_locale' => 'en', '_route' => 'en_welcome'), $router->match('/welcome-on-our-website'));

        $this->assertEquals(array(
            '_controller' => 'JMS\I18nRoutingBundle\Controller\RedirectController::redirectAction',
            'path'        => '/willkommen-auf-unserer-webseite',
            'host'        => 'de.test',
            'permanent'   => true,
            'scheme'      => 'http',
            'httpPort'    => 80,
            'httpsPort'   => 443,
            '_route'      => 'de_welcome',
        ), $router->match('/willkommen-auf-unserer-webseite'));
    }

    private function getRouter($config = 'routing.yml')
    {
        $container = new Container();
        $container->set('routing.loader', new YamlFileLoader(new FileLocator(__DIR__.'/Fixture')));

        $translator = new Translator('en', new MessageSelector());
        $translator->setFallbackLocale('en');
        $translator->addLoader('yml', new TranslationLoader());
        $translator->addResource('yml', file_get_contents(__DIR__.'/Fixture/routes.de.yml'), 'de', 'routes');
        $translator->addResource('yml', file_get_contents(__DIR__.'/Fixture/routes.en.yml'), 'en', 'routes');
        $container->set('i18n_loader', new I18nLoader($translator, array('en', 'de', 'fr'), 'routes', sys_get_temp_dir()));

        $router = new I18nRouter($container, $config);
        $router->setI18nLoaderId('i18n_loader');
        $router->setDefaultLocale('en');

        return $router;
    }
}