{% extends layout_template_name %}
{% block styles_head %}
{{ parent() }}
<link href="//fonts.googleapis.com/css?family=Roboto:500" rel="stylesheet" type="text/css"/>
<link href="{{ path_to_this_module }}/library/css/login_form.css" rel="stylesheet" type="text/css"/>
{% endblock %}
{% block js_bottom %}
{{ parent() }}
<script src="{{ path_to_this_module }}/library/js/login_form.js" type="text/javascript"></script>
{% endblock %}
{% block content %}
<div id="row-outer-login-form" class="row-outer">
    <div class="row-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-md-6 col-10">
                <section class="widget widget-login animated fadeInUp">
                    <header>
                        <h3>Create Your Account</h3>
                    </header>
                    <div class="widget-body">
                        <p class="widget-login-info">
                            Already have an account? <a href="/authenticate">Log in »</a>
                        </p>
                        <div class="login-form-google">
                            <p class="widget-login-info-google">
                                Use your Google Account to sign up
                            </p>
                            <img src="{{ path_to_this_module }}/library/images/btn_google_signin_dark_normal_web.png">
                        </div>
                        <div class="login-form__or">OR</div>
                        <p class="widget-login-info">
                            Use your Email to sign up
                        </p>
                        <form class="login-form mt-lg" action="{{ path_to_this_module }}/" method="POST">
                            <div class="control-group form-group{{ errors ? ' error' : '' }}">
                                <label class="control-label" for="username">
                                    <i aria-hidden="true" class="fa fa-fw fa-user"></i>
                                    <span>Email:</span>
                                </label>
                                <input class="form-control" id="username" name="username" type="text" placeholder="Username" title="Username"/>
                            </div>
                            <div class="control-group form-group{{ errors ? ' error' : '' }}">
                                <label class="control-label" for="password">
                                    <i aria-hidden="true" class="fa fa-fw fa-key"></i>
                                    <span>Password:</span>
                                </label>
                                <input class="form-control" id="password" name="password" type="password" autocomplete="off" placeholder="Password"
                                       title="Password"/>
                            </div>

                            <div class="clearfix">
                                <button class="btn btn-primary float-md-right" title="Signup" type="submit">
                                    <i aria-hidden="true" class="fa fa-fw fa-sign-in"></i>
                                    <span>Signup</span>
                                </button>
                            </div>

                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
{% endblock %}