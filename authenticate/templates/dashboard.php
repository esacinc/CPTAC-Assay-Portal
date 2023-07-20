{% extends layout_template_name %}
{% block content %}
<div class="row-fluid">
    <div class="columns small-12 small-centered large-12 large-centered">
        <div class="row-fluid">
            <h2>{{ page_title }}</h2>
            <hr/>
            <div class="columns small-4 large-4 component-panel component-link-panel">
                <div>
                    <a href="/authenticate" title="Login or Register">
                        <i aria-hidden="true" class="fa fa-fw fa-sign-in"></i>
                        <span>Login or Register</span>
                    </a>
                </div>
            </div>
            <div class="columns small-4 large-4 component-panel component-link-panel">
                <div>
                    <a href="/tutorials" title="Tutorials">
                        <i aria-hidden="true" class="fa fa-fw fa-question-circle"></i>
                        <span>Tutorials</span>
                    </a>
                    <p>{{ swpg_module_list["tutorials"]["module_description"] }}</p>
                </div>
            </div>
            <div class="columns small-4 large-4 component-panel component-link-panel">
                <div>
                    <a href="/support" title="Assay Portal Support">
                        <i aria-hidden="true" class="fa fa-fw fa-bell"></i>
                        <span>Assay Portal Support</span>
                    </a>
                    <p>{{ swpg_module_list["support"]["module_description"] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}