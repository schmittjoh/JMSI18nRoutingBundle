<?php

namespace JMS\I18nRoutingBundle\Router;

use Symfony\Component\HttpFoundation\Request;

/**
 * Default Locale Resolver.
 *
 * These checks are performed by this method:
 *
 *     1. Check if the host is associated with a specific locale
 *     2. Check for a query parameter named "hl"
 *     3. Check for a locale in the session
 *     4. Check for a cookie named "hl"
 *     5. Check the Accept header for supported languages
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class DefaultLocaleResolver implements LocaleResolverInterface
{
    private $cookieName;
    private $hostMap;

    public function __construct($cookieName, array $hostMap = array())
    {
        $this->cookieName = $cookieName;
        $this->hostMap = $hostMap;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveLocale(Request $request, array $availableLocales)
    {
        if ($this->hostMap && isset($this->hostMap[$host = $request->getHost()])) {
            return $this->hostMap[$host];
        }

        // if a locale has been specifically set as a query parameter, use it
        if ($request->query->has('hl')) {
            $hostLanguage = $request->query->get('hl');

            if (preg_match('#^[a-z]{2}(?:_[a-z]{2})?$#i', $hostLanguage)) {
                return $hostLanguage;
            }
        }

        // check if a session exists, and if it contains a locale
        if ($request->hasPreviousSession()) {
            $session = $request->getSession();
            if ($session->has('_locale')) {
                return $session->get('_locale');
            }
        }

        // if user sends a cookie, use it
        if ($request->cookies->has($this->cookieName)) {
            $hostLanguage = $request->cookies->get($this->cookieName);

            if (preg_match('#^[a-z]{2}(?:_[a-z]{2})?$#i', $hostLanguage)) {
                return $hostLanguage;
            }
        }

        // use accept header for locale matching if sent
        if ($languages = $request->getLanguages()) {
            foreach ($languages as $lang) {
                if (in_array($lang, $availableLocales, true)) {
                    return $lang;
                }
            }
        }

        return null;
    }
}