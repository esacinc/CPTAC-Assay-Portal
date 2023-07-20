<div class="skip-link"> <a href="#main-content" class="element-invisible element-focusable"> Skip to main content </a> </div>
<!--start nci_header-->
<div class="">
    <div class="">
        <div class="container">
            <div class="d-flex flex-row">
                <div style="flex:8">
                    <div id="block-block-1">
                        <div class="">
                            <a href="https://proteomics.cancer.gov/"><img alt="NCI OCCPR logo" src="/site/library/images/cabazon_works_images/NIH-OCCPR_Header.png" /></a>
                        </div>
                    </div>
                </div>
                <div style="flex:4">
                    <div id="block-block-43" class="top-header-links" >
                        <div class="content" >
                            <p>
                                <a href="https://proteomics.cancer.gov/contact-us">Contact Us</a> | <a href="https://proteomics.cancer.gov/sign-email-updates">Sign Up for updates</a>
                            </p>
                        </div>
                    </div>
                    <div id="block-search-form" class="" >
                        <div class="content" >
                            <form class="form-search content-search" action="https://proteomics.cancer.gov/assay-portal/available-assays/assay-portal" method="post" id="search-block-form" accept-charset="UTF-8">
                                <div>
                                    <div class="">
                                        <h2 class="element-invisible">Search form</h2>
                                        <div class="field append">
                                            <input title="Enter the terms you wish to search for." class="col-10 container-fluid row wide input headsearch form-text" placeholder="Search..." type="text" id="edit-search-block-form--2" name="search_block_form" value="" size="15" maxlength="128" />
                                            <button type="submit" class="medium button col-sm-4 headsearch-btn">SEARCH</button>
                                        </div>
                                        <button class="secondary button radius postfix col-2 container-fluid row element-invisible form-submit" id="edit-submit" name="op" value="Search" type="submit">Search</button>
                                        <input type="hidden" name="form_build_id" value="form-4NWNgv_rQ_6pZNewqO_IQffceMYpThiMPeR2oCEeKQE" />
                                        <input type="hidden" name="form_id" value="search_block_form" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--end nci_header-->
<!--start nci_slogan-->
<div class="full-width banner minibarslogan">
    <section class="container">
        <div class="d-flex flex-row slogan-banner-block">
            <div id="block-block-2" style="flex:12">
                <div class="content" >
                    <p>Center for Strategic Scientific Initiatives</p>
                </div>
            </div>
        </div>
    </section>
</div>
<!--end nci_slogan-->

<!--.page -->
<div role="document" class="page">
    <div class="">
        <!--.l-header region -->
        <header role="banner" class="l-header">
            <!-- Title, slogan and menu -->
            <section class="container">
                <div class="container-fluid row col-sm-12 col-lg-12">
                    <div class="menu-wrapper">
                        <div class="block block-menu-block main-menu-block block-menu-block-4">
                            <div class="content">
                                <div class="menu-block-wrapper menu-block-4 menu-level-2">
                                    <ul class="menu pull-left">
                                        <li class="first expanded">
                                        <span class="nolink">
                                            <i aria-hidden="true" class="fa fa-fw fa-2x fa-cubes"></i>
                                            <span>Home</span>
                                        </span>
                                            <ul class="menu">
                                                <li class="first leaf">
                                                    <a href="/modules">Home</a>
                                                </li>
                                                {% if is_authenticated %}
                                                <li class="leaf">
                                                    Logged in as: {{ session[session_key].givenname }} {{ session[session_key].sn }}
                                                </li>
                                                {% if preferences_url is not empty %}
                                                <li class="leaf">
                                                    <a href="{{ preferences_url }}">Preferences</a>
                                                </li>
                                                {% endif %}
                                                <li class="last leaf">
                                                    <a href="{{ logout_url }}">Logout</a>
                                                </li>
                                                {% elseif login_url is not empty %}
                                                <li class="last leaf login-link">
                                                    <a onclick="javascript:$.cookie('{{ redirect_cookie_key }}', '{{ request_uri }}', { expires: 7, path: '/' }); window.location.href='{{ login_url }}'" href="javascript:void(0);">Login</a>
                                                </li>
                                                {% endif %}
                                            </ul>
                                        </li>
                                        {% if is_authenticated %}
                                        {% set permissions_assays_preview = not swpg_module_list["assays_preview"]["menu_hidden"] and swpg_module_list["assays_preview"]["pages"][0]["display"] %}
                                        {% set permissions_assays_manage = not swpg_module_list["assays_manage"]["menu_hidden"] and swpg_module_list["assays_manage"]["pages"][0]["display"] %}
                                        {% if permissions_assays_preview or permissions_assays_manage or not swpg_module_list["assays_import"]["menu_hidden"] %}
                                        <li class="expanded">
                                                <span class="nolink">
                                                    <i aria-hidden="true" class="fa fa-fw fa-2x fa-cogs"></i>
                                                    <span>Assays</span>
                                                </span>
                                            <ul class="menu">
                                                {% if permissions_assays_preview %}
                                                <li class="first leaf">
                                                    <a href="/assays_preview/">browse and Preview Assays</a>
                                                </li>
                                                {% endif %}
                                                {% if permissions_assays_manage %}
                                                <li class="leaf">
                                                    <a href="/assays_manage/">Manage Assay Approvals</a>
                                                </li>
                                                {% endif %}
                                                {% if not swpg_module_list["assays_import"]["menu_hidden"] %}
                                                {% if swpg_module_list["assays_import"]["pages"][0]["display"] %}
                                                <li class="leaf">
                                                    <a href="/assays_import/">browse Assay Imports</a>
                                                </li>
                                                {% endif %}
                                                {% if swpg_module_list["assays_import"]["pages"][1]["display"] %}
                                                <li class="last leaf">
                                                    <a href="/assays_import/insert_update/">Enter New Assay Import Metadata</a>
                                                </li>
                                                {% endif %}
                                                {% endif %}
                                            </ul>
                                        </li>
                                        {% endif %}
                                        {% if not swpg_module_list["user_account"]["menu_hidden"] %}
                                        <li class="expanded">
                                                <span class="nolink">
                                                    <i aria-hidden="true" class="fa fa-fw fa-2x fa-user-circle"></i>
                                                    <span>Users</span>
                                                </span>
                                            <ul class="menu">
                                                {% if swpg_module_list["user_account"]["pages"][0]["display"] %}
                                                <li class="first leaf">
                                                    <a href="/user_account/">browse User Accounts</a>
                                                </li>
                                                {% endif %}
                                                {% if swpg_module_list["user_account"]["pages"][1]["display"] %}
                                                <li class="last leaf">
                                                    <a href="/user_account/find/">Find User Account</a>
                                                </li>
                                                {% endif %}
                                            </ul>
                                        </li>
                                        {% endif %}
                                        {% if not swpg_module_list["group"]["menu_hidden"] %}
                                        <li class="expanded">
                                                <span class="nolink">
                                                    <i aria-hidden="true" class="fa fa-fw fa-2x fa-users"></i>
                                                    <span>Groups</span>
                                                </span>
                                            <ul class="menu">
                                                {% if swpg_module_list["group"]["pages"][0]["display"] %}
                                                <li class="first leaf">
                                                    <a href="/group/">browse Groups</a>
                                                </li>
                                                {% endif %}
                                                {% if swpg_module_list["group"]["pages"][1]["display"] %}
                                                <li class="last leaf">
                                                    <a href="/group/manage/">Create Group</a>
                                                </li>
                                                {% endif %}
                                            </ul>
                                        </li>
                                        {% endif %}
                                        {% set permissions_tutorials = not swpg_module_list["tutorials"]["menu_hidden"] and swpg_module_list["tutorials"]["pages"][0]["display"] %}
                                        {% if permissions_tutorials or not swpg_module_list["support"]["menu_hidden"] %}
                                        <li class="expanded">
                                                <span class="nolink">
                                                    <i aria-hidden="true" class="fa fa-fw fa-2x fa-book"></i>
                                                    <span>Documentation</span>
                                                </span>
                                            <ul class="menu">
                                                {% if permissions_tutorials %}
                                                <li class="first leaf">
                                                    <a href="/tutorials/">Tutorials</a>
                                                </li>
                                                {% endif %}
                                                {% if not swpg_module_list["support"]["menu_hidden"] %}
                                                {% if swpg_module_list["support"]["pages"][0]["display"] %}
                                                <li class="leaf">
                                                    <a href="/support/browse/">browse Support</a>
                                                </li>
                                                {% endif %}
                                                {% if swpg_module_list["support"]["pages"][1]["display"] %}
                                                <li class="leaf">
                                                    <a href="/support/settings/">Support Settings/Configuration</a>
                                                </li>
                                                {% endif %}
                                                {% if swpg_module_list["support"]["pages"][2]["display"] %}
                                                <li class="leaf">
                                                    <a href="/support/categories/">browse Support Categories</a>
                                                </li>
                                                {% endif %}
                                                {% if swpg_module_list["support"]["pages"][3]["display"] %}
                                                <li class="last leaf">
                                                    <a href="/support/categories/manage/">Add Support Category</a>
                                                </li>
                                                {% endif %}
                                                {% endif %}
                                            </ul>
                                        </li>
                                        {% endif %}
                                        {% endif %}
                                        <li class="expanded">
                                        <span class="nolink">
                                            <i aria-hidden="true" class="fa fa-fw fa-2x fa-question-circle"></i>
                                            <span>Help</span>
                                        </span>
                                            <ul class="menu">
                                                <li class="leaf">
                                                    <a href="/support/">Contact Us</a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- End title, slogan and menu -->

            <!--.l-header-region -->
            <!--<section class="l-header-region container">-->

            <div id="container">
                <div class="header header-banner">
                    <img alt="Assay Portal Available Assays" src="/site/library/images/cabazon_works_images/assay_portal_header_banner.jpg" />
                </div>
            </div>





            <!--/.l-header-region -->

        </header>

        <!--/.l-header -->
    </div>
