<?php

namespace JMS\I18nRoutingBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Tobias Nyholm
 */
class I18nRoutingExtension extends \Twig_Extension
{
    /**
     * @var RequestStack requestStack
     */
    private $requestStack;

    /**
     * @var array locales
     */
    private $locales;

    /**
     * @param RequestStack $requestStack
     * @param array        $locales
     */
    public function __construct(RequestStack $requestStack, $locales)
    {
        $this->requestStack = $requestStack;
        $this->locales = $locales;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('hreflang', array($this, 'getHreflang'), array(
                'needs_environment'=>true,
                'is_safe'=>array('html'),
                )),
        );
    }

    /**
     * Return HTML with hreflang attributes
     *
     * @param \Twig_Environment $env
     */
    public function getHreflang(\Twig_Environment $env)
    {
        $request = $this->requestStack->getMasterRequest();
        $routeParams = $request->attributes->get('_route_params');
        if (!isset($routeParams['localized']) || !$routeParams['localized']) {
            return;
        }

        return $env->render('JMSI18nRoutingBundle::hreflang.html.twig', array(
            'locales'=>$this->locales,
            'route'=>$request->attributes->get('_route'),
            'routeParams'=>$routeParams,
        ));
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