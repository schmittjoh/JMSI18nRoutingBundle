Configuration
-------------
The bundle supports three different strategies out-of-the-box to make the
most common scenarios a bit easier. You can switch between these strategies
at any time.

.. note ::

    You need to manually clear your cache when switching between different 
    strategies.

1. Scenario: Prefixing All Routes With The Locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block ::

    .. code-block :: yaml

        jms_i18n_routing:
            default_locale: en
            locales: [en, de]
            strategy: prefix
            
    .. code-block :: xml
    
        <jms-i18n-routing
            default-locale="en"
            locales="en, de"
            strategy="prefix" />

Resulting URLs::

- /de/kontakt
- /en/contact


2. Scenario: Prefixing All Routes With The Locale except those of the default locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block ::

    .. code-block :: yaml

        jms_i18n_routing:
            default_locale: en
            locales: [de, en]
            strategy: prefix_except_default

    .. code-block :: xml
    
        <jms-i18n-routing
            default-locale="en"
            locales="de, en"
            strategy="prefix_except_default" />
        
Resulting URLs::

- /de/kontakt
- /contact

3. Scenario: Using different hosts for each locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block ::

    .. code-block :: yaml

        jms_i18n_routing:
            default_locale: en
            locales: [en, de]
            strategy: custom
            hosts:
                en: foo.com
                de: foo.de 
            redirect_to_host: true
    
    .. code-block :: xml
    
        <jms-i18n-routing 
            default-locale="en" 
            locales="en, de" 
            strategy="custom"
            redirect-to-host="true">
            
            <host locale="en">foo.com</host>
            <host locale="de">foo.de</host>
            
        </jms-i18n-routing>

Whenever a pattern is matched to a different host's locale a redirect is used, 
unless ``redirect_to_host`` is set to false, in which case a ``ResourceNotFoundException`` 
is thrown which typically results in a 404 error.

Resulting URLs::

- http://foo.de/kontakt
- http://foo.com/contact

.. note ::

    The router will automatically detect when an absolute URL is necessary, and
    then add the host automatically.

4. Scenario: Something Else
~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block ::

    .. code-block :: yaml
    
        jms_i18n_routing:
            default_locale: en
            locales: [en, de]
            strategy: custom
            
    .. code-block :: xml
    
        <jms-i18n-routing
            default-locale="en"
            locales="en, de"
            strategy="custom" />

Resulting URLs::

- /kontakt
- /contact
