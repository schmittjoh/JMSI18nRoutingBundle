<?php

namespace JMS\I18nRoutingBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Chooses the default locale.
 *
 * This listener chooses the default locale to use on the first request of a
 * user to the application.
 *
 * This listener is only active if the strategy is "prefix".
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class LocaleChoosingListener
{
    private $defaultLocale;
    private $locales;

    public function __construct($defaultLocale, array $locales)
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if ('' !== rtrim($request->getPathInfo(), '/')) {
            return;
        }

        $ex = $event->getException();
        if (!$ex instanceof NotFoundHttpException || !$ex->getPrevious() instanceof ResourceNotFoundException) {
            return;
        }

        $event->setResponse(new RedirectResponse($request->getBaseUrl().'/'.$request->getPreferredLanguage(array_merge(array($this->defaultLocale), $this->locales)).$request->getPathInfo()));
    }
}