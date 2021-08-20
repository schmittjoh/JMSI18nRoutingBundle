Creating a Language Switcher
============================

Configure your locales in config.yml
------------------------------------
.. code-block :: yaml
    # app/config/config.yml
    twig:
        ...
        globals:
            locales: [en, fr, nl]

Add your language switcher in your template
-------------------------------------------
.. code-block :: twig
    # src/AppBundle/Ressources/views/layout.html.twig
    ...
    <div class="lang-switcher">
			{% set routepath = app.request.attributes.get('_route') %}
			{% set routeparam = app.request.attributes.get('_route_params') %}
			{% for locale in locales %}
			  {% if locale == app.request.locale %}
			  	{{ locale }}
			  {% else %}
			  	<a href="{{ path(routepath, routeparam|merge({"_locale": locale})) }}">{{ locale }}</a>
			  {% endif %}
			  {% if not loop.last %}
			  |
			  {% endif %}
			{% endfor %}
		</div>
