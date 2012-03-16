========
Overview
========

This bundle allows you to create i18n routes. Key points:

- uses the Translation component; translate URLs just like you would translate 
  any other text on your website
- allows you to use different hosts per locale
- does not require you to change your development processes
- can translate all routes whether they are coming from third-party bundles,
  or your own application


Installation
------------
Checkout a copy of the code::

    git submodule add https://github.com/schmittjoh/JMSI18nRoutingBundle.git vendor/bundles/JMS/I18nRoutingBundle

Then register the bundle with your kernel::

    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
        // ...
    );

Make sure that you also register the namespaces with the autoloader::

    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...
        'JMS'              => __DIR__.'/../vendor/bundles',
        // ...
    ));

Also, check that you have enabled the Translator::

    # app/config/config.yml
    framework:
        translator: { enabled: true }

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
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Whenever a pattern is matched to a different host's locale a redirect
is used, unless redirect_to_host = false, in which case a
ResourceNotFoundException is thrown

Config::

    jms_i18n_routing:
        default_locale: en
        locales: [en, de]
        strategy: custom
        hosts:
            en: foo.com
            de: foo.de 
        redirect_to_host: true/false

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


Usage
-----
You can continue to create routes like you would do normally. In fact,
during development you don't need to make any special changes to your existing 
routes to make them translatable.

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

Other Resources
---------------
There exists another bundle, which allows you to translate URLs
(https://github.com/BeSimple/BeSimpleI18nRoutingBundle). The approaches are a bit
different, see yourself which one fits your development style better.
