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

namespace JMS\I18nRoutingBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Sets the locale based on the host which is being accessed.
 *
 * This listener is only active if the users specifies a host map.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class LocaleChangingListener
{
    private $hostMap;

    public function __construct(array $hostMap)
    {
        $this->hostMap = $hostMap;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $host = $request->getHost();

        if (isset($this->hostMap[$host])) {
            if (method_exists($request, 'setLocale')) {
                $request->setLocale($this->hostMap[$host]);
            } else {
                $request->getSession()->setLocale($this->hostMap[$host]);
            }
        }
    }
}