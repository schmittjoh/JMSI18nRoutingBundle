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
    private $cookieName;
    private $cookieLifetime;
    private $cookiePath;
    private $cookieDomain;
    private $cookieSecure;
    private $cookieHttponly;

    public function __construct($cookieName, $cookieLifetime, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttponly)
    {
        $this->cookieName = $cookieName;
        $this->cookieLifetime = $cookieLifetime;
        $this->cookiePath = $cookiePath;
        $this->cookieDomain = $cookieDomain;
        $this->cookieSecure = $cookieSecure;
        $this->cookieHttponly = $cookieHttponly;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        //Check if the current response contains an error. If it does, do not set the cookie as the Locale may not be properly set
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType() || !($event->getResponse()->isSuccessful() || $event->getResponse()->isRedirection())) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->cookies->has($this->cookieName)
                || $request->cookies->get($this->cookieName) !== $request->getLocale()) {
            $event->getResponse()->headers->setCookie(new Cookie($this->cookieName, $request->getLocale(), time() + $this->cookieLifetime, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttponly));
        }
    }
}
