<?php

namespace JMS\I18nRoutingBundle\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Sets the user's language as a cookie.
 *
 * This is necessary if you are not using a host map, and still would like to
 * use Varnish in front of your Symfony2 application.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class CookieSettingListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->cookies->has('hl')
                || $request->cookies->get('hl') !== $request->getLocale()) {
            $event->getResponse()->headers->setCookie(new Cookie('hl', $request->getLocale(), time() + 86400 * 365));
        }
    }
}