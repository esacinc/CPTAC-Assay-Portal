{% extends layout_template_name %}
{% block styles_head %}
    {{ parent() }}
    <link href="{{ path_to_this_module }}/library/css/register_form.css" rel="stylesheet" type="text/css"/>
{% endblock %}
{% block js_bottom %}
    {{ parent() }}
    <script src="https://www.google.com/recaptcha/api.js" type="text/javascript" async defer></script>
    <script src="{{ path_to_this_module }}/library/js/register_form.js" type="text/javascript"></script>
{% endblock %}
{% block content %}
    <div class="row-outer">
        <div class="row-fluid">
            <h2>{{ page_title }}</h2>
            <hr/>
            <div class="columns small-12 large-12">
                <form id="form-register" action="{{ path_to_this_module }}/register/" method="POST">
                    <h4>Note: Fields marked with "*" are required.</h4>
                    {% if (data_validator_result and data_validator_result.hasErrors()) %}
                        <div class="alert alert-block alert-danger">
                            <strong>Errors:</strong>
                            <ul>
                                {% for data_validator_error in data_validator_result.errors %}
                                    <li>{{ data_validator_error.message|e }}</li>
                                {% endfor %}
                            </ul>
                        </div>
                    {% endif %}
                    <div class="control-group form-group{{ (data_validator_result and data_validator_result.hasErrors('g-recaptcha-response')) ? ' error' : ((data and data['g-recaptcha-response']) ? ' success' : '') }}">
                        <div class="g-recaptcha" data-sitekey="{{ google_recaptcha_site_key }}"></div>
                    </div>
                    <div class="control-group form-group{{ (data_validator_result and data_validator_result.hasErrors('email')) ? ' error' : ((data and data['email']) ? ' success' : '') }}">
                        <label class="control-label" for="email">
                            <strong>*</strong>
                            Email Address:
                        </label>
                        <input class="form-control" id="email" name="email" type="text" title="Email Address" value="{{ user_account_info.email }}"/>
                    </div>
                    <div class="control-group form-group{{ (data_validator_result and data_validator_result.hasErrors('password')) ? ' error' : ((data and data['password']) ? ' success' : '') }}">
                        <label class="control-label" for="password">
                            <strong>*</strong>
                            Password:
                        </label>
                        <input class="form-control" id="password" name="password" type="password" title="Password"/>
                    </div>
                    <div class="control-group form-group{{ (data_validator_result and data_validator_result.hasErrors('password_confirm')) ? ' error' : ((data and data['password_confirm']) ? ' success' : '') }}">
                        <label class="control-label" for="password-confirm">
                            <strong>*</strong>
                            Password Confirmation:
                        </label>
                        <input class="form-control" id="password-confirm" name="password_confirm" type="password" title="Password Confirmation"/>
                    </div>
                    <div class="control-group form-group{{ (data_validator_result and data_validator_result.hasErrors('given_name')) ? ' error' : ((data and data['given_name']) ? ' success' : '') }}">
                        <label class="control-label" for="given-name">
                            <strong>*</strong>
                            First Name:
                        </label>
                        <input class="form-control" id="given-name" name="given_name" type="text" title="First Name"
                            value="{{ user_account_info.given_name }}"/>
                    </div>
                    <div class="control-group form-group{{ (data_validator_result and data_validator_result.hasErrors('sn')) ? ' error' : ((data and data['sn']) ? ' success' : '') }}">
                        <label class="control-label" for="sn">
                            <strong>*</strong>
                            Last Name:
                        </label>
                        <input class="form-control" id="sn" name="sn" type="text" title="Last Name" value="{{ user_account_info.sn }}"/>
                    </div>
                    <div class="control-group form-group{{ (data_validator_result and data_validator_result.hasErrors('title_suffix')) ? ' error' : ((data and data['title_suffix']) ? ' success' : '') }}">
                        <label class="control-label" for="title-suffix">Title Suffix:</label>
                        <select class="form-control" id="title-suffix" name="title_suffix" title="Title Suffix">
                            <option selected="selected" title="None" value="">None</option>
                            <option title="Master of Arts" value="M.A.">M.A.</option>
                            <option title="Master of Business Administration" value="M.B.A.">M.B.A.</option>
                            <option title="Doctor of Medicine" value="M.D.">M.D.</option>
                            <option title="Master of Fine Arts" value="M.F.A.">M.F.A.</option>
                            <option title="Master of Science" value="M.Sc.">M.Sc.</option>
                            <option title="Doctor of Philosophy" value="Ph.D.">Ph.D.</option>
                            <option title="Project Management Professional" value="PMP">PMP</option>
                        </select>
                    </div>
                    <div class="control-group form-group{{ (data_validator_result and data_validator_result.hasErrors('organization')) ? ' error' : ((data and data['organization']) ? ' success' : '') }}">
                        <label class="control-label" for="organization">
                            <strong>*</strong>
                            Institution/Organization:
                        </label>
                        <input class="form-control" id="organization" name="organization" type="text" title="Institution/Organization"/>
                    </div>
                    <div class="control-group form-group{{ (data_validator_result and data_validator_result.hasErrors('country_abbr')) ? ' error' : ((data and data['country_abbr']) ? ' success' : '') }}">
                        <label class="control-label" for="country-abbr">
                            <strong>*</strong>
                            Country:
                        </label>
                        <select class="form-control" id="country-abbr" name="country_abbr" title="Country">
                            <option selected="selected" title="United States of America" value="USA">United States of America</option>
                            {% for country in countries %}
                                {% if (country['country_code'] != 'USA') %}
                                    <option title="{{ country['country_name']|e }}" value="{{ country['country_code']|e }}">{{ country['country_name']|e }}</option>
                                {% endif %}
                            {% endfor %}
                        </select>
                    </div>
                    <hr id="aup-divider"/>
                    <h3>Access Agreement Acceptable Use Policy (AUP)</h3>
                    <div id="aup-download">
                        <a class="btn btn-default" href="{{ path_to_this_module }}/library/includes/isp_AUP_v1_20120824.pdf"
                            title="Access Agreement Acceptable Use Policy (AUP) PDF Download">
                            <i aria-hidden="true" class="fa fa-fw fa-file-pdf-o"></i>
                            Download (PDF)
                        </a>
                    </div>
                    <div id="aup">
                        <h4>Privileged-level Access Agreement (PAA)</h4>
                        <p>
                            The following rules of conduct and acceptable use policy apply to all users of the {{ site_name }} website whether NIH employees,
                            contractors, or external users. Because written guidance cannot cover every contingency, you are asked to go beyond the stated
                            rules, using your best judgment and highest ethical standards to guide your actions. These rules are based on Federal laws and
                            regulations and NIH policies. As such there are consequences for non-compliance.
                        </p>
                        <p>
                            User understands that user has requested access permission to provide data to Information Systems Program (ISP). All data provided
                            represents data submitted and/or to be submitted to the scientific community on behalf of Frederick National Laboratory for Cancer
                            Research (FNLCR).
                        </p>
                        <h4>Acknowledgement of Responsibilities</h4>
                        <p>User will protect the account access and authentication to the highest level of data or resource it secures.</p>
                        <p>User will NOT share authentication data entrusted for users use.</p>
                        <p>
                            User is responsible for all actions taken under users account and understand that the exploitation of this account would have
                            catastrophic effects to company reputation, networks and applications for which user has access. User will ONLY use the special
                            access or privileges granted to user to perform authorized tasks or mission related functions. User will only use my privileged
                            account for official administrative actions.
                        </p>
                        <p>
                            User will not attempt to "hack" the network or applications, subvert data protection schemes, gain, access, share, or elevate
                            permissions to data or PII for which user is not authorized.
                        </p>
                        <p>
                            User will immediately report any indication of intrusion, unexplained degradation or interruption of system or network services,
                            illegal or improper possession of content or files, or the actual or possible compromise of data, files, access controls, or
                            systems to <a href="mailto:helpuser@mail.nih.gov">helpuser@mail.nih.gov</a>
                        </p>
                        <p>
                            User will not install unauthorized or malicious code, backdoors, software (e.g. games, entertainment software, instant messaging,
                            collaborative applications, etc) or hardware.
                        </p>
                        <p>
                            User will not create or elevate access rights of others; share permissions to UI for which they are not authorized; nor allow
                            others access to UI or networks under my privileged account.
                        </p>
                        <p>
                            User is prohibited from accessing, storing, processing, displaying, distributing, transmitting and viewing material that is;
                            pornographic, racist, defamatory, vulgar, hate-crime related, subversive in nature, or involves chain letters, spam, or similarly
                            related criminal offenses such as encouragement of criminal activity, or violation of State, Federal, national, or international
                            law.
                        </p>
                        <p>
                            User is prohibited from storing, accessing, processing, sharing, removing, or distributing Classified, Proprietary, Sensitive,
                            Privacy Act, and other protected or privileged information that violates established security and information release policies.
                        </p>
                        <p>
                            User is prohibited from promoting partisan political activity, disseminating religious materials outside an established command
                            religious program, and distributing fund raising information on activities, either for profit or non-profit, unless the activity is
                            specifically approved by the company.
                        </p>
                        <p>
                            User is prohibited from using, or allowing others to use, granted resources for personal use or gain such as posting, editing, or
                            maintaining personal or unofficial information or pages, web-blogs, or blogging sites, advertising or solicitation of services or
                            sale of personal property (e.g. eBay) or stock trading.
                        </p>
                        <p>User understands that all information published, drafted or uploaded is subject to monitoring.</p>
                        <p>User will obtain and maintain required certification(s) in accordance with NIH policy to retain privileged level access.</p>
                        <p>
                            User understand that failure to comply with the above requirements is a violation of the trust extended to me for the privileged
                            access roles and may result in consequences in accordance to corporate policy.
                        </p>
                    </div>
                    <div id="aup-accept">
                        <button class="btn btn-small btn-danger" data-toggle="button" id="btn-aup-accept" type="button">
                            <i aria-hidden="true" class="fa fa-fw fa-times"></i>
                        </button>
                        <input type="hidden" name="acceptable_use_policy" value="" autocomplete="off"/>
                        I have read this Acceptable Use Policy and I agree to comply with the requirements provided in this document.
                    </div>
                    <div class="control-group form-group" id="form-group-register-controls">
                        <button class="btn btn-primary" title="Submit" type="submit">
                            <i aria-hidden="true" class="fa fa-fw fa-check"></i>
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
{% endblock %}