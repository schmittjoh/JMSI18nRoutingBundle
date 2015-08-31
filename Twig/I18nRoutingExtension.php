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

namespace JMS\I18nRoutingBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class I18nRoutingExtension extends \Twig_Extension
{
    /**
     * @var RequestStack requestStack
     */
    private $requestStack;

    /**
     * This is only used in Symfony 2.3
     *
     * @var ContainerInterface container
     */
    private $container;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('hreflang', array($this, 'getHreflang'), array(
                'needs_environment' => true,
                'is_safe' => array('html'),
                )),
        );
    }

    /**
     * Return HTML with hreflang attributes.
     *
     * @param \Twig_Environment $env
     */
    public function getHreflang(\Twig_Environment $env)
    {
        if (null === $request = $this->getRequest()) {
            return;
        }

        if (null === $routeParams = $request->attributes->get('_route_params')) {
            return;
        }

        if (!isset($routeParams['_localized']) || !$routeParams['_localized']) {
            return;
        }

        return $env->render('JMSI18nRoutingBundle::hreflang.html.twig', array(
            'locales' => $routeParams['_all_locales'],
            'route' => $request->attributes->get('_route'),
            'routeParams' => $routeParams,
        ));
    }

    /**
     * @param ContainerInterface $container
     *
     * @return I18nRoutingExtension
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @param RequestStack $requestStack
     *
     * @return I18nRoutingExtension
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

        return $this;
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    private function getRequest()
    {
        if ($this->requestStack !== null) {
            return $this->requestStack->getMasterRequest();
        }

        if ($this->container !== null) {
            $this->container->get('request');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'i18n_routing_extension';
    }
}
