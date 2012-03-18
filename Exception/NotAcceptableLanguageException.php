<?php

namespace JMS\I18nRoutingBundle\Exception;

class NotAcceptableLanguageException extends NotAcceptableException
{
    private $requestedLanguage;
    private $availableLanguages;

    public function __construct($requestedLanguage, array $availableLanguages)
    {
        parent::__construct(sprintf('The requested language "%s" was not available. Only the following languages are available: "%s"', $requestedLanguage, implode(', ', $availableLanguages)));

        $this->requestedLanguage = $requestedLanguage;
        $this->availableLanguages = $availableLanguages;
    }

    public function getRequestedLanguage()
    {
        return $this->requestedLanguage;
    }

    public function getAvailableLanguages()
    {
        return $this->availableLanguages;
    }
}