{% extends layout_template_name %}
{% block content %}
<div class="row-fluid">
    <div class="columns small-12 small-centered large-12 large-centered">
{% if module.menu_hidden != true %}
    {% set module_index = -1 %}
    {% for module in swpg_module_list if module.menu_hidden != true %}
        {% set module_index = module_index + 1 %}
        {% if module_index % 3 == 0  %}
            {% if module_index > 0 %}
                </div>
            {% endif %}
            <div class="row-fluid">
        {% endif %}
        <div class="columns small-4 large-4 component-panel component-link-panel">
            <div>
                <a href="{{ module.path_to_this_module }}/" title="{{ module.module_name }} Module: {{ module.module_description }}">
                    <i aria-hidden="true" class="{{ module.module_icon_css_classes }}"></i>
                    <span>{{ module.module_name }}</span>
                </a>
                <p>{{ module.module_description }}</p>
            </div>
        </div>
    {% endfor %}
    </div>
{% endif %}
    </div>
</div>
{% endblock %}