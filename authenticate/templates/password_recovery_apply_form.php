{% extends layout_template_name %}
{% block styles_head %}
    {{ parent() }}
    <link href="{{ path_to_this_module }}/library/css/password_recovery_form.css" rel="stylesheet" type="text/css"/>
{% endblock %}
{% block js_bottom %}
    {{ parent() }}
    <script src="{{ path_to_this_module }}/library/js/password_recovery_form.js" type="text/javascript"></script>
{% endblock %}
{% block content %}
    <div id="row-outer-password-recovery-form" class="row-outer">
        <div class="row-fluid">
            <h2>{{ page_title }}</h2>
            <hr/>
            <div class="columns small-12 large-12">
                {% if password_recovery_apply_submitted and password_recovery_apply_submitted == true %}
                    <div class="alert alert-block alert-success">
                        <p>
                            Your CPTAC Assay Portal user password has been reset.<br/>
                            Please <a href="javascript:void(0);" onclick="$.cookie('{{ redirect_cookie_key }}', '', { expires: 7, path: '/' }); window.location.href='{{ login_url }}'">login</a> to continue.
                        </p>
                    </div>
                {% else %}
                    <form id="form-password-recovery"
                        action="{{ path_to_this_module }}/password_recovery/apply/?password_reset_selector={{ password_reset_selector }}&password_reset_token={{ password_reset_token }}"
                        method="POST">
                        {% if errors %}
                            <div class="alert alert-block alert-danger">
                                <h4>Unable to Reset CPTAC Assay Portal User Password</h4>
                                <p>
                                    {% for error in errors %}
                                        {{ error }}
                                    {% endfor %}
                                </p>
                            </div>
                        {% endif %}
                        <div class="control-group form-group{{ errors ? ' error' : '' }}">
                            <label class="control-label" for="password">
                                <i aria-hidden="true" class="fa fa-fw fa-key"></i>
                                New Password:
                            </label>
                            <input class="form-control" id="password" name="password" type="password" autocomplete="off" placeholder="New Password"
                                title="New Password"/>
                        </div>
                        <div class="control-group form-group{{ errors ? ' error' : '' }}">
                            <label class="control-label" for="password-confirm">
                                <i aria-hidden="true" class="fa fa-fw fa-key"></i>
                                Confirm New Password:
                            </label>
                            <input class="form-control" id="password-confirm" name="password_confirm" type="password" autocomplete="off"
                                placeholder="Confirm New Password" title="Confirm New Password"/>
                        </div>
                        <div class="control-group form-group" id="form-group-password-recovery-controls">
                            <button class="btn btn-primary" title="Submit" type="submit">
                                <i aria-hidden="true" class="fa fa-fw fa-check"></i>
                                Submit
                            </button>
                        </div>
                    </form>
                {% endif %}
            </div>
        </div>
    </div>
{% endblock %}