Usage
=====

Introduction
------------
You can continue to create routes like you would do normally. In fact,
during development you don't need to make any special changes to your existing 
routes to make them translatable.

Translating Routes
------------------
Once your code is stable enough to begin translation, you can use the ``translation:extract``
command that is provided by JMSTranslationBundle_:

.. code-block :: bash

    $ php app/console translation:extract de --enable-extractor=jms_i18n_routing # ...
    
Please refer to the `bundle's documentation`_ for more information.

.. _JMSTranslationBundle: https://github.com/schmittjoh/JMSTranslationBundle
.. _bundle's documentation: https://jmsyst.com/bundles/JMSTranslationBundle

Generating Routes
-----------------
By default, the router uses the following algorithm to determine which locale to
use for route generation:

1. use the _locale parameter which was passed to the generate() method
2. use the _locale parameter which is present in the request context
3. use the configured default locale

Some examples below::

    <!-- uses locale of the request context to generate the route -->
    <a href="{{ path("contact") }}">Contact</a>
    
    <!-- sometimes it's necessary to generate routes for a locale other than that
         of the request context, then you can pass it explicitly -->
    <a href="{{ path("homepage", {"_locale": "de"}) }}">Deutsch</a>
    <a href="{{ path("homepage", {"_locale": "en"}) }}">English</a>
    
Leaving routes untranslated
---------------------------
If you don't want to translate a single given route, you can begin the route name with "_" (e.g. "_contact") or disable it in the routing configuration:

.. code-block :: yaml

    # app/config/routing.yml
    homepage:
        ...
        options: { i18n: false }

Prefixing routes before the _locale
-----------------------------------
If you want to add a prefix before the _locale string (e.g. /admin/en/dashboard), you can add the "i18n_prefix" option.

.. code-block :: yaml

    # app/config/routing.yml
    dashboard:
        ...
        options: { i18n_prefix: admin }

More Resources
--------------

.. toctree ::
    :hidden:
    
    /cookbook/language_switcher
    
- :doc:`Creating a Language Switcher </cookbook/language_switcher>`
