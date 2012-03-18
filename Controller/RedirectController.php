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

namespace JMS\I18nRoutingBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Redirect Controller.
 *
 * Used by the I18nRouter to redirect between different hosts.
 *
 * @license Portions of this code were received from the Symfony2 project under
 *          the MIT license. All other code is subject to the Apache2 license.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class RedirectController
{
    public function redirectAction(Request $request, $path, $host = null, $permanent = false, $scheme = null, $httpPort = 80, $httpsPort = 443)
    {
        if (!$path) {
            return new Response(null, 410);
        }

        if (null === $scheme) {
            $scheme = $request->getScheme();
        }

        $qs = $request->getQueryString();
        if ($qs) {
            $qs = '?'.$qs;
        }

        $port = '';
        if ('http' === $scheme && 80 != $httpPort) {
            $port = ':'.$httpPort;
        } elseif ('https' === $scheme && 443 != $httpsPort) {
            $port = ':'.$httpsPort;
        }

        $url = $scheme.'://'.($host ?: $request->getHost()).$port.$request->getBaseUrl().$path.$qs;

        return new RedirectResponse($url, $permanent ? 301 : 302);
    }
}