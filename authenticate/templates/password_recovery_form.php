{% extends layout_template_name %}
{% block styles_head %}
    {{ parent() }}
    <link href="{{ path_to_this_module }}/library/css/password_recovery_form.css" rel="stylesheet" type="text/css"/>
{% endblock %}
{% block js_bottom %}
    {{ parent() }}
    <script src="https://www.google.com/recaptcha/api.js" type="text/javascript" async defer></script>
    <script src="{{ path_to_this_module }}/library/js/password_recovery_form.js" type="text/javascript"></script>
{% endblock %}
{% block content %}
    <div id="row-outer-password-recovery-form" class="row-outer">
        <div class="row-fluid">
            <h2>{{ page_title }}</h2>
            <hr/>
            <div class="columns small-12 large-12">
                {% if password_recovery_submitted and password_recovery_submitted == true %}
                    <div class="alert alert-block alert-success">
                        <p>
                            An email was sent to the email address associated with the provided CPTAC Assay Portal username.<br/>
                            Please follow the link in the email to reset your password.
                        </p>
                    </div>
                {% else %}
                    <p class="muted">
                        If you are using your NIH Active Directory credentials go
                        <a href="https://iforgotmypassword.nih.gov/aims/ps/">here</a>.
                    </p>
                    <p class="muted">
                        If you are using your Google credentials go
                        <a href="https://support.google.com/mail/answer/41078?hl=en">here</a>.
                    </p>
                    <p class="muted">
                        If you are using CPTAC Assay Portal credentials use the Password Reset below.
                    </p>
                    <form id="form-password-recovery" action="{{ path_to_this_module }}/password_recovery/" method="POST">
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
                            <div class="g-recaptcha" data-sitekey="{{ google_recaptcha_site_key }}"></div>
                        </div>
                        <div class="control-group form-group{{ errors ? ' error' : '' }}">
                            <label class="control-label" for="username">
                                <i aria-hidden="true" class="fa fa-fw fa-user"></i>
                                Username:
                            </label>
                            <input class="form-control" id="username" name="username" type="text" autofocus="autofocus" placeholder="Username"
                                title="Username"/>
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