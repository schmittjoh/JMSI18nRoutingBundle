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

namespace JMS\I18nRoutingBundle\Exception;

class NotAcceptableLanguageException extends NotAcceptableException
{
    private $requestedLanguage;
    private $availableLanguages;

    public function __construct($requestedLanguage, array $availableLanguages)
    {
        parent::__construct(sprintf('The requested language "%s" was not available. Available languages: "%s"', $requestedLanguage, implode(', ', $availableLanguages)));

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