Usage
=====

Introduction
------------
You can continue to create routes like you would do normally. In fact,
during development you don't need to make any special changes to your existing 
routes to make them translatable.

Translating Routes
------------------
Once, you decide that your code is stable enough to begin translation, you can
use one of the following options to generate a translation file:

1. Using the extraction command provided by this bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
For ease of use, this bundle already provides a rudimentary command for generating 
a translation file (it has some limitations, but it might be enough if you just 
want to try this bundle):: 

    php app/console i18n:extract-routes <locale>

    # if you want to delete translations for removed routes, add the "--delete" option
    php app/console i18n:extract-routes de --delete

    # you can also preview any changes, with the "--dry-run" option
    php app/console i18n:extract-routes de --dry-run

You can then start translating your routes in the generated file, or pass the 
translation file on to a translator.

The generated file with translations for the routes will be placed at app/Resources/translations/routes.XX.yml
(one file per each locale defined in config, where XX is the locale code) and will look like this::

    #filename: app/Resources/translations/routes.es.yml
    home: /
    search_list: '/lista/{city}'

for a routing.yml like this::

    #filename: src\Acme\DemoBundle\Resources\config
    home:
        pattern:  /
        defaults: { _controller: AcmeDemoBundle:Home:index }

    search_list:
        pattern:  /list/{city}
        defaults: { _controller: AcmeDemoBundle:List:list }
    
    _robotstxt
        pattern:  /robots.txt
        defaults: { _controller: AcmeDemoBundle:Robots:txt }

Take into account that all routes which name begins with "_" will be ignored (like _robotstxt in the example ).

2. Using the extraction command provided by the JMSTranslationBundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
This bundle is also integrated with the JMSTranslationBundle_ which provides several
more features like dumping to different formats, retaining the source translation in
the translation file, and some more. If you have the bundle installed you can
extract translations with this command:

    php app/console translation:extract de --enable-extractor=jms_i18n_routing ...
    
Please refer to the `bundle's documentation`_ for more information.

.. _JMSTranslationBundle: https://github.com/schmittjoh/JMSTranslationBundle
.. _bundle's documentation: https://github.com/schmittjoh/JMSTranslationBundle/blob/master/Resources/doc/index.rst

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
    
More Resources
--------------

.. toctree ::
    :hidden:
    
    /cookbook/language_switcher
    
- :doc:`Creating a Language Switcher </cookbook/language_switcher>`
