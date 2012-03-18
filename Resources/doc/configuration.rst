Configuration
-------------
The bundle supports three different strategies out-of-the-box to make the
most common scenarios a bit easier. You can switch between these strategies
at any time (just make sure to clear the appdevUrl* files in your cache dir).

1. Scenario: Prefixing All Routes With The Locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Config::

    jms_i18n_routing:
        default_locale: en
        locales: [en, de]
        strategy: prefix

Resulting URLs::

- /de/kontakt
- /en/contact


2. Scenario: Prefixing All Routes With The Locale except those of the default locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Config::

    jms_i18n_routing:
        default_locale: en
        locales: [de, en]
        strategy: prefix_except_default
        
Resulting URLs::

- /de/kontakt
- /contact

3. Scenario: Using different hosts for each locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Config::

    jms_i18n_routing:
        default_locale: en
        locales: [en, de]
        strategy: custom
        hosts:
            en: foo.com
            de: foo.de 

Resulting URLs::

- http://foo.de/kontakt
- http://foo.com/contact

(URLs will only be absolute when necessary)

4. Scenario: something else
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Config:: 

    jms_i18n_routing:
        default_locale: en
        locales: [en, de]
        strategy: custom

Resulting URLs::

- /kontakt
- /contact